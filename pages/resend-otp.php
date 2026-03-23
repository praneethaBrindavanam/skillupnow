<?php
session_start();

$verificationType = $_GET['type'] ?? '';

// Determine which pending data to use
$pendingData = null;
$sessionKey = '';

switch ($verificationType) {
    case 'signup':
        $pendingData = $_SESSION['pending_signup'] ?? null;
        $sessionKey = 'pending_signup';
        break;
    case 'signin':
        $pendingData = $_SESSION['pending_signin'] ?? null;
        $sessionKey = 'pending_signin';
        break;
    case 'google-signup':
        $pendingData = $_SESSION['pending_google_signup'] ?? null;
        $sessionKey = 'pending_google_signup';
        break;
    default:
        header('Location: signin.php');
        exit();
}

if (!$pendingData) {
    header('Location: signin.php');
    exit();
}

// Generate new OTP
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Update OTP in session
$_SESSION[$sessionKey]['otp'] = $otp;
$_SESSION[$sessionKey]['otp_created'] = time();

// Send OTP email
require_once __DIR__ . '/../includes/email-config.php';

$action = ($verificationType === 'signin') ? 'signin' : 'signup';
$emailSent = sendOTPEmail($pendingData['email'], $pendingData['full_name'], $otp, $action);

if ($emailSent) {
    $_SESSION['otp_success'] = 'Verification code resent successfully!';
} else {
    error_log("Failed to resend OTP to: " . $pendingData['email']);
    $_SESSION['otp_errors'] = ['Failed to send code. Please try again.'];
}

header('Location: verify-otp.php');
exit();
?>