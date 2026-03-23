<?php
session_start();
require_once 'admin-config.php';

requireSuperAdmin(); // Only super admins can delete

$adminId = intval($_GET['id'] ?? 0);
$currentAdminId = getCurrentAdminId();

// Prevent deleting self
if ($adminId == $currentAdminId) {
    $_SESSION['error'] = "You cannot delete your own admin account.";
    header("Location: manage-admins.php");
    exit();
}

if ($adminId <= 0) {
    $_SESSION['error'] = "Invalid admin ID.";
    header("Location: manage-admins.php");
    exit();
}

$conn = getDbConnection();

// Get admin details
$stmt = $conn->prepare("SELECT username, full_name FROM admins WHERE admin_id = ?");
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

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete admin
        $stmt = $conn->prepare("DELETE FROM admins WHERE admin_id = ?");
        $stmt->bind_param("i", $adminId);
        
        if ($stmt->execute()) {
            logAdminActivity($currentAdminId, 'delete', 'admins', $adminId, "Deleted admin: " . $admin['username']);
            $_SESSION['success'] = "Admin '" . htmlspecialchars($admin['full_name']) . "' has been deleted successfully.";
            $stmt->close();
            closeDbConnection($conn);
            header("Location: manage-admins.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete admin. Please try again.";
            $stmt->close();
            closeDbConnection($conn);
            header("Location: manage-admins.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Delete admin error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while deleting the admin.";
        closeDbConnection($conn);
        header("Location: manage-admins.php");
        exit();
    }
}

closeDbConnection($conn);

$pageTitle = "Delete Admin";
include 'admin-header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <a href="manage-admins.php" style="color: var(--primary-teal); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Admins
        </a>
        <h1 style="margin-bottom: 0.5rem; color: #DC2626;">
            <i class="fas fa-exclamation-triangle"></i> Delete Admin
        </h1>
        <p style="color: var(--gray-600); margin: 0;">
            This action cannot be undone
        </p>
    </div>

    <!-- Warning Card -->
    <div style="background: white; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        
        <!-- Red Header -->
        <div style="background: linear-gradient(135deg, #FCA5A5, #EF4444); padding: 2rem; text-align: center;">
            <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; color: #EF4444;"></i>
            </div>
            <h2 style="color: white; margin: 0;">Confirm Deletion</h2>
        </div>

        <!-- Content -->
        <div style="padding: 2rem;">
            
            <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="color: #991B1B; margin: 0 0 1rem 0;">
                    <i class="fas fa-exclamation-circle"></i> Warning!
                </h3>
                <p style="margin: 0; color: #7F1D1D; line-height: 1.6;">
                    You are about to permanently delete the following admin account. This action will:
                </p>
                <ul style="margin: 1rem 0 0 0; padding-left: 1.5rem; color: #7F1D1D;">
                    <li>Remove all admin access</li>
                    <li>Delete activity logs associated with this admin</li>
                    <li>Cannot be reversed or undone</li>
                </ul>
            </div>

            <!-- Admin Details -->
            <div style="background: var(--gray-50); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem;">
                <h4 style="margin: 0 0 1rem 0; color: var(--gray-700);">Admin to be deleted:</h4>
                
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: var(--radius-md);">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.5rem; flex-shrink: 0;">
                        <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p style="margin: 0 0 0.25rem 0; font-weight: 700; font-size: 1.125rem; color: var(--gray-900);">
                            <?= htmlspecialchars($admin['full_name']) ?>
                        </p>
                        <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                            @<?= htmlspecialchars($admin['username']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Confirmation Form -->
            <form method="POST">
                <input type="hidden" name="confirm_delete" value="1">
                
                <div style="background: #FFFBEB; border: 2px solid #F59E0B; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                    <p style="margin: 0; color: #92400E; font-weight: 600; text-align: center;">
                        <i class="fas fa-shield-alt"></i> Are you absolutely sure you want to delete this admin?
                    </p>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <a href="manage-admins.php" class="btn btn-outline" style="flex: 1; justify-content: center;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn" style="flex: 1; justify-content: center; background: #EF4444; color: white; border: none;"
                        onclick="return confirm('FINAL CONFIRMATION: Delete admin \'<?= htmlspecialchars($admin['full_name']) ?>\'? This cannot be undone!');">
                        <i class="fas fa-trash"></i> Yes, Delete Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Additional Warning -->
    <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: var(--radius-md); padding: 1rem; margin-top: 2rem; text-align: center;">
        <p style="margin: 0; color: #92400E; font-size: 0.875rem;">
            <i class="fas fa-info-circle"></i> Tip: Instead of deleting, you can deactivate the admin account in the edit page
        </p>
    </div>

</div>

<?php include 'admin-footer.php'; ?>