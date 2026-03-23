<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$pageTitle = "Admin Sign In";
$BASE_URL = "/skillupnow";

$errors = $_SESSION['admin_errors'] ?? [];
unset($_SESSION['admin_errors']);

$email = $_SESSION['admin_email'] ?? '';
unset($_SESSION['admin_email']);

$successMessage = $_SESSION['admin_signup_success'] ?? '';
unset($_SESSION['admin_signup_success']);
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

<div style="width: 100%; max-width: 450px;">
    
    <!-- Logo -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <i class="fas fa-shield-alt" style="font-size: 2.5rem; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
        </div>
        <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;">Admin Portal</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 0;">SkillUp Now Administration</p>
    </div>

    <!-- Sign In Form -->
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-xl); box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        
        <h2 style="margin-bottom: 0.5rem; text-align: center;">Sign In</h2>
        <p style="color: var(--gray-600); text-align: center; margin-bottom: 2rem;">
            Access the admin dashboard
        </p>

        <?php if ($successMessage): ?>
            <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-check-circle" style="color: #10B981;"></i>
                    <p style="margin: 0; color: #065F46;"><?= htmlspecialchars($successMessage) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-exclamation-circle" style="color: #DC2626;"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p style="margin: 0; color: #991B1B;"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form action="process-signin.php" method="POST">
            
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" id="email" name="email" required
                    value="<?= htmlspecialchars($email) ?>"
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="admin@skillupnow.com">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" required
                    style="width: 100%; padding: 0.875rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1rem; background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center;">
            <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0 0 1rem 0;">
                Don't have an admin account?
                <a href="signup.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">
                    Sign Up
                </a>
            </p>
            <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0;">
                <i class="fas fa-shield-alt"></i> Authorized personnel only
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

</body>
</html>