<?php
// config/google.php
return [
    'client_id'     => '532699318803-vahaeh2b7b4c1gn5taca7tbjk1erane3.apps.googleusercontent.com',
    'client_secret' => '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i',
    'redirect_uri'  => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/newsletter_automatique/app/controllers/GoogleAuthController.php',
];
