<?php
session_start();

$pageTitle = "Sign Up";
$errors = $_SESSION['signup_errors'] ?? [];
$formData = $_SESSION['signup_data'] ?? [];

// Clear errors after displaying
unset($_SESSION['signup_errors']);
unset($_SESSION['signup_data']);

include '../includes/header.php';
?>

<section class="section" style="padding: 6rem 2rem 4rem 2rem;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;">Create Your Account</h2>
                    <p style="color: var(--gray-600); margin: 0;">Join thousands of students learning together</p>
                </div>
                
                <!-- Error Display -->
                <?php if (!empty($errors)): ?>
                <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                        <i class="fas fa-exclamation-circle" style="color: #DC2626; margin-top: 0.25rem; font-size: 1.25rem;"></i>
                        <div style="flex: 1;">
                            <strong style="color: #DC2626; display: block; margin-bottom: 0.5rem;">Please fix the following errors:</strong>
                            <ul style="margin: 0; padding-left: 1.25rem; color: #991B1B;">
                                <?php foreach ($errors as $error): ?>
                                    <li style="margin-bottom: 0.25rem;"><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Client-Side Error Display (hidden by default) -->
                <div id="error-container" style="display: none; background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                        <i class="fas fa-exclamation-circle" style="color: #DC2626; margin-top: 0.25rem; font-size: 1.25rem;"></i>
                        <div style="flex: 1;">
                            <strong style="color: #DC2626; display: block; margin-bottom: 0.5rem;">Errors:</strong>
                            <ul id="error-list" style="margin: 0; padding-left: 1.25rem; color: #991B1B;">
                            </ul>
                        </div>
                    </div>
                </div>
                
                <form action="process-signup.php" method="POST" id="signupForm" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- Account Type -->
                    <div>
                        <label style="display: block; margin-bottom: 0.75rem; font-weight: 600; color: var(--gray-700);">
                            I want to <span style="color: #DC2626;">*</span>
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <label class="radio-label">
                                <input type="radio" name="account_type" value="learner" required 
                                    <?= (!isset($formData['account_type']) || $formData['account_type'] === 'learner') ? 'checked' : '' ?>>
                                <div>
                                    <div style="font-weight: 600; color: var(--gray-800);">Learn Skills</div>
                                    <div style="font-size: 0.875rem; color: var(--gray-500);">As a Student</div>
                                </div>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="account_type" value="tutor" required
                                    <?= (isset($formData['account_type']) && $formData['account_type'] === 'tutor') ? 'checked' : '' ?>>
                                <div>
                                    <div style="font-weight: 600; color: var(--gray-800);">Teach Skills</div>
                                    <div style="font-size: 0.875rem; color: var(--gray-500);">As a Tutor</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Full Name -->
                    <div>
                        <label for="fullname" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Full Name <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="text" id="fullname" name="fullname" required
                            value="<?= htmlspecialchars($formData['fullname'] ?? '') ?>"
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="John Doe">
                    </div>
                    
                    <!-- Username -->
                    <div>
                        <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Username <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="text" id="username" name="username" required minlength="4" maxlength="20"
                            value="<?= htmlspecialchars($formData['username'] ?? '') ?>"
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="johndoe123"
                            pattern="^[a-zA-Z0-9_]{4,20}$">
                        <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem; margin-bottom: 0;">
                            <i class="fas fa-info-circle"></i> 4-20 characters, letters, numbers and underscore only
                        </p>
                    </div>
                    
                    <!-- College Email -->
                    <div>
                        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            College Email <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                            value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="you@gvpce.ac.in">
                        <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem; margin-bottom: 0;">
                            <i class="fas fa-info-circle"></i> Only @gvpce.ac.in, @au.edu.in, @anits.edu.in, @raghu.edu.in allowed
                        </p>
                    </div>
                    
                    <!-- College Name -->
                    <div>
                        <label for="college" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            College/University <span style="color: #DC2626;">*</span>
                        </label>
                        <select id="college" name="college" required
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; background: white;">
                            <option value="">-- Select your college --</option>
                            <option value="Gayatri Vidya Parishad College of Engineering" <?= (isset($formData['college']) && $formData['college'] === 'Gayatri Vidya Parishad College of Engineering') ? 'selected' : '' ?>>Gayatri Vidya Parishad College of Engineering (GVPCE)</option>
                            <option value="Andhra University" <?= (isset($formData['college']) && $formData['college'] === 'Andhra University') ? 'selected' : '' ?>>Andhra University (AU)</option>
                            <option value="Anil Neerukonda Institute of Technology & Sciences" <?= (isset($formData['college']) && $formData['college'] === 'Anil Neerukonda Institute of Technology & Sciences') ? 'selected' : '' ?>>Anil Neerukonda Institute of Technology & Sciences (ANITS)</option>
                            <option value="Raghu Engineering College" <?= (isset($formData['college']) && $formData['college'] === 'Raghu Engineering College') ? 'selected' : '' ?>>Raghu Engineering College</option>
                        </select>
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Password <span style="color: #DC2626;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" required minlength="8"
                                style="width: 100%; padding: 0.875rem 1rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                                placeholder="At least 8 characters">
                            <button type="button" class="password-toggle" data-target="password"
                                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); font-size: 1.25rem;">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem; margin-bottom: 0;">
                            <i class="fas fa-shield-alt"></i> Must include: uppercase, lowercase, number & special character
                        </p>
                        <!-- Password Strength Indicator -->
                        <div style="margin-top: 0.75rem;">
                            <div id="password-strength" style="height: 4px; background: var(--gray-200); border-radius: 2px; overflow: hidden;">
                                <div id="password-strength-bar" style="height: 100%; width: 0%; transition: all 0.3s;"></div>
                            </div>
                            <p id="password-strength-text" style="font-size: 0.75rem; margin-top: 0.25rem; color: var(--gray-500);"></p>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Confirm Password <span style="color: #DC2626;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                                style="width: 100%; padding: 0.875rem 1rem; padding-right: 3rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                                placeholder="Re-enter your password">
                            <button type="button" class="password-toggle" data-target="confirm_password"
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
                    
                    <!-- Terms & Conditions -->
                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                        <input type="checkbox" id="terms" name="terms" required
                            style="width: 18px; height: 18px; cursor: pointer; margin-top: 0.25rem;">
                        <label for="terms" style="color: var(--gray-700); cursor: pointer; user-select: none; font-size: 0.875rem; line-height: 1.5;">
                            I agree to the <a href="terms.php" style="color: var(--primary-teal); font-weight: 600;">Terms</a> 
                            and <a href="privacy.php" style="color: var(--primary-teal); font-weight: 600;">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        Create Account <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                
                <!-- OR Divider -->
                <div style="display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0;">
                    <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                    <span style="color: var(--gray-500); font-size: 0.875rem; font-weight: 600;">OR</span>
                    <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                </div>
                
                <!-- Google Sign Up Button -->
                <a href="google-signup.php" style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 0.875rem; border: 2px solid var(--gray-300); border-radius: var(--radius-md); background: white; color: var(--gray-700); font-weight: 600; text-decoration: none; transition: all 0.2s;">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign up with Google
                </a>
                
                <!-- Sign In Link -->
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                    <p style="color: var(--gray-600); margin: 0;">
                        Already have an account? 
                        <a href="signin.php" style="color: var(--primary-teal); font-weight: 600;">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.radio-label {
    padding: 1rem; 
    border: 2px solid var(--gray-200); 
    border-radius: var(--radius-md);
    cursor: pointer; 
    transition: all var(--transition-base);
    display: flex; 
    align-items: center; 
    gap: 0.75rem;
}
.radio-label input[type="radio"] { 
    width: 20px; 
    height: 20px; 
    accent-color: var(--primary-teal); 
}
.radio-label:has(input:checked) {
    border-color: var(--primary-cyan);
    background: linear-gradient(135deg, rgba(79, 209, 197, 0.05), rgba(56, 178, 172, 0.05));
}
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

