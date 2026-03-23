<?php
// Google OAuth Configuration

return [
'client_id' => 'GOOGLE_CLIENT_ID_PLACEHOLDER', // Add your own
'client_secret' => 'GOOGLE_CLIENT_SECRET_PLACEHOLDER', // Add your own
    'redirect_uri' => 'http://localhost/skillupnow/pages/google-callback.php',
    
    // Google OAuth URLs
    'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_url' => 'https://oauth2.googleapis.com/token',
    'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
    
    // Scopes
    'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
];
?>