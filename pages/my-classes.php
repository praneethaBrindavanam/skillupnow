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

$pageTitle = "My Classes";
$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];
$conn = getDbConnection();

// Handle cancel class action
if (isset($_POST['cancel_class'])) {
    $classId = intval($_POST['class_id']);
    
    $stmt = $conn->prepare("SELECT class_id FROM scheduled_classes WHERE class_id = ? AND tutor_id = ?");
    $stmt->bind_param("ii", $classId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $classStmt = $conn->prepare("
            SELECT sc.*, s.skill_name
            FROM scheduled_classes sc
            INNER JOIN skills s ON sc.skill_id = s.skill_id
            WHERE sc.class_id = ?
        ");
        $classStmt->bind_param("i", $classId);
        $classStmt->execute();
        $classData = $classStmt->get_result()->fetch_assoc();
        $classStmt->close();
        
        $studentsStmt = $conn->prepare("
            SELECT u.email, u.full_name
            FROM class_enrollments ce
            INNER JOIN users u ON ce.student_id = u.user_id
            WHERE ce.class_id = ?
        ");
        $studentsStmt->bind_param("i", $classId);
        $studentsStmt->execute();
        $studentsResult = $studentsStmt->get_result();
        $students = [];
        while ($student = $studentsResult->fetch_assoc()) {
            $students[] = $student;
        }
        $studentsStmt->close();
        
        $updateStmt = $conn->prepare("UPDATE scheduled_classes SET status = 'cancelled' WHERE class_id = ?");
        $updateStmt->bind_param("i", $classId);
        
        if ($updateStmt->execute()) {
            foreach ($students as $student) {
                $to = $student['email'];
                $subject = "Class Cancelled: " . $classData['class_title'];
                $message = "
                    <html>
                    <body>
                        <h2>Class Cancelled</h2>
                        <p>Dear {$student['full_name']},</p>
                        <p>We regret to inform you that the following class has been cancelled:</p>
                        <ul>
                            <li><strong>Class:</strong> {$classData['class_title']}</li>
                            <li><strong>Skill:</strong> {$classData['skill_name']}</li>
                            <li><strong>Date:</strong> " . date('F d, Y', strtotime($classData['class_date'])) . "</li>
                            <li><strong>Time:</strong> " . date('h:i A', strtotime($classData['start_time'])) . "</li>
                        </ul>
                        <p>We apologize for any inconvenience.</p>
                        <p>Best regards,<br>SkillUp Now Team</p>
                    </body>
                    </html>
                ";
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: noreply@skillupnow.com\r\n";
                
                @mail($to, $subject, $message, $headers);
            }
            
            $_SESSION['success'] = "Class cancelled successfully. " . count($students) . " students have been notified via email.";
        }
        $updateStmt->close();
    }
    
    header("Location: my-classes.php");
    exit();
}

if ($userType === 'tutor') {
    $classes = [];
    $stmt = $conn->prepare("
        SELECT 
            sc.*,
            s.skill_name,
            (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id) as enrolled_count
        FROM scheduled_classes sc
        INNER JOIN skills s ON sc.skill_id = s.skill_id
        WHERE sc.tutor_id = ?
        ORDER BY sc.class_date DESC, sc.start_time DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();
    
} else {
    $classes = [];
    $stmt = $conn->prepare("
        SELECT 
            sc.*,
            s.skill_name,
            u.full_name as tutor_name,
            u.custom_user_id as tutor_id_display,
            ce.enrollment_status,
            ce.enrolled_at,
            (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id) as enrolled_count
        FROM class_enrollments ce
        INNER JOIN scheduled_classes sc ON ce.class_id = sc.class_id
        INNER JOIN skills s ON sc.skill_id = s.skill_id
        INNER JOIN users u ON sc.tutor_id = u.user_id
        WHERE ce.student_id = ?
        ORDER BY sc.class_date DESC, sc.start_time DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();
}

$upcomingClasses = [];
$pastClasses = [];
$cancelledClasses = [];
$today = date('Y-m-d');

foreach ($classes as $class) {
    if ($class['status'] === 'cancelled') {
        $cancelledClasses[] = $class;
    } elseif ($class['class_date'] >= $today && $class['status'] === 'scheduled') {
        $upcomingClasses[] = $class;
    } else {
        $pastClasses[] = $class;
    }
}

closeDbConnection($conn);
include 'dashboard-header.php';
?>

<style>
.filter-tabs {
    display: flex;
    gap: 12px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 12px 24px;
    border-radius: 12px;
    border: 2px solid #E5E7EB;
    background: white;
    font-weight: 600;
    font-size: 0.938rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-tab:hover {
    border-color: #4FD1C5;
    background: #F0FDFA;
}

.filter-tab.active {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    color: white;
    border-color: #4FD1C5;
    box-shadow: 0 4px 12px rgba(79, 209, 197, 0.3);
}

.filter-tab .count {
    background: rgba(255,255,255,0.3);
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.813rem;
    font-weight: 700;
}

.filter-tab.active .count {
    background: rgba(255,255,255,0.3);
}

.class-section {
    display: none;
}

.class-section.active {
    display: block;
}

.class-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    margin-bottom: 20px;
}

.class-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.class-card.upcoming {
    border-color: #10B981;
    background: linear-gradient(to right, rgba(16, 185, 129, 0.02), white);
}

.class-card.cancelled {
    border-color: #EF4444;
    background: linear-gradient(to right, rgba(239, 68, 68, 0.02), white);
}

.class-card.past {
    border-color: #E5E7EB;
    opacity: 0.85;
}

.class-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    gap: 16px;
    flex-wrap: wrap;
}

.class-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.skill-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.813rem;
    font-weight: 600;
}

.class-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin: 16px 0;
    padding: 16px;
    background: #F9FAFB;
    border-radius: 12px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #4B5563;
    font-size: 0.938rem;
}

.meta-item i {
    width: 20px;
    text-align: center;
    color: #6B7280;
}

.class-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #E5E7EB;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.938rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none;
}

