<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

if ($_SESSION['user_type'] !== 'tutor') {
    header("Location: student-dashboard.php");
    exit();
}

$pageTitle = "My Students";

$conn = getDbConnection();
$tutorId = $_SESSION['user_id'];

// Get students who follow this tutor
$students = [];
$query = "
    SELECT 
        u.user_id,
        u.custom_user_id,
        u.full_name,
        u.email,
        u.college_name,
        u.bio,
        u.phone,
        uf.created_at as following_since,
        (SELECT COUNT(*) FROM class_enrollments ce 
         JOIN scheduled_classes sc ON ce.class_id = sc.class_id 
         WHERE ce.student_id = u.user_id AND sc.tutor_id = ?) as enrolled_classes
    FROM user_follows uf
    INNER JOIN users u ON uf.student_id = u.user_id
    WHERE uf.tutor_id = ?
        AND u.user_type = 'learner'
        AND u.is_active = 1
    ORDER BY uf.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $tutorId, $tutorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Get student's skills
    $stmt2 = $conn->prepare("
        SELECT s.skill_name, s.skill_category 
        FROM user_skills us
        JOIN skills s ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        LIMIT 5
    ");
    $stmt2->bind_param("i", $row['user_id']);
    $stmt2->execute();
    $skillsResult = $stmt2->get_result();
    $row['skills'] = [];
    while ($skill = $skillsResult->fetch_assoc()) {
        $row['skills'][] = $skill;
    }
    $stmt2->close();
    
    $students[] = $row;
}
$stmt->close();

closeDbConnection($conn);

include 'dashboard-header.php';
?>

<div style="max-width: 1400px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem;">
            <i class="fas fa-users"></i> My Students
        </h1>
        <p style="color: var(--gray-600);">
            Students who are following you
        </p>
    </div>

    <?php if (empty($students)): ?>
        <!-- No Students Following -->
        <div style="background: white; padding: 4rem 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center;">
            <div style="width: 120px; height: 120px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto;">
                <i class="fas fa-user-slash" style="font-size: 3rem; color: var(--gray-400);"></i>
            </div>
            <h2 style="color: var(--gray-700); margin-bottom: 1rem;">No Students Yet</h2>
            <p style="color: var(--gray-500); max-width: 500px; margin: 0 auto 2rem auto;">
                Complete your profile and add your teaching skills so students can discover and follow you!
            </p>
            <a href="profile.php" class="btn btn-primary">
                <i class="fas fa-user-edit"></i> Update Profile
            </a>
        </div>
    <?php else: ?>
        <!-- Stats -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Total Students Following</p>
                    <h2 style="margin: 0; font-size: 2rem; color: #F687B3;"><?= count($students) ?></h2>
                </div>
            </div>
        </div>

        <!-- Students Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
            <?php foreach ($students as $student): ?>
                <div style="background: white; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                    
                    <!-- Student Header -->
                    <div style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; color: var(--primary-teal); flex-shrink: 0;">
                                <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="color: white; margin: 0 0 0.25rem 0; font-size: 1.125rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($student['full_name']) ?>
                                </h3>
                                <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 0.875rem;">
                                    <?= htmlspecialchars($student['custom_user_id']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 1.5rem;">
                        
                        <!-- Student Info -->
                        <div style="margin-bottom: 1rem;">
                            <p style="color: var(--gray-600); margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                                <i class="fas fa-university"></i> <?= htmlspecialchars($student['college_name']) ?>
                            </p>
                            <p style="color: var(--gray-600); margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($student['email']) ?>
                            </p>
                            <?php if (!empty($student['phone'])): ?>
                                <p style="color: var(--gray-600); margin: 0; font-size: 0.875rem;">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($student['phone']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Bio -->
                        <?php if (!empty($student['bio'])): ?>
                            <div style="margin-bottom: 1rem;">
                                <p style="color: var(--gray-600); font-size: 0.875rem; line-height: 1.5; margin: 0;">
                                    <?= htmlspecialchars(substr($student['bio'], 0, 100)) ?>
                                    <?= strlen($student['bio']) > 100 ? '...' : '' ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Student's Learning Skills -->
                        <?php if (!empty($student['skills'])): ?>
                            <div style="margin-bottom: 1rem;">
                                <p style="font-size: 0.75rem; color: var(--gray-500); margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">
                                    Learning Goals
                                </p>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <?php foreach (array_slice($student['skills'], 0, 3) as $skill): ?>
                                        <span style="background: linear-gradient(135deg, rgba(79, 209, 197, 0.1), rgba(56, 178, 172, 0.1)); border: 1px solid var(--primary-cyan); padding: 0.25rem 0.5rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; color: var(--gray-700);">
                                            <?= htmlspecialchars($skill['skill_name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($student['skills']) > 3): ?>
                                        <span style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: var(--radius-full); font-size: 0.75rem; color: var(--gray-600);">
                                            +<?= count($student['skills']) - 3 ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Stats -->
                        <div style="border-top: 1px solid var(--gray-200); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 0.75rem; color: var(--gray-500);">
                                <i class="fas fa-clock"></i> Following since <?= date('M d, Y', strtotime($student['following_since'])) ?>
                            </div>
                            <div style="background: #F59E0B; color: white; padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 600;">
                                <?= $student['enrolled_classes'] ?> classes
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include 'dashboard-footer.php'; ?>