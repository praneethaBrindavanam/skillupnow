<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if Google signup data exists
if (!isset($_SESSION['google_signup_data'])) {
    header('Location: signup.php');
    exit();
}

$googleData = $_SESSION['google_signup_data'];

// Database config
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

function redirectWithError($errors, $formData) {
    $_SESSION['google_profile_errors'] = $errors;
    $_SESSION['google_profile_data'] = $formData;
    header("Location: complete-google-profile.php");
    exit();
}

// Get form data
$username = trim($_POST['username'] ?? '');
$college = $_POST['college'] ?? '';
$accountType = $_POST['account_type'] ?? 'learner';

// Validation
$errors = [];

if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
    $errors[] = "Username must be 4-20 characters (letters, numbers, underscore only)";
}

if (empty($college)) {
    $errors[] = "Please select your college";
}

if (!in_array($accountType, ['learner', 'tutor'])) {
    $errors[] = "Invalid account type";
}

if (!empty($errors)) {
    redirectWithError($errors, [
        'username' => $username,
        'college' => $college,
        'account_type' => $accountType
    ]);
}

// Connect to database
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    redirectWithError(['Database error. Please try again.'], $_POST);
}

// Check if username exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    $conn->close();
    redirectWithError(['Username already taken. Please choose another.'], [
        'college' => $college,
        'account_type' => $accountType
    ]);
}
$stmt->close();

// Generate custom user ID
function generateUserId($conn, $accountType) {
    $prefix = ($accountType === 'tutor') ? 'SNT' : 'SNU';
    
    $sql = "SELECT custom_user_id FROM users WHERE custom_user_id LIKE ? ORDER BY user_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $pattern = $prefix . '_%';
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['custom_user_id'];
        $number = intval(substr($lastId, 4)) + 1;
    } else {
        $number = 1;
    }
    
    $stmt->close();
    
    return $prefix . '_' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

try {
    $customUserId = generateUserId($conn, $accountType);
    $conn->close();
    
    // For Google users, password_hash can be empty or a placeholder
    $passwordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);
    
    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store everything in session for OTP verification
    $_SESSION['pending_google_signup'] = [
        'custom_user_id' => $customUserId,
        'username' => $username,
        'google_id' => $googleData['google_id'],
        'full_name' => $googleData['full_name'],
        'email' => $googleData['email'],
        'password_hash' => $passwordHash,
        'college' => $college,
        'account_type' => $accountType,
        'profile_picture' => $googleData['profile_picture'],
        'otp' => $otp,
        'otp_created' => time()
    ];
    
    // Send OTP email
    require_once __DIR__ . '/../includes/email-config.php';
    $emailSent = sendOTPEmail($googleData['email'], $googleData['full_name'], $otp, 'signup');
    
    if (!$emailSent) {
        error_log("Failed to send OTP to: " . $googleData['email']);
    }
    
    // Clear Google signup data
    unset($_SESSION['google_signup_data']);
    unset($_SESSION['google_profile_errors']);
    unset($_SESSION['google_profile_data']);
    
    // Redirect to OTP verification
    header("Location: verify-otp.php");
    exit();
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    if (isset($conn)) $conn->close();
    
    redirectWithError(['An error occurred. Please try again.'], $_POST);
}
?>