<?php
session_start();

// Load Google config
$googleConfig = require_once '../includes/google-config.php';

// Database config
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

// Check for errors
if (isset($_GET['error'])) {
    $_SESSION['signup_errors'] = ['Google authentication was cancelled or failed.'];
    header('Location: signup.php');
    exit();
}

// Verify state token (CSRF protection)
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['google_oauth_state'] ?? '')) {
    $_SESSION['signup_errors'] = ['Invalid state token. Please try again.'];
    header('Location: signup.php');
    exit();
}

// Get authorization code
if (!isset($_GET['code'])) {
    $_SESSION['signup_errors'] = ['No authorization code received from Google.'];
    header('Location: signup.php');
    exit();
}

$code = $_GET['code'];

// Exchange authorization code for access token
$tokenData = [
    'code' => $code,
    'client_id' => $googleConfig['client_id'],
    'client_secret' => $googleConfig['client_secret'],
    'redirect_uri' => $googleConfig['redirect_uri'],
    'grant_type' => 'authorization_code'
];

$ch = curl_init($googleConfig['token_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("Google token exchange failed: $response");
    $_SESSION['signup_errors'] = ['Failed to authenticate with Google. Please try again.'];
    header('Location: signup.php');
    exit();
}

$tokenResponse = json_decode($response, true);
$accessToken = $tokenResponse['access_token'] ?? null;

if (!$accessToken) {
    $_SESSION['signup_errors'] = ['Failed to get access token from Google.'];
    header('Location: signup.php');
    exit();
}

// Get user info from Google
$ch = curl_init($googleConfig['userinfo_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$userInfoResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("Google userinfo failed: $userInfoResponse");
    $_SESSION['signup_errors'] = ['Failed to get user information from Google.'];
    header('Location: signup.php');
    exit();
}

$googleUser = json_decode($userInfoResponse, true);

// Extract user data
$googleId = $googleUser['id'] ?? null;
$email = $googleUser['email'] ?? null;
$fullName = $googleUser['name'] ?? '';
$profilePicture = $googleUser['picture'] ?? null;
$isVerified = $googleUser['verified_email'] ?? false;

if (!$googleId || !$email) {
    $_SESSION['signup_errors'] = ['Failed to get required information from Google.'];
    header('Location: signup.php');
    exit();
}

// Clear OAuth session data
unset($_SESSION['google_oauth_state']);
$action = $_SESSION['google_oauth_action'] ?? 'signup';
unset($_SESSION['google_oauth_action']);

// Connect to database
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $_SESSION['signup_errors'] = ['Database error. Please try again later.'];
    header('Location: signup.php');
    exit();
}

// Check if user already exists with this Google ID
$stmt = $conn->prepare("SELECT user_id, custom_user_id, username, full_name, email, user_type, college_name FROM users WHERE google_id = ?");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists - sign them in
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW(), profile_picture = ? WHERE user_id = ?");
    $updateStmt->bind_param("si", $profilePicture, $user['user_id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Set session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['custom_user_id'] = $user['custom_user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['is_verified'] = 1; // Google accounts are verified
    $_SESSION['college_name'] = $user['college_name'];
    $_SESSION['signin_success'] = true;
    
    $conn->close();
    
    // Redirect to dashboard
    if ($user['user_type'] === 'tutor') {
        header('Location: tutor-dashboard.php');
    } else {
        header('Location: student-dashboard.php');
    }
    exit();
}

$stmt->close();

// Check if email already exists (without Google ID)
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND (google_id IS NULL OR google_id = '')");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    $_SESSION['signup_errors'] = ['An account with this email already exists. Please sign in with your password.'];
    header('Location: signin.php');
    exit();
}

$stmt->close();

// New user - need to complete profile
// Store Google data in session and redirect to complete profile page
$_SESSION['google_signup_data'] = [
    'google_id' => $googleId,
    'email' => $email,
    'full_name' => $fullName,
    'profile_picture' => $profilePicture,
    'is_verified' => $isVerified
];

$conn->close();

// Redirect to complete Google profile
header('Location: complete-google-profile.php');
exit();
?>