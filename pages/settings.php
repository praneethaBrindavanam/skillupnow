<?php
session_start();
require_once '../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Settings";
$userId = $_SESSION['user_id'];
$conn = getDbConnection();

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

closeDbConnection($conn);

include 'dashboard-header.php';
?>

<style>
.settings-container {
    max-width: 900px;
    margin: 0 auto;
}

.settings-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #F3F4F6;
}

.settings-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid #F3F4F6;
}

.settings-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.settings-title {
    flex: 1;
}

.settings-title h2 {
    margin: 0 0 4px 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.settings-title p {
    margin: 0;
    font-size: 0.875rem;
    color: #6B7280;
}

.setting-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid #F3F4F6;
}

.setting-row:last-child {
    border-bottom: none;
}

.setting-info {
    flex: 1;
}

.setting-label {
    margin: 0 0 4px 0;
    font-size: 0.938rem;
    font-weight: 600;
    color: #111827;
}

.setting-description {
    margin: 0;
    font-size: 0.813rem;
    color: #6B7280;
}

.setting-value {
    font-size: 0.875rem;
    color: #4B5563;
    padding: 8px 16px;
    background: #F9FAFB;
    border-radius: 8px;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 28px;
    background: #E5E7EB;
    border-radius: 14px;
    cursor: pointer;
    transition: background 0.3s;
}

.toggle-switch.active {
    background: #4FD1C5;
}

.toggle-switch::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: white;
    border-radius: 50%;
    transition: left 0.3s;
}

.toggle-switch.active::after {
    left: 25px;
}

.danger-zone {
    border: 2px solid #FEE2E2;
    background: #FEF2F2;
}

.btn-danger {
    background: #EF4444;
    color: white;
    border: 2px solid #DC2626;
}

.btn-danger:hover {
    background: #DC2626;
}
</style>

<div class="settings-container">
    
    <!-- Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="margin-bottom: 8px; font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-cog"></i> Settings
        </h1>
        <p style="color: #6B7280; font-size: 1rem; margin: 0;">
            Manage your account settings and preferences
        </p>
    </div>

    <!-- Profile Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="settings-title">
                <h2>Profile Information</h2>
                <p>Update your personal details</p>
            </div>
            <a href="profile.php" class="btn btn-primary" style="text-decoration: none;">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Full Name</p>
                <p class="setting-description">Your display name</p>
            </div>
            <span class="setting-value"><?= htmlspecialchars($user['full_name']) ?></span>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Student ID</p>
                <p class="setting-description">Your unique identifier</p>
            </div>
            <span class="setting-value"><?= htmlspecialchars($user['custom_user_id']) ?></span>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Email Address</p>
                <p class="setting-description">Used for login and notifications</p>
            </div>
            <span class="setting-value"><?= htmlspecialchars($user['email']) ?></span>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">College</p>
                <p class="setting-description">Your institution</p>
            </div>
            <span class="setting-value"><?= htmlspecialchars($user['college_name']) ?></span>
        </div>
    </div>

    <!-- Account Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="settings-title">
                <h2>Account Security</h2>
                <p>Manage your password and security</p>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Password</p>
                <p class="setting-description">Change your password</p>
            </div>
            <button class="btn btn-outline" onclick="alert('Change password feature coming soon!')">
                <i class="fas fa-key"></i> Change Password
            </button>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Two-Factor Authentication</p>
                <p class="setting-description">Add extra security to your account</p>
            </div>
            <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Active Sessions</p>
                <p class="setting-description">See where you're logged in</p>
            </div>
            <button class="btn btn-outline" onclick="alert('Session management coming soon!')">
                <i class="fas fa-laptop"></i> View Sessions
            </button>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="settings-title">
                <h2>Notifications</h2>
                <p>Choose what updates you receive</p>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Email Notifications</p>
                <p class="setting-description">Receive updates via email</p>
            </div>
            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Class Reminders</p>
                <p class="setting-description">Get notified before classes start</p>
            </div>
            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">New Classes from Tutors</p>
                <p class="setting-description">Alert when followed tutors schedule classes</p>
            </div>
            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Marketing Emails</p>
                <p class="setting-description">Updates and offers from SkillUp Now</p>
            </div>
            <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="settings-title">
                <h2>Privacy</h2>
                <p>Control your visibility and data</p>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Profile Visibility</p>
                <p class="setting-description">Who can see your profile</p>
            </div>
            <select style="padding: 8px 16px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem;">
                <option>Everyone</option>
                <option>Tutors Only</option>
                <option>Private</option>
            </select>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Show Email to Tutors</p>
                <p class="setting-description">Let tutors contact you directly</p>
            </div>
            <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Activity Status</p>
                <p class="setting-description">Show when you're online</p>
            </div>
            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
        </div>
    </div>

    <!-- Preferences -->
    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-icon">
                <i class="fas fa-palette"></i>
            </div>
            <div class="settings-title">
                <h2>Preferences</h2>
                <p>Customize your experience</p>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Language</p>
                <p class="setting-description">Choose your preferred language</p>
            </div>
            <select style="padding: 8px 16px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem;">
                <option>English</option>
                <option>Hindi</option>
                <option>Telugu</option>
            </select>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Time Zone</p>
                <p class="setting-description">For accurate class times</p>
            </div>
            <select style="padding: 8px 16px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem;">
                <option>IST (UTC+5:30)</option>
                <option>EST (UTC-5:00)</option>
                <option>PST (UTC-8:00)</option>
            </select>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label">Theme</p>
                <p class="setting-description">Choose your display mode</p>
            </div>
            <select style="padding: 8px 16px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem;">
                <option>Light Mode</option>
                <option>Dark Mode</option>
                <option>Auto</option>
            </select>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="settings-card danger-zone">
        <div class="settings-header">
            <div class="settings-icon" style="background: #EF4444;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="settings-title">
                <h2 style="color: #DC2626;">Danger Zone</h2>
                <p style="color: #991B1B;">Irreversible actions</p>
            </div>
        </div>

        <div class="setting-row">
            <div class="setting-info">
                <p class="setting-label" style="color: #DC2626;">Delete Account</p>
                <p class="setting-description">Permanently delete your account and all data</p>
            </div>
            <button class="btn btn-danger" onclick="if(confirm('Are you sure? This cannot be undone!')) alert('Account deletion feature coming soon!')">
                <i class="fas fa-trash"></i> Delete Account
            </button>
        </div>
    </div>

    <!-- Help Link -->
    <div style="text-align: center; margin-top: 32px; padding: 20px;">
        <p style="color: #6B7280; margin: 0 0 12px 0;">
            Need help with something?
        </p>
        <a href="help-support.php" class="btn btn-outline" style="text-decoration: none;">
            <i class="fas fa-question-circle"></i> Help & Support
        </a>
    </div>

</div>

<?php include 'dashboard-footer.php'; ?>