<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$verificationType = $_POST['verification_type'] ?? '';
$submittedOTP = trim($_POST['otp'] ?? '');

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

// Validation
if (empty($submittedOTP)) {
    $_SESSION['otp_errors'] = ['Please enter the verification code'];
    header('Location: verify-otp.php');
    exit();
}

if (strlen($submittedOTP) !== 6 || !ctype_digit($submittedOTP)) {
    $_SESSION['otp_errors'] = ['Invalid code format. Please enter 6 digits.'];
    header('Location: verify-otp.php');
    exit();
}

// Check OTP expiry (5 minutes)
if ((time() - $pendingData['otp_created']) > 300) {
    $_SESSION['otp_errors'] = ['Verification code expired. Please request a new one.'];
    header('Location: verify-otp.php');
    exit();
}

// Verify OTP
if ($submittedOTP !== $pendingData['otp']) {
    $_SESSION['otp_errors'] = ['Incorrect verification code. Please try again.'];
    header('Location: verify-otp.php');
    exit();
}

// OTP is valid!
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
    $_SESSION['otp_errors'] = ['Database error. Please try again.'];
    header('Location: verify-otp.php');
    exit();
}

// Handle based on verification type
if ($verificationType === 'signin') {
    // SIGN IN - User already exists, just log them in
    
    // Set session variables
    $_SESSION['user_id'] = $pendingData['user_id'];
    $_SESSION['custom_user_id'] = $pendingData['custom_user_id'];
    $_SESSION['username'] = $pendingData['username'];
    $_SESSION['user_email'] = $pendingData['email'];
    $_SESSION['user_name'] = $pendingData['full_name'];
    $_SESSION['user_type'] = $pendingData['user_type'];
    $_SESSION['is_verified'] = $pendingData['is_verified'];
    $_SESSION['college_name'] = $pendingData['college_name'];
    $_SESSION['signin_success'] = true;
    
    // Handle remember me
    if ($pendingData['remember']) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        setcookie('remember_user', $pendingData['user_id'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $pendingData['user_id']);
    $stmt->execute();
    $stmt->close();
    
    // Clear pending data
    unset($_SESSION[$sessionKey]);
    unset($_SESSION['otp_errors']);
    
    $conn->close();
    
    // Redirect to dashboard
    if ($pendingData['user_type'] === 'tutor') {
        header("Location: tutor-dashboard.php");
    } else {
        header("Location: student-dashboard.php");
    }
    exit();
    
} elseif ($verificationType === 'signup' || $verificationType === 'google-signup') {
    // SIGNUP - Create new user account
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO users (
                custom_user_id,
                username,
                " . ($verificationType === 'google-signup' ? 'google_id,' : '') . "
                full_name, 
                email, 
                password_hash, 
                college_name, 
                user_type, 
                is_verified,
                " . ($verificationType === 'google-signup' ? 'profile_picture,' : '') . "
                is_active,
                created_at
            ) VALUES (?, ?, " . ($verificationType === 'google-signup' ? '?,' : '') . " ?, ?, ?, ?, ?, ?, " . ($verificationType === 'google-signup' ? '?,' : '') . " 1, NOW())
        ");
        
        if ($verificationType === 'google-signup') {
            $stmt->bind_param(
                "sssssssiss",
                $pendingData['custom_user_id'],
                $pendingData['username'],
                $pendingData['google_id'],
                $pendingData['full_name'],
                $pendingData['email'],
                $pendingData['password_hash'],
                $pendingData['college'],
                $pendingData['account_type'],
                $isVerified = 1,
                $pendingData['profile_picture']
            );
        } else {
            $stmt->bind_param(
                "sssssssi",
                $pendingData['custom_user_id'],
                $pendingData['username'],
                $pendingData['full_name'],
                $pendingData['email'],
                $pendingData['password_hash'],
                $pendingData['college'],
                $pendingData['account_type'],
                $isVerified = 0
            );
        }
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['custom_user_id'] = $pendingData['custom_user_id'];
            $_SESSION['username'] = $pendingData['username'];
            $_SESSION['user_email'] = $pendingData['email'];
            $_SESSION['user_name'] = $pendingData['full_name'];
            $_SESSION['user_type'] = $pendingData['account_type'] ?? $pendingData['user_type'];
            $_SESSION['is_verified'] = $verificationType === 'google-signup' ? 1 : 0;
            $_SESSION['college_name'] = $pendingData['college'];
            $_SESSION['signup_success'] = true;
            
            // Clear pending data
            unset($_SESSION[$sessionKey]);
            unset($_SESSION['otp_errors']);
            
            $stmt->close();
            $conn->close();
            
            // Redirect to dashboard
            $userType = $pendingData['account_type'] ?? $pendingData['user_type'];
            if ($userType === 'tutor') {
                header("Location: tutor-dashboard.php");
            } else {
                header("Location: student-dashboard.php");
            }
            exit();
            
        } else {
            throw new Exception("Insert failed: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        error_log("Account creation error: " . $e->getMessage());
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
        
        $_SESSION['otp_errors'] = ['Failed to create account. Please try again.'];
        header('Location: verify-otp.php');
        exit();
    }
}
?>