<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'learner') {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: browse.php");
    exit();
}

$studentId = $_SESSION['user_id'];
$tutorId = isset($_POST['tutor_id']) ? intval($_POST['tutor_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($tutorId == 0) {
    $_SESSION['error'] = "Invalid tutor";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'browse.php'));
    exit();
}

$conn = getDbConnection();

if ($action === 'follow') {
    // Check if already following
    $stmt = $conn->prepare("SELECT follow_id FROM user_follows WHERE student_id = ? AND tutor_id = ?");
    $stmt->bind_param("ii", $studentId, $tutorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Already following
        $stmt->close();
        $_SESSION['error'] = "You are already following this tutor";
        closeDbConnection($conn);
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'browse.php'));
        exit();
    }
    $stmt->close();
    
    // Insert new follow
    $stmt = $conn->prepare("INSERT INTO user_follows (student_id, tutor_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $studentId, $tutorId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tutor followed successfully!";
    } else {
        $_SESSION['error'] = "Failed to follow tutor";
    }
    $stmt->close();
    
} elseif ($action === 'unfollow') {
    // Delete follow
    $stmt = $conn->prepare("DELETE FROM user_follows WHERE student_id = ? AND tutor_id = ?");
    $stmt->bind_param("ii", $studentId, $tutorId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tutor unfollowed successfully!";
    } else {
        $_SESSION['error'] = "Failed to unfollow tutor";
    }
    $stmt->close();
    
} else {
    $_SESSION['error'] = "Invalid action";
}

closeDbConnection($conn);

// Redirect back to referring page
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'browse.php'));
exit();
?>