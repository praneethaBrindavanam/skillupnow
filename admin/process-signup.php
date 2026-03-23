<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit();
}

// Function to redirect with errors
function redirectWithError($errors, $formData) {
    $_SESSION['admin_errors'] = $errors;
    $_SESSION['admin_signup_data'] = $formData;
    header("Location: signup.php");
    exit();
}

// Validate CAPTCHA first
$captcha_input = strtoupper(trim($_POST['captcha'] ?? ''));
$captcha_session = $_SESSION['captcha_code'] ?? '';

if (empty($captcha_input) || $captcha_input !== $captcha_session) {
    redirectWithError(['Verification code is incorrect. Please try again.'], $_POST);
}

// Clear CAPTCHA from session
unset($_SESSION['captcha_code']);

// Get form data
$fullName = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$adminRole = $_POST['admin_role'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

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

if (!in_array($adminRole, ['admin', 'superadmin'])) {
    $errors[] = "Please select a valid admin role";
}

// Password validation
if (empty($password) || strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
} else {
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match";
}

if (!empty($errors)) {
    redirectWithError($errors, [
        'full_name' => $fullName,
        'username' => $username,
        'email' => $email,
        'admin_role' => $adminRole
    ]);
}

// Database connection
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("DB error: " . $e->getMessage());
    redirectWithError(['Database error. Please try again.'], [
        'full_name' => $fullName,
        'username' => $username,
        'email' => $email,
        'admin_role' => $adminRole
    ]);
}

// Check if username already exists
try {
    $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $conn->close();
        redirectWithError(['Username already taken. Please choose another.'], [
            'full_name' => $fullName,
            'email' => $email,
            'admin_role' => $adminRole
        ]);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Username check error: " . $e->getMessage());
    $conn->close();
    redirectWithError(['Database error.'], [
        'full_name' => $fullName,
        'username' => $username,
        'email' => $email,
        'admin_role' => $adminRole
    ]);
}

// Check if email already exists
try {
    $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $conn->close();
        redirectWithError(['Email already registered. Please sign in.'], [
            'full_name' => $fullName,
            'username' => $username,
            'admin_role' => $adminRole
        ]);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Email check error: " . $e->getMessage());
    $conn->close();
    redirectWithError(['Database error.'], [
        'full_name' => $fullName,
        'username' => $username,
        'email' => $email,
        'admin_role' => $adminRole
    ]);
}

$conn->close();

// Generate OTP
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Store data in session for OTP verification
$_SESSION['pending_admin_signup'] = [
    'full_name' => $fullName,
    'username' => $username,
    'email' => $email,
    'admin_role' => $adminRole,
    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
    'otp' => $otp,
    'otp_created' => time()
];

// Send OTP email
require_once __DIR__ . '/../includes/email-config.php';

$emailSent = sendOTPEmail($email, $fullName, $otp, 'admin-signup');

if (!$emailSent) {
    error_log("Failed to send admin OTP to: $email");
}

// Redirect to OTP verification
header("Location: verify-admin-otp.php");
exit();
?>