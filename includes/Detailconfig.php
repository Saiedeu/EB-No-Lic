<?php
/**
 * Exchange Bridge - Professional Configuration File
 * 
 * This file contains all configuration constants for the Exchange Bridge application.
 * It combines enhanced security features with comprehensive application settings.
 * 
 * @package     ExchangeBridge
 * @version     2.0.0
 * @author      Exchange Bridge Team
 * @created     2025-07-10
 * @updated     2025-07-10
 * 
 * SECURITY NOTICE:
 * - Keep this file secure and never expose it publicly
 * - Regularly update sensitive keys and tokens
 * - Monitor access logs for unauthorized attempts
 */

// =============================================================================
// SECURITY & ACCESS CONTROL
// =============================================================================

// Prevent direct access to this configuration file
if (!defined('EXCHANGE_BRIDGE_ACCESS') && !defined('ALLOW_ACCESS')) {
    http_response_code(403);
    header('Content-Type: text/plain');
    exit('403 Forbidden: Direct access to configuration file is not allowed.');
}

// Ensure the constant is defined for backward compatibility
if (!defined('ALLOW_ACCESS')) {
    define('ALLOW_ACCESS', true);
}

// =============================================================================
// DATABASE CONFIGURATION
// =============================================================================

// Primary database connection settings
if (!defined('DB_HOST')) define('DB_HOST', 'sql300.infinityfree.com');
if (!defined('DB_USER')) define('DB_USER', 'if0_39024958');
if (!defined('DB_PASS')) define('DB_PASS', 'SaidurRahman10');
if (!defined('DB_NAME')) define('DB_NAME', 'if0_39024958_V2');

if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
if (!defined('DB_COLLATE')) define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Database connection options
if (!defined('DB_PERSISTENT')) define('DB_PERSISTENT', false);
if (!defined('DB_TIMEOUT')) define('DB_TIMEOUT', 30);

// =============================================================================
// SITE CONFIGURATION
// =============================================================================

// Core site settings
if (!defined('SITE_URL')) define('SITE_URL', 'https://saieed-rahman.rf.gd/V2');
if (!defined('SITE_NAME')) define('SITE_NAME', 'Exchange Bridge');
if (!defined('SITE_TAGLINE')) define('SITE_TAGLINE', 'Exchange Taka Globally');
if (!defined('SITE_VERSION')) define('SITE_VERSION', '2.0.0');
if (!defined('TIMEZONE')) define('TIMEZONE', 'Asia/Dhaka');

// URL configurations
if (!defined('ADMIN_URL')) define('ADMIN_URL', SITE_URL . '/admin');
if (!defined('ASSETS_URL')) define('ASSETS_URL', SITE_URL . '/assets');
if (!defined('API_URL')) define('API_URL', SITE_URL . '/api');
if (!defined('UPLOADS_URL')) define('UPLOADS_URL', SITE_URL . '/uploads');

// SEO and Meta configurations
if (!defined('DEFAULT_META_TITLE')) define('DEFAULT_META_TITLE', 'Exchange Bridge - Fast & Secure Currency Exchange');
if (!defined('DEFAULT_META_DESCRIPTION')) define('DEFAULT_META_DESCRIPTION', 'Exchange Bridge offers fast, secure, and reliable currency exchange services globally with competitive rates and instant transactions.');
if (!defined('DEFAULT_META_KEYWORDS')) define('DEFAULT_META_KEYWORDS', 'currency exchange, money transfer, forex, taka exchange, global transfer');
if (!defined('DEFAULT_META_AUTHOR')) define('DEFAULT_META_AUTHOR', 'Exchange Bridge Team');

// Branding
if (!defined('TXT_LOGO')) define('TXT_LOGO', 'Exchange<span class="text-yellow-300">Bridge</span>');
if (!defined('SITE_FAVICON')) define('SITE_FAVICON', ASSETS_URL . '/images/favicon.ico');
if (!defined('SITE_LOGO')) define('SITE_LOGO', ASSETS_URL . '/images/logo.png');

// =============================================================================
// SECURITY CONFIGURATION
// =============================================================================

// Session security
if (!defined('SESSION_PREFIX')) define('SESSION_PREFIX', '00e6d73b6327f411');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 7200); // 2 hours
if (!defined('SESSION_REGENERATE_INTERVAL')) define('SESSION_REGENERATE_INTERVAL', 300); // 5 minutes

