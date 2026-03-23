<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$pageTitle = "My Profile";

$conn = getDbConnection();
$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];

// Get user data (ALL fields are in users table for your schema)
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get all skills grouped by category (ONLY AVAILABLE SKILLS WITH MOODLE COURSES)
$skills = [];
$stmt = $conn->query("
    SELECT skill_id, skill_name, skill_category, skill_subcategory 
    FROM skills 
    WHERE is_available = 1 AND moodle_course_id IS NOT NULL
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

closeDbConnection($conn);

$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_errors']);

include 'dashboard-header.php';
?>

<!-- Success Message -->
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

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
        <ul style="margin: 0; padding-left: 1.5rem; color: #991B1B;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div style="max-width: 1200px; margin: 0 auto;">
    
    <form action="save-profile.php" method="POST" id="profileForm">
        
        <!-- Profile Header Section -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <div style="display: flex; align-items: start; gap: 2rem; flex-wrap: wrap;">
                <!-- Avatar -->
                <div style="text-align: center;">
                    <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; color: white; margin-bottom: 1rem;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <p style="margin: 0; font-size: 0.875rem; color: var(--gray-600);">
                        <?= htmlspecialchars($user['custom_user_id']) ?>
                    </p>
                </div>
                
                <!-- User Info -->
                <div style="flex: 1;">
                    <h1 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($user['full_name']) ?></h1>
                    <p style="color: var(--gray-600); margin-bottom: 0.5rem;">
                        <i class="fas fa-user"></i> <?= $userType === 'learner' ? 'Student' : 'Tutor' ?>
                    </p>
                    <p style="color: var(--gray-600); margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                    </p>
                    <p style="color: var(--gray-600); margin-bottom: 0.5rem;">
                        <i class="fas fa-university"></i> <?= htmlspecialchars($user['college_name']) ?>
                    </p>
                    <p style="color: var(--gray-600); margin: 0;">
                        <i class="fas fa-calendar"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem;">
                <i class="fas fa-user-edit"></i> Basic Information
            </h2>
            
            <div style="display: grid; gap: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            Full Name <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                            value="<?= htmlspecialchars($user['full_name']) ?>"
                            minlength="3" maxlength="100"
                            pattern="[A-Za-z\s]+"
                            title="Only letters and spaces allowed"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);">
                        <span style="font-size: 0.75rem; color: var(--gray-500);">Only letters and spaces (3-100 characters)</span>
                    </div>

                    <div>
                        <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            Username <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="text" id="username" name="username" required
                            value="<?= htmlspecialchars($user['username']) ?>"
                            minlength="4" maxlength="20"
                            pattern="[A-Za-z0-9_]+"
                            title="Only letters, numbers, and underscores"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);">
                        <span style="font-size: 0.75rem; color: var(--gray-500);">4-20 characters (letters, numbers, _)</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone"
                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                            pattern="[0-9]{10}"
                            maxlength="10"
                            title="Enter 10-digit phone number"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                            placeholder="9876543210">
                        <span style="font-size: 0.75rem; color: var(--gray-500);">10 digits only (no spaces or +91)</span>
                    </div>

                    <div>
                        <label for="year_of_study" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            Year of Study
                        </label>
                        <select id="year_of_study" name="year_of_study"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);">
                            <option value="">Select Year</option>
                            <option value="1" <?= ($user['year_of_study'] ?? '') == '1' ? 'selected' : '' ?>>1st Year</option>
                            <option value="2" <?= ($user['year_of_study'] ?? '') == '2' ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3" <?= ($user['year_of_study'] ?? '') == '3' ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4" <?= ($user['year_of_study'] ?? '') == '4' ? 'selected' : '' ?>>4th Year</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="department" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                        Department/Branch
                    </label>
                    <input type="text" id="department" name="department"
                        value="<?= htmlspecialchars($user['department'] ?? '') ?>"
                        maxlength="100"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                        placeholder="e.g., Computer Science, Electronics">
                    <span style="font-size: 0.75rem; color: var(--gray-500);">Maximum 100 characters</span>
                </div>

                <div>
                    <label for="bio" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                        Bio <span style="color: var(--gray-500); font-weight: normal;">(Tell us about yourself)</span>
                    </label>
                    <textarea id="bio" name="bio" rows="4"
                        maxlength="500"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); resize: vertical;"
                        placeholder="I am a <?= $userType === 'learner' ? 'student' : 'tutor' ?> passionate about..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    <span style="font-size: 0.75rem; color: var(--gray-500);" id="bio-count">0 / 500 characters</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div>
                        <label for="linkedin_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            <i class="fab fa-linkedin"></i> LinkedIn Profile
                        </label>
                        <input type="url" id="linkedin_url" name="linkedin_url"
                            value="<?= htmlspecialchars($user['linkedin_url'] ?? '') ?>"
                            pattern="https?://.*linkedin\.com/.*"
                            title="Enter valid LinkedIn URL"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                            placeholder="https://linkedin.com/in/yourprofile">
                        <span style="font-size: 0.75rem; color: var(--gray-500);">Must be a valid LinkedIn URL</span>
                    </div>

                    <div>
                        <label for="github_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            <i class="fab fa-github"></i> GitHub Profile
                        </label>
                        <input type="url" id="github_url" name="github_url"
                            value="<?= htmlspecialchars($user['github_url'] ?? '') ?>"
                            pattern="https?://.*github\.com/.*"
                            title="Enter valid GitHub URL"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                            placeholder="https://github.com/yourusername">
                        <span style="font-size: 0.75rem; color: var(--gray-500);">Must be a valid GitHub URL</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills Selection -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="margin-bottom: 0.5rem;">
                <i class="fas fa-star"></i> 
                <?= $userType === 'learner' ? 'Skills I Want to Learn' : 'Skills I Can Teach' ?>
                <span style="color: #DC2626;">*</span>
            </h2>
            <p style="color: var(--gray-600); margin-bottom: 1.5rem; font-size: 0.875rem;">
                Select at least 3 skills
            </p>

            <!-- Category Tabs -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--gray-200); flex-wrap: wrap;">
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

        <!-- Submit Buttons -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="<?= $userType === 'learner' ? 'student-dashboard.php' : 'tutor-dashboard.php' ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Profile
                </button>
            </div>
        </div>
    </form>

