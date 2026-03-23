<?php
session_start();

// Determine which type of verification (signup or signin)
$verificationType = '';
$pendingData = null;

if (isset($_SESSION['pending_signup'])) {
    $verificationType = 'signup';
    $pendingData = $_SESSION['pending_signup'];
} elseif (isset($_SESSION['pending_signin'])) {
    $verificationType = 'signin';
    $pendingData = $_SESSION['pending_signin'];
} elseif (isset($_SESSION['pending_google_signup'])) {
    $verificationType = 'google-signup';
    $pendingData = $_SESSION['pending_google_signup'];
} else {
    // No pending verification
    header('Location: signin.php');
    exit();
}

$errors = $_SESSION['otp_errors'] ?? [];
$success = $_SESSION['otp_success'] ?? '';
unset($_SESSION['otp_errors']);
unset($_SESSION['otp_success']);

$pageTitle = "Verify Your Email";
include '../includes/header.php';

// Set title and message based on type
$title = $verificationType === 'signin' ? 'Verify It\'s You' : 'Verify Your Email';
$subtitle = $verificationType === 'signin' ? 'We\'ve sent a security code to' : 'We\'ve sent a 6-digit code to';
?>

<section class="section" style="padding: 6rem 2rem 4rem 2rem;">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <div style="background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
                        <?= $verificationType === 'signin' ? '🔐' : '✉️' ?>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;"><?= $title ?></h2>
                    <p style="color: var(--gray-600); margin: 0;">
                        <?= $subtitle ?><br>
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
                
                <!-- OTP Form -->
                <form action="verify-otp-process.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <input type="hidden" name="verification_type" value="<?= $verificationType ?>">
                    
                    <div>
                        <label for="otp" style="display: block; margin-bottom: 0.75rem; font-weight: 600; color: var(--gray-700); text-align: center;">
                            Enter Verification Code
                        </label>
                        <input type="text" id="otp" name="otp" required maxlength="6" pattern="[0-9]{6}"
                            style="width: 100%; padding: 1.25rem; border: 2px solid var(--gray-300); border-radius: var(--radius-md); font-size: 2rem; text-align: center; letter-spacing: 0.5rem; font-weight: 700;"
                            placeholder="000000" autocomplete="off" autofocus>
                        <p style="font-size: 0.875rem; color: var(--gray-500); text-align: center; margin-top: 0.75rem;">
                            Enter the 6-digit code from your email
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                        <?= $verificationType === 'signin' ? 'Verify & Sign In' : 'Verify & Complete Signup' ?>
                    </button>
                </form>
                
                <!-- Resend OTP -->
                <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
                    <p style="color: var(--gray-600); margin-bottom: 0.75rem;">Didn't receive the code?</p>
                    <a href="resend-otp.php?type=<?= $verificationType ?>" style="color: var(--primary-teal); font-weight: 600;">
                        <i class="fas fa-redo"></i> Resend Code
                    </a>
                </div>
                
                <!-- Timer -->
                <div id="timer" style="text-align: center; margin-top: 1rem; color: var(--gray-500); font-size: 0.875rem;">
                    Code expires in: <span id="countdown" style="font-weight: 700; color: var(--primary-teal);">5:00</span>
                </div>
                
            </div>
        </div>
    </div>
</section>

<script>
// OTP Input - Auto format
const otpInput = document.getElementById('otp');
otpInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Countdown Timer
let timeLeft = 300; // 5 minutes
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

<?php include '../includes/footer.php'; ?>