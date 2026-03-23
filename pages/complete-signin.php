<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_signin'])) {
    header("Location: signin.php");
    exit();
}

$pending = $_SESSION['pending_signin'];
$otp_input = trim($_POST['otp'] ?? '');

// Validate OTP from session (not database!)
$otp_correct = $pending['otp'] ?? '';
$otp_created = $pending['otp_created'] ?? 0;
$otp_age = time() - $otp_created;

// Check if OTP expired (10 minutes = 600 seconds)
if ($otp_age > 600) {
    unset($_SESSION['pending_signin']);
    $_SESSION['signin_errors'] = ['OTP expired. Please sign in again.'];
    header("Location: signin.php");
    exit();
}

// Check if OTP matches
if ($otp_input !== $otp_correct) {
    $_SESSION['otp_errors'] = ['Invalid code. Please try again.'];
    error_log("OTP mismatch - Input: [$otp_input] vs Correct: [$otp_correct]");
    header("Location: verify-signin-otp.php");
    exit();
}

// OTP is correct! Log user in
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) throw new Exception("Connection failed");
    $conn->set_charset("utf8mb4");
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $pending['user_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Last login update error line " . __LINE__ . ": " . $e->getMessage());
}

// Set session variables
$_SESSION['user_id'] = $pending['user_id'];
$_SESSION['custom_user_id'] = $pending['custom_user_id'];
$_SESSION['username'] = $pending['username'];
$_SESSION['user_email'] = $pending['email'];
$_SESSION['user_name'] = $pending['full_name'];
$_SESSION['user_type'] = $pending['user_type'];
$_SESSION['is_verified'] = $pending['is_verified'];
$_SESSION['college_name'] = $pending['college_name'];
$_SESSION['signin_success'] = true;

// Clear pending data
unset($_SESSION['pending_signin']);
unset($_SESSION['signin_errors']);
unset($_SESSION['otp_errors']);

// Remember me cookie
if ($pending['remember']) {
    $token = bin2hex(random_bytes(32));
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    setcookie('remember_user', $pending['user_id'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
}

// Redirect to dashboard
if ($pending['user_type'] === 'tutor') {
    header("Location: tutor-dashboard.php");
} else {
    header("Location: student-dashboard.php");
}
exit();
?>