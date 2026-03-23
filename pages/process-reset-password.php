<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit();
}

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

// Validate token
if (empty($token)) {
    $errors[] = "Invalid reset token";
}

// Validate password
if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long";
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = "Password must contain at least one uppercase letter";
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors[] = "Password must contain at least one lowercase letter";
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one number";
}

// Validate password confirmation
if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match";
}

if (!empty($errors)) {
    $_SESSION['reset_errors'] = $errors;
    header("Location: reset-password.php?token=" . urlencode($token));
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
    $_SESSION['reset_errors'] = ["Unable to connect to database. Please try again later."];
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
}

// Verify token and get user
$stmt = $conn->prepare("SELECT user_id, email, full_name FROM users WHERE verification_token = ? AND is_active = 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['reset_errors'] = ["Invalid or expired reset token. Please request a new password reset link."];
    $stmt->close();
    $conn->close();
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Hash the new password
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Update password and clear token
$stmt = $conn->prepare("UPDATE users SET password_hash = ?, verification_token = NULL, updated_at = NOW() WHERE user_id = ?");
$stmt->bind_param("si", $passwordHash, $user['user_id']);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Try to send confirmation email
    if (file_exists('../includes/email-config.php')) {
        require_once '../includes/email-config.php';
        
        $to = $user['email'];
        $subject = "Password Successfully Reset - SkillUpNow";
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f5f5f5;">
            <div style="max-width:600px;margin:0 auto;background-color:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <div style="background:linear-gradient(135deg,#4FD1C5,#319795);padding:40px 20px;text-align:center;">
                    <h1 style="color:white;margin:0;font-size:28px;">✅ Password Reset Successful</h1>
                </div>
                <div style="padding:40px 30px;">
                    <div style="background:#D1FAE5;border-left:4px solid #10B981;padding:15px;margin:0 0 20px 0;border-radius:4px;">
                        <p style="margin:0;color:#065F46;font-weight:bold;">Your password has been successfully reset!</p>
                    </div>
                    <p style="font-size:16px;color:#333;margin:0 0 15px 0;">Hi <strong>' . htmlspecialchars($user['full_name']) . '</strong>,</p>
                    <p style="font-size:16px;color:#333;margin:0 0 15px 0;">Your SkillUpNow account password has been changed successfully.</p>
                    <p style="font-size:16px;color:#333;margin:0 0 10px 0;"><strong>What\'s next?</strong></p>
                    <ul style="font-size:16px;color:#333;line-height:1.8;">
                        <li>You can now sign in with your new password</li>
                        <li>Make sure to remember your new password</li>
                        <li>Consider using a password manager for security</li>
                    </ul>
                    <div style="text-align:center;margin:30px 0;">
                        <a href="http://' . $_SERVER['HTTP_HOST'] . '/skillupnow/pages/signin.php" style="display:inline-block;padding:16px 40px;background-color:#319795;color:white;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px;">Sign In Now</a>
                    </div>
                    <div style="background:#FEF3C7;border-left:4px solid #F59E0B;padding:15px;margin:20px 0;border-radius:4px;">
                        <p style="margin:0 0 5px 0;color:#92400E;font-weight:bold;">⚠️ Didn\'t make this change?</p>
                        <p style="margin:0;color:#92400E;font-size:14px;">If you did not reset your password, please contact us immediately at support@skillupnow.com</p>
                    </div>
                    <p style="font-size:16px;color:#333;margin:30px 0 0 0;">Best regards,<br><strong>The SkillUpNow Team</strong></p>
                </div>
                <div style="background-color:#f9f9f9;padding:25px 30px;text-align:center;border-top:1px solid #e0e0e0;">
                    <p style="margin:0;font-size:13px;color:#666;">&copy; ' . date('Y') . ' SkillUpNow. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Try to send email
        if (function_exists('sendEmail')) {
            @sendEmail($to, $subject, $message);
        } else {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: SkillUpNow <noreply@skillupnow.com>\r\n";
            @mail($to, $subject, $message, $headers);
        }
    }
    
    $_SESSION['reset_success'] = "Password reset successfully! You can now sign in with your new password.";
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
} else {
    $_SESSION['reset_errors'] = ["Failed to reset password. Please try again."];
    $stmt->close();
    $conn->close();
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
}
?>