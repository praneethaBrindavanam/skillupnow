<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$tutorId = $_SESSION['user_id'];
$conn = getDbConnection();

echo "<h2>Diagnostic Check</h2>";
echo "<p>User ID: " . $tutorId . "</p>";
echo "<p>User Type: " . $_SESSION['user_type'] . "</p>";

// Check 1: Do you have skills in user_skills?
echo "<h3>1. Skills in user_skills table:</h3>";
$stmt = $conn->prepare("SELECT * FROM user_skills WHERE user_id = ?");
$stmt->bind_param("i", $tutorId);
$stmt->execute();
$result = $stmt->get_result();
echo "<p>Found " . $result->num_rows . " skills</p>";
while ($row = $result->fetch_assoc()) {
    echo "<pre>" . print_r($row, true) . "</pre>";
}
$stmt->close();

// Check 2: Do skills table have required columns?
echo "<h3>2. Skills table columns:</h3>";
$result = $conn->query("SHOW COLUMNS FROM skills");
echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
}
echo "</table>";

// Check 3: Try the actual query
echo "<h3>3. Running the actual query:</h3>";
try {
    $stmt = $conn->prepare("
        SELECT 
            s.skill_id, 
            s.skill_name, 
            s.skill_category,
            s.moodle_course_id,
            s.moodle_enrollment_key,
            s.moodle_course_url
        FROM user_skills us
        JOIN skills s ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        ORDER BY s.skill_category, s.skill_name
    ");
    $stmt->bind_param("i", $tutorId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<p>Query returned " . $result->num_rows . " rows</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
    $stmt->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

// Check 4: skill_verifications table exists?
echo "<h3>4. Checking skill_verifications table:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'skill_verifications'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Table exists</p>";
    
    // Try to get data
    $stmt = $conn->prepare("SELECT * FROM skill_verifications WHERE user_id = ?");
    $stmt->bind_param("i", $tutorId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<p>Found " . $result->num_rows . " verification records</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>❌ Table does NOT exist - Run tutor_verification_system.sql</p>";
}

closeDbConnection($conn);
?>