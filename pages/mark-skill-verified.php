<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: signin.php");
    exit();
}

$skillId = intval($_GET['skill_id'] ?? 0);

if ($skillId <= 0) {
    $_SESSION['error'] = "Invalid skill ID";
    header("Location: tutor-verification.php");
    exit();
}

$userId = $_SESSION['user_id'];
$conn = getDbConnection();

// Get skill info
$stmt = $conn->prepare("SELECT skill_name FROM skills WHERE skill_id = ?");
$stmt->bind_param("i", $skillId);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$skill) {
    $_SESSION['error'] = "Skill not found";
    header("Location: tutor-verification.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certificateUrl = trim($_POST['certificate_url'] ?? '');
    $examScore = floatval($_POST['exam_score'] ?? 0);
    
    if ($examScore < 75) {
        $_SESSION['error'] = "Score must be at least 75% to get verified";
    } else {
        // Update verification status
        $stmt = $conn->prepare("
            UPDATE skill_verifications 
            SET is_verified = 1,
                moodle_exam_completed = 1,
                test_score = ?,
                moodle_certificate_url = ?,
                verified_at = NOW(),
                updated_at = NOW()
            WHERE user_id = ? AND skill_id = ?
        ");
        $stmt->bind_param("dsii", $examScore, $certificateUrl, $userId, $skillId);
        
        if ($stmt->execute()) {
            // Check if tutor should be fully verified
            $stmt2 = $conn->prepare("
                SELECT COUNT(*) as verified_count 
                FROM skill_verifications 
                WHERE user_id = ? AND is_verified = 1
            ");
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $result = $stmt2->get_result()->fetch_assoc();
            
            // If at least one skill is verified, mark tutor as verified
            if ($result['verified_count'] > 0) {
                $stmt3 = $conn->prepare("UPDATE users SET is_tutor_verified = 1 WHERE user_id = ?");
                $stmt3->bind_param("i", $userId);
                $stmt3->execute();
                $stmt3->close();
            }
            $stmt2->close();
            
            $_SESSION['success'] = "Congratulations! You are now certified to teach " . $skill['skill_name'] . "!";
            header("Location: tutor-verification.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update verification status";
        }
        $stmt->close();
    }
}

closeDbConnection($conn);

$pageTitle = "Mark Skill as Verified";
include 'dashboard-header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    
    <div style="margin-bottom: 2rem;">
        <a href="tutor-verification.php" style="color: var(--primary-teal); text-decoration: none; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Verification
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
            <p style="margin: 0; color: #991B1B;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <h1 style="margin-bottom: 0.5rem;">
            <i class="fas fa-certificate"></i> Mark as Verified
        </h1>
        <p style="color: var(--gray-600); margin-bottom: 2rem;">
            Skill: <strong><?= htmlspecialchars($skill['skill_name']) ?></strong>
        </p>

        <!-- Instructions -->
        <div style="background: #DBEAFE; border-left: 4px solid #3B82F6; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="color: #1E40AF; margin: 0 0 1rem 0;">
                <i class="fas fa-info-circle"></i> After Completing Your Moodle Exam
            </h3>
            <ul style="margin: 0; padding-left: 1.5rem; color: #1E3A8A; line-height: 1.8;">
                <li>Check your exam score in Moodle</li>
                <li>If you scored <strong>75% or higher</strong>, you can proceed</li>
                <li>Download your certificate from Moodle (if available)</li>
                <li>Enter your score and certificate URL below</li>
                <li>Click "Confirm Verification" to activate teaching for this skill</li>
            </ul>
        </div>

        <form method="POST" style="display: grid; gap: 1.5rem;">
            
            <div>
                <label for="exam_score" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    Your Exam Score (%) <span style="color: #DC2626;">*</span>
                </label>
                <input type="number" id="exam_score" name="exam_score" required
                    min="0" max="100" step="0.01"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;">
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: var(--gray-600);">
                    Minimum 75% required to get verified
                </p>
            </div>

            <div>
                <label for="certificate_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    Certificate URL (Optional)
                </label>
                <input type="url" id="certificate_url" name="certificate_url"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="https://skillupnow.moodlecloud.com/...">
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: var(--gray-600);">
                    Link to your Moodle certificate (if available)
                </p>
            </div>

            <!-- Warning -->
            <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: var(--radius-md); padding: 1.5rem;">
                <h4 style="color: #92400E; margin: 0 0 0.5rem 0;">
                    <i class="fas fa-exclamation-triangle"></i> Important
                </h4>
                <p style="margin: 0; color: #78350F; font-size: 0.875rem; line-height: 1.6;">
                    By clicking "Confirm Verification", you certify that you have completed the Moodle exam 
                    and achieved the score mentioned above. False information may lead to account suspension.
                </p>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                <a href="tutor-verification.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-circle"></i> Confirm Verification
                </button>
            </div>
        </form>

    </div>

</div>

<?php include 'dashboard-footer.php'; ?>