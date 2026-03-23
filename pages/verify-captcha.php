<?php
require_once '../includes/config.php';

startSession();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('signin.php');
}

// Get form type
$formType = $_POST['form_type'] ?? '';

// Get CAPTCHA response
$userCaptcha = strtoupper(trim($_POST['captcha'] ?? ''));
$sessionCaptcha = $_SESSION['captcha_code'] ?? '';
$captchaTime = $_SESSION['captcha_time'] ?? 0;

// Validate CAPTCHA
$errors = [];

if (empty($userCaptcha)) {
    $errors[] = 'Please enter the verification code';
}

// Check if CAPTCHA expired (5 minutes)
if ((time() - $captchaTime) > 300) {
    $errors[] = 'Verification code expired. Please try again.';
}

// Verify CAPTCHA
if (empty($errors) && $userCaptcha !== $sessionCaptcha) {
    $errors[] = 'Invalid verification code. Please try again.';
}

// If CAPTCHA validation failed
if (!empty($errors)) {
    // Clear CAPTCHA for security
    unset($_SESSION['captcha_code']);
    unset($_SESSION['captcha_time']);
    
    $_SESSION[($formType === 'signup' ? 'signup' : 'signin') . '_errors'] = $errors;
    
    // Save form data
    if ($formType === 'signup') {
        $_SESSION['signup_data'] = [
            'fullname' => $_POST['fullname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'college' => $_POST['college'] ?? '',
            'account_type' => $_POST['account_type'] ?? 'learner'
        ];
        redirect('signup.php');
    } else {
        $_SESSION['signin_email'] = $_POST['email'] ?? '';
        redirect('signin.php');
    }
}

// CAPTCHA verification successful!
// Clear CAPTCHA from session
unset($_SESSION['captcha_code']);
unset($_SESSION['captcha_time']);

// Store verification status
$_SESSION['captcha_verified'] = true;
$_SESSION['captcha_verify_time'] = time();

// Forward to appropriate processor
if ($formType === 'signup') {
    // Store all POST data in session for process-signup.php
    $_SESSION['temp_signup_data'] = $_POST;
    redirect('process-signup.php');
} else {
    // Store all POST data in session for process-signin.php
    $_SESSION['temp_signin_data'] = $_POST;
    redirect('process-signin.php');
}
?>