<?php
session_start();

// Clear all session variables
session_unset();

// Destroy the session entirely
session_destroy();

// Redirect to referring page if available, otherwise default to index.php
$redirect = '../index.php';  // default fallback

if (!empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];

    // Optional: make sure the referer is from your own site (for security)
    $host = parse_url($referer, PHP_URL_HOST);
    if ($host === $_SERVER['HTTP_HOST']) {
        $redirect = $referer;
    }
}

header("Location: $redirect");
exit;