</div>

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

input:invalid, textarea:invalid, select:invalid {
    border-color: #FCA5A5 !important;
}

input:valid, textarea:valid {
    border-color: #10B981 !important;
}
</style>

<script>
// Bio character count
const bioTextarea = document.getElementById('bio');
const bioCount = document.getElementById('bio-count');

function updateBioCount() {
    const length = bioTextarea.value.length;
    bioCount.textContent = `${length} / 500 characters`;
    if (length > 500) {
        bioCount.style.color = '#DC2626';
    } else if (length > 400) {
        bioCount.style.color = '#F59E0B';
    } else {
        bioCount.style.color = 'var(--gray-500)';
    }
}

bioTextarea.addEventListener('input', updateBioCount);
updateBioCount();

// Phone number validation
const phoneInput = document.getElementById('phone');
phoneInput.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
});

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
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const count = document.querySelectorAll('input[name="skills[]"]:checked').length;
    if (count < 3) {
        e.preventDefault();
        alert('Please select at least 3 skills');
        document.getElementById('selected-count').scrollIntoView({ behavior: 'smooth' });
        return false;
    }
    
    // Validate phone if filled
    const phone = phoneInput.value;
    if (phone && phone.length !== 10) {
        e.preventDefault();
        alert('Phone number must be exactly 10 digits');
        phoneInput.focus();
        return false;
    }
    
    return true;
});
</script>

<?php include 'dashboard-footer.php'; ?>