// Password strength checker
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('password-strength-bar');
const strengthText = document.getElementById('password-strength-text');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    // Check length
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Check for uppercase
    if (/[A-Z]/.test(password)) strength++;
    
    // Check for lowercase
    if (/[a-z]/.test(password)) strength++;
    
    // Check for numbers
    if (/[0-9]/.test(password)) strength++;
    
    // Check for special characters
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
    
    // Update UI
    let width = 0;
    let color = '';
    let text = '';
    
    if (strength === 0) {
        width = 0;
        text = '';
    } else if (strength <= 2) {
        width = 33;
        color = '#EF4444';
        text = 'Weak';
    } else if (strength <= 4) {
        width = 66;
        color = '#F59E0B';
        text = 'Medium';
    } else {
        width = 100;
        color = '#10B981';
        text = 'Strong';
    }
    
    strengthBar.style.width = width + '%';
    strengthBar.style.backgroundColor = color;
    strengthText.textContent = text;
    strengthText.style.color = color;
});

// Email validation
const emailInput = document.getElementById('email');
const allowedDomains = ['@gvpce.ac.in', '@au.edu.in', '@anits.edu.in', '@raghu.edu.in'];

emailInput.addEventListener('blur', function() {
    const email = this.value.toLowerCase();
    const isValid = allowedDomains.some(domain => email.endsWith(domain));
    
    if (email && !isValid) {
        this.style.borderColor = '#EF4444';
        showError(['Please use an email from allowed colleges: GVPCE, AU, ANITS, or Raghu']);
    } else {
        this.style.borderColor = 'var(--gray-200)';
        hideError();
    }
});

