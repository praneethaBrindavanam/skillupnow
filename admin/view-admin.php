<?php
$pageTitle = "View Admin";
include 'admin-header.php';

// Get admin ID from URL
$adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($adminId <= 0) {
    $_SESSION['error'] = "Invalid admin ID";
    header("Location: manage-admins.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'skillupnow';
$db_username = 'root';
$db_password = '';

try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Connection failed");
}

// Get admin details
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Admin not found";
    header("Location: manage-admins.php");
    exit();
}

$admin = $result->fetch_assoc();
$stmt->close();

// Check permissions - regular admin can only view their own profile
if ($adminRole !== 'superadmin' && $admin['admin_id'] != $_SESSION['admin_id']) {
    $_SESSION['error'] = "You can only view your own profile";
    header("Location: manage-admins.php");
    exit();
}

$conn->close();
?>

<!-- Back Button -->
<div style="margin-bottom: 2rem;">
    <a href="manage-admins.php" style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Admins
    </a>
</div>

<!-- Admin Profile Card -->
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
                <?php if ($admin['admin_id'] == $_SESSION['admin_id']): ?>
                    <span style="background: rgba(255,255,255,0.2); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; margin-top: 0.5rem; display: inline-block;">
                        <i class="fas fa-user"></i> You
                    </span>
                <?php endif; ?>
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
                    
                    <?php if (!empty($admin['phone_number'])): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Phone Number</label>
                        <div style="color: var(--gray-900); font-weight: 600;">
                            <?= htmlspecialchars($admin['country_code'] ?? '+91') ?> <?= htmlspecialchars($admin['phone_number']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h3 style="margin-bottom: 1.5rem; color: var(--gray-900);">
                    <i class="fas fa-map-marker-alt"></i> Contact Information
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php if (!empty($admin['address'])): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Address</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= nl2br(htmlspecialchars($admin['address'])) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($admin['city'])): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">City</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['city']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($admin['state'])): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">State</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['state']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($admin['pincode'])): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Pincode</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['pincode']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($admin['country'])): ?>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Country</label>
                        <div style="color: var(--gray-900); font-weight: 600;"><?= htmlspecialchars($admin['country']) ?></div>
                    </div>
                    <?php endif; ?>
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
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Created At</label>
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
                        <label style="display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem;">Status</label>
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
    <?php if ($adminRole === 'superadmin' || $admin['admin_id'] == $_SESSION['admin_id']): ?>
        <a href="edit-admin.php?id=<?= $admin['admin_id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
    <?php endif; ?>
    
    <?php if ($adminRole === 'superadmin' && $admin['admin_id'] != $_SESSION['admin_id']): ?>
        <a href="delete-admin.php?id=<?= $admin['admin_id'] ?>" 
           onclick="return confirm('Are you sure you want to delete this admin? This action cannot be undone.');"
           class="btn" style="background: #EF4444; color: white;">
            <i class="fas fa-trash"></i> Delete Admin
        </a>
    <?php endif; ?>
</div>

<?php include 'admin-footer.php'; ?>