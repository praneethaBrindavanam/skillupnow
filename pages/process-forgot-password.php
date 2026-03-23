<?php
session_start();

// Database configuration (matching your process-signin.php)
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

// Try to load email configuration if it exists
$emailConfigExists = file_exists('../includes/email-config.php');
if ($emailConfigExists) {
    require_once '../includes/email-config.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$errors = [];

// Validate email
if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address";
}

if (!empty($errors)) {
    $_SESSION['forgot_errors'] = $errors;
    header("Location: forgot-password.php");
    exit();
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
    $_SESSION['forgot_errors'] = ["Unable to connect to database. Please try again later."];
    header("Location: forgot-password.php");
    exit();
}

// Check if user exists with this email
$stmt = $conn->prepare("SELECT user_id, full_name, email, firstname FROM users WHERE email = ? AND is_active = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Don't reveal if email exists (security)
    error_log("Password reset requested for non-existent email: " . $email);
    $_SESSION['forgot_success'] = "If an account exists with this email, you will receive a password reset link shortly.";
    $stmt->close();
    $conn->close();
    header("Location: forgot-password.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Generate secure random token
$resetToken = bin2hex(random_bytes(32)); // 64 character token

// Store token in database
$stmt = $conn->prepare("UPDATE users SET verification_token = ?, updated_at = NOW() WHERE user_id = ?");
$stmt->bind_param("si", $resetToken, $user['user_id']);
$stmt->execute();
$stmt->close();
$conn->close();

// Build reset link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
// Build correct path based on current file location
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /skillupnow/pages
$basePath = dirname($scriptDir);               // e.g. /skillupnow
$resetLink = $protocol . "://" . $host . $basePath . "/pages/reset-password.php?token=" . $resetToken;

// Log for debugging
error_log("========================================");
error_log("PASSWORD RESET REQUEST");
error_log("Email: " . $email);
error_log("Token: " . $resetToken);
error_log("Reset Link: " . $resetLink);
error_log("========================================");

// Prepare email content
$userName = !empty($user['firstname']) ? $user['firstname'] : $user['full_name'];
$to = $user['email'];
$subject = "Password Reset Request - SkillNest";

// HTML email message
$htmlMessage = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f5f5f5;">
    <div style="max-width:600px;margin:0 auto;background-color:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <div style="background:linear-gradient(135deg,#4FD1C5,#319795);padding:40px 20px;text-align:center;">
            <h1 style="color:white;margin:0;font-size:28px;">🔐 Password Reset</h1>
        </div>
        <div style="padding:40px 30px;">
            <p style="font-size:16px;color:#333;margin:0 0 15px 0;">Hi <strong>' . htmlspecialchars($userName) . '</strong>,</p>
            <p style="font-size:16px;color:#333;margin:0 0 15px 0;">We received a request to reset your password.</p>
            <p style="font-size:16px;color:#333;margin:0 0 30px 0;">Click the button below to reset your password:</p>
            <div style="text-align:center;margin:30px 0;">
                <a href="' . $resetLink . '" style="display:inline-block;padding:16px 40px;background-color:#319795;color:white;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px;">Reset My Password</a>
            </div>
            <p style="font-size:14px;color:#666;margin:20px 0 10px 0;">Or copy this link:</p>
            <div style="background-color:#f9f9f9;padding:15px;border-radius:5px;border:1px solid #e0e0e0;word-break:break-all;">
                <a href="' . $resetLink . '" style="color:#319795;font-size:14px;">' . $resetLink . '</a>
            </div>
            <div style="background-color:#FEF3C7;border-left:4px solid #F59E0B;padding:20px;margin:30px 0;border-radius:4px;">
                <p style="margin:0 0 10px 0;font-weight:bold;color:#92400E;font-size:15px;">⚠️ Important:</p>
                <ul style="margin:0;padding-left:20px;color:#92400E;font-size:14px;line-height:1.8;">
                    <li>This link expires in 1 hour</li>
                    <li>If you didn\'t request this, ignore this email</li>
                    <li>Never share this link with anyone</li>
                </ul>
            </div>
            <p style="font-size:16px;color:#333;margin:30px 0 0 0;">Best regards,<br><strong>The SkillNest Team</strong></p>
        </div>
        <div style="background-color:#f9f9f9;padding:25px 30px;text-align:center;border-top:1px solid #e0e0e0;">
            <p style="margin:0;font-size:13px;color:#666;">&copy; ' . date('Y') . ' SkillNest. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
';

// Try to send email
$mailSent = false;

// Method 1: Try sendPasswordResetEmail() function (from email-config.php)
if (function_exists('sendPasswordResetEmail')) {
    try {
        $mailSent = sendPasswordResetEmail($to, $userName, $resetLink);
        if ($mailSent) {
            error_log("✓ Email sent via sendPasswordResetEmail()");
        }
    } catch (Exception $e) {
        error_log("✗ sendPasswordResetEmail() failed: " . $e->getMessage());
    }
}

// Method 2: Try sendEmail() function (from email-config.php)
if (!$mailSent && function_exists('sendEmail')) {
    try {
        $mailSent = sendEmail($to, $subject, $htmlMessage);
        if ($mailSent) {
            error_log("✓ Email sent via sendEmail() function");
        }
    } catch (Exception $e) {
        error_log("✗ sendEmail() failed: " . $e->getMessage());
    }
}

// Method 3: Try sendSimpleEmail() function
if (!$mailSent && function_exists('sendSimpleEmail')) {
    try {
        $mailSent = sendSimpleEmail($to, $subject, $htmlMessage);
        if ($mailSent) {
            error_log("✓ Email sent via sendSimpleEmail() function");
        }
    } catch (Exception $e) {
        error_log("✗ sendSimpleEmail() failed: " . $e->getMessage());
    }
}

// Method 4: Try PHP mail() function
if (!$mailSent) {
    try {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: SkillNest <noreply@skillnest.com>\r\n";
        $mailSent = @mail($to, $subject, $htmlMessage, $headers);
        if ($mailSent) {
            error_log("✓ Email sent via mail() function");
        } else {
            error_log("✗ mail() function failed");
        }
    } catch (Exception $e) {
        error_log("✗ mail() exception: " . $e->getMessage());
    }
}

// Prepare response message
if ($mailSent) {
    $_SESSION['forgot_success'] = "✅ Password reset link has been sent to your email. Please check your inbox and spam folder.";
} else {
    // Email failed - provide direct link for testing
    $_SESSION['forgot_success'] = "Password reset requested for your account.";
    $_SESSION['forgot_success'] .= '<br><br><div style="background:#FEF3C7;padding:20px;border-radius:8px;border-left:4px solid #F59E0B;margin-top:15px;">';
    $_SESSION['forgot_success'] .= '<p style="margin:0 0 10px 0;color:#92400E;font-weight:bold;font-size:15px;">⚠️ Email Service Unavailable</p>';
    $_SESSION['forgot_success'] .= '<p style="margin:0 0 10px 0;color:#92400E;font-size:14px;">Our email service is temporarily down. Please use this direct reset link:</p>';
    $_SESSION['forgot_success'] .= '<a href="' . $resetLink . '" style="display:inline-block;margin:10px 0;padding:12px 24px;background:#F59E0B;color:white;text-decoration:none;border-radius:6px;font-weight:bold;">Click Here to Reset Password</a>';
    $_SESSION['forgot_success'] .= '<p style="margin:10px 0 0 0;color:#92400E;font-size:12px;">Or copy this link: <br><code style="background:white;padding:5px;display:block;margin-top:5px;word-break:break-all;border-radius:4px;">' . $resetLink . '</code></p>';
    $_SESSION['forgot_success'] .= '</div>';
}

header("Location: forgot-password.php");
exit();
?>