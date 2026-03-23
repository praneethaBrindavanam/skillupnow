<?php
/**
 * Email Configuration for SkillUpNow
 * Handles all email sending including OTP and password reset
 */

// Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ============================================
// SMTP SETTINGS - DO NOT CHANGE THESE
// ============================================
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'vegiprasanthi697@gmail.com'); // Your Gmail
define('SMTP_PASSWORD', 'cmrh bvme ziqa lhcx');        // Your App Password
define('SMTP_FROM',     'vegiprasanthi697@gmail.com'); // FIXED: Must match username
define('SMTP_NAME',     'SkillUpNow');
// ============================================

/**
 * Core send function - all emails go through this
 */
function sendEmail($to, $subject, $htmlMessage, $fromName = 'SkillUpNow') {
    try {
        $mail = new PHPMailer(true);

        // Debug - set to 0 in production, 2 to see errors in logs
        $mail->SMTPDebug  = 0;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: " . $str);
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Sender - MUST match Gmail username to avoid rejection
        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(SMTP_FROM, SMTP_NAME);

        // Content
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Subject  = $subject;
        $mail->Body     = $htmlMessage;
        $mail->AltBody  = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>'], "\n", $htmlMessage));

        $mail->send();
        error_log("✓ Email sent to: " . $to . " | Subject: " . $subject);
        return true;

    } catch (Exception $e) {
        error_log("✗ Email FAILED to: " . $to);
        error_log("✗ PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send OTP Email (for signin / signup)
 */
function sendOTPEmail($to, $name, $otp, $type = 'signin') {
    $subject = $type === 'signup'
        ? 'Verify Your Email - SkillUpNow'
        : 'Your Sign In Code - SkillUpNow';

    $actionText = $type === 'signup' ? 'verify your email' : 'complete your sign in';

    $htmlMessage = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f5f5f5;">
        <div style="max-width:600px;margin:0 auto;background-color:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
            <div style="background:linear-gradient(135deg,#4FD1C5,#319795);padding:40px 20px;text-align:center;">
                <h1 style="color:white;margin:0;font-size:28px;">🔐 Your Verification Code</h1>
            </div>
            <div style="padding:40px 30px;">
                <p style="font-size:16px;color:#333;margin:0 0 20px 0;">Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
                <p style="font-size:16px;color:#333;margin:0 0 30px 0;">Use this code to ' . $actionText . ':</p>
                <div style="background:#F0FDFA;border:2px solid #319795;border-radius:10px;padding:30px;text-align:center;margin:30px 0;">
                    <p style="margin:0 0 10px 0;font-size:14px;color:#666;text-transform:uppercase;letter-spacing:1px;">Your Verification Code</p>
                    <p style="margin:0;font-size:48px;font-weight:bold;color:#319795;letter-spacing:8px;font-family:monospace;">' . $otp . '</p>
                </div>
                <div style="background:#FEF3C7;border-left:4px solid #F59E0B;padding:15px;margin:30px 0;border-radius:4px;">
                    <p style="margin:0 0 8px 0;color:#92400E;font-size:14px;font-weight:bold;">⚠️ Important:</p>
                    <ul style="margin:0;padding-left:20px;color:#92400E;font-size:14px;line-height:1.8;">
                        <li>This code expires in <strong>10 minutes</strong></li>
                        <li>Never share this code with anyone</li>
                        <li>If you didn\'t request this, ignore this email</li>
                    </ul>
                </div>
                <p style="font-size:16px;color:#333;margin:30px 0 0 0;">Best regards,<br><strong>The SkillUpNow Team</strong></p>
            </div>
            <div style="background-color:#f9f9f9;padding:25px 30px;text-align:center;border-top:1px solid #e0e0e0;">
                <p style="margin:0;font-size:13px;color:#666;">&copy; ' . date('Y') . ' SkillUpNow. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($to, $subject, $htmlMessage);
}

/**
 * Send Password Reset Email
 */
function sendPasswordResetEmail($to, $name, $resetLink) {
    $subject = "Password Reset Request - SkillUpNow";

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
                <p style="font-size:16px;color:#333;margin:0 0 15px 0;">Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
                <p style="font-size:16px;color:#333;margin:0 0 15px 0;">We received a request to reset your password.</p>
                <p style="font-size:16px;color:#333;margin:0 0 30px 0;">Click the button below to reset your password:</p>
                <div style="text-align:center;margin:30px 0;">
                    <a href="' . $resetLink . '" style="display:inline-block;padding:16px 40px;background-color:#319795;color:white;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px;">Reset My Password</a>
                </div>
                <p style="font-size:14px;color:#666;margin:20px 0 10px 0;">Or copy and paste this link in your browser:</p>
                <div style="background-color:#f9f9f9;padding:15px;border-radius:5px;border:1px solid #e0e0e0;word-break:break-all;">
                    <a href="' . $resetLink . '" style="color:#319795;font-size:14px;">' . $resetLink . '</a>
                </div>
                <div style="background-color:#FEF3C7;border-left:4px solid #F59E0B;padding:20px;margin:30px 0;border-radius:4px;">
                    <p style="margin:0 0 10px 0;font-weight:bold;color:#92400E;font-size:15px;">⚠️ Important:</p>
                    <ul style="margin:0;padding-left:20px;color:#92400E;font-size:14px;line-height:1.8;">
                        <li>This link expires in <strong>1 hour</strong></li>
                        <li>If you didn\'t request this, ignore this email</li>
                        <li>Never share this link with anyone</li>
                    </ul>
                </div>
                <p style="font-size:16px;color:#333;margin:30px 0 0 0;">Best regards,<br><strong>The SkillUpNow Team</strong></p>
            </div>
            <div style="background-color:#f9f9f9;padding:25px 30px;text-align:center;border-top:1px solid #e0e0e0;">
                <p style="margin:0;font-size:13px;color:#666;">&copy; ' . date('Y') . ' SkillUpNow. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($to, $subject, $htmlMessage);
}
?>