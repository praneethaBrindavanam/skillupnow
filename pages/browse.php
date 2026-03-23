<?php
session_start();
require_once '../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

if ($_SESSION['user_type'] !== 'learner') {
    header("Location: tutor-dashboard.php");
    exit();
}

$pageTitle = "Browse Tutors";
$studentId = $_SESSION['user_id'];
$conn = getDbConnection();

// Get student's skills for matching
$studentSkills = [];
$stmt = $conn->prepare("SELECT skill_id FROM user_skills WHERE user_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $studentSkills[] = $row['skill_id'];
}
$stmt->close();

// Get ALL tutors from users table
$allTutors = [];
$stmt = $conn->query("
    SELECT 
        user_id,
        custom_user_id,
        full_name,
        email,
        bio
    FROM users
    WHERE user_type = 'tutor'
    AND is_active = 1
    ORDER BY full_name ASC
");

while ($row = $stmt->fetch_assoc()) {
    // Get tutor's skills
    $skillStmt = $conn->prepare("
        SELECT s.skill_id, s.skill_name
        FROM user_skills us
        INNER JOIN skills s ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        ORDER BY s.skill_name
    ");
    $skillStmt->bind_param("i", $row['user_id']);
    $skillStmt->execute();
    $skillResult = $skillStmt->get_result();
    $skills = [];
    $skillIds = [];
    while ($skillRow = $skillResult->fetch_assoc()) {
        $skills[] = $skillRow['skill_name'];
        $skillIds[] = $skillRow['skill_id'];
    }
    $row['skills'] = $skills;
    $row['skill_ids'] = $skillIds;
    $skillStmt->close();
    
    // Check if student is following this tutor
    $followStmt = $conn->prepare("SELECT follow_id FROM user_follows WHERE student_id = ? AND tutor_id = ?");
    $followStmt->bind_param("ii", $studentId, $row['user_id']);
    $followStmt->execute();
    $row['is_following'] = $followStmt->get_result()->num_rows > 0;
    $followStmt->close();
    
    // Get follower count
    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM user_follows WHERE tutor_id = ?");
    $countStmt->bind_param("i", $row['user_id']);
    $countStmt->execute();
    $row['follower_count'] = $countStmt->get_result()->fetch_assoc()['count'];
    $countStmt->close();
    
    // Get upcoming classes count
    $classStmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM scheduled_classes 
        WHERE tutor_id = ? AND status = 'scheduled' AND class_date >= CURDATE()
    ");
    $classStmt->bind_param("i", $row['user_id']);
    $classStmt->execute();
    $row['class_count'] = $classStmt->get_result()->fetch_assoc()['count'];
    $classStmt->close();
    
    // Check if tutor has matching skills
    $row['has_match'] = false;
    if (!empty($studentSkills) && !empty($skillIds)) {
        foreach ($skillIds as $skillId) {
            if (in_array($skillId, $studentSkills)) {
                $row['has_match'] = true;
                break;
            }
        }
    }
    
    $allTutors[] = $row;
}
$stmt->close();

// Separate matched and other tutors
$matchedTutors = [];
$otherTutors = [];
foreach ($allTutors as $tutor) {
    if ($tutor['has_match']) {
        $matchedTutors[] = $tutor;
    } else {
        $otherTutors[] = $tutor;
    }
}

closeDbConnection($conn);
include 'dashboard-header.php';
?>

<style>
.tutor-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid #E5E7EB;
}

.tutor-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transform: translateY(-4px);
}

.tutor-card.matched {
    border-color: #10B981;
}

.tutor-header {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    padding: 32px 24px;
    text-align: center;
}

.tutor-header.matched {
    background: linear-gradient(135deg, #10B981, #059669);
}

.tutor-avatar {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 50%;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 800;
    color: #4FD1C5;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.tutor-header.matched .tutor-avatar {
    color: #10B981;
}

.tutor-body {
    padding: 24px;
}

.skill-tag {
    display: inline-block;
    background: #F3F4F6;
    color: #4B5563;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 0.813rem;
    font-weight: 600;
    margin: 0 6px 6px 0;
}

.skill-tag.matched {
    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
    color: #065F46;
}

.tutor-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding: 16px;
    background: #F9FAFB;
    border-radius: 12px;
    margin: 16px 0;
}

.stat-item {
    text-align: center;
}

.stat-value {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 800;
    color: #4FD1C5;
}

