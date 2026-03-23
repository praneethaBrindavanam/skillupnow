<?php
session_start();
require_once 'admin-config.php';

requireAdminLogin();

$adminId = intval($_GET['id'] ?? 0);
$currentAdminId = getCurrentAdminId();
$currentAdminRole = getCurrentAdminRole();

// Check permissions
if ($currentAdminRole !== 'superadmin' && $adminId != $currentAdminId) {
    $_SESSION['error'] = "You don't have permission to edit this admin.";
    header("Location: manage-admins.php");
    exit();
}

if ($adminId <= 0) {
    $_SESSION['error'] = "Invalid admin ID.";
    header("Location: manage-admins.php");
    exit();
}

$conn = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $adminRole = $_POST['admin_role'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Profile fields (matching your schema)
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $countryCode = trim($_POST['country_code'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($fullName) || strlen($fullName) < 3) {
        $errors[] = "Full name must be at least 3 characters";
    }
    
    if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $errors[] = "Username must be 4-20 characters (letters, numbers, underscore only)";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    // Phone validation - 10 digits, numbers only
    if (!empty($phoneNumber) && !preg_match('/^\d{10}$/', $phoneNumber)) {
        $errors[] = "Phone number must be exactly 10 digits (numbers only)";
    }
    
    // Pincode validation - 6 digits, numbers only
    if (!empty($pincode) && !preg_match('/^\d{6}$/', $pincode)) {
        $errors[] = "Pincode must be exactly 6 digits (numbers only)";
    }
    
    // Country code validation
    if (!empty($countryCode) && !preg_match('/^\+?\d{1,4}$/', $countryCode)) {
        $errors[] = "Invalid country code format";
    }
    
    // Only superadmin can change role
    if ($currentAdminRole !== 'superadmin') {
        $adminRole = null; // Don't change role for regular admin
    } elseif (!in_array($adminRole, ['admin', 'superadmin'])) {
        $errors[] = "Invalid admin role";
    }
    
    // Password validation (optional)
    if (!empty($newPassword) && strlen($newPassword) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if (empty($errors)) {
        try {
            // Check if username/email already exists (excluding current admin)
            $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE (username = ? OR email = ?) AND admin_id != ?");
            $stmt->bind_param("ssi", $username, $email, $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Username or email already exists";
                $stmt->close();
            } else {
                $stmt->close();
                
                // Build update query
                if ($currentAdminRole === 'superadmin') {
                    // Superadmin can update everything including role and status
                    if (!empty($newPassword)) {
                        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
                        $stmt = $conn->prepare("UPDATE admins SET full_name = ?, username = ?, email = ?, admin_role = ?, password_hash = ?, is_active = ?, phone_number = ?, address = ?, city = ?, state = ?, pincode = ?, country = ?, country_code = ?, updated_at = NOW() WHERE admin_id = ?");
                        $stmt->bind_param("sssssisssssssi", $fullName, $username, $email, $adminRole, $passwordHash, $isActive, $phoneNumber, $address, $city, $state, $pincode, $country, $countryCode, $adminId);
                    } else {
                        $stmt = $conn->prepare("UPDATE admins SET full_name = ?, username = ?, email = ?, admin_role = ?, is_active = ?, phone_number = ?, address = ?, city = ?, state = ?, pincode = ?, country = ?, country_code = ?, updated_at = NOW() WHERE admin_id = ?");
                        $stmt->bind_param("ssssisssssssi", $fullName, $username, $email, $adminRole, $isActive, $phoneNumber, $address, $city, $state, $pincode, $country, $countryCode, $adminId);
                    }
                } else {
                    // Regular admin can only update their own basic info
                    if (!empty($newPassword)) {
                        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
                        $stmt = $conn->prepare("UPDATE admins SET full_name = ?, username = ?, email = ?, password_hash = ?, phone_number = ?, address = ?, city = ?, state = ?, pincode = ?, country = ?, country_code = ?, updated_at = NOW() WHERE admin_id = ?");
                        $stmt->bind_param("sssssssssssi", $fullName, $username, $email, $passwordHash, $phoneNumber, $address, $city, $state, $pincode, $country, $countryCode, $adminId);
                    } else {
                        $stmt = $conn->prepare("UPDATE admins SET full_name = ?, username = ?, email = ?, phone_number = ?, address = ?, city = ?, state = ?, pincode = ?, country = ?, country_code = ?, updated_at = NOW() WHERE admin_id = ?");
                        $stmt->bind_param("ssssssssssi", $fullName, $username, $email, $phoneNumber, $address, $city, $state, $pincode, $country, $countryCode, $adminId);
                    }
                }
                
                if ($stmt->execute()) {
                    logAdminActivity($currentAdminId, 'update', 'admins', $adminId, "Updated admin: $username");
                    $_SESSION['success'] = "Admin profile updated successfully";
                    
                    // Update session if editing own account
                    if ($adminId == $currentAdminId) {
                        $_SESSION['admin_name'] = $fullName;
                        $_SESSION['admin_username'] = $username;
                        $_SESSION['admin_email'] = $email;
                    }
                    
                    $stmt->close();
                    closeDbConnection($conn);
                    header("Location: manage-admins.php");
                    exit();
                } else {
                    $errors[] = "Failed to update admin. Please try again.";
                    $stmt->close();
                }
            }
        } catch (Exception $e) {
            error_log("Edit admin error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again.";
        }
    }
}

// Get admin data
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDbConnection($conn);
    $_SESSION['error'] = "Admin not found.";
    header("Location: manage-admins.php");
    exit();
}

$admin = $result->fetch_assoc();
$stmt->close();
closeDbConnection($conn);

$pageTitle = "Edit Admin";
include 'admin-header.php';
?>

<div style="max-width: 1000px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <a href="manage-admins.php" style="color: var(--primary-teal); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Admins
        </a>
        <h1 style="margin-bottom: 0.5rem;">
            <i class="fas fa-user-edit"></i> Edit Admin Profile
        </h1>
        <p style="color: var(--gray-600); margin: 0;">
            <?= $adminId == $currentAdminId ? 'Update your complete admin profile' : 'Modify admin account and profile details' ?>
        </p>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
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

    <!-- Edit Form -->
    <form method="POST" style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <!-- Basic Information Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-user"></i> Basic Information
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                
                <!-- Full Name -->
                <div>
                    <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Full Name <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                        value="<?= htmlspecialchars($admin['full_name']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="Enter full name">
                </div>

                <!-- Username -->
                <div>
                    <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Username <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="username" name="username" required
                        value="<?= htmlspecialchars($admin['username']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="4-20 characters (letters, numbers, underscore)">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        4-20 characters, letters, numbers and underscore only
                    </small>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Email Address <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        value="<?= htmlspecialchars($admin['email']) ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="admin@example.com">
                </div>

                <!-- Admin Role (Super Admin only) -->
                <?php if ($currentAdminRole === 'superadmin'): ?>
                    <div>
                        <label for="admin_role" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Admin Role <span style="color: #DC2626;">*</span>
                        </label>
                        <select id="admin_role" name="admin_role" required
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;">
                            <option value="admin" <?= $admin['admin_role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="superadmin" <?= $admin['admin_role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-phone"></i> Contact Information
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                
                <!-- Country Code -->
                <div>
                    <label for="country_code" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Country Code
                    </label>
                    <input type="text" id="country_code" name="country_code"
                        value="<?= htmlspecialchars($admin['country_code'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="+91"
                        maxlength="5">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        E.g., +91 for India, +1 for USA
                    </small>
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone_number" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Phone Number
                    </label>
                    <input type="text" id="phone_number" name="phone_number"
                        value="<?= htmlspecialchars($admin['phone_number'] ?? '') ?>"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                        placeholder="9876543210"
                        maxlength="10"
                        pattern="\d{10}">
                    <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                        Exactly 10 digits, numbers only
                    </small>
                </div>
            </div>
        </div>

        <!-- Address Information Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-map-marker-alt"></i> Address Information
            </h3>
            
            <div style="display: grid; gap: 1.5rem;">
                
                <!-- Street Address -->
                <div>
                    <label for="address" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                        Street Address
                    </label>
                    <textarea id="address" name="address" rows="3"
                        style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; font-family: inherit; resize: vertical;"
                        placeholder="Enter street address, apartment, suite, etc."><?= htmlspecialchars($admin['address'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    
                    <!-- City -->
                    <div>
                        <label for="city" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            City
                        </label>
                        <input type="text" id="city" name="city"
                            value="<?= htmlspecialchars($admin['city'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="Enter city">
                    </div>

                    <!-- State -->
                    <div>
                        <label for="state" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            State / Province
                        </label>
                        <input type="text" id="state" name="state"
                            value="<?= htmlspecialchars($admin['state'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="Enter state">
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label for="pincode" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Pincode / ZIP
                        </label>
                        <input type="text" id="pincode" name="pincode"
                            value="<?= htmlspecialchars($admin['pincode'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="123456"
                            maxlength="6"
                            pattern="\d{6}">
                        <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                            Exactly 6 digits, numbers only
                        </small>
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                            Country
                        </label>
                        <input type="text" id="country" name="country"
                            value="<?= htmlspecialchars($admin['country'] ?? '') ?>"
                            style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                            placeholder="Enter country">
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Section -->
        <div style="margin-bottom: 2.5rem;">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-lock"></i> Change Password
            </h3>
            
            <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
                <p style="margin: 0; color: #92400E; font-size: 0.875rem;">
                    <i class="fas fa-info-circle"></i> Leave blank to keep current password
                </p>
            </div>
            
            <div>
                <label for="new_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">
                    New Password (Optional)
                </label>
                <input type="password" id="new_password" name="new_password"
                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem;"
                    placeholder="Enter new password (minimum 8 characters)">
                <small style="color: var(--gray-500); font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                    Minimum 8 characters required
                </small>
            </div>
        </div>

        <!-- Status (Super Admin only) -->
        <?php if ($currentAdminRole === 'superadmin'): ?>
            <div style="margin-bottom: 2.5rem;">
                <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-toggle-on"></i> Account Status
                </h3>
                
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 1rem; background: var(--gray-50); border-radius: var(--radius-md); border: 2px solid var(--gray-200); transition: all 0.2s;">
                    <input type="checkbox" name="is_active" value="1" <?= $admin['is_active'] ? 'checked' : '' ?>
                        style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <span style="font-weight: 600; color: var(--gray-700); display: block;">
                            Account Active
                        </span>
                        <span style="font-size: 0.875rem; color: var(--gray-600);">
                            Inactive admins cannot sign in to the system
                        </span>
                    </div>
                </label>
            </div>
        <?php endif; ?>

        <!-- Account Info -->
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
            <h4 style="margin: 0 0 1rem 0; color: var(--gray-700); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-info-circle"></i> Account Information
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; font-size: 0.875rem; color: var(--gray-600);">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-calendar" style="width: 20px;"></i> 
                    <span><strong>Created:</strong> <?= date('M d, Y', strtotime($admin['created_at'])) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-clock" style="width: 20px;"></i> 
                    <span><strong>Updated:</strong> <?= date('M d, Y', strtotime($admin['updated_at'])) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-sign-in-alt" style="width: 20px;"></i> 
                    <span><strong>Last Login:</strong> <?= $admin['last_login'] ? date('M d, Y h:i A', strtotime($admin['last_login'])) : 'Never' ?></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 2px solid var(--gray-200);">
            <a href="manage-admins.php" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </div>
    </form>

</div>

<script>
// Client-side validation for better UX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const phoneInput = document.getElementById('phone_number');
    const pincodeInput = document.getElementById('pincode');
    const countryCodeInput = document.getElementById('country_code');
    
    // Phone number validation - only allow digits
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });
    }
    
    // Pincode validation - only allow digits
    if (pincodeInput) {
        pincodeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });
    }
    
    // Country code validation
    if (countryCodeInput) {
        countryCodeInput.addEventListener('input', function(e) {
            let value = this.value;
            // Allow + at the start followed by digits
            if (value.length > 0 && value[0] !== '+') {
                value = '+' + value.replace(/\D/g, '');
            } else {
                value = value.substring(0, 1) + value.substring(1).replace(/\D/g, '');
            }
            this.value = value.substring(0, 5);
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let errors = [];
        
        // Phone validation
        if (phoneInput.value && !/^\d{10}$/.test(phoneInput.value)) {
            errors.push('Phone number must be exactly 10 digits');
        }
        
        // Pincode validation
        if (pincodeInput.value && !/^\d{6}$/.test(pincodeInput.value)) {
            errors.push('Pincode must be exactly 6 digits');
        }
        
        // Country code validation
        if (countryCodeInput.value && !/^\+?\d{1,4}$/.test(countryCodeInput.value)) {
            errors.push('Invalid country code format');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
        }
    });
});
</script>

<?php include 'admin-footer.php'; ?>