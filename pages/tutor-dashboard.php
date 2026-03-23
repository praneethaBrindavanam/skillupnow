<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Check if user is a tutor
if ($_SESSION['user_type'] !== 'tutor') {
    header("Location: student-dashboard.php");
    exit();
}

$pageTitle = "Tutor Dashboard";

// Database connection
$conn = getDbConnection();
$userId = $_SESSION['user_id'];

// Check if profile exists and is completed
$stmt = $conn->prepare("SELECT profile_completed, is_tutor_verified FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profileCompleted = ($user['profile_completed'] == 1);
$isTutorVerified = ($user['is_tutor_verified'] == 1);

// Get tutor's teaching skills count
$skillsCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_skills WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $skillsCount = $row['count'];
}
$stmt->close();

// Get verification status
$verifiedSkillsCount = 0;
$pendingSkillsCount = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) as verified_count
    FROM skill_verifications
    WHERE user_id = ? AND is_verified = 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $verifiedSkillsCount = $row['verified_count'];
}
$stmt->close();

$pendingSkillsCount = $skillsCount - $verifiedSkillsCount;

// Get tutor's teaching skills
$teachingSkills = [];
$stmt = $conn->prepare("
    SELECT s.skill_name, s.skill_category, sv.is_verified
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    LEFT JOIN skill_verifications sv ON us.user_id = sv.user_id AND us.skill_id = sv.skill_id
    WHERE us.user_id = ?
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $teachingSkills[] = $row;
}
$stmt->close();

// Get student count (followers)
$studentCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_follows WHERE tutor_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $studentCount = $row['count'];
}
$stmt->close();

closeDbConnection($conn);

include 'dashboard-header.php';
?>