// Form validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const errors = [];
    
    // Get form values
    const fullname = document.getElementById('fullname').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim().toLowerCase();
    const college = document.getElementById('college').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.getElementById('terms').checked;
    
    // Validate fullname
    if (fullname.length < 3) {
        errors.push('Full name must be at least 3 characters');
    }
    
    // Validate username
    const usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
    if (!usernameRegex.test(username)) {
        errors.push('Username must be 4-20 characters (letters, numbers, underscore only)');
    }
    
    // Validate email domain
    const isValidDomain = allowedDomains.some(domain => email.endsWith(domain));
    if (!isValidDomain) {
        errors.push('Email must be from GVPCE, AU, ANITS, or Raghu college');
    }
    
    // Validate college selection
    if (!college) {
        errors.push('Please select your college');
    }
    
    // Validate password strength
    if (password.length < 8) {
        errors.push('Password must be at least 8 characters');
    }
    if (!/[A-Z]/.test(password)) {
        errors.push('Password must contain at least one uppercase letter');
    }
    if (!/[a-z]/.test(password)) {
        errors.push('Password must contain at least one lowercase letter');
    }
    if (!/[0-9]/.test(password)) {
        errors.push('Password must contain at least one number');
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        errors.push('Password must contain at least one special character (!@#$%^&*...)');
    }
    
    // Validate password match
    if (password !== confirmPassword) {
        errors.push('Passwords do not match');
    }
    
    // Validate terms
    if (!terms) {
        errors.push('You must accept the Terms and Privacy Policy');
    }
    
    // Validate CAPTCHA
    const captcha = document.getElementById('captcha').value.trim();
    if (!captcha) {
        errors.push('Please enter the verification code');
    } else if (captcha.length !== 6) {
        errors.push('Verification code must be 6 characters');
    }
    
    // Show errors or submit
    if (errors.length > 0) {
        showError(errors);
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
    }
    
    // Submit form
    this.submit();
});

function showError(errors) {
    const container = document.getElementById('error-container');
    const list = document.getElementById('error-list');
    
    list.innerHTML = '';
    errors.forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        li.style.marginBottom = '0.25rem';
        list.appendChild(li);
    });
    
    container.style.display = 'block';
}

function hideError() {
    document.getElementById('error-container').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>