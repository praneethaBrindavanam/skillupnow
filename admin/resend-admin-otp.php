<?php
session_start();

// Check if pending admin signup exists
if (!isset($_SESSION['pending_admin_signup'])) {
    header('Location: signup.php');
    exit();
}

$pendingData = $_SESSION['pending_admin_signup'];

// Generate new OTP
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Update OTP in session
$_SESSION['pending_admin_signup']['otp'] = $otp;
$_SESSION['pending_admin_signup']['otp_created'] = time();

// Send OTP email
require_once __DIR__ . '/../includes/email-config.php';
$emailSent = sendOTPEmail($pendingData['email'], $pendingData['full_name'], $otp, 'admin-signup');

if ($emailSent) {
    $_SESSION['admin_otp_success'] = 'Verification code resent successfully!';
} else {
    error_log("Failed to resend admin OTP to: " . $pendingData['email']);
    $_SESSION['admin_otp_errors'] = ['Failed to send code. Please try again.'];
}

header('Location: verify-admin-otp.php');
exit();
?>