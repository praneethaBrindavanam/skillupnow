<?php
session_start();

// Check if Google signup data exists
if (!isset($_SESSION['google_signup_data'])) {
    header('Location: signup.php');
    exit();
}

$googleData = $_SESSION['google_signup_data'];
$errors = $_SESSION['google_profile_errors'] ?? [];
$formData = $_SESSION['google_profile_data'] ?? [];

unset($_SESSION['google_profile_errors']);
unset($_SESSION['google_profile_data']);

$pageTitle = "Complete Your Profile";
include '../includes/header.php';
?>

<section class="section" style="padding: 6rem 2rem 4rem 2rem;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl);">
                
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <?php if ($googleData['profile_picture']): ?>
                        <img src="<?= htmlspecialchars($googleData['profile_picture']) ?>" 
                             style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 1rem;">
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem; font-weight: 700;">
                            <?= strtoupper(substr($googleData['full_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h2 style="margin-bottom: 0.5rem;">Complete Your Profile</h2>
                    <p style="color: var(--gray-600); margin: 0;">
                        Welcome, <?= htmlspecialchars($googleData['full_name']) ?>!<br>
                        Just a few more details to get started
                    </p>
                </div>
                
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
                
                <form action="process-google-signup.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
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
                    
                    <!-- Email Display (Read-only) -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Email (from Google)
                        </label>
                        <input type="text" value="<?= htmlspecialchars($googleData['email']) ?>" readonly
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; background: var(--gray-100); color: var(--gray-600);">
                        <p style="font-size: 0.875rem; color: #10B981; margin-top: 0.5rem; margin-bottom: 0;">
                            <i class="fas fa-check-circle"></i> Verified by Google
                        </p>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        Complete Registration <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                
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
</style>

<?php include '../includes/footer.php'; ?>