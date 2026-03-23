<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$pageTitle = "Admin Sign Up";
$BASE_URL = "/skillupnow";

// Get form data from session if validation failed
$formData = $_SESSION['admin_signup_data'] ?? [];
unset($_SESSION['admin_signup_data']);

$errors = $_SESSION['admin_errors'] ?? [];
unset($_SESSION['admin_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - SkillUp Now</title>
    <link rel="stylesheet" href="<?= $BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--primary-teal) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">

<div style="width: 100%; max-width: 500px;">
    
    <!-- Logo -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <a href="<?= $BASE_URL ?>/index.php" style="text-decoration: none;">
            <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div style="font-size: 2.5rem; font-weight: bold; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">S</div>
            </div>
            <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;">SkillUp Now</h1>
            <p style="color: rgba(255,255,255,0.9); margin: 0;">Admin Registration</p>
        </a>
    </div>

    <!-- Sign Up Form -->
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-xl); box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        
        <h2 style="margin-bottom: 0.5rem; text-align: center;">Create Admin Account</h2>
        <p style="color: var(--gray-600); text-align: center; margin-bottom: 2rem;">
            Register as an administrator
        </p>

        <?php if (!empty($errors)): ?>
            <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: start; gap: 0.75rem;">
                    <i class="fas fa-exclamation-circle" style="color: #DC2626; margin-top: 0.125rem;"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p style="margin: 0 0 0.25rem 0; color: #991B1B;"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form action="process-signup.php" method="POST">
            
            <!-- Full Name -->
            <div style="margin-bottom: 1.5rem;">
                <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <input type="text" id="full_name" name="full_name" required
                    value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="Enter your full name">
            </div>

            <!-- Username -->
            <div style="margin-bottom: 1.5rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-at"></i> Username
                </label>
                <input type="text" id="username" name="username" required minlength="4" maxlength="20"
                    value="<?= htmlspecialchars($formData['username'] ?? '') ?>"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="Choose a username"
                    pattern="^[a-zA-Z0-9_]{4,20}$">
                <p style="font-size: 0.75rem; color: var(--gray-500); margin: 0.5rem 0 0 0;">
                    4-20 characters, letters, numbers and underscore only
                </p>
            </div>

            <!-- Email -->
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" id="email" name="email" required
                    value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="admin@example.com">
            </div>

            <!-- Admin Role -->
            <div style="margin-bottom: 1.5rem;">
                <label for="admin_role" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-shield-alt"></i> Admin Role
                </label>
                <select id="admin_role" name="admin_role" required
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; background: white;">
                    <option value="">Select Role</option>
                    <option value="admin" <?= ($formData['admin_role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="superadmin" <?= ($formData['admin_role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: var(--gray-500);">
                    Admin: Manage students, tutors, skills | Super Admin: Full system control
                </p>
            </div>

            <!-- Password -->
            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" required minlength="8"
                        style="width: 100%; padding: 0.875rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="Create a strong password">
                    <button type="button" class="password-toggle" data-target="password"
                        style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 1.25rem;">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
                <p style="font-size: 0.75rem; color: var(--gray-500); margin: 0.5rem 0 0 0;">
                    Must include: uppercase, lowercase, number & special character
                </p>
            </div>

            <!-- Confirm Password -->
            <div style="margin-bottom: 1.5rem;">
                <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                        style="width: 100%; padding: 0.875rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="Re-enter your password">
                    <button type="button" class="password-toggle" data-target="confirm_password"
                        style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 1.25rem;">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <!-- CAPTCHA -->
            <div style="margin-bottom: 1.5rem;">
                <label for="captcha" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-shield-alt"></i> Verification Code <span style="color: #DC2626;">*</span>
                </label>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                    <img src="../pages/captcha.php" id="captcha-image" alt="CAPTCHA" 
                        style="border: 2px solid var(--primary-cyan); border-radius: var(--radius-md); cursor: pointer; height: 50px;"
                        onclick="this.src='../pages/captcha.php?'+Math.random();" 
                        title="Click to refresh">
                    <button type="button" onclick="document.getElementById('captcha-image').src='../pages/captcha.php?'+Math.random();"
                        style="padding: 0.75rem 1rem; background: var(--gray-100); border: 2px solid var(--gray-300); border-radius: var(--radius-md); cursor: pointer; color: var(--gray-700); font-weight: 600; white-space: nowrap;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <input type="text" id="captcha" name="captcha" required maxlength="6"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; text-transform: uppercase;"
                    placeholder="Enter code shown above" autocomplete="off">
                <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem; margin-bottom: 0;">
                    <i class="fas fa-info-circle"></i> Enter the 6 characters shown in the image
                </p>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1rem;">
                <i class="fas fa-user-plus"></i> Create Admin Account
            </button>
        </form>

        <!-- Sign In Link -->
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center;">
            <p style="color: var(--gray-600); margin: 0;">
                Already have an account?
                <a href="signin.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">
                    Sign In
                </a>
            </p>
        </div>
    </div>

    <!-- Back to Main Site -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="<?= $BASE_URL ?>/index.php" style="color: white; font-weight: 600; text-decoration: none; font-size: 0.875rem;">
            <i class="fas fa-arrow-left"></i> Back to Main Site
        </a>
    </div>
</div>

<style>
input:focus, select:focus {
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

</body>
</html>