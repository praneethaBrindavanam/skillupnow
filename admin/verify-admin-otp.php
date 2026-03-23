<?php
session_start();

// Check if pending admin signup exists
if (!isset($_SESSION['pending_admin_signup'])) {
    header('Location: signup.php');
    exit();
}

$pendingData = $_SESSION['pending_admin_signup'];
$errors = $_SESSION['admin_otp_errors'] ?? [];
$success = $_SESSION['admin_otp_success'] ?? '';
unset($_SESSION['admin_otp_errors']);
unset($_SESSION['admin_otp_success']);

$BASE_URL = "/skillupnow";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Admin - SkillUp Now</title>
    <link rel="stylesheet" href="<?= $BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--primary-teal) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">

<div style="width: 100%; max-width: 500px;">
    
    <!-- Logo -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="font-size: 2.5rem;">✉️</div>
        </div>
        <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;">Verify Your Email</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 0;">Admin Account Verification</p>
    </div>

    <!-- OTP Form -->
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-xl); box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 0.5rem;">Enter Verification Code</h2>
            <p style="color: var(--gray-600); margin: 0;">
                We've sent a 6-digit code to<br>
                <strong><?= htmlspecialchars($pendingData['email']) ?></strong>
            </p>
        </div>

        <!-- Success Message -->
        <?php if (!empty($success)): ?>
            <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem; text-align: center;">
                <p style="margin: 0; color: #065F46; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Error Display -->
        <?php if (!empty($errors)): ?>
            <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.25rem; color: #991B1B;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="verify-admin-otp-process.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div>
                <input type="text" id="otp" name="otp" required maxlength="6" pattern="[0-9]{6}"
                    style="width: 100%; padding: 1.25rem; border: 2px solid var(--gray-300); border-radius: var(--radius-md); font-size: 2rem; text-align: center; letter-spacing: 0.5rem; font-weight: 700;"
                    placeholder="000000" autocomplete="off" autofocus>
                <p style="font-size: 0.875rem; color: var(--gray-500); text-align: center; margin-top: 0.75rem;">
                    Enter the 6-digit code from your email
                </p>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                <i class="fas fa-check"></i> Verify & Create Account
            </button>
        </form>

        <!-- Resend OTP -->
        <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
            <p style="color: var(--gray-600); margin-bottom: 0.75rem;">Didn't receive the code?</p>
            <a href="resend-admin-otp.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">
                <i class="fas fa-redo"></i> Resend Code
            </a>
        </div>

        <!-- Timer -->
        <div id="timer" style="text-align: center; margin-top: 1rem; color: var(--gray-500); font-size: 0.875rem;">
            Code expires in: <span id="countdown" style="font-weight: 700; color: var(--primary-teal);">10:00</span>
        </div>
    </div>

    <!-- Back Link -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="signup.php" style="color: white; font-weight: 600; text-decoration: none; font-size: 0.875rem;">
            <i class="fas fa-arrow-left"></i> Back to Sign Up
        </a>
    </div>
</div>

<script>
// OTP Input - Auto format
const otpInput = document.getElementById('otp');
otpInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Countdown Timer (10 minutes)
let timeLeft = 600;
const countdown = document.getElementById('countdown');

const timer = setInterval(() => {
    timeLeft--;
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    countdown.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft <= 0) {
        clearInterval(timer);
        countdown.parentElement.innerHTML = '<span style="color: #EF4444;">Code expired. Please request a new one.</span>';
    }
}, 1000);
</script>

</body>
</html>