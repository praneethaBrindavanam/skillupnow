<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$skillId = intval($data['skill_id'] ?? 0);

if ($skillId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid skill ID']);
    exit();
}

$conn = getDbConnection();

try {
    // Check if verification record exists
    $stmt = $conn->prepare("
        SELECT verification_id FROM skill_verifications 
        WHERE user_id = ? AND skill_id = ?
    ");
    $stmt->bind_param("ii", $userId, $skillId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE skill_verifications 
            SET moodle_exam_started = 1,
                instructions_accepted = 1,
                instructions_accepted_at = NOW(),
                updated_at = NOW()
            WHERE user_id = ? AND skill_id = ?
        ");
        $stmt->bind_param("ii", $userId, $skillId);
        $stmt->execute();
    } else {
        // Create new record
        $stmt = $conn->prepare("
            INSERT INTO skill_verifications 
            (user_id, skill_id, moodle_exam_started, instructions_accepted, instructions_accepted_at, created_at)
            VALUES (?, ?, 1, 1, NOW(), NOW())
        ");
        $stmt->bind_param("ii", $userId, $skillId);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Exam started']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

closeDbConnection($conn);
?>