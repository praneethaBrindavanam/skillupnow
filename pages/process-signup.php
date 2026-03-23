<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

// Function to redirect with error
function redirectWithError($errors, $formData) {
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_data'] = $formData;
    header("Location: signup.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError(['Invalid request method'], []);
}

// Verify CAPTCHA first
$captcha_input = strtoupper(trim($_POST['captcha'] ?? ''));
$captcha_session = $_SESSION['captcha_code'] ?? '';

if (empty($captcha_input)) {
    redirectWithError(['Verification code is required'], $_POST);
}

if ($captcha_input !== $captcha_session) {
    redirectWithError(['Verification code is incorrect. Please try again.'], $_POST);
}

// Clear used CAPTCHA
unset($_SESSION['captcha_code']);

// Get and sanitize form data
$fullname = trim($_POST['fullname'] ?? '');
$username_input = trim($_POST['username'] ?? '');
$email = trim(strtolower($_POST['email'] ?? ''));
$college = $_POST['college'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$accountType = $_POST['account_type'] ?? 'learner';
$termsAccepted = isset($_POST['terms']);

// Validation array
$errors = [];

// Validate full name
if (empty($fullname)) {
    $errors[] = "Full name is required";
} elseif (strlen($fullname) < 3) {
    $errors[] = "Full name must be at least 3 characters";
}

// Validate username
if (empty($username_input)) {
    $errors[] = "Username is required";
} elseif (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username_input)) {
    $errors[] = "Username must be 4-20 characters (letters, numbers, underscore only)";
}

// Validate email domain
$allowedDomains = ['@gvpce.ac.in', '@au.edu.in', '@anits.edu.in', '@raghu.edu.in'];
$validDomain = false;
foreach ($allowedDomains as $domain) {
    if (str_ends_with($email, $domain)) {
        $validDomain = true;
        break;
    }
}

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
} elseif (!$validDomain) {
    $errors[] = "Email must be from GVPCE, AU, ANITS, or Raghu college";
}

// Validate college
if (empty($college)) {
    $errors[] = "Please select your college";
}

// Validate password
if (empty($password)) {
    $errors[] = "Password is required";
} else {
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
}

// Validate password match
if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match";
}

// Validate account type
if (!in_array($accountType, ['learner', 'tutor'])) {
    $errors[] = "Invalid account type";
}

// Validate terms
if (!$termsAccepted) {
    $errors[] = "You must accept the Terms and Privacy Policy";
}

// If there are errors, redirect back
if (!empty($errors)) {
    redirectWithError($errors, [
        'fullname' => $fullname,
        'username' => $username_input,
        'email' => $email,
        'college' => $college,
        'account_type' => $accountType
    ]);
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
    redirectWithError(['Unable to connect to database. Please try again later.'], $_POST);
}

// Check if username already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    redirectWithError(['Database error. Please try again.'], $_POST);
}

$stmt->bind_param("s", $username_input);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    $conn->close();
    redirectWithError(['Username already taken. Please choose another.'], [
        'fullname' => $fullname,
        'email' => $email,
        'college' => $college,
        'account_type' => $accountType
    ]);
}
$stmt->close();

// Check if email already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    redirectWithError(['Database error. Please try again.'], $_POST);
}

$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    $conn->close();
    redirectWithError(['Email already registered. Please sign in instead.'], [
        'fullname' => $fullname,
        'username' => $username_input,
        'college' => $college,
        'account_type' => $accountType
    ]);
}
$stmt->close();

// Generate custom user ID
function generateUserId($conn, $accountType) {
    $prefix = ($accountType === 'tutor') ? 'SNT' : 'SNU';
    
    // Get the highest existing ID for this type
    $sql = "SELECT custom_user_id FROM users WHERE custom_user_id LIKE ? ORDER BY user_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $prefix . '_001'; // Fallback
    }
    
    $pattern = $prefix . '_%';
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['custom_user_id'];
        // Extract number and increment
        $number = intval(substr($lastId, 4)) + 1;
    } else {
        $number = 1;
    }
    
    $stmt->close();
    
    // Format: SNU_001 or SNT_001
    return $prefix . '_' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

try {
    // Generate custom user ID
    $customUserId = generateUserId($conn, $accountType);
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (
            custom_user_id,
            username,
            full_name, 
            email, 
            password_hash, 
            college_name, 
            user_type, 
            is_verified,
            is_active,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, NOW())
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param(
        "sssssss",
        $customUserId,
        $username_input,
        $fullname,
        $email,
        $passwordHash,
        $college,
        $accountType
    );
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;  // Property, not method
        
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['custom_user_id'] = $customUserId;
        $_SESSION['username'] = $username_input;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_type'] = $accountType;
        $_SESSION['is_verified'] = 0;
        $_SESSION['college_name'] = $college;
        $_SESSION['signup_success'] = true;
        
        // Clear any old error data
        unset($_SESSION['signup_errors']);
        unset($_SESSION['signup_data']);
        
        $stmt->close();
        $conn->close();
        
        // Redirect to dashboard
        if ($accountType === 'tutor') {
            header("Location: tutor-dashboard.php");
        } else {
            header("Location: student-dashboard.php");
        }
        exit();
        
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    redirectWithError(['An error occurred while creating your account. Please try again.'], [
        'fullname' => $fullname,
        'username' => $username_input,
        'email' => $email,
        'college' => $college,
        'account_type' => $accountType
    ]);
}
?>