<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

// Function to redirect with error
function redirectWithError($errors, $email = '') {
    $_SESSION['signin_errors'] = $errors;
    $_SESSION['signin_email'] = $email;
    header("Location: signin.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError(['Invalid request method']);
}

// Verify CAPTCHA first
$captcha_input = strtoupper(trim($_POST['captcha'] ?? ''));
$captcha_session = $_SESSION['captcha_code'] ?? '';

if (empty($captcha_input)) {
    redirectWithError(['Verification code is required'], $_POST['email'] ?? '');
}

if ($captcha_input !== $captcha_session) {
    redirectWithError(['Verification code is incorrect. Please try again.'], $_POST['email'] ?? '');
}

// Clear used CAPTCHA
unset($_SESSION['captcha_code']);

// Get and sanitize form data
$email = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
$errors = [];

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

if (empty($password)) {
    $errors[] = "Password is required";
}

// If validation fails
if (!empty($errors)) {
    redirectWithError($errors, $email);
}

// Connect to database
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    redirectWithError(['Unable to connect to database. Please try again later.'], $email);
}

try {
    // Get user from database
    $stmt = $conn->prepare("
        SELECT 
            user_id,
            custom_user_id,
            username,
            full_name, 
            email, 
            password_hash, 
            user_type, 
            is_verified, 
            is_active,
            college_name
        FROM users 
        WHERE email = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User not found
        $stmt->close();
        $conn->close();
        redirectWithError(["Invalid email or password"], $email);
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        $conn->close();
        redirectWithError(["Invalid email or password"], $email);
    }
    
    // Check if account is active
    if ($user['is_active'] == 0) {
        $conn->close();
        redirectWithError(["Your account has been deactivated. Please contact support."], $email);
    }
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    if ($updateStmt) {
        $updateStmt->bind_param("i", $user['user_id']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    $conn->close();
    
    // Generate OTP and store in SESSION (not database!)
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Send OTP email
    require_once __DIR__ . '/../includes/email-config.php';
    $emailSent = sendOTPEmail($user['email'], $user['full_name'], $otp, 'signin');
    
    if (!$emailSent) {
        error_log("Failed to send OTP to: " . $user['email']);
    }
    
    // Store everything in session
    $_SESSION['pending_signin'] = [
        'user_id' => $user['user_id'],
        'custom_user_id' => $user['custom_user_id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'user_type' => $user['user_type'],
        'is_verified' => $user['is_verified'],
        'college_name' => $user['college_name'],
        'remember' => $remember,
        'otp' => $otp,
        'otp_created' => time()
    ];
    
    header("Location: verify-otp.php");
    exit();
    
} catch (Exception $e) {
    error_log("Sign in error: " . $e->getMessage());
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    redirectWithError(['An error occurred. Please try again later.'], $email);
}
?>