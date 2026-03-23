<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Update Moodle Course URLs";
$conn = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skillId = intval($_POST['skill_id']);
    $moodleCourseUrl = trim($_POST['moodle_course_url']);
    
    if ($skillId > 0 && !empty($moodleCourseUrl)) {
        // Extract course ID from URL if provided
        preg_match('/id=(\d+)/', $moodleCourseUrl, $matches);
        $courseId = $matches[1] ?? null;
        
        $stmt = $conn->prepare("UPDATE skills SET moodle_course_url = ?, moodle_course_id = ? WHERE skill_id = ?");
        $stmt->bind_param("sii", $moodleCourseUrl, $courseId, $skillId);
        
        if ($stmt->execute()) {
            $success = "Course URL updated successfully!";
        } else {
            $error = "Failed to update: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get tutor's skills with current Moodle info
$tutorId = $_SESSION['user_id'];
$tutorSkills = [];
$stmt = $conn->prepare("
    SELECT 
        s.skill_id, 
        s.skill_name, 
        s.moodle_course_id,
        s.moodle_enrollment_key,
        s.moodle_course_url
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = ?
    ORDER BY s.skill_name
");
$stmt->bind_param("i", $tutorId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tutorSkills[] = $row;
}
$stmt->close();

closeDbConnection($conn);

include 'dashboard-header.php';
?>

<div style="max-width: 1000px; margin: 0 auto;">
    
    <div style="margin-bottom: 2rem;">
        <a href="tutor-verification.php" style="color: var(--primary-teal); text-decoration: none; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Verification
        </a>
    </div>

    <h1 style="margin-bottom: 1rem;">
        <i class="fas fa-link"></i> Update Moodle Course URLs
    </h1>

    <?php if (isset($success)): ?>
        <div style="background: #D1FAE5; border: 2px solid #10B981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <p style="margin: 0; color: #065F46;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div style="background: #FEE2E2; border: 2px solid #EF4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <p style="margin: 0; color: #991B1B;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <!-- Instructions -->
    <div style="background: #DBEAFE; border-left: 4px solid #3B82F6; padding: 1.5rem; margin-bottom: 2rem; border-radius: 0.5rem;">
        <h3 style="color: #1E40AF; margin: 0 0 1rem 0;">
            <i class="fas fa-info-circle"></i> How to Find Your Moodle Course URLs
        </h3>
        <ol style="margin: 0; padding-left: 1.5rem; color: #1E3A8A; line-height: 1.8;">
            <li>Go to your Moodle site: <strong>https://skillupnow.moodlecloud.com</strong></li>
            <li>Log in to Moodle</li>
            <li>Find the course for this skill</li>
            <li>Click on the course to open it</li>
            <li>Copy the URL from the address bar</li>
            <li>Paste it in the field below</li>
            <li>The URL should look like: <code>https://skillupnow.moodlecloud.com/course/view.php?id=X</code></li>
        </ol>
    </div>

    <!-- Skills List -->
    <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">Your Skills</h2>

        <?php if (empty($tutorSkills)): ?>
            <p style="color: var(--gray-600); text-align: center; padding: 2rem;">
                No skills found. Please add skills in your profile first.
            </p>
        <?php else: ?>
            <div style="display: grid; gap: 1.5rem;">
                <?php foreach ($tutorSkills as $skill): ?>
                    <div style="border: 2px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem;">
                        <h3 style="margin: 0 0 1rem 0; color: var(--gray-900);">
                            <?= htmlspecialchars($skill['skill_name']) ?>
                        </h3>

                        <form method="POST" style="display: grid; gap: 1rem;">
                            <input type="hidden" name="skill_id" value="<?= $skill['skill_id'] ?>">

                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                    Enrollment Key:
                                </label>
                                <code style="background: #F3F4F6; padding: 0.5rem; display: inline-block; border-radius: 0.25rem;">
                                    <?= htmlspecialchars($skill['moodle_enrollment_key']) ?>
                                </code>
                            </div>

                            <div>
                                <label for="url_<?= $skill['skill_id'] ?>" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                    Current Moodle Course URL:
                                </label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input 
                                        type="url" 
                                        id="url_<?= $skill['skill_id'] ?>" 
                                        name="moodle_course_url" 
                                        value="<?= htmlspecialchars($skill['moodle_course_url'] ?? '') ?>"
                                        placeholder="https://skillupnow.moodlecloud.com/course/view.php?id=X"
                                        style="flex: 1; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <?php if (!empty($skill['moodle_course_url'])): ?>
                                    <a href="<?= htmlspecialchars($skill['moodle_course_url']) ?>" target="_blank" style="font-size: 0.875rem; color: #3B82F6; text-decoration: none; margin-top: 0.5rem; display: inline-block;">
                                        <i class="fas fa-external-link-alt"></i> Test this URL
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($skill['moodle_course_url'])): ?>
                                <div style="background: #F9FAFB; padding: 1rem; border-radius: 0.5rem;">
                                    <p style="margin: 0; font-size: 0.875rem; color: var(--gray-600);">
                                        <strong>Current Course ID:</strong> 
                                        <?php 
                                        preg_match('/id=(\d+)/', $skill['moodle_course_url'], $matches);
                                        echo $matches[1] ?? 'Not found in URL';
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Help Section -->
    <div style="background: #FEF3C7; border: 2px solid #F59E0B; padding: 1.5rem; border-radius: 0.75rem; margin-top: 2rem;">
        <h3 style="color: #92400E; margin: 0 0 1rem 0;">
            <i class="fas fa-question-circle"></i> Need Help Finding Course IDs?
        </h3>
        <p style="color: #78350F; margin: 0 0 1rem 0;">
            If you don't have courses created in Moodle yet, you'll need to:
        </p>
        <ol style="margin: 0; padding-left: 1.5rem; color: #78350F; line-height: 1.6;">
            <li>Log in to your Moodle admin panel</li>
            <li>Create a course for each skill</li>
            <li>Add quizzes/exams to each course</li>
            <li>Note down the course URL after creating it</li>
            <li>Come back here and update the URLs</li>
        </ol>
    </div>

</div>

<?php include 'dashboard-footer.php'; ?>