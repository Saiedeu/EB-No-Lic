<?php
/**
 * Security Functions for Exchange Bridge
 * Additional protection mechanisms
 */

// Prevent direct access
if (!defined('EB_SCRIPT_RUNNING')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

/**
 * Create protected .htaccess files
 */
function createProtectedDirectories() {
    $protectedDirs = [
        __DIR__ . '/../config',
        __DIR__ . '/../includes',
        __DIR__ . '/../logs'
    ];
    
    $htaccessContent = "Order deny,allow\nDeny from all\n";
    
    foreach ($protectedDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $htaccessFile = $dir . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, $htaccessContent);
        }
    }
}

/**
 * Validate installation integrity
 */
function validateInstallationIntegrity() {
    $requiredFiles = [
        'config/config.php',
        'config/license.php',
        'includes/license_check.php',
        'db.php',
        'functions.php'
    ];
    
    $missing = [];
    foreach ($requiredFiles as $file) {
        if (!file_exists(__DIR__ . '/../' . $file)) {
            $missing[] = $file;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Installation integrity check failed. Missing files: ' . implode(', ', $missing));
    }
    
    return true;
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = '') {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/security.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = "$timestamp [$event] IP: $ip | Details: $details | User-Agent: $userAgent" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Check for common hacking attempts
 */
function detectHackingAttempts() {
    $suspiciousPatterns = [
        '/\.\./','/<script/i','/union.*select/i','/drop.*table/i',
        '/exec\(/i','/system\(/i','/eval\(/i','/base64_decode/i'
    ];
    
    $inputs = array_merge($_GET, $_POST, $_COOKIE);
    
    foreach ($inputs as $key => $value) {
        if (is_string($value)) {
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    logSecurityEvent('HACKING_ATTEMPT', "Suspicious input detected: $key = $value");
                    http_response_code(403);
                    exit('Access denied');
                }
            }
        }
    }
}

// Initialize security measures
createProtectedDirectories();
detectHackingAttempts();