<?php
session_start();
require_once '../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'learner') {
    header("Location: signin.php");
    exit();
}

$studentId = $_SESSION['user_id'];
$classId = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($classId == 0) {
    $_SESSION['error'] = "Invalid class ID";
    header("Location: browse.php");
    exit();
}

$conn = getDbConnection();

// Get class details
$stmt = $conn->prepare("
    SELECT 
        sc.*,
        s.skill_name,
        u.user_id as tutor_user_id,
        u.full_name as tutor_name,
        u.custom_user_id as tutor_custom_id,
        (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id) as enrolled_count,
        (SELECT COUNT(*) FROM class_enrollments WHERE class_id = sc.class_id AND student_id = ?) as is_enrolled,
        (SELECT COUNT(*) FROM user_follows WHERE student_id = ? AND tutor_id = sc.tutor_id) as is_following
    FROM scheduled_classes sc
    INNER JOIN skills s ON sc.skill_id = s.skill_id
    INNER JOIN users u ON sc.tutor_id = u.user_id
    WHERE sc.class_id = ?
");
$stmt->bind_param("iii", $studentId, $studentId, $classId);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    $_SESSION['error'] = "Class not found";
    header("Location: browse.php");
    exit();
}

// Check if already enrolled
if ($class['is_enrolled']) {
    $_SESSION['error'] = "You are already enrolled in this class";
    header("Location: my-classes.php");
    exit();
}

// Check if class is full
if ($class['enrolled_count'] >= $class['max_students']) {
    $_SESSION['error'] = "This class is full";
    header("Location: browse.php");
    exit();
}

// Check if following tutor (required)
if (!$class['is_following']) {
    $_SESSION['error'] = "You must follow the tutor before enrolling in their class";
    header("Location: browse.php");
    exit();
}

// Get student details
$stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE user_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

closeDbConnection($conn);

$pageTitle = "Enroll in Class";
include 'dashboard-header.php';
?>

