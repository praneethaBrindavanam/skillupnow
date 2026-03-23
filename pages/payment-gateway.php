<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['payment_data'])) {
    header("Location: browse.php");
    exit();
}

$paymentData = $_SESSION['payment_data'];
$pageTitle = "Payment";
include 'dashboard-header.php';
?>

<style>
.payment-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    max-width: 500px;
    margin: 0 auto;
}

.secure-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
    color: #065F46;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    margin-bottom: 24px;
}
</style>

<div style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
    
    <div class="secure-badge">
        <i class="fas fa-lock"></i>
        <span>Secure Payment Gateway</span>
    </div>

    <div class="payment-card">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #4FD1C5, #38B2AC); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <i class="fas fa-credit-card" style="color: white; font-size: 2rem;"></i>
            </div>
            <h2 style="margin: 0 0 8px 0; font-size: 1.5rem; font-weight: 700;">Complete Payment</h2>
            <p style="margin: 0; color: #6B7280;">Order ID: <?= htmlspecialchars($paymentData['order_id']) ?></p>
        </div>

        <!-- Order Summary -->
        <div style="background: #F9FAFB; padding: 20px; border-radius: 12px; margin-bottom: 24px;">
            <h3 style="margin: 0 0 16px 0; font-size: 1.125rem; font-weight: 600;">Order Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span><?= htmlspecialchars($paymentData['class_title']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.875rem; color: #6B7280;">
                <span><?= htmlspecialchars($paymentData['skill_name']) ?></span>
                <span><?= date('M d, Y', strtotime($paymentData['class_date'])) ?></span>
            </div>
            <hr style="margin: 16px 0; border: none; border-top: 1px solid #E5E7EB;">
            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; color: #111827;">
                <span>Total Amount:</span>
                <span style="color: #10B981;">₹<?= number_format($paymentData['amount'], 2) ?></span>
            </div>
        </div>

        <!-- Payment Method -->
        <div style="margin-bottom: 24px;">
            <p style="margin: 0 0 12px 0; font-weight: 600; color: #374151;">
                <i class="fas fa-info-circle" style="color: #4FD1C5;"></i> Payment Method: 
                <?php
                $methods = [
                    'upi' => 'UPI Payment',
                    'card' => 'Debit/Credit Card',
                    'netbanking' => 'Net Banking'
                ];
                echo $methods[$paymentData['payment_method']] ?? 'Unknown';
                ?>
            </p>
        </div>

        <!-- Simulated Payment -->
        <form method="POST" action="process-payment.php" id="paymentForm">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($paymentData['order_id']) ?>">
            
            <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <p style="margin: 0; font-size: 0.875rem; color: #92400E;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Demo Mode:</strong> This is a simulated payment. In production, this would integrate with a real payment gateway like Razorpay or Paytm.
                </p>
            </div>

            <div style="display: grid; gap: 12px; margin-bottom: 24px;">
                <button type="submit" name="action" value="success" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 1.063rem;">
                    <i class="fas fa-check-circle"></i> Simulate Successful Payment
                </button>
                
                <button type="submit" name="action" value="failure" class="btn btn-outline" style="width: 100%; padding: 16px; font-size: 1.063rem; background: #FEE2E2; color: #DC2626; border-color: #EF4444;">
                    <i class="fas fa-times-circle"></i> Simulate Failed Payment
                </button>
            </div>

            <a href="enroll-class.php?class_id=<?= $paymentData['class_id'] ?>" style="display: block; text-align: center; color: #6B7280; text-decoration: none; font-size: 0.875rem;">
                <i class="fas fa-arrow-left"></i> Cancel and Go Back
            </a>
        </form>
    </div>

    <!-- Security Info -->
    <div style="text-align: center; margin-top: 32px; color: #6B7280; font-size: 0.875rem;">
        <p><i class="fas fa-shield-alt" style="color: #10B981;"></i> Your payment information is secure and encrypted</p>
    </div>

</div>

<script>
document.getElementById('paymentForm').addEventListener('submit', function() {
    const buttons = this.querySelectorAll('button[type="submit"]');
    buttons.forEach(btn => btn.disabled = true);
});
</script>

<?php include 'dashboard-footer.php'; ?>