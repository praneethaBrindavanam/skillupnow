<?php
session_start();
require_once 'admin-config.php';

requireAdminLogin();

$userId = intval($_GET['id'] ?? 0);
$currentAdminId = getCurrentAdminId();
$currentAdminRole = getCurrentAdminRole();

if ($userId <= 0) {
    $_SESSION['error'] = "Invalid tutor ID.";
    header("Location: manage-tutors.php");
    exit();
}

$conn = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $collegeName = trim($_POST['college_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Profile fields
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    // Social links
    $githubUrl = trim($_POST['github_url'] ?? '');
    $linkedinUrl = trim($_POST['linkedin_url'] ?? '');
    $portfolioUrl = trim($_POST['portfolio_url'] ?? '');
    
    // Tutor-specific fields
    $hourlyRate = trim($_POST['hourly_rate'] ?? '');
    $yearsOfExperience = trim($_POST['years_of_experience'] ?? '');
    $availabilityStatus = $_POST['availability_status'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($fullName) || strlen($fullName) < 3) {
        $errors[] = "Full name must be at least 3 characters";
    }
    
    if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $errors[] = "Username must be 4-20 characters (letters, numbers, underscore only)";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($collegeName)) {
        $errors[] = "College name is required";
    }
    
    // Phone validation - 10 digits, numbers only
    if (!empty($phone) && !preg_match('/^\d{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits (numbers only)";
    }
    
    // Pincode validation - 6 digits, numbers only
    if (!empty($pincode) && !preg_match('/^\d{6}$/', $pincode)) {
        $errors[] = "Pincode must be exactly 6 digits (numbers only)";
    }
    
    // Hourly rate validation
    if (!empty($hourlyRate) && (!is_numeric($hourlyRate) || $hourlyRate < 0)) {
        $errors[] = "Hourly rate must be a valid positive number";
    }
    
    // Years of experience validation
    if (!empty($yearsOfExperience) && (!is_numeric($yearsOfExperience) || $yearsOfExperience < 0 || $yearsOfExperience > 50)) {
        $errors[] = "Years of experience must be between 0 and 50";
    }
    
    if (empty($errors)) {
        try {
            // Check if username/email already exists (excluding current student)
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
            $stmt->bind_param("ssi", $username, $email, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Username or email already exists";
                $stmt->close();
            } else {
                $stmt->close();
                
                // Update users table
                if ($currentAdminRole === 'superadmin') {
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, college_name = ?, phone = ?, bio = ?, is_active = ?, updated_at = NOW() WHERE user_id = ? AND user_type = 'tutor'");
                    $stmt->bind_param("sssssiii", $fullName, $username, $email, $collegeName, $phone, $bio, $isActive, $userId);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, college_name = ?, phone = ?, bio = ?, updated_at = NOW() WHERE user_id = ? AND user_type = 'tutor'");
                    $stmt->bind_param("ssssssi", $fullName, $username, $email, $collegeName, $phone, $bio, $userId);
                }
                
                if ($stmt->execute()) {
                    $stmt->close();
                    
                    // Update or insert user_profiles
                    $stmt = $conn->prepare("SELECT profile_id FROM user_profiles WHERE user_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $profileExists = $stmt->get_result()->num_rows > 0;
                    $stmt->close();
                    
                    if ($profileExists) {
                        $stmt = $conn->prepare("UPDATE user_profiles SET date_of_birth = ?, gender = ?, address = ?, city = ?, state = ?, pincode = ?, country = ?, github_url = ?, linkedin_url = ?, portfolio_url = ?, bio = ?, hourly_rate = ?, years_of_experience = ?, availability_status = ? WHERE user_id = ?");
                        $stmt->bind_param("sssssssssssidsi", $dateOfBirth, $gender, $address, $city, $state, $pincode, $country, $githubUrl, $linkedinUrl, $portfolioUrl, $bio, $hourlyRate, $yearsOfExperience, $availabilityStatus, $userId);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, date_of_birth, gender, address, city, state, pincode, country, github_url, linkedin_url, portfolio_url, bio, hourly_rate, years_of_experience, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("isssssssssssids", $userId, $dateOfBirth, $gender, $address, $city, $state, $pincode, $country, $githubUrl, $linkedinUrl, $portfolioUrl, $bio, $hourlyRate, $yearsOfExperience, $availabilityStatus);
                    }
                    
                    $stmt->execute();
                    $stmt->close();
                    
                    logAdminActivity($currentAdminId, 'update', 'users', $userId, "Updated tutor: $username");
                    $_SESSION['success'] = "Tutor profile updated successfully";
                    
                    closeDbConnection($conn);
                    header("Location: view-tutor.php?id=$userId");
                    exit();
                } else {
                    $errors[] = "Failed to update tutor. Please try again.";
                    $stmt->close();
                }
            }
        } catch (Exception $e) {
            error_log("Edit tutor error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again.";
        }
    }
}

// Get tutor data
$stmt = $conn->prepare("
    SELECT 
        u.*,
        up.date_of_birth,
        up.gender,
        up.address,
        up.city,
        up.state,
        up.pincode,
        up.country,
        up.github_url,
        up.linkedin_url,
        up.portfolio_url,
        up.hourly_rate,
        up.years_of_experience,
        up.availability_status
    FROM users u
    LEFT JOIN user_profiles up ON u.user_id = up.user_id
    WHERE u.user_id = ? AND u.user_type = 'tutor'
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDbConnection($conn);
    $_SESSION['error'] = "Tutor not found.";
    header("Location: manage-tutors.php");
    exit();
}

$tutor = $result->fetch_assoc();
$stmt->close();

// Get tutor skills
$skills = [];
$stmt = $conn->prepare("SELECT skill_id, skill_name, proficiency_level FROM user_skills WHERE user_id = ? ORDER BY skill_name");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}
$stmt->close();

closeDbConnection($conn);

$pageTitle = "Edit Tutor";
include 'admin-header.php';
?>

<div style="max-width: 1000px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <a href="manage-tutors.php" style="color: var(--primary-teal); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Tutors
        </a>
        <h1 style="margin-bottom: 0.5rem;">
            <i class="fas fa-user-edit"></i> Edit Tutor Profile
        </h1>
        <p style="color: var(--gray-600); margin: 0;">
            Update profile information for <?= htmlspecialchars($tutor['full_name']) ?>
        </p>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: start; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle" style="color: #DC2626; margin-top: 0.125rem;"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <p style="margin: 0 0 0.25rem 0; color: #991B1B;"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <!-- Basic Information Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-user"></i> Basic Information
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                
                <!-- Tutor ID (Read-only) -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Tutor ID
                    </label>
                    <input type="text" value="<?= htmlspecialchars($tutor['custom_user_id']) ?>" disabled
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; background: var(--gray-100); color: var(--gray-600);">
                </div>
                
                <!-- Full Name -->
                <div>
                    <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Full Name <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                        value="<?= htmlspecialchars($tutor['full_name']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="Enter full name">
                </div>

                <!-- Username -->
                <div>
                    <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Username <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="username" name="username" required
                        value="<?= htmlspecialchars($tutor['username']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="4-20 characters">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        4-20 characters, letters, numbers and underscore only
                    </small>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Email Address <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        value="<?= htmlspecialchars($tutor['email']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="tutor@example.com">
                </div>

                <!-- College Name -->
                <div>
                    <label for="college_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        College Name <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="college_name" name="college_name" required
                        value="<?= htmlspecialchars($tutor['college_name']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="Enter college name">
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Phone Number
                    </label>
                    <input type="text" id="phone" name="phone"
                        value="<?= htmlspecialchars($tutor['phone'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="9876543210"
                        maxlength="10"
                        pattern="\d{10}">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        Exactly 10 digits, numbers only
                    </small>
                </div>
            </div>
        </div>

        <!-- Professional Information Section (Tutor-Specific) -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-briefcase"></i> Professional Information
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                
                <!-- Hourly Rate -->
                <div>
                    <label for="hourly_rate" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Hourly Rate (₹)
                    </label>
                    <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="1"
                        value="<?= htmlspecialchars($tutor['hourly_rate'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="500">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        Your hourly tutoring rate in rupees
                    </small>
                </div>

                <!-- Years of Experience -->
                <div>
                    <label for="years_of_experience" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Years of Experience
                    </label>
                    <input type="number" id="years_of_experience" name="years_of_experience" min="0" max="50" step="0.5"
                        value="<?= htmlspecialchars($tutor['years_of_experience'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="2">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        Total years of teaching/tutoring experience
                    </small>
                </div>

                <!-- Availability Status -->
                <div>
                    <label for="availability_status" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Availability Status
                    </label>
                    <select id="availability_status" name="availability_status"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;">
                        <option value="">Select Status</option>
                        <option value="available" <?= ($tutor['availability_status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="busy" <?= ($tutor['availability_status'] ?? '') === 'busy' ? 'selected' : '' ?>>Busy</option>
                        <option value="unavailable" <?= ($tutor['availability_status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        Current availability for new students
                    </small>
                </div>
            </div>
        </div>

        <!-- Profile Details Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-id-card"></i> Profile Details
            </h3>
            
            <div style="display: grid; gap: 1.5rem;">
                <!-- Bio -->
                <div>
                    <label for="bio" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Bio
                    </label>
                    <textarea id="bio" name="bio" rows="4"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; font-family: inherit; resize: vertical;"
                        placeholder="Tell us about yourself..."><?= htmlspecialchars($tutor['bio'] ?? '') ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Date of Birth
                        </label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                            value="<?= htmlspecialchars($tutor['date_of_birth'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Gender
                        </label>
                        <select id="gender" name="gender"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($tutor['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($tutor['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($tutor['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-map-marker-alt"></i> Address Information
            </h3>
            
            <div style="display: grid; gap: 1.5rem;">
                <!-- Street Address -->
                <div>
                    <label for="address" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Street Address
                    </label>
                    <textarea id="address" name="address" rows="3"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; font-family: inherit; resize: vertical;"
                        placeholder="Enter street address, apartment, suite, etc."><?= htmlspecialchars($tutor['address'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <!-- City -->
                    <div>
                        <label for="city" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            City
                        </label>
                        <input type="text" id="city" name="city"
                            value="<?= htmlspecialchars($tutor['city'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="Enter city">
                    </div>

                    <!-- State -->
                    <div>
                        <label for="state" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            State / Province
                        </label>
                        <input type="text" id="state" name="state"
                            value="<?= htmlspecialchars($tutor['state'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="Enter state">
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label for="pincode" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Pincode / ZIP
                        </label>
                        <input type="text" id="pincode" name="pincode"
                            value="<?= htmlspecialchars($tutor['pincode'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="123456"
                            maxlength="6"
                            pattern="\d{6}">
                        <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                            Exactly 6 digits, numbers only
                        </small>
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Country
                        </label>
                        <input type="text" id="country" name="country"
                            value="<?= htmlspecialchars($tutor['country'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="Enter country">
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Links Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-link"></i> Social Links
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <!-- GitHub URL -->
                <div>
                    <label for="github_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        <i class="fab fa-github"></i> GitHub URL
                    </label>
                    <input type="url" id="github_url" name="github_url"
                        value="<?= htmlspecialchars($tutor['github_url'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="https://github.com/username">
                </div>

                <!-- LinkedIn URL -->
                <div>
                    <label for="linkedin_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        <i class="fab fa-linkedin"></i> LinkedIn URL
                    </label>
                    <input type="url" id="linkedin_url" name="linkedin_url"
                        value="<?= htmlspecialchars($tutor['linkedin_url'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="https://linkedin.com/in/username">
                </div>

                <!-- Portfolio URL -->
                <div>
                    <label for="portfolio_url" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        <i class="fas fa-globe"></i> Portfolio URL
                    </label>
                    <input type="url" id="portfolio_url" name="portfolio_url"
                        value="<?= htmlspecialchars($tutor['portfolio_url'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="https://yourportfolio.com">
                </div>
            </div>
        </div>

        <!-- Skills Display (Read-only) -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-code"></i> Skills (<?= count($skills) ?>)
            </h3>
            
            <?php if (empty($skills)): ?>
                <p style="color: var(--gray-500); text-align: center; padding: 2rem; background: var(--gray-50); border-radius: var(--radius-md);">
                    No skills added yet. Tutor can add skills from their profile.
                </p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                    <?php foreach ($skills as $skill): ?>
                        <div style="padding: 1rem; background: var(--gray-50); border-radius: var(--radius-md); border: 2px solid var(--gray-200);">
                            <p style="margin: 0 0 0.5rem 0; font-weight: 600; color: var(--gray-700);">
                                <?= htmlspecialchars($skill['skill_name']) ?>
                            </p>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; background: var(--gray-200); height: 6px; border-radius: 3px; overflow: hidden;">
                                    <div style="background: var(--primary-teal); height: 100%; width: <?= $skill['proficiency_level'] ?>%;"></div>
                                </div>
                                <span style="font-size: 0.875rem; color: var(--gray-600); font-weight: 600;">
                                    <?= $skill['proficiency_level'] ?>%
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status (Super Admin only) -->
        <?php if ($currentAdminRole === 'superadmin'): ?>
            <div style="margin-bottom: 2.5rem;">
                <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-toggle-on"></i> Account Status
                </h3>
                
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 1rem; background: var(--gray-50); border-radius: var(--radius-md); border: 2px solid var(--gray-200); transition: all 0.2s;">
                    <input type="checkbox" name="is_active" value="1" <?= $tutor['is_active'] ? 'checked' : '' ?>
                        style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <span style="font-weight: 600; color: var(--gray-700); display: block;">
                            Account Active
                        </span>
                        <span style="font-size: 0.875rem; color: var(--gray-600);">
                            Inactive tutors cannot sign in to the system
                        </span>
                    </div>
                </label>
            </div>
        <?php endif; ?>

        <!-- Account Info -->
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
            <h4 style="margin: 0 0 1rem 0; color: var(--gray-700); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-info-circle"></i> Account Information
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; font-size: 0.875rem; color: var(--gray-600);">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-calendar" style="width: 20px;"></i> 
                    <span><strong>Created:</strong> <?= date('M d, Y', strtotime($tutor['created_at'])) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-clock" style="width: 20px;"></i> 
                    <span><strong>Updated:</strong> <?= date('M d, Y', strtotime($tutor['updated_at'])) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-sign-in-alt" style="width: 20px;"></i> 
                    <span><strong>Last Login:</strong> <?= $tutor['last_login'] ? date('M d, Y h:i A', strtotime($tutor['last_login'])) : 'Never' ?></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 2px solid var(--gray-200);">
            <a href="view-tutor.php?id=<?= $userId ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-save"></i> Update Tutor
            </button>
        </div>
    </form>

</div>

<script>
// Client-side validation for better UX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const phoneInput = document.getElementById('phone');
    const pincodeInput = document.getElementById('pincode');
    
    // Phone number validation - only allow digits
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });
    }
    
    // Pincode validation - only allow digits
    if (pincodeInput) {
        pincodeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let errors = [];
        
        // Phone validation
        if (phoneInput.value && !/^\d{10}$/.test(phoneInput.value)) {
            errors.push('Phone number must be exactly 10 digits');
        }
        
        // Pincode validation
        if (pincodeInput.value && !/^\d{6}$/.test(pincodeInput.value)) {
            errors.push('Pincode must be exactly 6 digits');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
        }
    });
});
</script>

<?php include 'admin-footer.php'; ?>