<!-- Success Messages -->
<?php if (isset($_SESSION['signup_success'])): ?>
    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 50px; height: 50px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <h3 style="color: #065F46; margin-bottom: 0.5rem;">🎉 Tutor Account Created Successfully!</h3>
                <p style="margin: 0; color: #047857;">Welcome to SkillUp Now! Your teaching journey begins now.</p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['signup_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['signin_success'])): ?>
    <div style="background: #DBEAFE; border: 2px solid #3B82F6; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 50px; height: 50px; background: #3B82F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <div>
                <h3 style="color: #1E40AF; margin-bottom: 0.5rem;">Welcome Back!</h3>
                <p style="margin: 0; color: #1E3A8A;">You have successfully signed in to your account.</p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['signin_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['profile_success'])): ?>
    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 50px; height: 50px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <h3 style="color: #065F46; margin-bottom: 0.5rem;">✅ Profile Updated Successfully!</h3>
                <p style="margin: 0; color: #047857;"><?= htmlspecialchars($_SESSION['profile_success']) ?></p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['profile_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 50px; height: 50px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <h3 style="color: #065F46; margin-bottom: 0.5rem;">Success!</h3>
                <p style="margin: 0; color: #047857;"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!$profileCompleted || $skillsCount < 3): ?>
    <!-- Profile Incomplete Warning -->
    <div style="background: #FEF3C7; border: 3px solid #F59E0B; border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: start; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <div style="flex: 1;">
                <h2 style="color: #92400E; margin: 0 0 1rem 0;">Complete Your Profile to Start Teaching!</h2>
                <p style="color: #78350F; margin: 0 0 1.5rem 0; line-height: 1.6;">
                    <?php if (!$profileCompleted): ?>
                        📝 Your tutor profile is incomplete. Add your expertise and teaching skills to get discovered by students.
                    <?php elseif ($skillsCount < 3): ?>
                        🎯 You need at least 3 skills selected. Tell us what you can teach so students can find you!
                    <?php endif; ?>
                </p>
                <a href="profile.php" class="btn btn-primary" style="background: #F59E0B; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-user-edit"></i>
                    Complete Profile Now
                </a>
            </div>
        </div>
    </div>
<?php elseif (!$isTutorVerified || $verifiedSkillsCount == 0): ?>
    <!-- Verification Required Warning -->
    <div style="background: linear-gradient(135deg, #FEE2E2, #FECACA); border: 3px solid #EF4444; border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: start; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; background: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-shield-alt" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <div style="flex: 1;">
                <h2 style="color: #991B1B; margin: 0 0 1rem 0;">
                    🎓 Complete Skill Certification to Start Teaching!
                </h2>
                <p style="color: #7F1D1D; margin: 0 0 1rem 0; line-height: 1.6;">
                    You have selected <strong><?= $skillsCount ?> skills</strong>, but <strong>none are certified yet</strong>. 
                    You must pass Moodle certification exams for at least one skill before you can schedule classes.
                </p>
                <div style="background: white; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--gray-600); font-size: 0.875rem;">Certification Progress</span>
                        <span style="color: var(--gray-600); font-size: 0.875rem; font-weight: 600;"><?= $verifiedSkillsCount ?> / <?= $skillsCount ?></span>
                    </div>
                    <div style="background: var(--gray-200); height: 8px; border-radius: 999px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, #EF4444, #DC2626); height: 100%; width: <?= $skillsCount > 0 ? ($verifiedSkillsCount / $skillsCount * 100) : 0 ?>%; transition: width 0.3s;"></div>
                    </div>
                </div>
                <a href="tutor-verification.php" class="btn btn-primary" style="background: #EF4444; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-clipboard-check"></i>
                    Start Skill Certification
                </a>
            </div>
        </div>
    </div>
<?php elseif ($pendingSkillsCount > 0): ?>
    <!-- Partial Verification Notice -->
    <div style="background: linear-gradient(135deg, #DBEAFE, #BFDBFE); border: 2px solid #3B82F6; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem; justify-content: space-between; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <h3 style="color: #1E40AF; margin: 0 0 0.5rem 0;">
                    <i class="fas fa-info-circle"></i> More Skills to Certify
                </h3>
                <p style="margin: 0; color: #1E3A8A;">
                    You have <strong><?= $verifiedSkillsCount ?> certified skills</strong> and <strong><?= $pendingSkillsCount ?> pending</strong>. 
                    Certify more skills to teach more subjects!
                </p>
            </div>
            <a href="tutor-verification.php" class="btn" style="background: #3B82F6; color: white;">
                <i class="fas fa-clipboard-check"></i> Continue Certification
            </a>
        </div>
    </div>
<?php endif; ?>

<div style="max-width: 1400px; margin: 0 auto;">
    <!-- Welcome Section -->
    <div style="background: linear-gradient(135deg, #F687B3, #ED64A6); color: white; padding: 2.5rem; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="color: white; margin-bottom: 0.5rem;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>! 👨‍🏫</h1>
        <p style="margin: 0; opacity: 0.9; font-size: 1.125rem;">
            <?php if ($verifiedSkillsCount > 0): ?>
                Ready to inspire students today?
            <?php else: ?>
                Complete skill certification to start teaching!
            <?php endif; ?>
        </p>
    </div>
    
    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Teaching Skills</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F687B3;"><?= $skillsCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Certified Skills</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: <?= $verifiedSkillsCount > 0 ? '#10B981' : '#EF4444' ?>;"><?= $verifiedSkillsCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: <?= $verifiedSkillsCount > 0 ? '#10B981' : '#EF4444' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-certificate"></i>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Total Students</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: var(--primary-teal);"><?= $studentCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Sessions</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F59E0B;">0</h2>
                </div>
                <div style="width: 50px; height: 50px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="tutor-verification.php" class="btn btn-primary" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-shield-alt"></i> Skill Certification
            </a>
            <a href="profile.php" class="btn btn-outline" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-user-edit"></i> Update Profile
            </a>
            <a href="my-students.php" class="btn btn-outline" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-users"></i> My Students
            </a>
            <?php if ($verifiedSkillsCount > 0): ?>
                <a href="schedule.php" class="btn btn-outline" style="justify-content: center; text-decoration: none;">
                    <i class="fas fa-calendar-alt"></i> Schedule Class
                </a>
            <?php else: ?>
                <button class="btn btn-outline" style="justify-content: center; opacity: 0.5; cursor: not-allowed;" disabled title="Complete certification first">
                    <i class="fas fa-calendar-alt"></i> Schedule Class
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Teaching Skills -->
    <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">
                <?= ($profileCompleted && $skillsCount >= 3) ? 'Your Teaching Expertise' : 'Getting Started' ?>
            </h2>
            <?php if ($profileCompleted && $skillsCount >= 3): ?>
                <a href="tutor-verification.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">
                    View All Skills →
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($profileCompleted && $skillsCount >= 3): ?>
            <!-- Show teaching skills -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem;">
                <?php foreach ($teachingSkills as $skill): ?>
                    <div style="background: <?= $skill['is_verified'] ? 'linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1))' : 'linear-gradient(135deg, rgba(246, 135, 179, 0.1), rgba(237, 100, 166, 0.1))' ?>; border: 2px solid <?= $skill['is_verified'] ? '#10B981' : '#F687B3' ?>; padding: 0.75rem 1.25rem; border-radius: var(--radius-full); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas <?= $skill['is_verified'] ? 'fa-certificate' : 'fa-clock' ?>" style="color: <?= $skill['is_verified'] ? '#10B981' : '#ED64A6' ?>;"></i>
                        <span style="font-weight: 600; color: var(--gray-800);"><?= htmlspecialchars($skill['skill_name']) ?></span>
                        <span style="font-size: 0.75rem; color: var(--gray-500); background: white; padding: 0.25rem 0.5rem; border-radius: var(--radius-sm);">
                            <?= $skill['is_verified'] ? '✅ Certified' : '⏳ Pending' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <?php if ($skillsCount > 5): ?>
                    <div style="background: var(--gray-100); padding: 0.75rem 1.25rem; border-radius: var(--radius-full); font-weight: 600; color: var(--gray-600);">
                        +<?= $skillsCount - 5 ?> more
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($verifiedSkillsCount > 0): ?>
                <p style="margin: 0; color: var(--gray-600); text-align: center; padding: 2rem;">
                    🎓 Students can find and book sessions with you!
                </p>
            <?php else: ?>
                <p style="margin: 0; color: #DC2626; text-align: center; padding: 2rem; background: #FEE2E2; border-radius: var(--radius-md);">
                    ⚠️ Complete certification exams to start teaching
                </p>
            <?php endif; ?>
        <?php else: ?>
            <!-- Getting Started Steps -->
            <div style="display: grid; gap: 1rem;">
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        1️⃣
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Complete Your Profile</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Add your information and select at least 3 skills you can teach
                        </p>
                    </div>
                </div>
                
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #EF4444; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        2️⃣
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Get Certified on Moodle</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Pass certification exams for your skills (75% minimum score required)
                        </p>
                    </div>
                </div>
                
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #10B981; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        3️⃣
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Start Teaching!</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Once certified, schedule classes and start earning
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'dashboard-footer.php'; ?>