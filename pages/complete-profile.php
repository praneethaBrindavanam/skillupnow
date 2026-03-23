<?php
require_once '../includes/config.php';
startSession();

if (!isLoggedIn()) {
    redirect('signin.php');
}

$pageTitle = "Complete Your Profile";

$conn = getDbConnection();
$userId = getCurrentUserId();
$userType = getCurrentUserType();

// Get all skills grouped by category
$skills = [];
$stmt = $conn->query("
    SELECT skill_id, skill_name, skill_category, skill_subcategory 
    FROM skills 
    ORDER BY skill_category, skill_subcategory, skill_name
");
while ($row = $stmt->fetch_assoc()) {
    $category = $row['skill_category'];
    $subcategory = $row['skill_subcategory'];
    if (!isset($skills[$category])) {
        $skills[$category] = [];
    }
    if (!isset($skills[$category][$subcategory])) {
        $skills[$category][$subcategory] = [];
    }
    $skills[$category][$subcategory][] = $row;
}

// Get user's current skills
$userSkills = [];
$stmt = $conn->prepare("SELECT skill_id FROM user_skills WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $userSkills[] = $row['skill_id'];
}
$stmt->close();

// Get current profile if exists
$profile = null;
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}
$stmt->close();

closeDbConnection($conn);

$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_errors']);

include '../includes/header-logged-in.php';
?>

<section class="section" style="padding: 4rem 2rem;">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto;">
            
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="margin-bottom: 0.5rem;">Complete Your Profile</h1>
                <p style="color: var(--gray-600);">
                    <?= $userType === 'learner' ? 'Select the skills you want to learn' : 'Select the skills you can teach' ?>
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
                    <ul style="margin: 0; padding-left: 1.5rem; color: #991B1B;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="save-profile.php" method="POST" style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);">
                
                <!-- Basic Info -->
                <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid var(--gray-200);">
                    <h3 style="margin-bottom: 1.5rem;">Basic Information</h3>
                    
                    <div style="display: grid; gap: 1.5rem;">
                        <div>
                            <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                                Phone Number
                            </label>
                            <input type="tel" id="phone" name="phone" 
                                value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"
                                style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                                placeholder="9876543210">
                        </div>

                        <div>
                            <label for="bio" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                                Bio <span style="color: var(--gray-500); font-weight: normal;">(Tell us about yourself)</span>
                            </label>
                            <textarea id="bio" name="bio" rows="4"
                                style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); resize: vertical;"
                                placeholder="I am a student interested in..."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Skills Selection -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 0.5rem;">
                        <?= $userType === 'learner' ? 'What do you want to learn?' : 'What can you teach?' ?>
                        <span style="color: #DC2626;">*</span>
                    </h3>
                    <p style="color: var(--gray-600); margin-bottom: 1.5rem; font-size: 0.875rem;">
                        Select at least 3 skills
                    </p>

                    <!-- Category Tabs -->
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--gray-200);">
                        <button type="button" class="category-tab active" data-category="all"
                            style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid var(--primary-cyan); color: var(--primary-teal); font-weight: 600; cursor: pointer;">
                            All Skills
                        </button>
                        <button type="button" class="category-tab" data-category="technical"
                            style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: var(--gray-600); font-weight: 600; cursor: pointer;">
                            Technical
                        </button>
                        <button type="button" class="category-tab" data-category="academic"
                            style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: var(--gray-600); font-weight: 600; cursor: pointer;">
                            Academic
                        </button>
                    </div>

                    <div id="skills-container">
                        <?php foreach ($skills as $category => $subcategories): ?>
                            <?php foreach ($subcategories as $subcategory => $skillsList): ?>
                                <div class="skill-group" data-category="<?= $category ?>" style="margin-bottom: 2rem;">
                                    <h4 style="color: var(--primary-teal); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas <?= $category === 'technical' ? 'fa-code' : 'fa-graduation-cap' ?>"></i>
                                        <?= htmlspecialchars($subcategory) ?>
                                    </h4>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                                        <?php foreach ($skillsList as $skill): ?>
                                            <label class="skill-checkbox" style="cursor: pointer;">
                                                <input type="checkbox" name="skills[]" value="<?= $skill['skill_id'] ?>"
                                                    <?= in_array($skill['skill_id'], $userSkills) ? 'checked' : '' ?>
                                                    style="display: none;">
                                                <span style="display: inline-block; padding: 0.75rem 1.25rem; border: 2px solid var(--gray-200); border-radius: var(--radius-full); transition: all 0.2s;">
                                                    <?= htmlspecialchars($skill['skill_name']) ?>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>

                    <div id="selected-count" style="background: var(--gray-100); padding: 1rem; border-radius: var(--radius-md); text-align: center; font-weight: 600; color: var(--gray-700);">
                        Selected: <span id="count">0</span> skills (minimum 3 required)
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="<?= $userType === 'learner' ? 'student-dashboard.php' : 'tutor-dashboard.php' ?>" class="btn btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Save Profile & Continue
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</section>

<style>
.skill-checkbox input:checked + span {
    background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
    color: white;
    border-color: var(--primary-teal);
}

.skill-checkbox span:hover {
    border-color: var(--primary-cyan);
    transform: translateY(-2px);
}

.category-tab.active {
    border-bottom-color: var(--primary-cyan) !important;
    color: var(--primary-teal) !important;
}

.category-tab:hover {
    color: var(--primary-teal);
}
</style>

<script>
// Count selected skills
function updateCount() {
    const count = document.querySelectorAll('input[name="skills[]"]:checked').length;
    document.getElementById('count').textContent = count;
    
    const countDiv = document.getElementById('selected-count');
    if (count < 3) {
        countDiv.style.background = '#FEE2E2';
        countDiv.style.color = '#991B1B';
    } else {
        countDiv.style.background = '#D1FAE5';
        countDiv.style.color = '#065F46';
    }
}

// Category filter
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        document.querySelectorAll('.skill-group').forEach(group => {
            if (category === 'all' || group.dataset.category === category) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    });
});

// Initialize count
document.querySelectorAll('input[name="skills[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateCount);
});

updateCount();

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const count = document.querySelectorAll('input[name="skills[]"]:checked').length;
    if (count < 3) {
        e.preventDefault();
        alert('Please select at least 3 skills');
        document.getElementById('selected-count').scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<?php include '../includes/footer.php'; ?>