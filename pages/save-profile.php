<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];
$conn = getDbConnection();

$errors = [];

// Validate and sanitize inputs
$fullName = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$yearOfStudy = trim($_POST['year_of_study'] ?? '');
$department = trim($_POST['department'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$linkedinUrl = trim($_POST['linkedin_url'] ?? '');
$githubUrl = trim($_POST['github_url'] ?? '');
$skills = $_POST['skills'] ?? [];

// Convert empty strings to NULL for optional fields
$phone = empty($phone) ? null : $phone;
$yearOfStudy = empty($yearOfStudy) ? null : intval($yearOfStudy);
$department = empty($department) ? null : $department;
$bio = empty($bio) ? null : $bio;
$linkedinUrl = empty($linkedinUrl) ? null : $linkedinUrl;
$githubUrl = empty($githubUrl) ? null : $githubUrl;

// Validation
if (empty($fullName)) {
    $errors[] = "Full name is required";
} elseif (!preg_match("/^[A-Za-z\s]{3,100}$/", $fullName)) {
    $errors[] = "Full name must be 3-100 characters and contain only letters and spaces";
}

if (empty($username)) {
    $errors[] = "Username is required";
} elseif (!preg_match("/^[A-Za-z0-9_]{4,20}$/", $username)) {
    $errors[] = "Username must be 4-20 characters (letters, numbers, underscore only)";
} else {
    // Check if username already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->bind_param("si", $username, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Username already taken";
    }
    $stmt->close();
}

if ($phone !== null && !preg_match("/^[0-9]{10}$/", $phone)) {
    $errors[] = "Phone number must be exactly 10 digits";
}

if ($department !== null && strlen($department) > 100) {
    $errors[] = "Department must be less than 100 characters";
}

if ($bio !== null && strlen($bio) > 500) {
    $errors[] = "Bio must be less than 500 characters";
}

if ($linkedinUrl !== null) {
    if (!filter_var($linkedinUrl, FILTER_VALIDATE_URL) || !preg_match("/linkedin\.com/i", $linkedinUrl)) {
        $errors[] = "LinkedIn URL must be a valid LinkedIn profile link";
    }
}

if ($githubUrl !== null) {
    if (!filter_var($githubUrl, FILTER_VALIDATE_URL) || !preg_match("/github\.com/i", $githubUrl)) {
        $errors[] = "GitHub URL must be a valid GitHub profile link";
    }
}

// Skills validation
if (empty($skills) || count($skills) < 3) {
    $errors[] = "Please select at least 3 skills";
}

// If there are errors, redirect back
if (!empty($errors)) {
    $_SESSION['profile_errors'] = $errors;
    header("Location: profile.php");
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // Check if all columns exist in users table
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'department'");
    $departmentExists = $result->num_rows > 0;
    
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'year_of_study'");
    $yearExists = $result->num_rows > 0;
    
    // Build UPDATE query based on existing columns
    if ($departmentExists && $yearExists) {
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name = ?,
                username = ?,
                phone = ?,
                bio = ?,
                linkedin_url = ?,
                github_url = ?,
                department = ?,
                year_of_study = ?,
                profile_completed = 1,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        $stmt->bind_param(
            "ssssssiii",
            $fullName,
            $username,
            $phone,
            $bio,
            $linkedinUrl,
            $githubUrl,
            $department,
            $yearOfStudy,
            $userId
        );
    } else {
        // Fallback if columns don't exist
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name = ?,
                username = ?,
                phone = ?,
                bio = ?,
                linkedin_url = ?,
                github_url = ?,
                profile_completed = 1,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        $stmt->bind_param(
            "ssssssi",
            $fullName,
            $username,
            $phone,
            $bio,
            $linkedinUrl,
            $githubUrl,
            $userId
        );
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update profile: " . $stmt->error);
    }
    $stmt->close();
    
    // Update session name if changed
    $_SESSION['user_name'] = $fullName;
    $_SESSION['username'] = $username;
    
    // Delete existing skills
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete old skills: " . $stmt->error);
    }
    $stmt->close();
    
    // Insert new skills
    $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id, created_at) VALUES (?, ?, NOW())");
    foreach ($skills as $skillId) {
        $skillId = intval($skillId);
        if ($skillId > 0) {  // Only insert valid skill IDs
            $stmt->bind_param("ii", $userId, $skillId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert skill: " . $stmt->error);
            }
        }
    }
    $stmt->close();
    
    // If tutor, try to create skill verification entries (only if table exists)
    if ($userType === 'tutor') {
        $result = $conn->query("SHOW TABLES LIKE 'skill_verifications'");
        if ($result->num_rows > 0) {
            // Table exists, create verification entries
            $stmt = $conn->prepare("
                INSERT IGNORE INTO skill_verifications (user_id, skill_id, created_at)
                VALUES (?, ?, NOW())
            ");
            foreach ($skills as $skillId) {
                $skillId = intval($skillId);
                if ($skillId > 0) {
                    $stmt->bind_param("ii", $userId, $skillId);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }
        // If table doesn't exist, silently skip (no error)
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['profile_success'] = "Your profile has been updated successfully! " . count($skills) . " skills saved.";
    
    // Redirect based on user type
    if ($userType === 'tutor') {
        header("Location: tutor-dashboard.php");
    } else {
        header("Location: student-dashboard.php");
    }
    
} catch (Exception $e) {
    $conn->rollback();
    
    // Log the actual error for debugging
    error_log("Profile save error for user $userId: " . $e->getMessage());
    
    $_SESSION['profile_errors'] = [
        "An error occurred while saving your profile: " . $e->getMessage()
    ];
    header("Location: profile.php");
}

closeDbConnection($conn);
exit();
?>