<?php
session_start();
require_once '../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'learner') {
    header("Location: signin.php");
    exit();
}

$pageTitle = "My Tutors";
$studentId = $_SESSION['user_id'];
$conn = getDbConnection();

// Get stats
$stmt = $conn->prepare("SELECT COUNT(DISTINCT tutor_id) as total FROM user_follows WHERE student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$totalTutorsFollowed = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT sc.class_id) as total
    FROM scheduled_classes sc
    INNER JOIN user_follows uf ON sc.tutor_id = uf.tutor_id
    WHERE uf.student_id = ? AND sc.class_date >= CURDATE() AND sc.status = 'scheduled'
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$totalAvailableClasses = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get DISTINCT tutor IDs first, then get tutor details
// This absolutely prevents duplicates
$tutorIds = [];
$stmt = $conn->prepare("SELECT DISTINCT tutor_id FROM user_follows WHERE student_id = ? ORDER BY tutor_id");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tutorIds[] = $row['tutor_id'];
}
$stmt->close();

// Now get tutor details for each unique ID
$followedTutors = [];
foreach ($tutorIds as $tutorId) {
    $stmt = $conn->prepare("
        SELECT user_id, custom_user_id, full_name, email, bio, profile_picture, is_tutor_verified
        FROM users
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $tutorId);
    $stmt->execute();
    $tutor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($tutor) {
        // Get skills
        $stmt = $conn->prepare("
            SELECT s.skill_id, s.skill_name
            FROM user_skills us
            INNER JOIN skills s ON us.skill_id = s.skill_id
            WHERE us.user_id = ?
            ORDER BY s.skill_name
        ");
        $stmt->bind_param("i", $tutorId);
        $stmt->execute();
        $skillResult = $stmt->get_result();
        $tutor['skills'] = [];
        while ($skill = $skillResult->fetch_assoc()) {
            $tutor['skills'][] = $skill;
        }
        $stmt->close();
        
        // Get classes
        $stmt = $conn->prepare("
            SELECT sc.class_id, sc.class_title, sc.class_date, sc.start_time, sc.duration_minutes,
                   sc.max_students, sc.is_free, sc.price, s.skill_name,
                   (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id) as enrolled_count,
                   (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id AND student_id = ?) as is_enrolled
            FROM scheduled_classes sc
            INNER JOIN skills s ON sc.skill_id = s.skill_id
            WHERE sc.tutor_id = ? AND sc.class_date >= CURDATE() AND sc.status = 'scheduled'
            ORDER BY sc.class_date ASC, sc.start_time ASC
            LIMIT 10
        ");
        $stmt->bind_param("ii", $studentId, $tutorId);
        $stmt->execute();
        $classResult = $stmt->get_result();
        $tutor['classes'] = [];
        while ($class = $classResult->fetch_assoc()) {
            $tutor['classes'][] = $class;
        }
        $stmt->close();
        
        $followedTutors[] = $tutor;
    }
}

// Sort by name
usort($followedTutors, function($a, $b) {
    return strcmp($a['full_name'], $b['full_name']);
});

closeDbConnection($conn);
include 'dashboard-header.php';
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #F3F4F6;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    border-color: #4FD1C5;
}

.stat-card.primary {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    border-color: #4FD1C5;
}

.stat-card.primary .stat-value,
.stat-card.primary .stat-label {
    color: white;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 12px;
    color: #4FD1C5;
}

.stat-card.primary .stat-icon {
    color: white;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
}

.stat-label {
    font-size: 0.875rem;
    color: #6B7280;
    margin: 4px 0 0 0;
    font-weight: 600;
}

.tutor-card {
    background: white;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #F3F4F6;
}

.tutor-card.has-classes {
    padding: 24px;
}

.tutor-card.no-classes {
    padding: 16px 20px;
}

.tutor-header {
    display: flex;
    align-items: center;
    gap: 16px;
}

.tutor-header.full {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F3F4F6;
}

.tutor-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.tutor-avatar.large {
    width: 80px;
    height: 80px;
    font-size: 2rem;
}

.tutor-info {
    flex: 1;
}

.tutor-name {
    margin: 0 0 4px 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
}

.tutor-name.large {
    font-size: 1.5rem;
}

.tutor-id {
    margin: 0 0 6px 0;
    font-size: 0.75rem;
    color: #6B7280;
    font-weight: 600;
}

.tutor-bio {
    margin: 0;
    font-size: 0.875rem;
    color: #4B5563;
}

.verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.688rem;
    font-weight: 600;
}

