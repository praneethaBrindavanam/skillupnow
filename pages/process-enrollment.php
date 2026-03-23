<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
$classId = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$agreeTerms = isset($_POST['agree_terms']);

if (!$agreeTerms) {
    $_SESSION['error'] = "You must agree to the terms and conditions";
    header("Location: enroll-class.php?class_id=" . $classId);
    exit();
}

$conn = getDbConnection();

// Get class details
$stmt = $conn->prepare("
    SELECT 
        sc.*,
        s.skill_name,
        u.full_name as tutor_name,
        u.email as tutor_email,
        (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id) as enrolled_count,
        (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id AND student_id = ?) as is_enrolled
    FROM scheduled_classes sc
    INNER JOIN skills s ON sc.skill_id = s.skill_id
    INNER JOIN users u ON sc.tutor_id = u.user_id
    WHERE sc.class_id = ?
");
$stmt->bind_param("ii", $studentId, $classId);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    $_SESSION['error'] = "Class not found";
    closeDbConnection($conn);
    header("Location: browse.php");
    exit();
}

// Check if already enrolled
if ($class['is_enrolled']) {
    $_SESSION['error'] = "You are already enrolled in this class";
    closeDbConnection($conn);
    header("Location: my-classes.php");
    exit();
}

// Check if class is full
if ($class['enrolled_count'] >= $class['max_students']) {
    $_SESSION['error'] = "This class is full";
    closeDbConnection($conn);
    header("Location: browse.php");
    exit();
}

// If it's a FREE class, enroll directly
if ($class['is_free']) {
    
    // Insert enrollment - FREE CLASS
    $stmt = $conn->prepare("INSERT INTO class_enrollments (class_id, student_id, enrollment_status, enrolled_at) VALUES (?, ?, 'confirmed', NOW())");
    $stmt->bind_param("ii", $classId, $studentId);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Ensure meeting link has https://
        $meetingLink = $class['meeting_link'];
        if (!preg_match("~^(?:f|ht)tps?://~i", $meetingLink)) {
            $meetingLink = 'https://' . $meetingLink;
        }
        
        // Send confirmation email to student
        $to = $email;
        $subject = "Enrollment Confirmed: " . $class['class_title'];
        $message = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #10B981; border-radius: 12px;'>
                    <h2 style='color: #10B981;'>✅ Enrollment Confirmed!</h2>
                    <p>Dear {$fullName},</p>
                    <p>You have successfully enrolled in:</p>
                    <div style='background: #F0FDF4; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p><strong>Class:</strong> {$class['class_title']}</p>
                        <p><strong>Skill:</strong> {$class['skill_name']}</p>
                        <p><strong>Tutor:</strong> {$class['tutor_name']}</p>
                        <p><strong>Date:</strong> " . date('F d, Y', strtotime($class['class_date'])) . "</p>
                        <p><strong>Time:</strong> " . date('h:i A', strtotime($class['start_time'])) . "</p>
                        <p><strong>Type:</strong> <span style='color: #10B981;'>FREE CLASS</span></p>
                    </div>
                    <p><strong>Meeting Link:</strong> <a href='{$meetingLink}'>{$meetingLink}</a></p>
                    <p>See you in class!</p>
                    <p>Best regards,<br><strong>SkillUp Now Team</strong></p>
                </div>
            </body>
            </html>
        ";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: SkillUp Now <noreply@skillupnow.com>\r\n";
        @mail($to, $subject, $message, $headers);
        
        // Send notification to tutor
        $tutorSubject = "New Student Enrolled: " . $class['class_title'];
        $tutorMessage = "
            <html>
            <body>
                <h2>New Student Enrolled!</h2>
                <p>Dear {$class['tutor_name']},</p>
                <p>A new student has enrolled in your class:</p>
                <p><strong>Student:</strong> {$fullName}</p>
                <p><strong>Class:</strong> {$class['class_title']}</p>
                <p><strong>Total Enrolled:</strong> " . ($class['enrolled_count'] + 1) . "/" . $class['max_students'] . "</p>
                <p>Best regards,<br>SkillUp Now Team</p>
            </body>
            </html>
        ";
        @mail($class['tutor_email'], $tutorSubject, $tutorMessage, $headers);
        
        $_SESSION['success'] = "Successfully enrolled in the class! Check your email for details.";
        closeDbConnection($conn);
        header("Location: my-classes.php");
        exit();
        
    } else {
        $stmt->close();
        $_SESSION['error'] = "Failed to enroll in class: " . $conn->error;
        closeDbConnection($conn);
        header("Location: enroll-class.php?class_id=" . $classId);
        exit();
    }
    
} else {
    // PAID CLASS - Process Payment
    
    // Validate payment method is selected
    if (empty($paymentMethod)) {
        $_SESSION['error'] = "Please select a payment method";
        closeDbConnection($conn);
        header("Location: enroll-class.php?class_id=" . $classId);
        exit();
    }
    
    // Calculate total amount
    $classFee = floatval($class['price']);
    $platformFee = $classFee * 0.05;
    $totalAmount = $classFee + $platformFee;
    
    // Generate unique order ID
    $orderId = 'ORD_' . time() . '_' . $studentId . '_' . $classId;
    
    // Check if payment columns exist
    $checkColumns = $conn->query("SHOW COLUMNS FROM class_enrollments LIKE 'payment_status'");
    if ($checkColumns->num_rows == 0) {
        $_SESSION['error'] = "Database error: Payment system not configured. Please contact admin.";
        closeDbConnection($conn);
        header("Location: enroll-class.php?class_id=" . $classId);
        exit();
    }
    
    // Insert enrollment with payment info - PAID CLASS
    // Using 'confirmed' for enrollment_status, 'pending' for payment_status
    $stmt = $conn->prepare("
        INSERT INTO class_enrollments 
        (class_id, student_id, enrollment_status, payment_status, payment_amount, payment_order_id, enrolled_at) 
        VALUES (?, ?, 'confirmed', 'pending', ?, ?, NOW())
    ");
    
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        closeDbConnection($conn);
        header("Location: enroll-class.php?class_id=" . $classId);
        exit();
    }
    
    $stmt->bind_param("iids", $classId, $studentId, $totalAmount, $orderId);
    
    if (!$stmt->execute()) {
        $_SESSION['error'] = "Failed to create enrollment: " . $stmt->error;
        $stmt->close();
        closeDbConnection($conn);
        header("Location: enroll-class.php?class_id=" . $classId);
        exit();
    }
    
    $stmt->close();
    
    // Store payment details in session for payment page
    $_SESSION['payment_data'] = [
        'order_id' => $orderId,
        'class_id' => $classId,
        'class_title' => $class['class_title'],
        'skill_name' => $class['skill_name'],
        'class_date' => $class['class_date'],
        'start_time' => $class['start_time'],
        'amount' => $totalAmount,
        'payment_method' => $paymentMethod,
        'student_name' => $fullName,
        'student_email' => $email,
        'student_phone' => $phone,
        'address' => $address
    ];
    
    closeDbConnection($conn);
    
    // Redirect to payment gateway
    header("Location: payment-gateway.php");
    exit();
}
?>