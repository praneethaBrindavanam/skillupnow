<?php
// Base URL for the project
$BASE_URL = "/skillupnow";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'User';
$userType = $_SESSION['user_type'] ?? 'learner';
$customUserId = $_SESSION['custom_user_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillUp Now - Digital skill learning platform for students">
    <title><?= isset($pageTitle) ? $pageTitle . ' - SkillUp Now' : 'SkillUp Now - Digital Skill Learning Platform'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $BASE_URL ?>/assets/images/favicon.png">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= $BASE_URL ?>/assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Header for Logged In Users -->
<header class="header" style="background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <nav class="nav-container">
        
        <!-- Logo -->
        <a href="<?= $BASE_URL ?>/index.php" class="logo">
            <div class="logo-icon">S</div>
            <span>SkillUpNow</span>
        </a>

        <!-- Navigation Menu -->
        <ul class="nav-menu">
            <?php if ($userType === 'learner'): ?>
                <li><a href="<?= $BASE_URL ?>/pages/student-dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a></li>
                <li><a href="<?= $BASE_URL ?>/pages/browse.php">
                    <i class="fas fa-search"></i> Find Tutors
                </a></li>
                <li><a href="<?= $BASE_URL ?>/pages/skills.php">
                    <i class="fas fa-book"></i> Skills
                </a></li>
            <?php else: ?>
                <li><a href="<?= $BASE_URL ?>/pages/tutor-dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a></li>
                <li><a href="<?= $BASE_URL ?>/pages/skills.php">
                    <i class="fas fa-graduation-cap"></i> Skills
                </a></li>
            <?php endif; ?>
        </ul>

        <!-- User Menu -->
        <div class="user-menu" style="display: flex; align-items: center; gap: 1rem;">
            <!-- User Info -->
            <div style="text-align: right; display: none; @media (min-width: 768px) { display: block; }">
                <div style="font-weight: 600; color: var(--gray-800); font-size: 0.875rem;">
                    <?= htmlspecialchars($userName) ?>
                </div>
                <div style="font-size: 0.75rem; color: var(--gray-500);">
                    <?= htmlspecialchars($customUserId) ?>
                </div>
            </div>

            <!-- User Avatar & Dropdown -->
            <div class="dropdown" style="position: relative;">
                <button class="user-avatar" onclick="toggleDropdown()" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem;">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="userDropdown" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 50px; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-xl); min-width: 200px; z-index: 1000;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                        <div style="font-weight: 600; color: var(--gray-800);">
                            <?= htmlspecialchars($userName) ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--gray-500);">
                            <?= $userType === 'learner' ? 'Student' : 'Tutor' ?>
                        </div>
                    </div>
                    <a href="<?= $BASE_URL ?>/pages/complete-profile.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--gray-700); text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                    <a href="<?= $BASE_URL ?>/pages/logout.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #DC2626; text-decoration: none; border-top: 1px solid var(--gray-200); transition: background 0.2s;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sign Out</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </nav>
</header>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const avatar = document.querySelector('.user-avatar');
    
    if (dropdown && avatar && !avatar.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
</script>

<!-- Main Content -->
<main class="main-content">