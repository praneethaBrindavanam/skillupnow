<?php
$pageTitle = "Manage Tutors";
include 'admin-header.php';

$currentAdminRole = getCurrentAdminRole();
$conn = getDbConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_status' && $userId > 0) {
        $stmt = $conn->prepare("UPDATE users SET is_active = 1 - is_active WHERE user_id = ? AND user_type = 'tutor'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        logAdminActivity(getCurrentAdminId(), 'update', 'users', $userId, 'Toggled tutor status');
        $_SESSION['admin_success'] = "Tutor status updated successfully";
    }
    
    if ($action === 'toggle_verification' && $userId > 0) {
        $stmt = $conn->prepare("UPDATE users SET is_tutor_verified = 1 - is_tutor_verified WHERE user_id = ? AND user_type = 'tutor'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        logAdminActivity(getCurrentAdminId(), 'update', 'users', $userId, 'Toggled tutor verification');
        $_SESSION['admin_success'] = "Tutor verification status updated successfully";
    }
    
    // Only superadmin can delete
    if ($action === 'delete' && $userId > 0 && $currentAdminRole === 'superadmin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_type = 'tutor'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        logAdminActivity(getCurrentAdminId(), 'delete', 'users', $userId, 'Deleted tutor account');
        $_SESSION['admin_success'] = "Tutor deleted successfully";
    }
    
    header("Location: manage-tutors.php");
    exit();
}

// Get all tutors with their profile info
$tutors = [];
$result = $conn->query("
    SELECT 
        u.user_id,
        u.custom_user_id,
        u.username,
        u.full_name,
        u.email,
        u.college_name,
        u.is_active,
        u.is_verified,
        u.is_tutor_verified,
        u.created_at,
        u.last_login,
        up.profile_completed,
        up.hourly_rate,
        (SELECT COUNT(*) FROM user_skills WHERE user_id = u.user_id) as skills_count
    FROM users u
    LEFT JOIN user_profiles up ON u.user_id = up.user_id
    WHERE u.user_type = 'tutor'
    ORDER BY u.created_at DESC
");
while ($row = $result->fetch_assoc()) {
    $tutors[] = $row;
}

closeDbConnection($conn);
?>

<?php if (isset($_SESSION['admin_success'])): ?>
    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
        <p style="margin: 0; color: #065F46;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['admin_success']) ?></p>
    </div>
    <?php unset($_SESSION['admin_success']); ?>
<?php endif; ?>

<div style="max-width: 1400px;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-chalkboard-teacher"></i> Manage Tutors</h1>
            <p style="color: var(--gray-600); margin: 0;">Total: <?= count($tutors) ?> tutors</p>
        </div>
    </div>

    <!-- Tutors Table -->
    <div style="background: white; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--gray-50); border-bottom: 2px solid var(--gray-200);">
                    <tr>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Tutor ID</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Name</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Email</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">College</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Skills</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Rate</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Verified</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Status</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tutors)): ?>
                        <tr>
                            <td colspan="9" style="padding: 3rem; text-align: center; color: var(--gray-500);">
                                <i class="fas fa-chalkboard-teacher" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p style="margin: 0;">No tutors found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tutors as $tutor): ?>
                            <tr style="border-bottom: 1px solid var(--gray-200); transition: background 0.2s;" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='white'">
                                <td style="padding: 1rem;">
                                    <span style="font-weight: 600; color: #F687B3;">
                                        <?= htmlspecialchars($tutor['custom_user_id']) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <div>
                                        <p style="margin: 0; font-weight: 600;"><?= htmlspecialchars($tutor['full_name']) ?></p>
                                        <p style="margin: 0; font-size: 0.875rem; color: var(--gray-500);">@<?= htmlspecialchars($tutor['username']) ?></p>
                                    </div>
                                </td>
                                <td style="padding: 1rem; color: var(--gray-600);">
                                    <?= htmlspecialchars($tutor['email']) ?>
                                    <?php if ($tutor['is_verified']): ?>
                                        <i class="fas fa-check-circle" style="color: #10B981; margin-left: 0.25rem;" title="Email Verified"></i>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; color: var(--gray-600);">
                                    <?= htmlspecialchars($tutor['college_name']) ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($tutor['skills_count'] > 0): ?>
                                        <span style="background: #FCE7F3; color: #BE185D; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                            <?= $tutor['skills_count'] ?> skills
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #FEE2E2; color: #991B1B; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                            No skills
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: var(--gray-700); font-weight: 600;">
                                    <?= $tutor['hourly_rate'] ? '₹' . number_format($tutor['hourly_rate']) . '/hr' : '-' ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($tutor['is_tutor_verified']): ?>
                                        <span style="background: #D1FAE5; color: #065F46; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-check-circle"></i> Yes
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #FEF3C7; color: #92400E; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($tutor['is_active']): ?>
                                        <span style="background: #D1FAE5; color: #065F46; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #FEE2E2; color: #991B1B; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-times-circle"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                        <!-- View Button -->
                                        <a href="view-tutor.php?id=<?= $tutor['user_id'] ?>" 
                                           style="background: var(--primary-teal); color: white; border: none; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem; text-decoration: none; display: inline-block;"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Edit Button -->
                                        <a href="edit-tutor.php?id=<?= $tutor['user_id'] ?>" 
                                           style="background: #3B82F6; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem; text-decoration: none; display: inline-block;"
                                           title="Edit Tutor">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Verify/Unverify Button -->
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="toggle_verification">
                                            <input type="hidden" name="user_id" value="<?= $tutor['user_id'] ?>">
                                            <button type="submit" 
                                                    style="background: <?= $tutor['is_tutor_verified'] ? '#F59E0B' : '#10B981' ?>; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem;"
                                                    onclick="return confirm('<?= $tutor['is_tutor_verified'] ? 'Unverify' : 'Verify' ?> this tutor?')"
                                                    title="<?= $tutor['is_tutor_verified'] ? 'Unverify' : 'Verify' ?> Tutor">
                                                <i class="fas fa-<?= $tutor['is_tutor_verified'] ? 'times' : 'check' ?>-circle"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Toggle Status Button -->
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $tutor['user_id'] ?>">
                                            <button type="submit" 
                                                    style="background: #6366F1; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem;"
                                                    onclick="return confirm('Toggle tutor status?')"
                                                    title="Toggle Status">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Delete Button (Superadmin Only) -->
                                        <?php if ($currentAdminRole === 'superadmin'): ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= $tutor['user_id'] ?>">
                                                <button type="submit" 
                                                        style="background: #EF4444; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem;"
                                                        onclick="return confirm('Are you sure you want to delete this tutor? This action cannot be undone.')"
                                                        title="Delete Tutor">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

</div>

<?php include 'admin-footer.php'; ?>