// CSRF Protection
if (!defined('CSRF_TOKEN_SECRET')) define('CSRF_TOKEN_SECRET', '17ba0c9d7db10da13c3f4a5bbd37b268542efc43b8d3a89c76b8fa687305112c');
if (!defined('CSRF_TOKEN_LIFETIME')) define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Encryption and Hashing
if (!defined('ENCRYPTION_KEY')) define('ENCRYPTION_KEY', '3e80357415daa38d00ad88b21fd618ff5a539422933f3145240d9c65ea97d0ef');
if (!defined('HASH_ALGORITHM')) define('HASH_ALGORITHM', 'sha256');
if (!defined('PASSWORD_HASH_ALGORITHM')) define('PASSWORD_HASH_ALGORITHM', PASSWORD_ARGON2ID);

// Cookie security
if (!defined('SECURE_COOKIES')) define('SECURE_COOKIES', true);
if (!defined('COOKIE_DOMAIN')) define('COOKIE_DOMAIN', '');
if (!defined('COOKIE_PATH')) define('COOKIE_PATH', '/');
if (!defined('COOKIE_HTTPONLY')) define('COOKIE_HTTPONLY', true);
if (!defined('COOKIE_SAMESITE')) define('COOKIE_SAMESITE', 'Strict');

// Login security
if (!defined('MAX_LOGIN_ATTEMPTS')) define('MAX_LOGIN_ATTEMPTS', 5);
if (!defined('LOGIN_LOCKOUT_TIME')) define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
if (!defined('PASSWORD_MIN_LENGTH')) define('PASSWORD_MIN_LENGTH', 8);
if (!defined('REQUIRE_STRONG_PASSWORD')) define('REQUIRE_STRONG_PASSWORD', true);

// Rate limiting
if (!defined('API_RATE_LIMIT_ENABLED')) define('API_RATE_LIMIT_ENABLED', true);
if (!defined('API_RATE_LIMIT_REQUESTS')) define('API_RATE_LIMIT_REQUESTS', 100);
if (!defined('API_RATE_LIMIT_WINDOW')) define('API_RATE_LIMIT_WINDOW', 3600); // 1 hour

// =============================================================================
// FILE UPLOAD CONFIGURATION
// =============================================================================

// Upload limits and restrictions
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', 10485760); // 10MB
if (!defined('ALLOWED_IMAGE_TYPES')) define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
if (!defined('ALLOWED_DOCUMENT_TYPES')) define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt', 'rtf']);
if (!defined('ALLOWED_UPLOAD_TYPES')) define('ALLOWED_UPLOAD_TYPES', 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,txt,rtf');

// Upload directories
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', dirname(__FILE__) . '/uploads');
if (!defined('TEMP_DIR')) define('TEMP_DIR', dirname(__FILE__) . '/temp');
if (!defined('BACKUP_DIR')) define('BACKUP_DIR', dirname(__FILE__) . '/backups');

// =============================================================================
// CACHE CONFIGURATION
// =============================================================================

// Cache settings
if (!defined('CACHE_ENABLED')) define('CACHE_ENABLED', true);
if (!defined('CACHE_DURATION')) define('CACHE_DURATION', 3600); // 1 hour
if (!defined('CACHE_DIR')) define('CACHE_DIR', dirname(__FILE__) . '/cache');
if (!defined('CACHE_PREFIX')) define('CACHE_PREFIX', 'eb_cache_');

// Cache types
if (!defined('ENABLE_QUERY_CACHE')) define('ENABLE_QUERY_CACHE', true);
if (!defined('ENABLE_PAGE_CACHE')) define('ENABLE_PAGE_CACHE', false);
if (!defined('ENABLE_API_CACHE')) define('ENABLE_API_CACHE', true);

// =============================================================================
// EMAIL CONFIGURATION
// =============================================================================

// Email settings
if (!defined('SMTP_ENABLED')) define('SMTP_ENABLED', true);
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', '');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', '');
if (!defined('SMTP_ENCRYPTION')) define('SMTP_ENCRYPTION', 'tls');

// Email defaults
if (!defined('DEFAULT_FROM_EMAIL')) define('DEFAULT_FROM_EMAIL', 'noreply@exchangebridge.com');
if (!defined('DEFAULT_FROM_NAME')) define('DEFAULT_FROM_NAME', SITE_NAME);
if (!defined('ADMIN_EMAIL')) define('ADMIN_EMAIL', 'admin@exchangebridge.com');

// =============================================================================
// APPLICATION CONFIGURATION
// =============================================================================

// Environment settings
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production'); // development, staging, production
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
if (!defined('MAINTENANCE_MODE')) define('MAINTENANCE_MODE', false);