.stat-label {
    margin: 4px 0 0 0;
    font-size: 0.75rem;
    color: #6B7280;
    font-weight: 600;
}

.btn-follow {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.938rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-follow.primary {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    color: white;
    box-shadow: 0 4px 12px rgba(79, 209, 197, 0.3);
}

.btn-follow.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 209, 197, 0.4);
}

.btn-follow.following {
    background: #F3F4F6;
    color: #4B5563;
    border: 2px solid #E5E7EB;
}
</style>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background: linear-gradient(135deg, #D1FAE5, #A7F3D0); border: 2px solid #10B981; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <p style="margin: 0; color: #065F46; font-weight: 600;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </p>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div style="max-width: 1400px; margin: 0 auto;">
    
    <div style="margin-bottom: 32px;">
        <h1 style="margin-bottom: 8px; font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-users"></i> Discover Tutors
        </h1>
        <p style="color: #6B7280; font-size: 1.125rem;">
            Find expert tutors to help you master new skills
        </p>
    </div>

    <!-- Debug Info -->
    <div style="background: #DBEAFE; padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 2px solid #3B82F6;">
        <strong>Debug:</strong> 
        Total Tutors: <?= count($allTutors) ?> | 
        Matched: <?= count($matchedTutors) ?> | 
        Others: <?= count($otherTutors) ?> | 
        Student Skills: <?= count($studentSkills) ?>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
        <div style="background: linear-gradient(135deg, #D1FAE5, #A7F3D0); border: 2px solid #10B981; border-radius: 16px; padding: 20px;">
            <div style="text-align: center;">
                <p style="margin: 0; font-size: 2rem; font-weight: 800; color: #15803D;"><?= count($matchedTutors) ?></p>
                <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #16A34A; font-weight: 600;">Matched Tutors</p>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #CCFBF1, #99F6E4); border: 2px solid #4FD1C5; border-radius: 16px; padding: 20px;">
            <div style="text-align: center;">
                <p style="margin: 0; font-size: 2rem; font-weight: 800; color: #0F766E;"><?= count($allTutors) ?></p>
                <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #14B8A6; font-weight: 600;">All Tutors</p>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #FCE7F3, #FBCFE8); border: 2px solid #EC4899; border-radius: 16px; padding: 20px;">
            <div style="text-align: center;">
                <a href="classes.php" style="text-decoration: none;">
                    <p style="margin: 0; font-size: 2rem; font-weight: 800; color: #9F1239;">→</p>
                    <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #BE185D; font-weight: 600;">View Classes</p>
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($allTutors)): ?>
        <div style="background: white; padding: 60px; border-radius: 20px; text-align: center;">
            <i class="fas fa-users" style="font-size: 4rem; color: #D1D5DB; margin-bottom: 24px;"></i>
            <h2 style="color: #111827; margin-bottom: 16px;">No Tutors Available Yet</h2>
            <p style="color: #6B7280;">Check back soon! Tutors are joining the platform regularly.</p>
        </div>
    <?php else: ?>
        
        <!-- MATCHED TUTORS -->
        <?php if (!empty($matchedTutors)): ?>
            <div style="margin-bottom: 48px;">
                <h2 style="margin-bottom: 24px; font-size: 1.5rem; font-weight: 700; color: #111827;">
                    <i class="fas fa-star" style="color: #10B981;"></i> Perfect Match For You (<?= count($matchedTutors) ?>)
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                    <?php foreach ($matchedTutors as $tutor): ?>
                        <div class="tutor-card matched">
                            <div class="tutor-header matched">
                                <div class="tutor-avatar">
                                    <?= strtoupper(substr($tutor['full_name'], 0, 1)) ?>
                                </div>
                                <h3 style="color: white; margin: 0 0 6px 0; font-size: 1.375rem; font-weight: 700;">
                                    <?= htmlspecialchars($tutor['full_name']) ?>
                                </h3>
                                <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 0.938rem;">
                                    <?= htmlspecialchars($tutor['custom_user_id']) ?>
                                </p>
                            </div>
                            
                            <div class="tutor-body">
                                <?php if (!empty($tutor['skills'])): ?>
                                    <div style="margin-bottom: 16px;">
                                        <p style="font-size: 0.75rem; color: #6B7280; margin: 0 0 8px 0; font-weight: 600; text-transform: uppercase;">
                                            Teaching
                                        </p>
                                        <?php foreach ($tutor['skills'] as $index => $skill): ?>
                                            <?php 
                                            $isMatched = !empty($studentSkills) && in_array($tutor['skill_ids'][$index], $studentSkills);
                                            ?>
                                            <span class="skill-tag <?= $isMatched ? 'matched' : '' ?>">
                                                <?php if ($isMatched): ?>
                                                    <i class="fas fa-star" style="font-size: 0.625rem;"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($skill) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="tutor-stats">
                                    <div class="stat-item">
                                        <p class="stat-value"><?= $tutor['follower_count'] ?></p>
                                        <p class="stat-label">Followers</p>
                                    </div>
                                    <div class="stat-item">
                                        <p class="stat-value" style="color: #EC4899;"><?= $tutor['class_count'] ?></p>
                                        <p class="stat-label">Classes</p>
                                    </div>
                                </div>

                                <?php if ($tutor['is_following']): ?>
                                    <form method="POST" action="follow-tutor.php" style="margin: 0;">
                                        <input type="hidden" name="tutor_id" value="<?= $tutor['user_id'] ?>">
                                        <input type="hidden" name="action" value="unfollow">
                                        <button type="submit" class="btn-follow following">
                                            <i class="fas fa-check-circle"></i> Following
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="follow-tutor.php" style="margin: 0;">
                                        <input type="hidden" name="tutor_id" value="<?= $tutor['user_id'] ?>">
                                        <input type="hidden" name="action" value="follow">
                                        <button type="submit" class="btn-follow primary">
                                            <i class="fas fa-user-plus"></i> Follow Tutor
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ALL OTHER TUTORS -->
        <?php if (!empty($otherTutors)): ?>
            <div>
                <h2 style="margin-bottom: 24px; font-size: 1.5rem; font-weight: 700; color: #111827;">
                    <i class="fas fa-graduation-cap" style="color: #4FD1C5;"></i> All Tutors (<?= count($otherTutors) ?>)
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                    <?php foreach ($otherTutors as $tutor): ?>
                        <div class="tutor-card">
                            <div class="tutor-header">
                                <div class="tutor-avatar">
                                    <?= strtoupper(substr($tutor['full_name'], 0, 1)) ?>
                                </div>
                                <h3 style="color: white; margin: 0 0 6px 0; font-size: 1.375rem; font-weight: 700;">
                                    <?= htmlspecialchars($tutor['full_name']) ?>
                                </h3>
                                <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 0.938rem;">
                                    <?= htmlspecialchars($tutor['custom_user_id']) ?>
                                </p>
                            </div>
                            
                            <div class="tutor-body">
                                <?php if (!empty($tutor['skills'])): ?>
                                    <div style="margin-bottom: 16px;">
                                        <p style="font-size: 0.75rem; color: #6B7280; margin: 0 0 8px 0; font-weight: 600; text-transform: uppercase;">
                                            Teaching
                                        </p>
                                        <?php foreach (array_slice($tutor['skills'], 0, 5) as $skill): ?>
                                            <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($tutor['skills']) > 5): ?>
                                            <span class="skill-tag" style="background: #E5E7EB; color: #6B7280;">
                                                +<?= count($tutor['skills']) - 5 ?> more
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="tutor-stats">
                                    <div class="stat-item">
                                        <p class="stat-value"><?= $tutor['follower_count'] ?></p>
                                        <p class="stat-label">Followers</p>
                                    </div>
                                    <div class="stat-item">
                                        <p class="stat-value" style="color: #EC4899;"><?= $tutor['class_count'] ?></p>
                                        <p class="stat-label">Classes</p>
                                    </div>
                                </div>

                                <?php if ($tutor['is_following']): ?>
                                    <form method="POST" action="follow-tutor.php" style="margin: 0;">
                                        <input type="hidden" name="tutor_id" value="<?= $tutor['user_id'] ?>">
                                        <input type="hidden" name="action" value="unfollow">
                                        <button type="submit" class="btn-follow following">
                                            <i class="fas fa-check-circle"></i> Following
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="follow-tutor.php" style="margin: 0;">
                                        <input type="hidden" name="tutor_id" value="<?= $tutor['user_id'] ?>">
                                        <input type="hidden" name="action" value="follow">
                                        <button type="submit" class="btn-follow primary">
                                            <i class="fas fa-user-plus"></i> Follow Tutor
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include 'dashboard-footer.php'; ?>