<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../includes/config.php';

// Prevent back button after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Schedule Class";
$tutorId = $_SESSION['user_id'];
$conn = getDbConnection();

// Check if tutor is verified
$stmt = $conn->prepare("SELECT is_tutor_verified FROM users WHERE user_id = ?");
$stmt->bind_param("i", $tutorId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$isVerified = ($user['is_tutor_verified'] == 1);
$stmt->close();

// Get ONLY VERIFIED skills
$verifiedSkills = [];
if ($isVerified) {
    // Check if verification table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'skill_verifications'");
    
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // Get ONLY verified skills
        $stmt = $conn->prepare("
            SELECT 
                s.skill_id,
                s.skill_name,
                sv.test_score
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
    }
}

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
        
        // Verify this skill is actually verified for this tutor
        $skillVerified = false;
        foreach ($verifiedSkills as $skill) {
            if ($skill['skill_id'] == $skillId) {
                $skillVerified = true;
                break;
            }
        }
        
        if (!$skillVerified) {
            $error = "You can only schedule classes for verified skills. Please complete certification first.";
        } elseif ($skillId == 0) {
            $error = "Please select a skill";
        } elseif (empty($classTitle)) {
            $error = "Class title is required";
        } elseif (empty($classDate)) {
            $error = "Class date is required";
        } elseif (empty($startTime)) {
            $error = "Start time is required";
        } elseif (empty($endTime)) {
            $error = "End time is required";
        } elseif ($startTime >= $endTime) {
            $error = "End time must be after start time";
        } elseif ($maxStudents < 1 || $maxStudents > 100) {
            $error = "Max students must be between 1 and 100";
        } elseif (empty($meetingLink)) {
            $error = "Meeting link is required";
        } else {
            // Calculate duration
            $startDateTime = new DateTime($classDate . ' ' . $startTime);
            $endDateTime = new DateTime($classDate . ' ' . $endTime);
            $interval = $startDateTime->diff($endDateTime);
            $duration = ($interval->h * 60) + $interval->i;
            
            // Insert class
            $insertQuery = "
                INSERT INTO scheduled_classes 
                (tutor_id, skill_id, class_title, class_description, class_date, 
                 start_time, end_time, duration_minutes, max_students, meeting_link, 
                 is_free, price, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())
            ";
            
            $stmt = $conn->prepare($insertQuery);
            
            if (!$stmt) {
                $error = "Failed to prepare statement: " . $conn->error;
            } else {
                $stmt->bind_param(
                    "iissssiisidd", 
                    $tutorId, 
                    $skillId, 
                    $classTitle, 
                    $classDescription, 
                    $classDate, 
                    $startTime, 
                    $endTime, 
                    $duration, 
                    $maxStudents, 
                    $meetingLink, 
                    $isFree, 
                    $price
                );
                
                if ($stmt->execute()) {
                    $classId = $stmt->insert_id;
                    $success = "Class scheduled successfully! Class ID: " . $classId;
                    // Clear form
                    $_POST = array();
                } else {
                    $error = "Failed to schedule class: " . $stmt->error;
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
        <h1 style="margin-bottom: 0.5rem;">
            <i class="fas fa-calendar-alt"></i> Schedule New Class
        </h1>
        <p style="color: var(--gray-600);">
            Create a new class for your verified skills
        </p>
    </div>

    <!-- Debug Info -->
    <div style="background: #DBEAFE; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 2px solid #3B82F6;">
        <strong>Debug:</strong> Tutor ID: <?= $tutorId ?> | Verified: <?= $isVerified ? 'Yes' : 'No' ?> | Certified Skills: <?= count($verifiedSkills) ?>
    </div>

    <!-- Success Message -->
    <?php if (!empty($success)): ?>
        <div style="background: #D1FAE5; border: 2px solid #10B981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <p style="margin: 0 0 0.5rem 0; color: #065F46;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </p>
            <a href="my-classes.php" style="color: #065F46; font-weight: 600; text-decoration: underline;">
                <i class="fas fa-arrow-right"></i> View My Classes
            </a>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div style="background: #FEE2E2; border: 2px solid #EF4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <p style="margin: 0; color: #991B1B;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (!$isVerified): ?>
        <!-- Not Verified Warning -->
        <div style="background: #FEE2E2; border: 3px solid #EF4444; border-radius: var(--radius-lg); padding: 2rem;">
            <div style="display: flex; gap: 1.5rem; align-items: start;">
                <div style="width: 60px; height: 60px; background: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-lock" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h2 style="color: #991B1B; margin: 0 0 1rem 0;">Verification Required</h2>
                    <p style="color: #7F1D1D; margin: 0 0 1.5rem 0;">
                        You must complete skill certification before you can schedule classes. 
                        Pass at least one Moodle certification exam to start teaching.
                    </p>
                    <a href="tutor-verification.php" class="btn btn-primary" style="background: #EF4444;">
                        <i class="fas fa-shield-alt"></i> Complete Verification
                    </a>
                </div>
            </div>
        </div>
    <?php elseif (empty($verifiedSkills)): ?>
        <!-- No Verified Skills -->
        <div style="background: #FEF3C7; border: 3px solid #F59E0B; border-radius: var(--radius-lg); padding: 2rem;">
            <div style="display: flex; gap: 1.5rem; align-items: start;">
                <div style="width: 60px; height: 60px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-exclamation-triangle" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h2 style="color: #92400E; margin: 0 0 1rem 0;">No Certified Skills Yet</h2>
                    <p style="color: #78350F; margin: 0 0 1.5rem 0;">
                        You haven't certified any skills yet. Complete certification exams to start scheduling classes.
                    </p>
                    <a href="tutor-verification.php" class="btn btn-primary" style="background: #F59E0B;">
                        <i class="fas fa-clipboard-check"></i> Get Certified
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Schedule Form -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            
            <form method="POST" action="" id="scheduleForm">
                
                <!-- Skill Selection -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--gray-800);">
                        <i class="fas fa-graduation-cap"></i> Class Information
                    </h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="skill_id" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Select Certified Skill <span style="color: #EF4444;">*</span>
                        </label>
                        <select name="skill_id" id="skill_id" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                            <option value="">Choose a certified skill...</option>
                            <?php foreach ($verifiedSkills as $skill): ?>
                                <option value="<?= $skill['skill_id'] ?>">
                                    <?= htmlspecialchars($skill['skill_name']) ?>
                                    <?php if (isset($skill['test_score'])): ?>
                                        - Certified (<?= number_format($skill['test_score'], 0) ?>%)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: var(--gray-500);">
                            <i class="fas fa-info-circle"></i> Only your certified skills are shown
                        </p>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="class_title" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Class Title <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" name="class_title" id="class_title" required maxlength="200" 
                               placeholder="e.g., Introduction to React Hooks"
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="class_description" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Class Description
                        </label>
                        <textarea name="class_description" id="class_description" rows="4" 
                                  placeholder="Describe what students will learn in this class..."
                                  style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem; resize: vertical;"></textarea>
                    </div>
                </div>
                
                <!-- Schedule -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--gray-800);">
                        <i class="fas fa-clock"></i> Schedule
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label for="class_date" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Date <span style="color: #EF4444;">*</span>
                            </label>
                            <input type="date" name="class_date" id="class_date" required 
                                   min="<?= date('Y-m-d') ?>"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                        </div>
                        
                        <div>
                            <label for="start_time" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Start Time <span style="color: #EF4444;">*</span>
                            </label>
                            <input type="time" name="start_time" id="start_time" required 
                                   style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                        </div>
                        
                        <div>
                            <label for="end_time" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                End Time <span style="color: #EF4444;">*</span>
                            </label>
                            <input type="time" name="end_time" id="end_time" required 
                                   style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="max_students" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Maximum Students <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="number" name="max_students" id="max_students" required min="1" max="100" value="10"
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                </div>
                
                <!-- Meeting Details -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--gray-800);">
                        <i class="fas fa-video"></i> Meeting Details
                    </h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="meeting_link" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Google Meet / Zoom Link <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="url" name="meeting_link" id="meeting_link" required 
                               placeholder="https://meet.google.com/xxx-xxxx-xxx"
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                </div>
                
                <!-- Pricing -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--gray-800);">
                        <i class="fas fa-rupee-sign"></i> Pricing
                    </h3>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="is_free" id="is_free" value="1" checked
                                   style="width: 20px; height: 20px; cursor: pointer;">
                            <span style="font-weight: 600; color: var(--gray-700);">
                                This is a FREE class
                            </span>
                        </label>
                    </div>
                    
                    <div>
                        <label for="price" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Price (₹)
                        </label>
                        <input type="number" name="price" id="price" min="0" step="0.01" value="0" disabled
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; font-size: 1rem;">
                    </div>
                </div>
                
                <!-- Submit -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="tutor-dashboard.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-calendar-check"></i> Schedule Class
                    </button>
                </div>
                
            </form>
        </div>
    <?php endif; ?>

</div>

<script>
document.getElementById('is_free').addEventListener('change', function() {
    document.getElementById('price').disabled = this.checked;
    if (this.checked) {
        document.getElementById('price').value = 0;
    }
});

document.getElementById('scheduleForm')?.addEventListener('submit', function() {
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';
    document.getElementById('submitBtn').disabled = true;
});
</script>

<?php include 'dashboard-footer.php'; ?>