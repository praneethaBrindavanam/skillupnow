<!DOCTYPE html>
<html>
<head>
    <title>SkillUp Now - Path Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        h1 { color: #4FD1C5; }
        a { color: #4FD1C5; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🔧 SkillUp Now - Path Test</h1>
    
    <div class="test-box">
        <h2>✅ PHP is Working!</h2>
        <p>Current PHP Version: <strong><?php echo phpversion(); ?></strong></p>
        <p>Server Time: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
    </div>
    
    <div class="test-box">
        <h2>📁 File System Check</h2>
        <p>Current Directory: <code><?php echo getcwd(); ?></code></p>
        
        <?php
        $files_to_check = [
            'index.php' => 'Homepage',
            'assets/css/style.css' => 'Stylesheet',
            'assets/js/script.js' => 'JavaScript',
            'includes/header.php' => 'Header',
            'includes/footer.php' => 'Footer',
            'pages/about.php' => 'About Page',
            'pages/contact.php' => 'Contact Page',
            'pages/signin.php' => 'Sign In Page',
            'pages/signup.php' => 'Sign Up Page'
        ];
        
        foreach ($files_to_check as $file => $name) {
            if (file_exists($file)) {
                echo "<p class='success'>✓ $name ($file)</p>";
            } else {
                echo "<p class='error'>✗ $name ($file) - NOT FOUND</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-box">
        <h2>🌐 Navigation Links</h2>
        <p><a href="/index.php">🏠 Homepage</a></p>
        <p><a href="/pages/about.php">ℹ️ About Us</a></p>
        <p><a href="/pages/contact.php">📧 Contact</a></p>
        <p><a href="/pages/signin.php">🔐 Sign In</a></p>
        <p><a href="/pages/signup.php">📝 Sign Up</a></p>
        <p><a href="/pages/how-it-works.php">❓ How It Works</a></p>
        <p><a href="/pages/browse.php">🔍 Browse Skills</a></p>
    </div>
    
    <div class="test-box">
        <h2>🎨 CSS Test</h2>
        <link rel="stylesheet" href="/assets/css/style.css">
        <div style="padding: 20px; background: linear-gradient(135deg, #4FD1C5, #38B2AC); color: white; border-radius: 8px; text-align: center;">
            <p style="margin: 0; font-size: 1.2rem; font-weight: bold;">If you see this styled box with gradient, CSS is loading correctly! ✨</p>
        </div>
    </div>
    
    <div class="test-box">
        <h2>🚀 Ready to Go!</h2>
        <p>Everything looks good! Click the Homepage link above to view your site.</p>
        <p style="margin-top: 20px;"><a href="/index.php" style="background: #4FD1C5; color: white; padding: 12px 24px; border-radius: 25px; display: inline-block;">Go to Homepage →</a></p>
    </div>
</body>
</html>