.btn-join {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    color: white;
}

.btn-join:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 209, 197, 0.4);
}

.btn-edit {
    background: #F3F4F6;
    color: #4B5563;
    border: 2px solid #E5E7EB;
}

.btn-edit:hover {
    background: #E5E7EB;
}

.btn-cancel {
    background: #FEE2E2;
    color: #DC2626;
    border: 2px solid #FECACA;
}

.btn-cancel:hover {
    background: #FECACA;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.upcoming {
    background: #D1FAE5;
    color: #065F46;
}

.status-badge.cancelled {
    background: #FEE2E2;
    color: #991B1B;
}

.status-badge.completed {
    background: #E5E7EB;
    color: #374151;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background: #E5E7EB;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10B981, #059669);
    border-radius: 10px;
}
</style>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background: linear-gradient(135deg, #D1FAE5, #A7F3D0); border: 2px solid #10B981; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-check" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <p style="margin: 0; color: #065F46; font-weight: 600;">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </p>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div style="max-width: 1200px; margin: 0 auto;">
    
    <div style="margin-bottom: 32px;">
        <h1 style="margin-bottom: 8px; font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-chalkboard-teacher"></i> My Classes
        </h1>
        <p style="color: #6B7280; font-size: 1rem;">
            <?= $userType === 'tutor' ? 'Manage your scheduled classes' : 'Classes you are enrolled in' ?>
        </p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 32px;">
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 2px solid #E5E7EB;">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #6B7280; font-size: 0.875rem; font-weight: 600;">Total Classes</p>
                    <h2 style="margin: 8px 0 0 0; font-size: 2.5rem; font-weight: 800; color: #111827;"><?= count($classes) ?></h2>
                </div>
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #4FD1C5, #38B2AC); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-book" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 2px solid #BBF7D0;">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #16A34A; font-size: 0.875rem; font-weight: 600;">Upcoming</p>
                    <h2 style="margin: 8px 0 0 0; font-size: 2.5rem; font-weight: 800; color: #15803D;"><?= count($upcomingClasses) ?></h2>
                </div>
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #10B981, #059669); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-calendar-check" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 2px solid #E5E7EB;">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #6B7280; font-size: 0.875rem; font-weight: 600;">Completed</p>
                    <h2 style="margin: 8px 0 0 0; font-size: 2.5rem; font-weight: 800; color: #4B5563;"><?= count($pastClasses) ?></h2>
                </div>
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #9CA3AF, #6B7280); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-check-circle" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($classes)): ?>
        <div style="background: white; padding: 60px 40px; border-radius: 20px; text-align: center;">
            <i class="fas fa-book" style="font-size: 4rem; color: #9CA3AF; margin-bottom: 20px;"></i>
            <h2 style="color: #111827; margin-bottom: 16px;">
                <?= $userType === 'tutor' ? 'No Classes Scheduled' : 'Not Enrolled in Any Classes' ?>
            </h2>
            <p style="color: #6B7280; margin-bottom: 32px;">
                <?= $userType === 'tutor' ? 'Schedule your first class!' : 'Browse tutors and enroll!' ?>
            </p>
            <?php if ($userType === 'tutor'): ?>
                <a href="schedule.php" class="btn btn-primary" style="padding: 14px 32px; text-decoration: none;">
                    <i class="fas fa-calendar-plus"></i> Schedule a Class
                </a>
            <?php else: ?>
                <a href="browse.php" class="btn btn-primary" style="padding: 14px 32px; text-decoration: none;">
                    <i class="fas fa-search"></i> Find Tutors
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="showSection('upcoming')" id="tab-upcoming">
                <i class="fas fa-calendar-alt"></i>
                Upcoming
                <span class="count"><?= count($upcomingClasses) ?></span>
            </button>
            
            <?php if ($userType === 'tutor' && !empty($cancelledClasses)): ?>
            <button class="filter-tab" onclick="showSection('cancelled')" id="tab-cancelled">
                <i class="fas fa-ban"></i>
                Cancelled
                <span class="count"><?= count($cancelledClasses) ?></span>
            </button>
            <?php endif; ?>
            
            <button class="filter-tab" onclick="showSection('completed')" id="tab-completed">
                <i class="fas fa-check-circle"></i>
                Completed
                <span class="count"><?= count($pastClasses) ?></span>
            </button>
        </div>

        <!-- Upcoming Classes Section -->
        <div id="section-upcoming" class="class-section active">
            <?php if (empty($upcomingClasses)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 12px;">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #D1D5DB; margin-bottom: 16px;"></i>
                    <p style="color: #6B7280; margin: 0;">No upcoming classes</p>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingClasses as $class): ?>
                    <div class="class-card upcoming">
                        <div class="class-header">
                            <div style="flex: 1;">
                                <h3 class="class-title"><?= htmlspecialchars($class['class_title']) ?></h3>
                                <div style="margin-top: 8px;">
                                    <span class="skill-badge">
                                        <i class="fas fa-graduation-cap"></i>
                                        <?= htmlspecialchars($class['skill_name']) ?>
                                    </span>
                                </div>
                            </div>
                            <span class="status-badge upcoming">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                Active
                            </span>
                        </div>

                        <div class="class-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span><strong><?= date('M d, Y', strtotime($class['class_date'])) ?></strong></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span><?= date('h:i A', strtotime($class['start_time'])) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span><?= $class['duration_minutes'] ?> mins</span>
                            </div>
                            <?php if ($userType === 'learner'): ?>
                                <div class="meta-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span><?= htmlspecialchars($class['tutor_name']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin: 16px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="font-size: 0.875rem; color: #6B7280; font-weight: 600;">
                                    <i class="fas fa-users"></i> Enrollment
                                </span>
                                <span style="font-size: 0.875rem; font-weight: 700; color: #10B981;">
                                    <?= $class['enrolled_count'] ?>/<?= $class['max_students'] ?> students
                                </span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= ($class['enrolled_count'] / $class['max_students']) * 100 ?>%;"></div>
                            </div>
                        </div>

                        <div class="class-actions">
                            <?php 
                            $meetingLink = $class['meeting_link'];
                            if (!preg_match("~^(?:f|ht)tps?://~i", $meetingLink)) {
                                $meetingLink = 'https://' . $meetingLink;
                            }
                            ?>
                            <a href="<?= htmlspecialchars($meetingLink) ?>" target="_blank" class="btn-action btn-join">
                                <i class="fas fa-video"></i> Join Class
                            </a>
                            
                            <?php if ($userType === 'tutor'): ?>
                                <a href="edit-class.php?id=<?= $class['class_id'] ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit Details
                                </a>
                                
                                <form method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('⚠️ Cancel this class?\n\n<?= $class['enrolled_count'] ?> students will be notified via email.');">
                                    <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                                    <button type="submit" name="cancel_class" class="btn-action btn-cancel">
                                        <i class="fas fa-times-circle"></i> Cancel Class
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Cancelled Classes Section -->
        <?php if ($userType === 'tutor'): ?>
        <div id="section-cancelled" class="class-section">
            <?php if (empty($cancelledClasses)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 12px;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #10B981; margin-bottom: 16px;"></i>
                    <p style="color: #6B7280; margin: 0;">No cancelled classes</p>
                </div>
            <?php else: ?>
                <?php foreach ($cancelledClasses as $class): ?>
                    <div class="class-card cancelled">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 8px 0; font-size: 1.125rem; font-weight: 700; color: #374151;">
                                    <?= htmlspecialchars($class['class_title']) ?>
                                </h3>
                                <div style="display: flex; flex-wrap: wrap; gap: 16px; color: #6B7280; font-size: 0.938rem;">
                                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($class['skill_name']) ?></span>
                                    <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($class['class_date'])) ?></span>
                                    <span><i class="fas fa-users"></i> <?= $class['enrolled_count'] ?> students</span>
                                </div>
                            </div>
                            <span class="status-badge cancelled">
                                <i class="fas fa-ban"></i> CANCELLED
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Completed Classes Section -->
        <div id="section-completed" class="class-section">
            <?php if (empty($pastClasses)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 12px;">
                    <i class="fas fa-calendar-check" style="font-size: 3rem; color: #D1D5DB; margin-bottom: 16px;"></i>
                    <p style="color: #6B7280; margin: 0;">No completed classes yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($pastClasses as $class): ?>
                    <div class="class-card past">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 16px;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 8px 0; font-size: 1.125rem; font-weight: 600; color: #4B5563;">
                                    <?= htmlspecialchars($class['class_title']) ?>
                                </h3>
                                <div style="margin-bottom: 12px;">
                                    <span style="background: #F3F4F6; color: #4B5563; padding: 4px 12px; border-radius: 12px; font-size: 0.813rem; font-weight: 600;">
                                        <?= htmlspecialchars($class['skill_name']) ?>
                                    </span>
                                    <?php if ($userType === 'learner'): ?>
                                        <span style="color: #6B7280; font-size: 0.875rem; margin-left: 12px;">
                                            <i class="fas fa-user-tie"></i> <?= htmlspecialchars($class['tutor_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 16px; color: #9CA3AF; font-size: 0.875rem;">
                                    <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($class['class_date'])) ?></span>
                                    <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($class['start_time'])) ?></span>
                                    <span><i class="fas fa-users"></i> <?= $class['enrolled_count'] ?> students</span>
                                </div>
                            </div>
                            <span class="status-badge completed">
                                <i class="fas fa-check"></i> Completed
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<script>
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.class-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active from all tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById('section-' + sectionName).classList.add('active');
    document.getElementById('tab-' + sectionName).classList.add('active');
}
</script>

<?php include 'dashboard-footer.php'; ?>