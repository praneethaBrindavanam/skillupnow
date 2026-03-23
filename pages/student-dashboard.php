<?php
session_start();
require_once '../includes/config.php';

// Prevent back button after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Check if user is a student
if ($_SESSION['user_type'] !== 'learner') {
    header("Location: tutor-dashboard.php");
    exit();
}

$pageTitle = "Student Dashboard";

// Database connection
$conn = getDbConnection();
$userId = $_SESSION['user_id'];

// Check if profile exists and is completed
$stmt = $conn->prepare("SELECT profile_completed FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profileCompleted = ($user['profile_completed'] == 1);

// Get student's learning goals count
$skillsCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_skills WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $skillsCount = $row['count'];
}
$stmt->close();

// Get following count
$followingCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_follows WHERE student_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $followingCount = $row['count'];
}
$stmt->close();

// Get enrolled classes count
$enrolledCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM class_enrollments WHERE student_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $enrolledCount = $row['count'];
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
                <h3 style="color: #065F46; margin-bottom: 0.5rem;">🎉 Account Created Successfully!</h3>
                <p style="margin: 0; color: #047857;">Welcome to SkillUp Now! Start your learning journey now.</p>
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

<?php if (!$profileCompleted || $skillsCount < 3): ?>
    <!-- Profile Incomplete Warning -->
    <div style="background: #FEF3C7; border: 3px solid #F59E0B; border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: start; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <div style="flex: 1;">
                <h2 style="color: #92400E; margin: 0 0 1rem 0;">Complete Your Profile to Get Started!</h2>
                <p style="color: #78350F; margin: 0 0 1.5rem 0; line-height: 1.6;">
                    <?php if (!$profileCompleted): ?>
                        📝 Your profile is incomplete. Add your learning goals so we can match you with the best tutors.
                    <?php elseif ($skillsCount < 3): ?>
                        🎯 You need at least 3 skills selected. Tell us what you want to learn!
                    <?php endif; ?>
                </p>
                <a href="profile.php" class="btn btn-primary" style="background: #F59E0B; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-user-edit"></i>
                    Complete Profile Now
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div style="max-width: 1400px; margin: 0 auto;">
    <!-- Welcome Section -->
    <div style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); color: white; padding: 2.5rem; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="color: white; margin-bottom: 0.5rem;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>! 🎓</h1>
        <p style="margin: 0; opacity: 0.9; font-size: 1.125rem;">
            Ready to learn something new today?
        </p>
    </div>
    
    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Learning Goals</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: var(--primary-teal);"><?= $skillsCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-bullseye"></i>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Following Tutors</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F687B3;"><?= $followingCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Enrolled Classes</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F59E0B;"><?= $enrolledCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="browse.php" class="btn btn-primary" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-search"></i> Find Tutors
            </a>
            <a href="my-tutors.php" class="btn btn-outline" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-chalkboard-teacher"></i> My Tutors
            </a>
            <a href="my-classes.php" class="btn btn-outline" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-book"></i> My Classes
            </a>
            <a href="profile.php" class="btn btn-outline" style="justify-content: center; text-decoration: none;">
                <i class="fas fa-user-edit"></i> Update Profile
            </a>
        </div>
    </div>
    
    <!-- Getting Started / Next Steps -->
    <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">
            <?= ($profileCompleted && $skillsCount >= 3) ? 'Your Learning Journey' : 'Getting Started' ?>
        </h2>
        
        <?php if ($profileCompleted && $skillsCount >= 3): ?>
            <p style="color: var(--gray-600); margin-bottom: 2rem;">
                Great! You've set up your profile. Here's what you can do next:
            </p>
            
            <div style="display: grid; gap: 1rem;">
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        🔍
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 0.5rem 0;">Find Expert Tutors</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Browse verified tutors who teach the skills you want to learn
                        </p>
                    </div>
                    <a href="browse.php" class="btn btn-primary">Browse →</a>
                </div>
                
                <?php if ($followingCount > 0): ?>
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #F687B3; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        👥
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 0.5rem 0;">Check Your Tutors</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            See upcoming classes from tutors you're following
                        </p>
                    </div>
                    <a href="my-tutors.php" class="btn btn-outline">View →</a>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        1️⃣
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Complete Your Profile</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Add your information and select at least 3 skills you want to learn
                        </p>
                    </div>
                </div>
                
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #F687B3; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        2️⃣
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Find Great Tutors</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Browse and follow verified tutors who can help you achieve your goals
                        </p>
                    </div>
                </div>
                
                <div style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem; display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #10B981; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        3️⃣
                    </div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Start Learning!</h3>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            Enroll in classes and begin your learning journey
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'dashboard-footer.php'; ?>