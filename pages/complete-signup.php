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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_signup'])) {
    header("Location: signup.php");
    exit();
}

$pending = $_SESSION['pending_signup'];
$otp_input = trim($_POST['otp'] ?? '');

// Validate OTP from session (not database!)
$otp_correct = $pending['otp'] ?? '';
$otp_created = $pending['otp_created'] ?? 0;
$otp_age = time() - $otp_created;

// Check if OTP expired (10 minutes = 600 seconds)
if ($otp_age > 600) {
    unset($_SESSION['pending_signup']);
    $_SESSION['signup_errors'] = ['OTP expired. Please sign up again.'];
    header("Location: signup.php");
    exit();
}

// Check if OTP matches
if ($otp_input !== $otp_correct) {
    $_SESSION['otp_errors'] = ['Invalid code. Please try again.'];
    error_log("OTP mismatch - Input: [$otp_input] vs Correct: [$otp_correct]");
    header("Location: verify-signup-otp.php");
    exit();
}

// OTP is correct! Create account
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) throw new Exception("Connection failed");
    $conn->set_charset("utf8mb4");
    
    // Generate custom user ID
    $prefix = ($pending['account_type'] === 'tutor') ? 'SNT' : 'SNU';
    $stmt = $conn->prepare("SELECT custom_user_id FROM users WHERE custom_user_id LIKE ? ORDER BY user_id DESC LIMIT 1");
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
    
    $customUserId = $prefix . '_' . str_pad($number, 3, '0', STR_PAD_LEFT);
    
    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (
            custom_user_id, username, full_name, email, password_hash, 
            college_name, user_type, is_verified, is_active, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, NOW())
    ");
    
    $stmt->bind_param(
        "sssssss",
        $customUserId,
        $pending['username'],
        $pending['fullname'],
        $pending['email'],
        $pending['password_hash'],
        $pending['college'],
        $pending['account_type']
    );
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id();
        
        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['custom_user_id'] = $customUserId;
        $_SESSION['username'] = $pending['username'];
        $_SESSION['user_email'] = $pending['email'];
        $_SESSION['user_name'] = $pending['fullname'];
        $_SESSION['user_type'] = $pending['account_type'];
        $_SESSION['is_verified'] = 0;
        $_SESSION['college_name'] = $pending['college'];
        $_SESSION['signup_success'] = true;
        
        // Clear pending data
        unset($_SESSION['pending_signup']);
        unset($_SESSION['signup_errors']);
        unset($_SESSION['otp_errors']);
        
        $stmt->close();
        $conn->close();
        
        // Redirect to dashboard
        if ($pending['account_type'] === 'tutor') {
            header("Location: tutor-dashboard.php");
        } else {
            header("Location: student-dashboard.php");
        }
        exit();
        
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Account creation error line " . __LINE__ . ": " . $e->getMessage());
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    $_SESSION['otp_errors'] = ['Error creating account. Please try again.'];
    header("Location: verify-signup-otp.php");
    exit();
}
?>