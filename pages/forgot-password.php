<?php
session_start();

$pageTitle = "Forgot Password";
$errors = $_SESSION['forgot_errors'] ?? [];
$success = $_SESSION['forgot_success'] ?? '';

// Clear errors after displaying
unset($_SESSION['forgot_errors']);
unset($_SESSION['forgot_success']);

include '../includes/header.php';
?>

<section class="section" style="min-height: calc(100vh - 80px - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <div style="background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;">Forgot Password?</h2>
                    <p style="color: var(--gray-600); margin: 0;">Enter your email and we'll send you a reset link</p>
                </div>
                
                <?php if ($success): ?>
                    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="fas fa-check-circle" style="color: #065F46; margin-top: 0.25rem; font-size: 1.25rem;"></i>
                            <div style="flex: 1; color: #065F46;">
                                <?= $success ?>
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
                
                <form action="process-forgot-password.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- Email -->
                    <div>
                        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            College Email <span style="color: #DC2626;">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                            placeholder="you@college.edu"
                            autofocus
                        >
                        <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem; margin-bottom: 0;">
                            <i class="fas fa-info-circle"></i> Enter the email you used to register
                        </p>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-paper-plane"></i>
                        Send Reset Link
                    </button>
                </form>
                
                <!-- Back to Sign In Link -->
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                    <p style="color: var(--gray-600); margin: 0;">
                        Remember your password? 
                        <a href="signin.php" style="color: var(--primary-teal); font-weight: 600;">
                            <i class="fas fa-arrow-left"></i> Back to Sign In
                        </a>
                    </p>
                </div>
            </div>
            
            <!-- Help Notice -->
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="font-size: 0.875rem; color: var(--gray-500);">
                    <i class="fas fa-question-circle"></i>
                    Need help? Contact support@skillnest.com
                </p>
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
</style>

<?php include '../includes/footer.php'; ?>