<?php
session_start();
require_once 'admin-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_admin_signup'])) {
    header("Location: signup.php");
    exit();
}

$inputOtp = trim($_POST['otp'] ?? '');
$pendingData = $_SESSION['pending_admin_signup'];

// Validate OTP
if (empty($inputOtp) || strlen($inputOtp) !== 6 || !ctype_digit($inputOtp)) {
    $_SESSION['admin_otp_errors'] = ["Invalid verification code format"];
    header("Location: verify-otp.php");
    exit();
}

// Check if OTP matches
if ($inputOtp !== $pendingData['otp']) {
    $_SESSION['admin_otp_errors'] = ["Invalid verification code. Please try again."];
    header("Location: verify-otp.php");
    exit();
}

// Check if OTP expired (10 minutes)
$currentTime = time();
$otpAge = $currentTime - $pendingData['otp_created'];

if ($otpAge > 600) { // 10 minutes
    $_SESSION['admin_otp_errors'] = ["Verification code has expired. Please request a new one."];
    header("Location: verify-otp.php");
    exit();
}

// Create admin account
$conn = getDbConnection();

try {
    $stmt = $conn->prepare("
        INSERT INTO admins (username, email, password_hash, full_name, admin_role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("sssss",
        $pendingData['username'],
        $pendingData['email'],
        $pendingData['password_hash'],
        $pendingData['full_name'],
        $pendingData['admin_role']
    );
    
    if ($stmt->execute()) {
        $adminId = $stmt->insert_id;
        $stmt->close();
        
        // Log activity
        logAdminActivity($adminId, 'create', 'admins', $adminId, 'New admin account created: ' . $pendingData['username']);
        
        closeDbConnection($conn);
        
        // Clear pending data
        unset($_SESSION['pending_admin_signup']);
        
        // Set success message
        $_SESSION['admin_signup_success'] = "Admin account created successfully! Please sign in.";
        
        // Redirect to signin
        header("Location: signin.php");
        exit();
        
    } else {
        $stmt->close();
        closeDbConnection($conn);
        
        $_SESSION['admin_otp_errors'] = ["Failed to create account. Username or email may already exist."];
        header("Location: verify-otp.php");
        exit();
    }
    
} catch (Exception $e) {
    error_log("Admin signup error: " . $e->getMessage());
    closeDbConnection($conn);
    
    $_SESSION['admin_otp_errors'] = ["An error occurred. Please try again."];
    header("Location: verify-otp.php");
    exit();
}
?>