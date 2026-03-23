<?php
session_start();

// Load Google config
$googleConfig = require_once '../includes/google-config.php';

// Generate state token for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;
$_SESSION['google_oauth_action'] = 'signin'; // Track if this is signup or signin

// Build Google OAuth URL
$params = [
    'client_id' => $googleConfig['client_id'],
    'redirect_uri' => $googleConfig['redirect_uri'],
    'response_type' => 'code',
    'scope' => $googleConfig['scope'],
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account' // Force account selection
];

$authUrl = $googleConfig['auth_url'] . '?' . http_build_query($params);

// Redirect to Google
header('Location: ' . $authUrl);
exit();
?>