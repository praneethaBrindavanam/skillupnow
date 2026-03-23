<?php
session_start();
require_once 'admin-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signin.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address";
}

if (empty($password)) {
    $errors[] = "Password is required";
}

if (!empty($errors)) {
    $_SESSION['admin_errors'] = $errors;
    $_SESSION['admin_email'] = $email;
    header("Location: signin.php");
    exit();
}

$conn = getDbConnection();

try {
    $stmt = $conn->prepare("
        SELECT admin_id, username, email, password_hash, full_name, admin_role, is_active 
        FROM admins 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeDbConnection($conn);
        $_SESSION['admin_errors'] = ["Invalid email or password"];
        $_SESSION['admin_email'] = $email;
        header("Location: signin.php");
        exit();
    }
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        closeDbConnection($conn);
        $_SESSION['admin_errors'] = ["Invalid email or password"];
        $_SESSION['admin_email'] = $email;
        header("Location: signin.php");
        exit();
    }
    
    // Check if account is active
    if ($admin['is_active'] == 0) {
        closeDbConnection($conn);
        $_SESSION['admin_errors'] = ["Your account has been deactivated. Contact system administrator."];
        header("Location: signin.php");
        exit();
    }
    
    // Update last login
    $stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
    $stmt->bind_param("i", $admin['admin_id']);
    $stmt->execute();
    $stmt->close();
    
    // Log activity
    logAdminActivity($admin['admin_id'], 'login', 'admins', $admin['admin_id'], 'Admin signed in');
    
    closeDbConnection($conn);
    
    // Set session
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['admin_role'];
    
    header("Location: dashboard.php");
    exit();
    
} catch (Exception $e) {
    error_log("Admin login error: " . $e->getMessage());
    $_SESSION['admin_errors'] = ["An error occurred. Please try again."];
    closeDbConnection($conn);
    header("Location: signin.php");
    exit();
}
?>