.skills-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}

.skill-tag-small {
    background: #F0FDFA;
    color: #0F766E;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.688rem;
    font-weight: 600;
    border: 1px solid #99F6E4;
}

.skills-section {
    margin-bottom: 20px;
}

.section-title {
    margin: 0 0 12px 0;
    font-size: 0.875rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
}

.skills-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.skill-tag {
    background: #F0FDFA;
    color: #0F766E;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #99F6E4;
}

.classes-section {
    background: #F9FAFB;
    border-radius: 12px;
    padding: 20px;
}

.classes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.class-count {
    background: #4FD1C5;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}

.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.class-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #E5E7EB;
}

.class-title {
    margin: 0 0 8px 0;
    font-size: 0.938rem;
    font-weight: 700;
    color: #111827;
}

.class-skill {
    display: inline-block;
    background: #DBEAFE;
    color: #1E40AF;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.class-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.class-detail {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.813rem;
    color: #4B5563;
}

.price-tag {
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
}

.price-tag.free {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
}

.enrollment-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid #E5E7EB;
}

.enrollment-count {
    font-size: 0.75rem;
    color: #6B7280;
}

.progress-bar {
    background: #E5E7EB;
    height: 4px;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 4px;
}

.progress-fill {
    background: linear-gradient(90deg, #4FD1C5, #38B2AC);
    height: 100%;
}

.enrolled-badge {
    background: #DBEAFE;
    color: #1E40AF;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.unfollow-btn {
    padding: 8px 16px;
    font-size: 0.75rem;
    border-radius: 8px;
}
</style>

<div style="max-width: 1200px; margin: 0 auto;">
    
    <div style="margin-bottom: 32px;">
        <h1 style="margin-bottom: 8px; font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-users"></i> My Tutors
        </h1>
        <p style="color: #6B7280; font-size: 1rem; margin: 0;">
            Manage your followed tutors and discover classes
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
            <p class="stat-value"><?= $totalTutorsFollowed ?></p>
            <p class="stat-label">Tutors Following</p>
        </div>

        <a href="available-classes.php" class="stat-card primary">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <p class="stat-value"><?= $totalAvailableClasses ?></p>
            <p class="stat-label">Available Classes →</p>
        </a>

        <a href="browse.php" class="stat-card">
            <div class="stat-icon"><i class="fas fa-search"></i></div>
            <p class="stat-value"><i class="fas fa-plus"></i></p>
            <p class="stat-label">Find More Tutors →</p>
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
            <p style="margin: 0; color: #065F46; font-weight: 600;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></p>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Debug: Show count -->
    <!-- Total tutors loaded: <?= count($followedTutors) ?> -->

    <?php if (empty($followedTutors)): ?>
        <div style="text-align: center; padding: 80px 20px;">
            <i class="fas fa-user-friends" style="font-size: 4rem; color: #D1D5DB;"></i>
            <h3 style="margin: 20px 0 12px 0; color: #374151;">No tutors yet</h3>
            <p style="margin: 0 0 24px 0; color: #6B7280;">Start following tutors</p>
            <a href="browse.php" class="btn btn-primary" style="padding: 12px 24px; text-decoration: none;">
                <i class="fas fa-search"></i> Browse Tutors
            </a>
        </div>
    <?php else: ?>
        
        <?php foreach ($followedTutors as $tutor): 
            $hasClasses = !empty($tutor['classes']);
        ?>
            <div class="tutor-card <?= $hasClasses ? 'has-classes' : 'no-classes' ?>">
                
                <div class="tutor-header <?= $hasClasses ? 'full' : 'compact' ?>">
                    <div class="tutor-avatar <?= $hasClasses ? 'large' : '' ?>">
                        <?= strtoupper(substr($tutor['full_name'], 0, 1)) ?>
                    </div>
                    
                    <div class="tutor-info">
                        <h2 class="tutor-name <?= $hasClasses ? 'large' : '' ?>">
                            <?= htmlspecialchars($tutor['full_name']) ?>
                            <?php if ($tutor['is_tutor_verified']): ?>
                                <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                            <?php endif; ?>
                        </h2>
                        <p class="tutor-id"><?= htmlspecialchars($tutor['custom_user_id']) ?></p>
                        <?php if ($hasClasses && !empty($tutor['bio'])): ?>
                            <p class="tutor-bio"><?= htmlspecialchars($tutor['bio']) ?></p>
                        <?php endif; ?>
                        
                        <?php if (!$hasClasses && !empty($tutor['skills'])): ?>
                            <div class="skills-compact">
                                <?php foreach (array_slice($tutor['skills'], 0, 4) as $skill): ?>
                                    <span class="skill-tag-small"><?= htmlspecialchars($skill['skill_name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="follow-tutor.php" style="margin: 0;">
                        <input type="hidden" name="tutor_id" value="<?= $tutor['user_id'] ?>">
                        <input type="hidden" name="action" value="unfollow">
                        <button type="submit" class="btn btn-outline unfollow-btn">
                            <i class="fas fa-user-minus"></i> Unfollow
                        </button>
                    </form>
                </div>

                <?php if ($hasClasses): ?>
                    <?php if (!empty($tutor['skills'])): ?>
                        <div class="skills-section">
                            <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Skills (<?= count($tutor['skills']) ?>)</h3>
                            <div class="skills-grid">
                                <?php foreach ($tutor['skills'] as $skill): ?>
                                    <span class="skill-tag"><?= htmlspecialchars($skill['skill_name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="classes-section">
                        <div class="classes-header">
                            <h3 class="section-title" style="margin: 0;"><i class="fas fa-calendar-alt"></i> Available Classes</h3>
                            <span class="class-count"><?= count($tutor['classes']) ?> Classes</span>
                        </div>

                        <div class="classes-grid">
                            <?php foreach ($tutor['classes'] as $class): ?>
                                <div class="class-card">
                                    <h4 class="class-title"><?= htmlspecialchars($class['class_title']) ?></h4>
                                    <span class="class-skill"><?= htmlspecialchars($class['skill_name']) ?></span>
                                    
                                    <div class="class-details">
                                        <div class="class-detail">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= date('M d, Y', strtotime($class['class_date'])) ?></span>
                                        </div>
                                        <div class="class-detail">
                                            <i class="fas fa-clock"></i>
                                            <span><?= date('h:i A', strtotime($class['start_time'])) ?></span>
                                        </div>
                                        <div class="class-detail">
                                            <i class="fas fa-hourglass-half"></i>
                                            <span><?= $class['duration_minutes'] ?> min</span>
                                        </div>
                                        <div class="class-detail">
                                            <?php if ($class['is_free']): ?>
                                                <span class="price-tag free">FREE</span>
                                            <?php else: ?>
                                                <span class="price-tag">₹<?= number_format($class['price'], 2) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="enrollment-status">
                                        <div style="flex: 1;">
                                            <div class="enrollment-count"><?= $class['enrolled_count'] ?>/<?= $class['max_students'] ?> enrolled</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?= ($class['enrolled_count'] / $class['max_students']) * 100 ?>%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-top: 12px;">
                                        <?php if ($class['is_enrolled']): ?>
                                            <span class="enrolled-badge"><i class="fas fa-check"></i> Enrolled</span>
                                        <?php elseif ($class['enrolled_count'] >= $class['max_students']): ?>
                                            <button class="btn btn-outline" disabled style="width: 100%; padding: 8px; font-size: 0.75rem; opacity: 0.5;">
                                                <i class="fas fa-lock"></i> Full
                                            </button>
                                        <?php else: ?>
                                            <a href="enroll-class.php?class_id=<?= $class['class_id'] ?>" class="btn btn-primary" style="width: 100%; padding: 8px; font-size: 0.75rem; text-decoration: none; display: block; text-align: center;">
                                                <i class="fas fa-plus"></i> Enroll
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
        
    <?php endif; ?>

</div>

<?php include 'dashboard-footer.php'; ?>