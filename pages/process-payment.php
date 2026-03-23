<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['payment_data'])) {
    header("Location: browse.php");
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$orderId = isset($_POST['order_id']) ? $_POST['order_id'] : '';
$paymentData = $_SESSION['payment_data'];
$studentId = $_SESSION['user_id'];

$conn = getDbConnection();

if ($action === 'success') {
    // Payment SUCCESSFUL
    
    // Update enrollment status
    $stmt = $conn->prepare("
        UPDATE class_enrollments 
        SET enrollment_status = 'confirmed', 
            payment_status = 'completed',
            payment_transaction_id = ?,
            payment_completed_at = NOW()
        WHERE payment_order_id = ? AND student_id = ?
    ");
    $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);
    $stmt->bind_param("ssi", $transactionId, $orderId, $studentId);
    $stmt->execute();
    $stmt->close();
    
    // Get class and tutor details for email
    $stmt = $conn->prepare("
        SELECT sc.*, s.skill_name, u.full_name as tutor_name, u.email as tutor_email
        FROM scheduled_classes sc
        INNER JOIN skills s ON sc.skill_id = s.skill_id
        INNER JOIN users u ON sc.tutor_id = u.user_id
        WHERE sc.class_id = ?
    ");
    $stmt->bind_param("i", $paymentData['class_id']);
    $stmt->execute();
    $class = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Send confirmation email to student
    $to = $paymentData['student_email'];
    $subject = "Payment Successful - Enrollment Confirmed";
    $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #10B981; border-radius: 12px;'>
                <h2 style='color: #10B981;'>✅ Payment Successful!</h2>
                <p>Dear {$paymentData['student_name']},</p>
                <p>Your payment has been processed successfully. You are now enrolled in:</p>
                <div style='background: #F0FDF4; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p><strong>Class:</strong> {$class['class_title']}</p>
                    <p><strong>Skill:</strong> {$class['skill_name']}</p>
                    <p><strong>Tutor:</strong> {$class['tutor_name']}</p>
                    <p><strong>Date:</strong> " . date('F d, Y', strtotime($class['class_date'])) . "</p>
                    <p><strong>Time:</strong> " . date('h:i A', strtotime($class['start_time'])) . "</p>
                </div>
                <div style='background: #E0F2FE; padding: 15px; border-radius: 8px;'>
                    <p style='margin: 0;'><strong>Payment Details:</strong></p>
                    <p style='margin: 5px 0 0 0;'>Order ID: {$orderId}</p>
                    <p style='margin: 5px 0 0 0;'>Transaction ID: {$transactionId}</p>
                    <p style='margin: 5px 0 0 0;'>Amount Paid: ₹" . number_format($paymentData['amount'], 2) . "</p>
                </div>
                <p><strong>Meeting Link:</strong> <a href='{$class['meeting_link']}'>{$class['meeting_link']}</a></p>
                <p style='margin-top: 30px;'>See you in class!</p>
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
    $tutorSubject = "New Paid Enrollment: " . $class['class_title'];
    $tutorMessage = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #4FD1C5;'>💰 New Paid Enrollment!</h2>
                <p>Dear {$class['tutor_name']},</p>
                <p>A student has successfully enrolled in your class:</p>
                <p><strong>Student:</strong> {$paymentData['student_name']}</p>
                <p><strong>Class:</strong> {$class['class_title']}</p>
                <p><strong>Amount Paid:</strong> ₹" . number_format($paymentData['amount'], 2) . "</p>
                <p>Best regards,<br>SkillUp Now Team</p>
            </div>
        </body>
        </html>
    ";
    @mail($class['tutor_email'], $tutorSubject, $tutorMessage, $headers);
    
    // Clear payment data from session
    unset($_SESSION['payment_data']);
    
    $_SESSION['success'] = "Payment successful! You are now enrolled in the class. Check your email for details.";
    closeDbConnection($conn);
    header("Location: payment-success.php?order_id=" . $orderId . "&txn=" . $transactionId);
    exit();
    
} else {
    // Payment FAILED
    
    // Update enrollment status to failed
    $stmt = $conn->prepare("
        UPDATE class_enrollments 
        SET enrollment_status = 'failed', 
            payment_status = 'failed'
        WHERE payment_order_id = ? AND student_id = ?
    ");
    $stmt->bind_param("si", $orderId, $studentId);
    $stmt->execute();
    $stmt->close();
    
    closeDbConnection($conn);
    
    $_SESSION['error'] = "Payment failed. Please try again.";
    header("Location: payment-failure.php?order_id=" . $orderId);
    exit();
}
?>