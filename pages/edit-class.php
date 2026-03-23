<?php
session_start();
require_once '../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Edit Class";
$tutorId = $_SESSION['user_id'];
$conn = getDbConnection();

// Get class ID
$classId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get class details
$class = null;
$stmt = $conn->prepare("
    SELECT sc.*, s.skill_id, s.skill_name
    FROM scheduled_classes sc
    INNER JOIN skills s ON sc.skill_id = s.skill_id
    WHERE sc.class_id = ? AND sc.tutor_id = ?
");
$stmt->bind_param("ii", $classId, $tutorId);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    $_SESSION['error'] = "Class not found or you don't have permission to edit it.";
    header("Location: my-classes.php");
    exit();
}

// Get tutor's verified skills
$verifiedSkills = [];
$stmt = $conn->prepare("
    SELECT s.skill_id, s.skill_name, sv.test_score
    FROM skill_verifications sv
    INNER JOIN skills s ON sv.skill_id = s.skill_id
    WHERE sv.user_id = ? AND sv.is_verified = 1
    ORDER BY s.skill_name
");
$stmt->bind_param("i", $tutorId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $verifiedSkills[] = $row;
}
$stmt->close();

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $skillId = isset($_POST['skill_id']) ? intval($_POST['skill_id']) : 0;
        $classTitle = isset($_POST['class_title']) ? trim($_POST['class_title']) : '';
        $classDescription = isset($_POST['class_description']) ? trim($_POST['class_description']) : '';
        $classDate = isset($_POST['class_date']) ? $_POST['class_date'] : '';
        $startTime = isset($_POST['start_time']) ? $_POST['start_time'] : '';
        $endTime = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        $maxStudents = isset($_POST['max_students']) ? intval($_POST['max_students']) : 10;
        $meetingLink = isset($_POST['meeting_link']) ? trim($_POST['meeting_link']) : '';
        $isFree = isset($_POST['is_free']) ? 1 : 0;
        $price = $isFree ? 0.00 : (isset($_POST['price']) ? floatval($_POST['price']) : 0.00);
        
        // Add https:// if not present in meeting link
        if (!empty($meetingLink) && !preg_match("~^(?:f|ht)tps?://~i", $meetingLink)) {
            $meetingLink = 'https://' . $meetingLink;
        }
        
        // Validation
        if ($skillId == 0) {
            $error = "Please select a skill";
        } elseif (empty($classTitle)) {
            $error = "Class title is required";
        } elseif (empty($classDate)) {
            $error = "Class date is required";
        } elseif (empty($startTime) || empty($endTime)) {
            $error = "Start and end times are required";
        } elseif ($startTime >= $endTime) {
            $error = "End time must be after start time";
        } elseif ($maxStudents < 1 || $maxStudents > 100) {
            $error = "Max students must be between 1 and 100";
        } elseif (empty($meetingLink)) {
            $error = "Meeting link is required";
        } else {
            // Calculate duration properly using timestamps
            $start = strtotime($classDate . ' ' . $startTime);
            $end = strtotime($classDate . ' ' . $endTime);
            $durationMinutes = ($end - $start) / 60;
            
            if ($durationMinutes <= 0) {
                $error = "End time must be after start time";
            } else {
                // Get enrolled students before updating
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
                
                // Update class
                $updateQuery = "
                    UPDATE scheduled_classes 
                    SET skill_id = ?, class_title = ?, class_description = ?, 
                        class_date = ?, start_time = ?, end_time = ?, 
                        duration_minutes = ?, max_students = ?, meeting_link = ?,
                        is_free = ?, price = ?, updated_at = NOW()
                    WHERE class_id = ? AND tutor_id = ?
                ";
                
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param(
                    "issssiiisddii", 
                    $skillId, $classTitle, $classDescription, 
                    $classDate, $startTime, $endTime, 
                    $durationMinutes, $maxStudents, $meetingLink,
                    $isFree, $price, $classId, $tutorId
                );
                
                if ($stmt->execute()) {
                    // Get updated skill name
                    $skillStmt = $conn->prepare("SELECT skill_name FROM skills WHERE skill_id = ?");
                    $skillStmt->bind_param("i", $skillId);
                    $skillStmt->execute();
                    $skillName = $skillStmt->get_result()->fetch_assoc()['skill_name'];
                    $skillStmt->close();
                    
                    // Ensure meeting link has https:// for email
                    $displayMeetingLink = $meetingLink;
                    if (!preg_match("~^(?:f|ht)tps?://~i", $displayMeetingLink)) {
                        $displayMeetingLink = 'https://' . $displayMeetingLink;
                    }
                    
                    // Send email notifications to students
                    foreach ($students as $student) {
                        $to = $student['email'];
                        $subject = "Class Updated: " . $classTitle;
                        $message = "
                            <html>
                            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #4FD1C5; border-radius: 10px;'>
                                    <h2 style='color: #4FD1C5; margin-bottom: 20px;'>Class Schedule Updated</h2>
                                    <p>Dear {$student['full_name']},</p>
                                    <p>The following class has been updated:</p>
                                    <div style='background: #F0FDFA; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                                        <p style='margin: 8px 0;'><strong>Class:</strong> {$classTitle}</p>
                                        <p style='margin: 8px 0;'><strong>Skill:</strong> {$skillName}</p>
                                        <p style='margin: 8px 0;'><strong>Date:</strong> " . date('F d, Y', strtotime($classDate)) . "</p>
                                        <p style='margin: 8px 0;'><strong>Time:</strong> " . date('h:i A', strtotime($startTime)) . " - " . date('h:i A', strtotime($endTime)) . "</p>
                                        <p style='margin: 8px 0;'><strong>Duration:</strong> {$durationMinutes} minutes</p>
                                        <p style='margin: 8px 0;'><strong>Meeting Link:</strong> <a href='{$displayMeetingLink}' style='color: #4FD1C5; text-decoration: none;'>{$displayMeetingLink}</a></p>
                                    </div>
                                    <p>Please note these changes to your schedule.</p>
                                    <p style='margin-top: 30px;'>Best regards,<br><strong>SkillUp Now Team</strong></p>
                                </div>
                            </body>
                            </html>
                        ";
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                        $headers .= "From: SkillUp Now <noreply@skillupnow.com>\r\n";
                        
                        @mail($to, $subject, $message, $headers);
                    }
                    
                    $_SESSION['success'] = "Class updated successfully! " . count($students) . " students have been notified via email.";
                    header("Location: my-classes.php");
                    exit();
                } else {
                    $error = "Failed to update class: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

closeDbConnection($conn);

include 'dashboard-header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    
    <div style="margin-bottom: 2rem;">
        <a href="my-classes.php" style="color: #4FD1C5; text-decoration: none; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to My Classes
        </a>
    </div>
    
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem; font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-edit"></i> Edit Class
        </h1>
        <p style="color: #6B7280; font-size: 1.125rem;">
            Update class details and notify enrolled students
        </p>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; background: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-exclamation-circle" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <p style="margin: 0; color: #991B1B; font-weight: 600; font-size: 1.063rem;">
                    <?= htmlspecialchars($error) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <form method="POST" action="" id="editForm">
            
            <!-- Skill Selection -->
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: #111827; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-graduation-cap"></i> Class Information
                </h3>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="skill_id" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                        Select Certified Skill <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="skill_id" id="skill_id" required style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                        <?php foreach ($verifiedSkills as $skill): ?>
                            <option value="<?= $skill['skill_id'] ?>" <?= $skill['skill_id'] == $class['skill_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($skill['skill_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="class_title" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                        Class Title <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="class_title" id="class_title" required maxlength="200" 
                           value="<?= htmlspecialchars($class['class_title']) ?>"
                           style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="class_description" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                        Class Description
                    </label>
                    <textarea name="class_description" id="class_description" rows="4" 
                              style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem; resize: vertical;"><?= htmlspecialchars($class['class_description']) ?></textarea>
                </div>
            </div>
            
            <!-- Schedule -->
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: #111827; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-clock"></i> Schedule
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label for="class_date" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            Date <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="date" name="class_date" id="class_date" required 
                               value="<?= $class['class_date'] ?>"
                               min="<?= date('Y-m-d') ?>"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                    
                    <div>
                        <label for="start_time" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            Start Time <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="time" name="start_time" id="start_time" required 
                               value="<?= $class['start_time'] ?>"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                    
                    <div>
                        <label for="end_time" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            End Time <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="time" name="end_time" id="end_time" required 
                               value="<?= $class['end_time'] ?>"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="max_students" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                        Maximum Students <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="number" name="max_students" id="max_students" required min="1" max="100" 
                           value="<?= $class['max_students'] ?>"
                           style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                </div>
            </div>
            
            <!-- Meeting Details -->
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: #111827; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-video"></i> Meeting Details
                </h3>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="meeting_link" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                        Google Meet / Zoom Link <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="meeting_link" id="meeting_link" required 
                           value="<?= htmlspecialchars($class['meeting_link']) ?>"
                           placeholder="https://meet.google.com/xxx-xxxx-xxx"
                           style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6B7280;">
                        <i class="fas fa-info-circle"></i> Enter the full meeting URL (will auto-add https:// if needed)
                    </p>
                </div>
            </div>
            
            <!-- Pricing -->
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: #111827; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-rupee-sign"></i> Pricing
                </h3>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="is_free" id="is_free" value="1" 
                               <?= $class['is_free'] ? 'checked' : '' ?>
                               style="width: 20px; height: 20px; cursor: pointer;">
                        <span style="font-weight: 600; color: #374151;">
                            This is a FREE class
                        </span>
                    </label>
                </div>
                
                <div>
                    <label for="price" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                        Price (₹)
                    </label>
                    <input type="number" name="price" id="price" min="0" step="0.01" 
                           value="<?= $class['price'] ?>"
                           <?= $class['is_free'] ? 'disabled' : '' ?>
                           style="width: 100%; padding: 0.75rem; border: 2px solid #E5E7EB; border-radius: 0.5rem; font-size: 1rem;">
                </div>
            </div>
            
            <!-- Submit -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="my-classes.php" class="btn btn-outline" style="padding: 12px 24px; text-decoration: none; display: inline-block;">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="padding: 12px 24px;">
                    <i class="fas fa-save"></i> Update Class & Notify Students
                </button>
            </div>
            
        </form>
    </div>

</div>

<script>
document.getElementById('is_free').addEventListener('change', function() {
    document.getElementById('price').disabled = this.checked;
    if (this.checked) {
        document.getElementById('price').value = 0;
    }
});

// Validate times before submit
document.getElementById('editForm').addEventListener('submit', function(e) {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (startTime && endTime && startTime >= endTime) {
        e.preventDefault();
        alert('⚠️ End time must be after start time!');
        return false;
    }
    
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    document.getElementById('submitBtn').disabled = true;
});
</script>

<?php include 'dashboard-footer.php'; ?>