// Error handling
if (!defined('ERROR_REPORTING')) define('ERROR_REPORTING', false);
if (!defined('LOG_ERRORS')) define('LOG_ERRORS', true);
if (!defined('LOG_QUERIES')) define('LOG_QUERIES', false);
if (!defined('ERROR_LOG_FILE')) define('ERROR_LOG_FILE', dirname(__FILE__) . '/logs/error.log');

// Performance settings
if (!defined('ENABLE_GZIP')) define('ENABLE_GZIP', true);
if (!defined('ENABLE_MINIFICATION')) define('ENABLE_MINIFICATION', true);
if (!defined('MEMORY_LIMIT')) define('MEMORY_LIMIT', '256M');
if (!defined('MAX_EXECUTION_TIME')) define('MAX_EXECUTION_TIME', 30);

// API settings
if (!defined('API_ENABLED')) define('API_ENABLED', true);
if (!defined('API_VERSION')) define('API_VERSION', 'v1');
if (!defined('API_AUTHENTICATION')) define('API_AUTHENTICATION', true);

// =============================================================================
// FEATURE FLAGS
// =============================================================================

// Feature toggles
if (!defined('ENABLE_REGISTRATION')) define('ENABLE_REGISTRATION', true);
if (!defined('ENABLE_SOCIAL_LOGIN')) define('ENABLE_SOCIAL_LOGIN', false);
if (!defined('ENABLE_TWO_FACTOR_AUTH')) define('ENABLE_TWO_FACTOR_AUTH', true);
if (!defined('ENABLE_NOTIFICATIONS')) define('ENABLE_NOTIFICATIONS', true);
if (!defined('ENABLE_ANALYTICS')) define('ENABLE_ANALYTICS', true);

// =============================================================================
// RUNTIME CONFIGURATION
// =============================================================================

// Set timezone
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(TIMEZONE);
}

// Configure error reporting based on environment
if (DEBUG_MODE && ENVIRONMENT !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', LOG_ERRORS ? 1 : 0);
}

// Configure session security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', COOKIE_HTTPONLY ? 1 : 0);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', SECURE_COOKIES ? 1 : 0);
    ini_set('session.cookie_samesite', COOKIE_SAMESITE);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.name', SESSION_PREFIX . '_session');
}

// Set memory and execution limits
ini_set('memory_limit', MEMORY_LIMIT);
set_time_limit(MAX_EXECUTION_TIME);

// Enable output compression if available
if (ENABLE_GZIP && function_exists('ob_gzhandler') && !ob_get_status()) {
    ob_start('ob_gzhandler');
}

// =============================================================================
// VALIDATION & FINAL CHECKS
// =============================================================================

// Validate critical configurations
if (empty(DB_HOST) || empty(DB_USER) || empty(DB_NAME)) {
    if (DEBUG_MODE) {
        trigger_error('Database configuration is incomplete', E_USER_ERROR);
    }
}

if (empty(SITE_URL) || empty(SITE_NAME)) {
    if (DEBUG_MODE) {
        trigger_error('Site configuration is incomplete', E_USER_ERROR);
    }
}

// Security validation
if (strlen(ENCRYPTION_KEY) < 32) {
    if (DEBUG_MODE) {
        trigger_error('Encryption key is too short (minimum 32 characters)', E_USER_WARNING);
    }
}

// Create required directories if they don't exist
$required_dirs = [
    defined('UPLOAD_DIR') ? UPLOAD_DIR : '',
    defined('CACHE_DIR') ? CACHE_DIR : '',
    defined('TEMP_DIR') ? TEMP_DIR : '',
    dirname(ERROR_LOG_FILE)
];

foreach ($required_dirs as $dir) {
    if (!empty($dir) && !is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// =============================================================================
// CONSTANTS STATUS LOG (DEBUG MODE ONLY)
// =============================================================================

if (DEBUG_MODE) {
    $config_status = [
        'loaded_at' => date('Y-m-d H:i:s'),
        'environment' => ENVIRONMENT,
        'debug_mode' => DEBUG_MODE,
        'cache_enabled' => CACHE_ENABLED,
        'secure_cookies' => SECURE_COOKIES,
        'maintenance_mode' => MAINTENANCE_MODE
    ];
    
    // Log configuration status
    error_log('[Exchange Bridge Config] Configuration loaded: ' . json_encode($config_status));
}

// Configuration successfully loaded
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
    define('CONFIG_VERSION', '2.0.0');
    define('CONFIG_LOADED_TIME', microtime(true));
}

/*
 * END OF CONFIGURATION FILE
 * 
 * All configuration constants have been defined above.
 * Any modifications should be made carefully and tested thoroughly.
 * 
 * For support or questions, contact the development team.
 */