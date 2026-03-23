<?php
session_start();
require_once '../includes/config.php';

$token = $_GET['token'] ?? '';
$errors = $_SESSION['reset_errors'] ?? [];
$success = $_SESSION['reset_success'] ?? '';

// Clear messages
unset($_SESSION['reset_errors']);
unset($_SESSION['reset_success']);

$validToken = false;
$user = null;

if (empty($token)) {
    $errors[] = "Invalid or missing reset token";
} else {
    // Verify token
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT user_id, full_name, email, verification_token FROM users WHERE verification_token = ? AND is_active = 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $validToken = true;
    } else {
        $errors[] = "Invalid or expired reset token. Please request a new password reset link.";
    }
    
    $stmt->close();
    closeDbConnection($conn);
}

$pageTitle = "Reset Password";
include '../includes/header.php';
?>

<section class="section" style="min-height: calc(100vh - 80px - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <div style="background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;">Reset Your Password</h2>
                    <?php if ($validToken): ?>
                        <p style="color: var(--gray-600); margin: 0;">
                            Hi <?= htmlspecialchars($user['full_name']) ?>, create a new password below
                        </p>
                    <?php else: ?>
                        <p style="color: var(--gray-600); margin: 0;">Set a new password for your account</p>
                    <?php endif; ?>
                </div>
                
                <?php if ($success): ?>
                    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="fas fa-check-circle" style="color: #065F46; margin-top: 0.25rem; font-size: 1.25rem;"></i>
                            <div style="flex: 1;">
                                <p style="margin: 0; color: #065F46;"><?= htmlspecialchars($success) ?></p>
                                <a href="signin.php" style="color: #065F46; font-weight: 600; text-decoration: underline; display: inline-block; margin-top: 0.5rem;">
                                    Go to Sign In →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="fas fa-exclamation-circle" style="color: #DC2626; margin-top: 0.25rem; font-size: 1.25rem;"></i>
                            <div style="flex: 1;">
                                <ul style="margin: 0; padding-left: 0; list-style: none; color: #991B1B;">
                                    <?php foreach ($errors as $error): ?>
                                        <li style="margin-bottom: 0.25rem;"><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($validToken && !$success): ?>
                    <form action="process-reset-password.php" method="POST" id="resetForm" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <!-- New Password -->
                        <div>
                            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                New Password <span style="color: #DC2626;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required
                                    minlength="8"
                                    style="width: 100%; padding: 0.875rem 1rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                                    placeholder="Enter new password"
                                    autofocus
                                >
                                <button 
                                    type="button" 
                                    class="password-toggle"
                                    data-target="password"
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 1.25rem;">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                            <div style="margin-top: 0.75rem;">
                                <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0 0 0.5rem 0; font-weight: 600;">
                                    Password must contain:
                                </p>
                                <ul style="font-size: 0.875rem; color: var(--gray-500); margin: 0; padding-left: 1.5rem;">
                                    <li id="length-check">At least 8 characters</li>
                                    <li id="uppercase-check">One uppercase letter</li>
                                    <li id="lowercase-check">One lowercase letter</li>
                                    <li id="number-check">One number</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                                Confirm New Password <span style="color: #DC2626;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    required
                                    style="width: 100%; padding: 0.875rem 1rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                                    placeholder="Confirm new password"
                                >
                                <button 
                                    type="button" 
                                    class="password-toggle"
                                    data-target="confirm_password"
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 1.25rem;">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                            <p id="match-message" style="font-size: 0.875rem; margin-top: 0.5rem; display: none;"></p>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-check-circle"></i>
                            Reset Password
                        </button>
                    </form>
                <?php elseif (!$validToken): ?>
                    <div style="text-align: center; padding: 2rem;">
                        <a href="forgot-password.php" class="btn btn-primary" style="display: inline-flex;">
                            <i class="fas fa-redo"></i>
                            Request New Reset Link
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Back to Sign In Link -->
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                    <p style="color: var(--gray-600); margin: 0;">
                        <a href="signin.php" style="color: var(--primary-teal); font-weight: 600;">
                            <i class="fas fa-arrow-left"></i> Back to Sign In
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
input:focus {
    outline: none;
    border-color: var(--primary-cyan) !important;
    box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.1);
}
.password-toggle:hover {
    color: var(--primary-teal);
}
.valid-check {
    color: #10B981 !important;
}
.valid-check::before {
    content: '✓ ';
    font-weight: bold;
}
</style>

<script>
// Password toggle functionality
document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye-slash';
        }
    });
});

// Password validation
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const form = document.getElementById('resetForm');

if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Check length
        const lengthCheck = document.getElementById('length-check');
        if (password.length >= 8) {
            lengthCheck.classList.add('valid-check');
        } else {
            lengthCheck.classList.remove('valid-check');
        }
        
        // Check uppercase
        const uppercaseCheck = document.getElementById('uppercase-check');
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.classList.add('valid-check');
        } else {
            uppercaseCheck.classList.remove('valid-check');
        }
        
        // Check lowercase
        const lowercaseCheck = document.getElementById('lowercase-check');
        if (/[a-z]/.test(password)) {
            lowercaseCheck.classList.add('valid-check');
        } else {
            lowercaseCheck.classList.remove('valid-check');
        }
        
        // Check number
        const numberCheck = document.getElementById('number-check');
        if (/[0-9]/.test(password)) {
            numberCheck.classList.add('valid-check');
        } else {
            numberCheck.classList.remove('valid-check');
        }
        
        checkPasswordMatch();
    });
    
    confirmInput.addEventListener('input', checkPasswordMatch);
    
    function checkPasswordMatch() {
        const matchMessage = document.getElementById('match-message');
        if (confirmInput.value === '') {
            matchMessage.style.display = 'none';
            return;
        }
        
        if (passwordInput.value === confirmInput.value) {
            matchMessage.textContent = '✓ Passwords match';
            matchMessage.style.color = '#10B981';
            matchMessage.style.display = 'block';
        } else {
            matchMessage.textContent = '✗ Passwords do not match';
            matchMessage.style.color = '#EF4444';
            matchMessage.style.display = 'block';
        }
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmInput.value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return;
        }
        
        if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
            e.preventDefault();
            alert('Password does not meet all requirements!');
            return;
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>