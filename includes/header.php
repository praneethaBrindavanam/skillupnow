<?php
// Base URL for the project (XAMPP)
$BASE_URL = "/skillupnow/skillupnow";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillUp Now - Digital skill learning platform for students. Connect with college students, exchange skills, and grow together.">
    <meta name="keywords" content="skill learning, education, mentorship, college students, peer learning">
    <meta name="author" content="GVP College of Engineering">

    <title>
        <?= isset($pageTitle) 
            ? $pageTitle . ' - SkillUp Now' 
            : 'SkillUp Now - Digital Skill Learning Platform'; ?>
    </title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $BASE_URL ?>/assets/images/favicon.png">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= $BASE_URL ?>/assets/css/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Header -->
<header class="header">
    <nav class="nav-container">

        <!-- Logo -->
        <a href="<?= $BASE_URL ?>/index.php" class="logo">
            <div class="logo-icon">S</div>
            <span>SkillUpNow</span>
        </a>

        <!-- Role Toggle Switch -->
        <div class="role-toggle-container">
            <div class="role-toggle">
                <button class="role-option active" data-role="students">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </button>
                <button class="role-option" data-role="tutors">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Tutors</span>
                </button>
                <div class="role-toggle-slider"></div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <ul class="nav-menu">
            <li><a href="<?= $BASE_URL ?>/index.php">Home</a></li>
            <li><a href="<?= $BASE_URL ?>/pages/skills.php">Skills</a></li>
            <li><a href="<?= $BASE_URL ?>/pages/browse.php">Browse</a></li>
            <li><a href="<?= $BASE_URL ?>/pages/about.php">About</a></li>
            <li><a href="<?= $BASE_URL ?>/pages/contact.php">Contact</a></li>
        </ul>

        <!-- Action Buttons -->
        <div class="nav-actions">
            <a href="<?= $BASE_URL ?>/pages/signin.php" class="btn btn-outline">Sign in</a>
            <a href="<?= $BASE_URL ?>/pages/signup.php" class="btn btn-primary">Get started</a>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </nav>
</header>

<!-- Main Content -->
<main class="main-content">