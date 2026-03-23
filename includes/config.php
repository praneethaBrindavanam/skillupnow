<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'skillupnow');

// reCAPTCHA Configuration (Get your keys from https://www.google.com/recaptcha/admin)
define('RECAPTCHA_SITE_KEY', 'YOUR_SITE_KEY_HERE'); // Replace with your site key
define('RECAPTCHA_SECRET_KEY', 'YOUR_SECRET_KEY_HERE'); // Replace with your secret key

// Email Configuration - Using Gmail SMTP
// To get App Password: Google Account > Security > 2-Step Verification > App passwords
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail
define('SMTP_PASSWORD', 'your-app-password'); // Your Gmail App Password
define('SMTP_FROM_EMAIL', 'noreply@skillupnow.com');
define('SMTP_FROM_NAME', 'SkillUp Now');

// OTP Configuration
define('OTP_EXPIRY_MINUTES', 10);
define('OTP_LENGTH', 6);

// Create database connection
function getDbConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Close database connection
function closeDbConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate college email
function isCollegeEmail($email) {
    $allowedDomains = ['.edu', '.ac.in', '.edu.in', '.ac.uk', '.edu.au'];
    
    foreach ($allowedDomains as $domain) {
        if (strpos(strtolower($email), $domain) !== false) {
            return true;
        }
    }
    
    return false;
}

// Verify reCAPTCHA
function verifyRecaptcha($token) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        return false;
    }
    
    $response = json_decode($result);
    return $response->success ?? false;
}

// Generate OTP
function generateOTP() {
    return str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
}

// Send OTP Email (Simple PHP mail - works on most servers)
function sendOTPEmail($email, $name, $otp, $purpose = 'signup') {
    $subject = ($purpose === 'signup') 
        ? 'Verify Your Email - SkillUp Now' 
        : 'Your Sign In Code - SkillUp Now';
    
    $message = ($purpose === 'signup') 
        ? getSignupOTPEmailTemplate($name, $otp) 
        : getSigninOTPEmailTemplate($name, $otp);
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

// Signup OTP Email Template
function getSignupOTPEmailTemplate($name, $otp) {
    $minutes = OTP_EXPIRY_MINUTES;
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #4FD1C5, #38B2AC); color: white; padding: 40px 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 40px 30px; }
            .otp-box { background: #f8f9fa; border-left: 4px solid #4FD1C5; padding: 20px; margin: 25px 0; text-align: center; }
            .otp-code { font-size: 36px; font-weight: bold; color: #38B2AC; letter-spacing: 8px; margin: 10px 0; }
            .footer { background: #f8f9fa; text-align: center; padding: 20px; font-size: 13px; color: #666; }
            .btn { display: inline-block; padding: 12px 30px; background: #4FD1C5; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 Welcome to SkillUp Now!</h1>
            </div>
            <div class='content'>
                <p>Hi <strong>$name</strong>,</p>
                <p>Thank you for signing up! To complete your registration, please verify your email using the code below:</p>
                
                <div class='otp-box'>
                    <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>Your Verification Code</p>
                    <div class='otp-code'>$otp</div>
                    <p style='margin: 10px 0 0 0; color: #999; font-size: 13px;'>⏱️ Valid for $minutes minutes</p>
                </div>
                
                <p>Enter this code on the verification page to activate your account.</p>
                <p style='color: #e53e3e; font-size: 14px;'><strong>⚠️ Security Notice:</strong> If you didn't create this account, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>© 2025 SkillUp Now - Digital Skill Learning Platform</p>
                <p>Developed by students of GVP College of Engineering</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Signin OTP Email Template
function getSigninOTPEmailTemplate($name, $otp) {
    $minutes = OTP_EXPIRY_MINUTES;
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #4FD1C5, #38B2AC); color: white; padding: 40px 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 40px 30px; }
            .otp-box { background: #f8f9fa; border-left: 4px solid #4FD1C5; padding: 20px; margin: 25px 0; text-align: center; }
            .otp-code { font-size: 36px; font-weight: bold; color: #38B2AC; letter-spacing: 8px; margin: 10px 0; }
            .footer { background: #f8f9fa; text-align: center; padding: 20px; font-size: 13px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Sign In Verification</h1>
            </div>
            <div class='content'>
                <p>Hi <strong>$name</strong>,</p>
                <p>Someone is trying to sign in to your SkillUp Now account. If this is you, use the code below:</p>
                
                <div class='otp-box'>
                    <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>Your Sign In Code</p>
                    <div class='otp-code'>$otp</div>
                    <p style='margin: 10px 0 0 0; color: #999; font-size: 13px;'>⏱️ Valid for $minutes minutes</p>
                </div>
                
                <p style='color: #e53e3e; font-size: 14px;'><strong>⚠️ Security Alert:</strong> If you didn't request this code, someone may be trying to access your account. Please change your password immediately.</p>
            </div>
            <div class='footer'>
                <p>© 2025 SkillUp Now - Digital Skill Learning Platform</p>
                <p>Developed by students of GVP College of Engineering</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Store OTP in database
function storeOTP($conn, $email, $otp, $purpose = 'signup') {
    $expiryTime = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    // Delete old OTPs for this email
    $deleteStmt = $conn->prepare("DELETE FROM otp_verifications WHERE email = ? AND purpose = ?");
    $deleteStmt->bind_param("ss", $email, $purpose);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    // Insert new OTP
    $stmt = $conn->prepare("INSERT INTO otp_verifications (email, otp, purpose, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $otp, $purpose, $expiryTime);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Verify OTP
function verifyOTP($conn, $email, $otp, $purpose = 'signup') {
    $stmt = $conn->prepare("
        SELECT id FROM otp_verifications 
        WHERE email = ? AND otp = ? AND purpose = ? AND expires_at > NOW() AND is_used = 0
    ");
    $stmt->bind_param("sss", $email, $otp, $purpose);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $otpId = $row['id'];
        
        // Mark OTP as used
        $updateStmt = $conn->prepare("UPDATE otp_verifications SET is_used = 1 WHERE id = ?");
        $updateStmt->bind_param("i", $otpId);
        $updateStmt->execute();
        $updateStmt->close();
        
        $stmt->close();
        return true;
    }
    
    $stmt->close();
    return false;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Start session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current user type
function getCurrentUserType() {
    startSession();
    return $_SESSION['user_type'] ?? null;
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit();
}
?>