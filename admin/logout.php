<?php
session_start();
require_once 'admin-config.php';

if (isset($_SESSION['admin_id'])) {
    logAdminActivity($_SESSION['admin_id'], 'logout', 'admins', $_SESSION['admin_id'], 'Admin signed out');
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to signin
header("Location: signin.php");
exit();
?>