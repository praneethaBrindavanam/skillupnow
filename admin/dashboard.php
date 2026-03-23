<?php
$pageTitle = "Dashboard";
include 'admin-header.php';

$stats = getAdminStats();

// Get recent activity
$conn = getDbConnection();
$recentActivity = [];
$result = $conn->query("
    SELECT al.*, a.username, a.full_name
    FROM admin_activity_log al
    JOIN admins a ON al.admin_id = a.admin_id
    ORDER BY al.created_at DESC
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $recentActivity[] = $row;
}
closeDbConnection($conn);
?>

<div style="max-width: 1400px;">
    
    <!-- Welcome Header -->
    <div style="background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white; padding: 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h1 style="color:  #38B2AC; margin-bottom: 0.5rem;">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>! 👋</h1>
        <p style="margin: 0; opacity: 0.9;">Here's what's happening on SkillUp Now</p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Total Students -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Total Students</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #4FD1C5;"><?= number_format($stats['total_students']) ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #4FD1C5, #38B2AC); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <a href="manage-students.php" style="display: block; margin-top: 1rem; color: #4FD1C5; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                View all →
            </a>
        </div>

        <!-- Total Tutors -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Total Tutors</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F687B3;"><?= number_format($stats['total_tutors']) ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <a href="manage-tutors.php" style="display: block; margin-top: 1rem; color: #F687B3; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                View all →
            </a>
        </div>

        <!-- Total Skills -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Total Skills</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #F59E0B;"><?= number_format($stats['total_skills']) ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <a href="manage-skills.php" style="display: block; margin-top: 1rem; color: #F59E0B; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                Manage skills →
            </a>
        </div>

        <!-- Active Users -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">Active Users (30d)</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2rem; color: #10B981;"><?= number_format($stats['active_users']) ?></h2>
                </div>
                <div style="width: 50px; height: 50px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <p style="margin-top: 1rem; color: var(--gray-600); font-size: 0.875rem;">
                <?php 
                $totalUsers = $stats['total_students'] + $stats['total_tutors'];
                $percentage = $totalUsers > 0 ? round(($stats['active_users'] / $totalUsers) * 100) : 0;
                ?>
                <?= $percentage ?>% of total users
            </p>
        </div>

    </div>

    <!-- Recent Activity -->
    <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">Recent Activity</h2>
            <a href="activity-log.php" style="color: var(--admin-primary); font-weight: 600; text-decoration: none; font-size: 0.875rem;">
                View all →
            </a>
        </div>

        <?php if (empty($recentActivity)): ?>
            <p style="color: var(--gray-500); text-align: center; padding: 2rem 0;">No activity recorded yet</p>
        <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                    <div style="border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <?php
                                $iconMap = [
                                    'create' => 'fa-plus',
                                    'update' => 'fa-edit',
                                    'delete' => 'fa-trash',
                                    'login' => 'fa-sign-in-alt',
                                    'logout' => 'fa-sign-out-alt',
                                    'view' => 'fa-eye'
                                ];
                                $icon = $iconMap[$activity['action_type']] ?? 'fa-circle';
                                ?>
                                <i class="fas <?= $icon ?>" style="color: var(--admin-primary);"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-weight: 600; color: var(--gray-800);">
                                    <?= htmlspecialchars($activity['full_name']) ?>
                                </p>
                                <p style="margin: 0; color: var(--gray-600); font-size: 0.875rem;">
                                    <?= htmlspecialchars($activity['description'] ?? ucfirst($activity['action_type']) . ' action') ?>
                                </p>
                            </div>
                        </div>
                        <p style="margin: 0; color: var(--gray-500); font-size: 0.75rem; white-space: nowrap;">
                            <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="manage-students.php" class="btn" style="background: linear-gradient(135deg, #4FD1C5, #38B2AC); color: white; justify-content: center; text-decoration: none;">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="manage-tutors.php" class="btn" style="background: linear-gradient(135deg, #F687B3, #ED64A6); color: white; justify-content: center; text-decoration: none;">
                <i class="fas fa-chalkboard-teacher"></i> Manage Tutors
            </a>
            <a href="manage-skills.php" class="btn" style="background: #F59E0B; color: white; justify-content: center; text-decoration: none;">
                <i class="fas fa-star"></i> Manage Skills
            </a>
            <?php if ($_SESSION['admin_role'] === 'superadmin'): ?>
                <a href="manage-admins.php" class="btn" style="background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); color: white; justify-content: center; text-decoration: none;">
                    <i class="fas fa-user-shield"></i> Manage Admins
                </a>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include 'admin-footer.php'; ?>