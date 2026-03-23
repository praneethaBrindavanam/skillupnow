<?php
$pageTitle = "Manage Admins";
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

// Get admins based on role
$admins = [];
if ($adminRole === 'superadmin') {
    // Superadmin sees all admins
    $result = $conn->query("SELECT * FROM admins ORDER BY created_at DESC");
} else {
    // Regular admin sees only their own account
    $stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}

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

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.5rem;"><?= $adminRole === 'superadmin' ? 'Manage Admins' : 'My Admin Profile' ?></h1>
        <p style="color: var(--gray-600); margin: 0;">
            <?= $adminRole === 'superadmin' ? 'View and manage administrator accounts' : 'View your admin account details' ?>
        </p>
    </div>
    <?php if ($adminRole === 'superadmin'): ?>
    <a href="add-admin.php" class="btn btn-primary" style="white-space: nowrap;">
        <i class="fas fa-plus"></i> Add New Admin
    </a>
    <?php endif; ?>
</div>

<!-- Admins Table -->
<div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--gray-50); border-bottom: 2px solid var(--gray-200);">
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">ID</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Admin</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Username</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Email</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Role</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Created</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admins)): ?>
                    <tr>
                        <td colspan="7" style="padding: 3rem; text-align: center; color: var(--gray-500);">
                            <i class="fas fa-user-shield" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--gray-300);"></i>
                            No admins found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($admins as $admin): ?>
                        <tr style="border-bottom: 1px solid var(--gray-200);">
                            <td style="padding: 1rem; color: var(--gray-600);">#<?= $admin['admin_id'] ?></td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                        <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--gray-900);"><?= htmlspecialchars($admin['full_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--gray-700);"><?= htmlspecialchars($admin['username']) ?></td>
                            <td style="padding: 1rem; color: var(--gray-600);"><?= htmlspecialchars($admin['email']) ?></td>
                            <td style="padding: 1rem;">
                                <?php if ($admin['admin_role'] === 'superadmin'): ?>
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); color: white; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                                        SUPER ADMIN
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: var(--gray-100); color: var(--gray-700); border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                                        ADMIN
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem; color: var(--gray-600); font-size: 0.875rem;">
                                <?= date('M d, Y', strtotime($admin['created_at'])) ?>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <!-- View Button -->
                                    <a href="view-admin.php?id=<?= $admin['admin_id'] ?>" 
                                       style="padding: 0.5rem 0.75rem; background: linear-gradient(135deg, rgba(79, 209, 197, 0.1), rgba(56, 178, 172, 0.1)); color: var(--primary-teal); border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: all 0.2s;"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Edit Button (Own account only for regular admin) -->
                                    <?php if ($adminRole === 'superadmin' || $admin['admin_id'] == $_SESSION['admin_id']): ?>
                                        <a href="edit-admin.php?id=<?= $admin['admin_id'] ?>" 
                                           style="padding: 0.5rem 0.75rem; background: rgba(245, 158, 11, 0.1); color: #F59E0B; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: all 0.2s;"
                                           title="Edit Admin">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="padding: 0.5rem 0.75rem; background: var(--gray-100); color: var(--gray-400); border-radius: 0.5rem; font-size: 0.875rem; cursor: not-allowed;" title="Cannot edit other admins">
                                            <i class="fas fa-edit"></i>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- Delete Button (Superadmin only, cannot delete self) -->
                                    <?php if ($adminRole === 'superadmin' && $admin['admin_id'] != $_SESSION['admin_id']): ?>
                                        <a href="delete-admin.php?id=<?= $admin['admin_id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this admin? This action cannot be undone.');"
                                           style="padding: 0.5rem 0.75rem; background: #FEE2E2; color: #EF4444; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: all 0.2s;"
                                           title="Delete Admin">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="padding: 0.5rem 0.75rem; background: var(--gray-100); color: var(--gray-400); border-radius: 0.5rem; font-size: 0.875rem; cursor: not-allowed;" title="<?= $admin['admin_id'] == $_SESSION['admin_id'] ? 'Cannot delete yourself' : 'Superadmin only' ?>">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<?php include 'admin-footer.php'; ?>