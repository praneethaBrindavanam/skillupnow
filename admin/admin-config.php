<?php
// Admin System Configuration

// Include main config
require_once __DIR__ . '/../includes/config.php';

// Admin-specific functions

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: signin.php");
        exit();
    }
}

function requireSuperAdmin() {
    if (!isAdminLoggedIn() || $_SESSION['admin_role'] !== 'superadmin') {
        $_SESSION['error'] = "Access denied. Super admin privileges required.";
        header("Location: dashboard.php");
        exit();
    }
}

function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

function getCurrentAdminRole() {
    return $_SESSION['admin_role'] ?? null;
}

function logAdminActivity($adminId, $actionType, $targetTable = null, $targetId = null, $description = null) {
    $conn = getDbConnection();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO admin_activity_log (admin_id, action_type, target_table, target_id, description, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ississ", $adminId, $actionType, $targetTable, $targetId, $description, $ipAddress);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Admin activity log error: " . $e->getMessage());
    }
    
    closeDbConnection($conn);
}

function getAdminStats() {
    $conn = getDbConnection();
    
    $stats = [
        'total_students' => 0,
        'total_tutors' => 0,
        'total_skills' => 0,
        'total_sessions' => 0,
        'active_users' => 0,
        'pending_verifications' => 0
    ];
    
    // Total students
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'learner'");
    $stats['total_students'] = $result->fetch_assoc()['count'];
    
    // Total tutors
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'tutor'");
    $stats['total_tutors'] = $result->fetch_assoc()['count'];
    
    // Total skills
    $result = $conn->query("SELECT COUNT(*) as count FROM skills");
    $stats['total_skills'] = $result->fetch_assoc()['count'];
    
    // Active users (logged in within last 30 days)
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['active_users'] = $result->fetch_assoc()['count'];
    
    // Pending verifications
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 0");
    $stats['pending_verifications'] = $result->fetch_assoc()['count'];
    
    closeDbConnection($conn);
    
    return $stats;
}
?>