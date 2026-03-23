<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if pending admin signup exists
if (!isset($_SESSION['pending_admin_signup'])) {
    header('Location: signup.php');
    exit();
}

$pendingData = $_SESSION['pending_admin_signup'];
$submittedOTP = trim($_POST['otp'] ?? '');

// Validation
if (empty($submittedOTP)) {
    $_SESSION['admin_otp_errors'] = ['Please enter the verification code'];
    header('Location: verify-admin-otp.php');
    exit();
}

if (strlen($submittedOTP) !== 6 || !ctype_digit($submittedOTP)) {
    $_SESSION['admin_otp_errors'] = ['Invalid code format. Please enter 6 digits.'];
    header('Location: verify-admin-otp.php');
    exit();
}

// Check OTP expiry (10 minutes)
if ((time() - $pendingData['otp_created']) > 600) {
    $_SESSION['admin_otp_errors'] = ['Verification code expired. Please request a new one.'];
    header('Location: verify-admin-otp.php');
    exit();
}

// Verify OTP
if ($submittedOTP !== $pendingData['otp']) {
    $_SESSION['admin_otp_errors'] = ['Incorrect verification code. Please try again.'];
    header('Location: verify-admin-otp.php');
    exit();
}

// OTP is valid - Create admin account
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['admin_otp_errors'] = ['Database error. Please try again.'];
    header('Location: verify-admin-otp.php');
    exit();
}

try {
    // Insert admin
    $stmt = $conn->prepare("
        INSERT INTO admins (username, email, password_hash, full_name, admin_role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param(
        "sssss",
        $pendingData['username'],
        $pendingData['email'],
        $pendingData['password_hash'],
        $pendingData['full_name'],
        $pendingData['admin_role']
    );
    
    if ($stmt->execute()) {
        $adminId = $conn->insert_id;
        $stmt->close();
        
        // Clear pending data
        unset($_SESSION['pending_admin_signup']);
        unset($_SESSION['admin_otp_errors']);
        
        $conn->close();
        
        // Set success message
        $_SESSION['admin_signup_success'] = "Admin account created successfully! Please sign in.";
        
        // Redirect to signin
        header("Location: signin.php");
        exit();
        
    } else {
        throw new Exception("Insert failed: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Admin creation error: " . $e->getMessage());
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    $_SESSION['admin_otp_errors'] = ['Failed to create account. Username or email may already exist.'];
    header('Location: verify-admin-otp.php');
    exit();
}
?>