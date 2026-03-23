<?php
$pageTitle = "My Profile";
include 'admin-header.php';

// Database connection
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get own admin details
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Admin profile not found";
    header("Location: dashboard.php");
    exit();
}

$admin = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem;">
        <p style="margin: 0; color: #065F46; font-weight: 600;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </p>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem;">
        <p style="margin: 0; color: #991B1B; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </p>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Profile Card -->
<div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem;">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); padding: 2rem; color: white;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: var(--primary-teal); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="color: white; margin-bottom: 0.5rem;"><?= htmlspecialchars($admin['full_name']) ?></h1>
                <p style="margin: 0; opacity: 0.9;">
                    <?= $admin['admin_role'] === 'superadmin' ? 'Super Administrator' : 'Administrator' ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Details -->
    <div style="padding: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            
            <!-- Personal Information -->
            <div>
                <h3 style="margin-bottom: 1.5rem; color: var(--gray-900);">
                    <i class="fas fa-user"></i> Personal Information
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Admin ID</label>
                        <div style="color: var(--gray-900); font-weight: 600;">#<?= $admin['admin_id'] ?></div>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Full Name</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['full_name']) ?></div>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Username</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['username']) ?></div>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Email</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['email']) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Account Information -->
            <div>
                <h3 style="margin-bottom: 1.5rem; color: var(--gray-900);">
                    <i class="fas fa-shield-alt"></i> Account Information
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Role</label>
                        <?php if ($admin['admin_role'] === 'superadmin'): ?>
                            <span style="display: inline-block; padding: 0.375rem 0.75rem; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); color: white; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                <i class="fas fa-crown"></i> Super Administrator
                            </span>
                        <?php else: ?>
                            <span style="display: inline-block; padding: 0.375rem 0.75rem; background: var(--gray-200); color: var(--gray-700); border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                <i class="fas fa-user-shield"></i> Administrator
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Account Created</label>
                        <div style="color: var(--gray-900); font-weight: 600;">
                            <?= date('F d, Y \a\t h:i A', strtotime($admin['created_at'])) ?>
                        </div>
                    </div>
                    
                    <?php if ($admin['last_login']): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Last Login</label>
                        <div style="color: var(--gray-900); font-weight: 600;">
                            <?= date('F d, Y \a\t h:i A', strtotime($admin['last_login'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Account Status</label>
                        <?php if ($admin['is_active']): ?>
                            <span style="display: inline-block; padding: 0.375rem 0.75rem; background: #D1FAE5; color: #065F46; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        <?php else: ?>
                            <span style="display: inline-block; padding: 0.375rem 0.75rem; background: #FEE2E2; color: #991B1B; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                <i class="fas fa-times-circle"></i> Inactive
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div style="display: flex; gap: 1rem; justify-content: flex-end;">
    <a href="edit-profile.php" class="btn btn-primary">
        <i class="fas fa-edit"></i> Edit Profile
    </a>
    
    <a href="change-password.php" class="btn btn-outline">
        <i class="fas fa-key"></i> Change Password
    </a>
</div>

<?php include 'admin-footer.php'; ?>