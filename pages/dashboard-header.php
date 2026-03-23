<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$userType = $_SESSION['user_type'];
$userName = $_SESSION['user_name'];
$customUserId = $_SESSION['custom_user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - SkillUp Now' : 'Dashboard - SkillUp Now'; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/skillupnow/assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-cyan: #4FD1C5;
            --primary-teal: #38B2AC;
            --accent-pink: #F687B3;
            --accent-purple: #9F7AEA;
            --accent-orange: #ED8936;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.25rem;
            --radius-md: 0.5rem;
            --radius-lg: 1rem;
            --radius-full: 9999px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F7FAFC;
            color: var(--gray-900);
        }
        
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            padding-top: 70px;
        }
        
        .dashboard-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            justify-content: space-between;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .dashboard-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            text-decoration: none;
            transition: opacity 0.2s;
        }
        
        .dashboard-logo:hover {
            opacity: 0.8;
        }
        
        .dashboard-logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.125rem;
            box-shadow: 0 4px 6px rgba(79, 209, 197, 0.3);
        }
        
        .dashboard-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
            margin-bottom: 0.125rem;
        }
        
        .user-id {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-family: 'Courier New', monospace;
            padding: 0.125rem 0.5rem;
            background: var(--gray-100);
            border-radius: 0.25rem;
            display: inline-block;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.125rem;
            box-shadow: 0 4px 6px rgba(79, 209, 197, 0.25);
            border: 3px solid white;
        }
        
        .dashboard-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--gray-200);
            position: fixed;
            left: 0;
            top: 70px;
            bottom: 0;
            overflow-y: auto;
            padding: 2rem 0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.25rem;
            padding: 0 1rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            border-radius: 0.5rem;
            font-size: 0.9375rem;
        }
        
        .sidebar-menu a:hover {
            background: linear-gradient(135deg, rgba(79, 209, 197, 0.08), rgba(56, 178, 172, 0.08));
            color: var(--primary-teal);
            transform: translateX(2px);
        }
        
        .sidebar-menu a.active {
            background: linear-gradient(135deg, rgba(79, 209, 197, 0.15), rgba(56, 178, 172, 0.15));
            color: var(--primary-teal);
            font-weight: 600;
            box-shadow: inset 3px 0 0 var(--primary-teal);
        }
        
        .sidebar-menu a i {
            width: 22px;
            text-align: center;
            font-size: 1.125rem;
        }
        
        .sidebar-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 1.25rem 1.5rem;
        }
        
        .dashboard-content {
            flex: 1;
            margin-left: 280px;
            padding: 2.5rem;
            background: #F7FAFC;
            min-height: calc(100vh - 70px);
        }
        
        .logout-btn {
            color: #EF4444 !important;
        }
        
        .logout-btn:hover {
            background: #FEE2E2 !important;
            color: #DC2626 !important;
        }
        
        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            color: white;
            box-shadow: 0 4px 6px rgba(79, 209, 197, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 209, 197, 0.4);
        }
        
        .btn-outline {
            background: white;
            color: var(--primary-teal);
            border: 2px solid var(--gray-300);
        }
        
        .btn-outline:hover {
            border-color: var(--primary-teal);
            background: rgba(79, 209, 197, 0.05);
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                z-index: 999;
            }
            
            .dashboard-sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .dashboard-content {
                margin-left: 0;
            }
            
            .user-info {
                display: none;
            }
            
            .dashboard-header {
                padding: 0 1rem;
            }
            
            .dashboard-content {
                padding: 1.5rem;
            }
        }
        
        /* Scrollbar Styling */
        .dashboard-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .dashboard-sidebar::-webkit-scrollbar-track {
            background: var(--gray-100);
        }
        
        .dashboard-sidebar::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }
        
        .dashboard-sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--primary-teal);
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <a href="<?= $userType === 'tutor' ? 'tutor-dashboard.php' : 'student-dashboard.php' ?>" class="dashboard-logo">
            <div class="dashboard-logo-icon">S</div>
            <span>SkillUp Now</span>
        </a>
        
        <div class="dashboard-user">
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-id"><?= htmlspecialchars($customUserId) ?></div>
            </div>
            <div class="user-avatar">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>
        </div>
    </header>
    
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= $userType === 'tutor' ? 'tutor-dashboard.php' : 'student-dashboard.php' ?>" class="<?= basename($_SERVER['PHP_SELF']) == ($userType === 'tutor' ? 'tutor-dashboard.php' : 'student-dashboard.php') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li>
                    <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                <?php if ($userType === 'learner'): ?>
                <li>
                    <a href="browse.php" class="<?= basename($_SERVER['PHP_SELF']) == 'browse.php' ? 'active' : '' ?>">
                        <i class="fas fa-search"></i>
                        <span>Find Tutors</span>
                    </a>
                </li>
                
                <li>
                    <a href="my-tutors.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my-tutors.php' ? 'active' : '' ?>">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>My Tutors</span>
                    </a>
                </li>
                
                <li>
                    <a href="my-classes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my-classes.php' ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                
                <?php else: ?>
                
                <!-- TUTOR VERIFICATION LINK - NEW -->
                <li>
                    <a href="tutor-verification.php" class="<?= basename($_SERVER['PHP_SELF']) == 'tutor-verification.php' ? 'active' : '' ?>">
                        <i class="fas fa-shield-alt"></i>
                        <span>Tutor Verification</span>
                    </a>
                </li>
                
                <li>
                    <a href="my-students.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my-students.php' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>My Students</span>
                    </a>
                </li>
                
                <li>
                    <a href="my-classes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my-classes.php' ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                
                <li>
                    <a href="schedule.php" class="<?= basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule Class</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <div class="sidebar-divider"></div>
                
                <li>
                    <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                
                <li>
                    <a href="help.php" class="<?= basename($_SERVER['PHP_SELF']) == 'help.php' ? 'active' : '' ?>">
                        <i class="fas fa-question-circle"></i>
                        <span>Help & Support</span>
                    </a>
                </li>
                
                <li>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-content">