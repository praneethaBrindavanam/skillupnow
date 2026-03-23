<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Tutor Verification";
$tutorId = $_SESSION['user_id'];
$conn = getDbConnection();

// Check if required columns exist
$columnsExist = true;
try {
    $result = $conn->query("SHOW COLUMNS FROM skills LIKE 'moodle_course_id'");
    if ($result->num_rows == 0) {
        $columnsExist = false;
        $errorMessage = "Database not set up. Please run COMPLETE_MOODLE_MAPPINGS_ALL_71_SKILLS.sql first.";
    }
} catch (Exception $e) {
    $columnsExist = false;
    $errorMessage = "Database error: " . $e->getMessage();
}

// Check if skill_verifications table exists
$verificationTableExists = true;
try {
    $result = $conn->query("SHOW TABLES LIKE 'skill_verifications'");
    if ($result->num_rows == 0) {
        $verificationTableExists = false;
        $errorMessage = "Verification table missing. Please run tutor_verification_system.sql first.";
    }
} catch (Exception $e) {
    $verificationTableExists = false;
    $errorMessage = "Database error: " . $e->getMessage();
}

$tutorSkills = [];
$verifiedCount = 0;
$pendingCount = 0;
$inProgressCount = 0;

if ($columnsExist && $verificationTableExists) {
    // Get tutor's selected skills with Moodle info
    try {
        $stmt = $conn->prepare("
            SELECT 
                s.skill_id, 
                s.skill_name, 
                s.skill_category,
                s.moodle_course_id,
                s.moodle_enrollment_key,
                s.moodle_course_url,
                sv.verification_id, 
                sv.is_verified, 
                sv.test_score, 
                sv.test_attempts, 
                sv.verified_at,
                sv.moodle_exam_started,
                sv.moodle_exam_completed,
                sv.moodle_certificate_url,
                sv.instructions_accepted
            FROM user_skills us
            JOIN skills s ON us.skill_id = s.skill_id
            LEFT JOIN skill_verifications sv ON us.user_id = sv.user_id AND us.skill_id = sv.skill_id
            WHERE us.user_id = ?
            ORDER BY s.skill_category, s.skill_name
        ");
        $stmt->bind_param("i", $tutorId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tutorSkills[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        $errorMessage = "Error loading skills: " . $e->getMessage();
    }

    // Count verification stats
    foreach ($tutorSkills as $skill) {
        if ($skill['is_verified']) {
            $verifiedCount++;
        } elseif ($skill['moodle_exam_started']) {
            $inProgressCount++;
        } else {
            $pendingCount++;
        }
    }
}

closeDbConnection($conn);

include 'dashboard-header.php';
?>

<?php if (!$columnsExist || !$verificationTableExists): ?>
    <!-- Database Setup Required -->
    <div style="max-width: 800px; margin: 2rem auto;">
        <div style="background: #FEE2E2; border: 3px solid #EF4444; border-radius: 1rem; padding: 2rem;">
            <div style="display: flex; gap: 1.5rem; align-items: start;">
                <div style="width: 60px; height: 60px; background: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-exclamation-triangle" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h2 style="color: #991B1B; margin: 0 0 1rem 0;">Database Setup Required</h2>
                    <p style="color: #7F1D1D; margin: 0 0 1rem 0; line-height: 1.6;">
                        <?= htmlspecialchars($errorMessage ?? 'Database tables not found.') ?>
                    </p>
                    <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <h3 style="margin: 0 0 1rem 0; color: #374151;">Required SQL Files:</h3>
                        <ol style="margin: 0; padding-left: 1.5rem; color: #4B5563; line-height: 1.8;">
                            <li><strong>COMPLETE_PROFILE_SETUP.sql</strong> - Adds profile fields</li>
                            <li><strong>tutor_verification_system.sql</strong> - Creates verification tables</li>
                            <li><strong>COMPLETE_MOODLE_MAPPINGS_ALL_71_SKILLS.sql</strong> - Maps Moodle courses</li>
                        </ol>
                    </div>
                    <p style="color: #7F1D1D; margin: 0; font-size: 0.875rem;">
                        Please run these SQL files in phpMyAdmin, then refresh this page.
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>

<div style="max-width: 1200px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem;">
            <i class="fas fa-shield-alt"></i> Tutor Skill Verification
        </h1>
        <p style="color: var(--gray-600);">
            Complete certification exams to start teaching
        </p>
    </div>

    <!-- Info Banner -->
    <div style="background: linear-gradient(135deg, #DBEAFE, #BFDBFE); border: 2px solid #3B82F6; border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; background: #3B82F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-info-circle" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 style="color: #1E40AF; margin: 0 0 0.5rem 0;">📚 Moodle Certification Exams</h3>
                <p style="color: #1E3A8A; margin: 0; line-height: 1.6;">
                    To ensure quality education, all tutors must pass a <strong>certification exam on Moodle</strong> for each skill. 
                    Exams are proctored using <strong>Safe Exam Browser (SEB)</strong>. 
                    You need to score <strong>75% or higher</strong> to get certified and receive your certificate!
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Total Skills</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: var(--gray-700);"><?= count($tutorSkills) ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: var(--gray-200); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-list" style="color: var(--gray-600);"></i>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Certified</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #10B981;"><?= $verifiedCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: #D1FAE5; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-certificate" style="color: #10B981;"></i>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">In Progress</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #3B82F6;"><?= $inProgressCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: #DBEAFE; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-hourglass-half" style="color: #3B82F6;"></i>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Pending</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F59E0B;"><?= $pendingCount ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-clock" style="color: #F59E0B;"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($tutorSkills)): ?>
        <!-- No Skills Selected -->
        <div style="background: white; padding: 4rem 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center;">
            <div style="width: 120px; height: 120px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto;">
                <i class="fas fa-graduation-cap" style="font-size: 3rem; color: var(--gray-400);"></i>
            </div>
            <h2 style="color: var(--gray-700); margin-bottom: 1rem;">No Skills Selected</h2>
            <p style="color: var(--gray-500); max-width: 500px; margin: 0 auto 2rem auto;">
                Please add your teaching skills in your profile before starting the verification process.
            </p>
            <a href="profile.php" class="btn btn-primary">
                <i class="fas fa-user-edit"></i> Update Profile
            </a>
        </div>
    <?php else: ?>
        <!-- Skills List -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 1.5rem;">Your Skills Certification Status</h2>

            <div style="display: grid; gap: 1rem;">
                <?php foreach ($tutorSkills as $skill): ?>
                    <div style="border: 2px solid <?= $skill['is_verified'] ? '#10B981' : ($skill['moodle_exam_started'] ? '#3B82F6' : '#E5E7EB') ?>; border-radius: var(--radius-lg); padding: 1.5rem; transition: all 0.3s;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            
                            <!-- Skill Info -->
                            <div style="flex: 1; min-width: 250px;">
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                                    <h3 style="margin: 0; color: var(--gray-900);">
                                        <?= htmlspecialchars($skill['skill_name']) ?>
                                    </h3>
                                    <span style="background: <?= $skill['skill_category'] === 'technical' ? '#DBEAFE' : '#FEF3C7' ?>; color: <?= $skill['skill_category'] === 'technical' ? '#1E40AF' : '#92400E' ?>; padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600;">
                                        <?= ucfirst($skill['skill_category']) ?>
                                    </span>
                                </div>
                                
                                <?php if ($skill['is_verified']): ?>
                                    <!-- VERIFIED -->
                                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                        <span style="background: #D1FAE5; color: #065F46; padding: 0.5rem 1rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-certificate"></i> Certified
                                        </span>
                                        <?php if ($skill['test_score']): ?>
                                            <span style="color: var(--gray-600); font-size: 0.875rem;">
                                                Score: <strong><?= number_format($skill['test_score'], 1) ?>%</strong>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($skill['verified_at']): ?>
                                            <span style="color: var(--gray-500); font-size: 0.75rem;">
                                                <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($skill['verified_at'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($skill['moodle_exam_started']): ?>
                                    <!-- IN PROGRESS -->
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <span style="background: #DBEAFE; color: #1E40AF; padding: 0.5rem 1rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-hourglass-half"></i> Exam In Progress
                                        </span>
                                        <a href="mark-skill-verified.php?skill_id=<?= $skill['skill_id'] ?>" style="color: #3B82F6; font-size: 0.875rem; text-decoration: none; font-weight: 600;">
                                            Mark as Verified →
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- NOT STARTED -->
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <span style="background: #FEF3C7; color: #92400E; padding: 0.5rem 1rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-clock"></i> Not Certified
                                        </span>
                                        <?php if (!empty($skill['moodle_course_url'])): ?>
                                            <span style="color: var(--gray-600); font-size: 0.75rem;">
                                                <i class="fas fa-check-circle" style="color: #10B981;"></i> Moodle exam available
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #DC2626; font-size: 0.75rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Exam not configured
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Button -->
                            <div>
                                <?php if ($skill['is_verified']): ?>
                                    <!-- Show Certificate -->
                                    <?php if (!empty($skill['moodle_certificate_url'])): ?>
                                        <a href="<?= htmlspecialchars($skill['moodle_certificate_url']) ?>" target="_blank" class="btn" style="background: #10B981; color: white;">
                                            <i class="fas fa-download"></i> View Certificate
                                        </a>
                                    <?php else: ?>
                                        <button class="btn" style="background: #10B981; color: white; cursor: default;" disabled>
                                            <i class="fas fa-check-circle"></i> Certified
                                        </button>
                                    <?php endif; ?>
                                <?php elseif (!empty($skill['moodle_course_url'])): ?>
                                    <!-- Take Exam Button -->
                                    <button class="btn btn-primary take-exam-btn" 
                                        data-skill-id="<?= $skill['skill_id'] ?>"
                                        data-skill-name="<?= htmlspecialchars($skill['skill_name']) ?>"
                                        data-enrollment-key="<?= htmlspecialchars($skill['moodle_enrollment_key'] ?? 'KEY_NOT_SET') ?>"
                                        data-course-url="<?= htmlspecialchars($skill['moodle_course_url']) ?>">
                                        <i class="fas fa-clipboard-check"></i> Take Certification Exam
                                    </button>
                                <?php else: ?>
                                    <!-- Exam Not Available -->
                                    <button class="btn btn-outline" disabled style="cursor: not-allowed; opacity: 0.5;">
                                        <i class="fas fa-exclamation-triangle"></i> Exam Not Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Instructions Modal -->
<div id="instructionsModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: var(--radius-lg); max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 1rem;">
        <div style="padding: 2rem; border-bottom: 2px solid var(--gray-200);">
            <h2 style="margin: 0; color: var(--gray-900);">
                <i class="fas fa-exclamation-triangle" style="color: #F59E0B;"></i> 
                IMPORTANT EXAM INSTRUCTIONS
            </h2>
        </div>
        
        <div style="padding: 2rem; line-height: 1.8;" id="instructionsContent">
            <!-- Instructions will be loaded here -->
        </div>
        
        <div style="padding: 2rem; border-top: 2px solid var(--gray-200); background: var(--gray-50);">
            <label style="display: flex; align-items: start; gap: 1rem; cursor: pointer; margin-bottom: 1.5rem;">
                <input type="checkbox" id="acceptInstructions" style="width: 24px; height: 24px; margin-top: 0.25rem; cursor: pointer;">
                <span style="font-weight: 600; color: var(--gray-800);">
                    I have read and understood all the instructions above. I agree to follow all the rules and understand that any violation may lead to disqualification.
                </span>
            </label>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" id="cancelExam" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" id="startExamBtn" class="btn btn-primary" disabled style="opacity: 0.5;">
                    <i class="fas fa-external-link-alt"></i> Start Exam in Moodle
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentExamData = {};

// Take Exam Button Click
document.querySelectorAll('.take-exam-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentExamData = {
            skillId: this.dataset.skillId,
            skillName: this.dataset.skillName,
            enrollmentKey: this.dataset.enrollmentKey,
            courseUrl: this.dataset.courseUrl
        };
        
        showInstructions();
    });
});

function showInstructions() {
    const modal = document.getElementById('instructionsModal');
    const content = document.getElementById('instructionsContent');
    
    content.innerHTML = `
        <h3 style="color: #10B981; margin-bottom: 1rem;">
            <i class="fas fa-laptop-code"></i> Certification Exam for: ${currentExamData.skillName}
        </h3>
        
        <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 1rem; margin-bottom: 1.5rem;">
            <strong style="color: #92400E;">Enrollment Key:</strong>
            <code style="background: white; padding: 0.5rem; display: inline-block; margin-left: 0.5rem; font-size: 1.1rem; color: #DC2626; font-weight: bold;">${currentExamData.enrollmentKey}</code>
        </div>
        
        <h4 style="color: #1E40AF; margin: 1.5rem 0 1rem 0;">🟢 Before Starting</h4>
        <ul style="margin-left: 1.5rem; color: var(--gray-700);">
            <li>Install <strong>Safe Exam Browser (SEB)</strong> on your system</li>
            <li>Ensure you have a <strong>stable internet connection</strong></li>
            <li>Keep your <strong>Enrollment Key</strong> ready (shown above)</li>
        </ul>
        
        <h4 style="color: #1E40AF; margin: 1.5rem 0 1rem 0;">🔐 Enrolling in the Course</h4>
        <ul style="margin-left: 1.5rem; color: var(--gray-700);">
            <li>Click "Start Exam in Moodle" button below</li>
            <li>Log in to Moodle with your credentials</li>
            <li>Enter the <strong>Enrollment Key</strong> when prompted</li>
            <li>Make sure the key is entered correctly</li>
        </ul>
        
        <h4 style="color: #1E40AF; margin: 1.5rem 0 1rem 0;">▶️ Starting the Exam</h4>
        <ul style="margin-left: 1.5rem; color: var(--gray-700);">
            <li>The exam must be opened <strong>only through SEB</strong></li>
            <li>Download the provided <strong>.seb config file</strong></li>
            <li><strong>Double-click the .seb file</strong> to automatically open the exam in SEB</li>
        </ul>
        
        <h4 style="color: #1E40AF; margin: 1.5rem 0 1rem 0;">✅ Passing Criteria</h4>
        <ul style="margin-left: 1.5rem; color: var(--gray-700);">
            <li>Minimum <strong>75% marks</strong> required to pass</li>
            <li>Make sure you submit the quiz properly</li>
            <li>After passing, return here to mark as verified</li>
        </ul>
    `;
    
    modal.style.display = 'flex';
    document.getElementById('acceptInstructions').checked = false;
    document.getElementById('startExamBtn').disabled = true;
}

// Accept checkbox
document.getElementById('acceptInstructions').addEventListener('change', function() {
    const startBtn = document.getElementById('startExamBtn');
    if (this.checked) {
        startBtn.disabled = false;
        startBtn.style.opacity = '1';
    } else {
        startBtn.disabled = true;
        startBtn.style.opacity = '0.5';
    }
});

// Cancel button
document.getElementById('cancelExam').addEventListener('click', function() {
    document.getElementById('instructionsModal').style.display = 'none';
});

// Start Exam button
document.getElementById('startExamBtn').addEventListener('click', function() {
    // Mark instructions as accepted (AJAX call to save this)
    fetch('mark-exam-started.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            skill_id: currentExamData.skillId
        })
    });
    
    // Open Moodle in new tab
    window.open(currentExamData.courseUrl, '_blank');
    
    // Close modal
    document.getElementById('instructionsModal').style.display = 'none';
    
    // Show success message
    alert('Exam opened in Moodle! Use the enrollment key: ' + currentExamData.enrollmentKey);
    
    // Reload page to update status
    setTimeout(() => {
        window.location.reload();
    }, 2000);
});
</script>

<?php endif; ?>

<?php include 'dashboard-footer.php'; ?>