<style>
.form-section {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.form-field {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #E5E7EB;
    border-radius: 8px;
    font-size: 1rem;
}

.form-input:disabled {
    background: #F9FAFB;
    cursor: not-allowed;
}

.price-badge {
    display: inline-block;
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 1.25rem;
    font-weight: 800;
}

.free-badge {
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
}
</style>

<div style="max-width: 800px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 32px;">
        <a href="browse.php" style="color: #4FD1C5; text-decoration: none; font-weight: 600; margin-bottom: 16px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Browse
        </a>
        
        <h1 style="margin-bottom: 8px; font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-user-plus"></i> Enroll in Class
        </h1>
        <p style="color: #6B7280; font-size: 1.125rem;">
            Review details and complete enrollment
        </p>
    </div>

    <!-- Class Details Card -->
    <div class="form-section" style="background: linear-gradient(135deg, #F0FDFA, white); border: 2px solid #4FD1C5;">
        <h3 style="margin: 0 0 16px 0; color: #111827; font-size: 1.5rem; font-weight: 700;">
            <?= htmlspecialchars($class['class_title']) ?>
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div>
                <p style="margin: 0; font-size: 0.875rem; color: #6B7280; font-weight: 600;">SKILL</p>
                <p style="margin: 4px 0 0 0; font-weight: 600; color: #111827;"><?= htmlspecialchars($class['skill_name']) ?></p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.875rem; color: #6B7280; font-weight: 600;">TUTOR</p>
                <p style="margin: 4px 0 0 0; font-weight: 600; color: #111827;"><?= htmlspecialchars($class['tutor_name']) ?></p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.875rem; color: #6B7280; font-weight: 600;">DATE</p>
                <p style="margin: 4px 0 0 0; font-weight: 600; color: #111827;"><?= date('M d, Y', strtotime($class['class_date'])) ?></p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.875rem; color: #6B7280; font-weight: 600;">TIME</p>
                <p style="margin: 4px 0 0 0; font-weight: 600; color: #111827;"><?= date('h:i A', strtotime($class['start_time'])) ?></p>
            </div>
        </div>

        <?php if (!empty($class['class_description'])): ?>
            <div style="padding: 16px; background: white; border-radius: 8px; margin-bottom: 16px;">
                <p style="margin: 0; color: #4B5563;"><?= htmlspecialchars($class['class_description']) ?></p>
            </div>
        <?php endif; ?>

        <div style="text-align: center; padding: 20px; background: white; border-radius: 12px;">
            <?php if ($class['is_free']): ?>
                <span class="price-badge free-badge">
                    <i class="fas fa-gift"></i> FREE CLASS
                </span>
            <?php else: ?>
                <span class="price-badge">
                    <i class="fas fa-rupee-sign"></i> <?= number_format($class['price'], 2) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enrollment Form -->
    <form method="POST" action="process-enrollment.php" id="enrollmentForm">
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        
        <!-- Student Details -->
        <div class="form-section">
            <h3 style="margin: 0 0 20px 0; color: #111827; font-size: 1.25rem; font-weight: 700;">
                <i class="fas fa-user"></i> Student Details
            </h3>

            <div class="form-field">
                <label class="form-label">
                    Full Name <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="full_name" class="form-input" 
                       value="<?= htmlspecialchars($student['full_name']) ?>"
                       <?= $class['is_free'] ? 'readonly' : 'required' ?>>
                <?php if ($class['is_free']): ?>
                    <p style="margin: 8px 0 0 0; font-size: 0.875rem; color: #6B7280;">
                        <i class="fas fa-lock"></i> This field is auto-filled from your profile
                    </p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label class="form-label">
                    Email Address <span style="color: #EF4444;">*</span>
                </label>
                <input type="email" name="email" class="form-input" 
                       value="<?= htmlspecialchars($student['email']) ?>"
                       <?= $class['is_free'] ? 'readonly' : 'required' ?>>
                <?php if ($class['is_free']): ?>
                    <p style="margin: 8px 0 0 0; font-size: 0.875rem; color: #6B7280;">
                        <i class="fas fa-lock"></i> Confirmation email will be sent here
                    </p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label class="form-label">
                    Phone Number <?= !$class['is_free'] ? '<span style="color: #EF4444;">*</span>' : '' ?>
                </label>
                <input type="tel" name="phone" class="form-input" 
                       value="<?= htmlspecialchars($student['phone'] ?? '') ?>"
                       placeholder="e.g., +91 9876543210"
                       <?= $class['is_free'] ? 'readonly' : 'required' ?>>
                <?php if (!$class['is_free']): ?>
                    <p style="margin: 8px 0 0 0; font-size: 0.875rem; color: #6B7280;">
                        <i class="fas fa-info-circle"></i> Required for payment confirmation
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!$class['is_free']): ?>
                <div class="form-field">
                    <label class="form-label">
                        Billing Address
                    </label>
                    <textarea name="address" class="form-input" rows="3" 
                              placeholder="Enter your billing address"></textarea>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment Summary (for paid classes) -->
        <?php if (!$class['is_free']): ?>
            <div class="form-section" style="background: linear-gradient(135deg, #FEF3C7, white); border: 2px solid #F59E0B;">
                <h3 style="margin: 0 0 20px 0; color: #111827; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-receipt"></i> Payment Summary
                </h3>

                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid #FDE68A;">
                    <span style="font-weight: 600; color: #374151;">Class Fee:</span>
                    <span style="font-weight: 700; color: #111827; font-size: 1.125rem;">₹<?= number_format($class['price'], 2) ?></span>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid #FDE68A;">
                    <span style="font-weight: 600; color: #374151;">Platform Fee (5%):</span>
                    <span style="font-weight: 700; color: #111827; font-size: 1.125rem;">₹<?= number_format($class['price'] * 0.05, 2) ?></span>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 16px 0; background: #FFFBEB; margin: 16px -24px -24px -24px; padding: 20px 24px; border-radius: 0 0 16px 16px;">
                    <span style="font-weight: 700; color: #111827; font-size: 1.25rem;">Total Amount:</span>
                    <span style="font-weight: 800; color: #B45309; font-size: 1.5rem;">₹<?= number_format($class['price'] * 1.05, 2) ?></span>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="form-section">
                <h3 style="margin: 0 0 20px 0; color: #111827; font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-credit-card"></i> Payment Method
                </h3>

                <div style="display: grid; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 2px solid #E5E7EB; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="payment_method" value="upi" required style="width: 20px; height: 20px;">
                        <i class="fas fa-mobile-alt" style="font-size: 1.5rem; color: #4FD1C5;"></i>
                        <div>
                            <p style="margin: 0; font-weight: 600; color: #111827;">UPI Payment</p>
                            <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #6B7280;">Pay using Google Pay, PhonePe, Paytm</p>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 2px solid #E5E7EB; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="payment_method" value="card" required style="width: 20px; height: 20px;">
                        <i class="fas fa-credit-card" style="font-size: 1.5rem; color: #4FD1C5;"></i>
                        <div>
                            <p style="margin: 0; font-weight: 600; color: #111827;">Debit/Credit Card</p>
                            <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #6B7280;">Visa, Mastercard, RuPay</p>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 2px solid #E5E7EB; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="payment_method" value="netbanking" required style="width: 20px; height: 20px;">
                        <i class="fas fa-university" style="font-size: 1.5rem; color: #4FD1C5;"></i>
                        <div>
                            <p style="margin: 0; font-weight: 600; color: #111827;">Net Banking</p>
                            <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #6B7280;">All major banks supported</p>
                        </div>
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <!-- Terms and Submit -->
        <div class="form-section">
            <label style="display: flex; align-items: start; gap: 12px; cursor: pointer; margin-bottom: 24px;">
                <input type="checkbox" name="agree_terms" required style="width: 20px; height: 20px; margin-top: 2px;">
                <span style="color: #4B5563; font-size: 0.938rem;">
                    I agree to the <a href="#" style="color: #4FD1C5; text-decoration: none; font-weight: 600;">Terms & Conditions</a> 
                    and <a href="#" style="color: #4FD1C5; text-decoration: none; font-weight: 600;">Refund Policy</a>
                </span>
            </label>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <a href="browse.php" class="btn btn-outline" style="padding: 14px 28px; text-decoration: none; display: inline-block;">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="padding: 14px 28px;">
                    <?php if ($class['is_free']): ?>
                        <i class="fas fa-check"></i> Confirm Enrollment
                    <?php else: ?>
                        <i class="fas fa-lock"></i> Proceed to Payment
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </form>

</div>

<script>
// Highlight selected payment method
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="payment_method"]').forEach(r => {
            r.parentElement.style.borderColor = '#E5E7EB';
            r.parentElement.style.background = 'white';
        });
        this.parentElement.style.borderColor = '#4FD1C5';
        this.parentElement.style.background = '#F0FDFA';
    });
});

// Form submission
document.getElementById('enrollmentForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    document.getElementById('submitBtn').disabled = true;
});
</script>

<?php include 'dashboard-footer.php'; ?>