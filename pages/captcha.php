<?php
session_start();

// Generate random CAPTCHA code (6 characters)
$captcha_code = '';
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$length = 6;

for ($i = 0; $i < $length; $i++) {
    $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
}

// Store in session (valid for 5 minutes)
$_SESSION['captcha_code'] = $captcha_code;
$_SESSION['captcha_time'] = time();

// Create image
$width = 200;
$height = 70;
$image = imagecreatetruecolor($width, $height);

// Gradient background
$bg_start = imagecolorallocate($image, 240, 248, 255);
$bg_end = imagecolorallocate($image, 255, 250, 240);

for ($y = 0; $y < $height; $y++) {
    $alpha = $y / $height;
    $r = (int)(240 + (255 - 240) * $alpha);
    $g = (int)(248 + (250 - 248) * $alpha);
    $b = (int)(255 + (240 - 255) * $alpha);
    $color = imagecolorallocate($image, $r, $g, $b);
    imageline($image, 0, $y, $width, $y, $color);
}

// Add noise - random lines
for ($i = 0; $i < 3; $i++) {
    $line_color = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Add noise - random dots
for ($i = 0; $i < 80; $i++) {
    $dot_color = imagecolorallocate($image, rand(200, 230), rand(200, 230), rand(200, 230));
    imagesetpixel($image, rand(0, $width), rand(0, $height), $dot_color);
}

// Text colors
$text_colors = [
    imagecolorallocate($image, 0, 0, 0),
    imagecolorallocate($image, 0, 51, 102),
    imagecolorallocate($image, 153, 0, 0),
    imagecolorallocate($image, 0, 102, 51),
    imagecolorallocate($image, 102, 0, 102),
];

// Draw CAPTCHA text
$x = 15;
for ($i = 0; $i < strlen($captcha_code); $i++) {
    $char = $captcha_code[$i];
    $y = rand(25, 40);
    $color = $text_colors[rand(0, count($text_colors) - 1)];
    
    // Use built-in large font (size 5)
    imagestring($image, 5, $x, $y, $char, $color);
    
    $x += 30;
}

// Border
$border = imagecolorallocate($image, 79, 209, 197);
imagerectangle($image, 0, 0, $width - 1, $height - 1, $border);

// Output
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
imagepng($image);
imagedestroy($image);
?>