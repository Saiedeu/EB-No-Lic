<?php
/**
 * Exchange Bridge Configuration File
 * Generated on: 2025-07-10 08:49:01 EDT
 * DO NOT EDIT MANUALLY UNLESS YOU KNOW WHAT YOU'RE DOING
 */

// Prevent direct access
if (!defined('ALLOW_ACCESS')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

// Database Configuration
define('DB_HOST', 'sql300.infinityfree.com');
define('DB_USER', 'if0_39024958');
define('DB_PASS', 'SaidurRahman10');
define('DB_NAME', 'if0_39024958_V2');
define('DB_PREFIX', 'eb_');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://saieed-rahman.rf.gd/V2');
define('SITE_NAME', 'Exchange Brid');
define('TIMEZONE', 'Asia/Dhaka');

// Security Configuration
define('SESSION_PREFIX', '4bc7250f3ea75686');
define('CSRF_TOKEN_SECRET', '02e60d3a7aeb5bf85e44b7001a4c86119f3fbc20cc8917699dc75ef236414afc');
define('ENCRYPTION_KEY', '5a4f2b91dae4eb3b68b668b380ca5a911c50e315337e7d3c566d04a91db747f0');
define('SECURE_COOKIES', true);

// Application Configuration
define('DEBUG_MODE', false);
define('ERROR_REPORTING', false);
define('LOG_ERRORS', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 10485760); // 10MB
define('ALLOWED_UPLOAD_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt');

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour

// Set timezone
date_default_timezone_set(TIMEZONE);

// Set error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', SECURE_COOKIES ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
