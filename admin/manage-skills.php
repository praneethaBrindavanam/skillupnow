<?php
$pageTitle = "Manage Skills";
include 'admin-header.php';

$conn = getDbConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $skillName = trim($_POST['skill_name'] ?? '');
            $skillCategory = $_POST['skill_category'] ?? '';
            $skillSubcategory = trim($_POST['skill_subcategory'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (!empty($skillName) && !empty($skillCategory)) {
                $stmt = $conn->prepare("INSERT INTO skills (skill_name, skill_category, skill_subcategory, description) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $skillName, $skillCategory, $skillSubcategory, $description);
                
                if ($stmt->execute()) {
                    logAdminActivity(getCurrentAdminId(), 'create', 'skills', $stmt->insert_id, "Added skill: $skillName");
                    $_SESSION['admin_success'] = "Skill added successfully";
                } else {
                    $_SESSION['admin_error'] = "Skill already exists or error occurred";
                }
                $stmt->close();
            }
        }
        
        if ($action === 'delete') {
            $skillId = intval($_POST['skill_id'] ?? 0);
            if ($skillId > 0) {
                $stmt = $conn->prepare("DELETE FROM skills WHERE skill_id = ?");
                $stmt->bind_param("i", $skillId);
                $stmt->execute();
                $stmt->close();
                
                logAdminActivity(getCurrentAdminId(), 'delete', 'skills', $skillId, 'Deleted skill');
                $_SESSION['admin_success'] = "Skill deleted successfully";
            }
        }
        
        header("Location: manage-skills.php");
        exit();
    }
}

// Get all skills grouped by category
$skills = [];
$result = $conn->query("
    SELECT 
        s.*,
        COUNT(DISTINCT us.user_id) as users_count
    FROM skills s
    LEFT JOIN user_skills us ON s.skill_id = us.skill_id
    GROUP BY s.skill_id
    ORDER BY s.skill_category, s.skill_subcategory, s.skill_name
");
while ($row = $result->fetch_assoc()) {
    $category = $row['skill_category'];
    if (!isset($skills[$category])) {
        $skills[$category] = [];
    }
    $skills[$category][] = $row;
}

closeDbConnection($conn);
?>

<?php if (isset($_SESSION['admin_success'])): ?>
    <div style="background: #D1FAE5; border: 2px solid #10B981; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
        <p style="margin: 0; color: #065F46;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['admin_success']) ?></p>
    </div>
    <?php unset($_SESSION['admin_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['admin_error'])): ?>
    <div style="background: #FEE2E2; border: 2px solid #EF4444; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem;">
        <p style="margin: 0; color: #991B1B;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['admin_error']) ?></p>
    </div>
    <?php unset($_SESSION['admin_error']); ?>
<?php endif; ?>

<div style="max-width: 1400px;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin-bottom: 0.5rem;">Manage Skills</h1>
            <p style="color: var(--gray-600); margin: 0;">
                Total: <?= array_sum(array_map('count', $skills)) ?> skills
            </p>
        </div>
        <button onclick="document.getElementById('addSkillModal').style.display='flex'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Skill
        </button>
    </div>

    <!-- Skills by Category -->
    <?php foreach ($skills as $category => $categorySkills): ?>
        <div style="background: white; border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; overflow: hidden;">
            <div style="background: <?= $category === 'technical' ? 'linear-gradient(135deg, #4FD1C5, #38B2AC)' : 'linear-gradient(135deg, #F59E0B, #D97706)' ?>; padding: 1.5rem; color: white;">
                <h2 style="margin: 0; color: white;">
                    <i class="fas <?= $category === 'technical' ? 'fa-code' : 'fa-graduation-cap' ?>"></i>
                    <?= ucfirst($category) ?> Skills (<?= count($categorySkills) ?>)
                </h2>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--gray-50); border-bottom: 2px solid var(--gray-200);">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Skill Name</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Subcategory</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700);">Description</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Users</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorySkills as $skill): ?>
                            <tr style="border-bottom: 1px solid var(--gray-200);">
                                <td style="padding: 1rem;">
                                    <span style="font-weight: 600; color: var(--gray-800);">
                                        <?= htmlspecialchars($skill['skill_name']) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: var(--gray-600);">
                                    <?= htmlspecialchars($skill['skill_subcategory']) ?>
                                </td>
                                <td style="padding: 1rem; color: var(--gray-600); font-size: 0.875rem;">
                                    <?= htmlspecialchars(substr($skill['description'] ?? '', 0, 60)) ?>
                                    <?= strlen($skill['description'] ?? '') > 60 ? '...' : '' ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: <?= $skill['users_count'] > 0 ? '#D1FAE5' : '#F3F4F6' ?>; color: <?= $skill['users_count'] > 0 ? '#065F46' : '#6B7280' ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                        <?= $skill['users_count'] ?> users
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <form method="POST" style="margin: 0; display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="skill_id" value="<?= $skill['skill_id'] ?>">
                                        <button type="submit" 
                                            style="background: #EF4444; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem;"
                                            onclick="return confirm('Delete this skill? This will remove it from all users.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<!-- Add Skill Modal -->
<div id="addSkillModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; border-radius: var(--radius-lg); padding: 2rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">Add New Skill</h2>
            <button onclick="document.getElementById('addSkillModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-500);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                    Skill Name <span style="color: #DC2626;">*</span>
                </label>
                <input type="text" name="skill_name" required
                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                    placeholder="e.g., Python Programming">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                    Category <span style="color: #DC2626;">*</span>
                </label>
                <select name="skill_category" required
                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);">
                    <option value="">Select Category</option>
                    <option value="technical">Technical</option>
                    <option value="academic">Academic</option>
                </select>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                    Subcategory
                </label>
                <input type="text" name="skill_subcategory"
                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md);"
                    placeholder="e.g., Programming, Web Development">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                    Description
                </label>
                <textarea name="description" rows="3"
                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); resize: vertical;"
                    placeholder="Brief description of the skill"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addSkillModal').style.display='none'" class="btn btn-outline">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Skill
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'admin-footer.php'; ?>