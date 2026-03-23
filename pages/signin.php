<?php
session_start();

$pageTitle = "Sign In";
$errors = $_SESSION['signin_errors'] ?? [];
$savedEmail = $_SESSION['signin_email'] ?? '';

// Clear errors after displaying
unset($_SESSION['signin_errors']);
unset($_SESSION['signin_email']);

include '../includes/header.php';
?>

<section class="section" style="min-height: calc(100vh - 80px - 300px); display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <div style="background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;">Welcome Back!</h2>
                    <p style="color: var(--gray-600); margin: 0;">Sign in to continue your learning journey</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="fas fa-exclamation-circle" style="color: #DC2626; margin-top: 0.25rem; font-size: 1.25rem;"></i>
                            <div style="flex: 1;">
                                <strong style="color: #DC2626; display: block; margin-bottom: 0.5rem;">Error:</strong>
                                <ul style="margin: 0; padding-left: 0; list-style: none; color: #991B1B;">
                                    <?php foreach ($errors as $error): ?>
                                        <li style="margin-bottom: 0.25rem;"><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form action="process-signin.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
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
                            value="<?= htmlspecialchars($savedEmail) ?>"
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                            placeholder="you@college.edu"
                            autofocus
                        >
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="password" style="font-weight: 600; color: var(--gray-700);">
                                Password <span style="color: #DC2626;">*</span>
                            </label>
                            <a href="forgot-password.php" style="color: var(--primary-teal); font-size: 0.875rem; font-weight: 500;">
                                Forgot password?
                            </a>
                        </div>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                style="width: 100%; padding: 0.875rem 1rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                                placeholder="Enter your password"
                            >
                            <button 
                                type="button" 
                                class="password-toggle"
                                data-target="password"
                                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 1.25rem;">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- CAPTCHA -->
                    <div>
                        <label for="captcha" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Verification Code <span style="color: #DC2626;">*</span>
                        </label>
                        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                            <img src="captcha.php" id="captcha-image" alt="CAPTCHA" 
                                style="border: 2px solid var(--primary-cyan); border-radius: var(--radius-md); cursor: pointer; height: 50px;"
                                onclick="this.src='captcha.php?'+Math.random();" 
                                title="Click to refresh">
                            <button type="button" onclick="document.getElementById('captcha-image').src='captcha.php?'+Math.random();"
                                style="padding: 0.75rem 1rem; background: var(--gray-100); border: 2px solid var(--gray-300); border-radius: var(--radius-md); cursor: pointer; color: var(--gray-700); font-weight: 600; white-space: nowrap;">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        <input type="text" id="captcha" name="captcha" required maxlength="6"
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; text-transform: uppercase;"
                            placeholder="Enter code shown above" autocomplete="off">
                        <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem; margin-bottom: 0;">
                            <i class="fas fa-shield-alt"></i> Enter the 6 characters shown in the image
                        </p>
                    </div>
                    <a href="forgot-password.php"
   style="color: var(--primary-teal); font-size: 0.875rem; font-weight: 500;">
    Forgot password? Click here to reset it.
</a>
                    <!-- Remember Me -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            style="width: 18px; height: 18px; cursor: pointer;"
                        >
                        <label for="remember" style="color: var(--gray-700); cursor: pointer; user-select: none;">
                            Remember me for 30 days
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        Sign In
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                
                <!-- OR Divider -->
                <div style="display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0;">
                    <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                    <span style="color: var(--gray-500); font-size: 0.875rem; font-weight: 600;">OR</span>
                    <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                </div>
                
                <!-- Google Sign In Button -->
                <a href="google-signin.php" style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 0.875rem; border: 2px solid var(--gray-300); border-radius: var(--radius-md); background: white; color: var(--gray-700); font-weight: 600; text-decoration: none; transition: all 0.2s;">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign in with Google
                </a>
                
                <!-- Sign Up Link -->
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                    <p style="color: var(--gray-600); margin: 0;">
                        Don't have an account? 
                        <a href="signup.php" style="color: var(--primary-teal); font-weight: 600;">
                            Sign up now
                        </a>
                    </p>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="font-size: 0.875rem; color: var(--gray-500);">
                    <i class="fas fa-lock"></i>
                    Your information is secure and encrypted
                </p>
            </div>
        </div>
    </div>
</section>

<style>
input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--primary-cyan) !important;
    box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.1);
}
.password-toggle:hover {
    color: var(--primary-teal);
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
</script>

<?php include '../includes/footer.php'; ?>