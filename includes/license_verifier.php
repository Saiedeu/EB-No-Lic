<?php
/**
 * Enhanced License Verification System
 * 
 * This script handles license validation with the license server
 * and ensures that the software only runs with a valid license.
 * 
 * Features:
 * - Regular license validation
 * - Response to license deactivation/deletion
 * - Support for both automatic and manual validation
 * - Grace period for temporary server issues
 * 
 * Version: 2.0.0
 */

// Define API constants if not already defined
if (!defined('API_URL')) define('API_URL', 'https://eb-admin.rf.gd/api.php');
if (!defined('API_KEY')) define('API_KEY', 'd7x9HgT2pL5vZwK8qY3rS6mN4jF1aE0b');
if (!defined('LICENSE_CHECK_INTERVAL')) define('LICENSE_CHECK_INTERVAL', 86400); // 24 hours
if (!defined('LICENSE_VERIFICATION_FILE')) define('LICENSE_VERIFICATION_FILE', 'verification.php');
if (!defined('LICENSE_GRACE_PERIOD')) define('LICENSE_GRACE_PERIOD', 604800); // 7 days

class LicenseVerifier {
    private $licenseKey;
    private $domain;
    private $ip;
    private $verificationFile;
    private $debugMode;
    private $productName;
    
    /**
     * Constructor
     * 
     * @param string $licenseKey The license key to verify (optional, can be loaded from file)
     * @param bool $debugMode Whether to enable detailed logging
     * @param string $productName Optional product name for multi-product setups
     */
    public function __construct($licenseKey = null, $debugMode = false, $productName = 'default') {
        $this->domain = $this->getCurrentDomain();
        $this->ip = $this->getClientIP();
        $this->verificationFile = __DIR__ . '/' . LICENSE_VERIFICATION_FILE;
        $this->debugMode = $debugMode;
        $this->productName = $productName;
        
        // Get license key from parameter or verification file
        if ($licenseKey) {
            $this->licenseKey = $licenseKey;
        } else {
            $this->licenseKey = $this->getLicenseKeyFromVerificationFile();
        }
        
        if ($this->debugMode) {
            $this->log("LicenseVerifier initialized with key: " . $this->licenseKey);
            $this->log("Domain: " . $this->domain);
            $this->log("IP: " . $this->ip);
        }
    }
    
    /**
     * Get current domain
     * 
     * @return string
     */
    private function getCurrentDomain() {
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        
        // Remove www. prefix if present
        $domain = preg_replace('/^www\./i', '', $domain);
        
        // Remove port number if present
        $domain = preg_replace('/:\d+$/', '', $domain);
        
        return strtolower(trim($domain));
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    private function getClientIP() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    /**
     * Get license key from verification file
     * 
     * @return string|null
     */
    private function getLicenseKeyFromVerificationFile() {
        if (!file_exists($this->verificationFile)) {
            if ($this->debugMode) $this->log("Verification file not found at: " . $this->verificationFile);
            return null;
        }
        
        $data = include $this->verificationFile;
        if (!isset($data['license_key'])) {
            if ($this->debugMode) $this->log("License key not found in verification file");
            return null;
        }
        
        return $data['license_key'];
    }
    
    /**
     * Verify license with server
     * 
     * @return bool True if license is valid
     * @throws Exception If license validation fails
     */
    public function verifyLicense() {
        if (empty($this->licenseKey)) {
            throw new Exception("License key is missing or invalid");
        }
        
        // First check verification file hash if it exists
        if (file_exists($this->verificationFile)) {
            if (!$this->verifyFileIntegrity()) {
                if ($this->debugMode) $this->log("File integrity check failed");
                throw new Exception("License verification failed. Verification file has been tampered with.");
            }
            
            // Check license status in verification file
            $data = include $this->verificationFile;
            if (isset($data['status']) && $data['status'] !== 'active') {
                if ($this->debugMode) $this->log("License is inactive in verification file");
                throw new Exception("License is inactive or has been revoked");
            }
            
            // Check domain in verification file
            if (isset($data['domain']) && $data['domain'] !== '*' && $data['domain'] !== $this->domain) {
                if ($this->debugMode) $this->log("Domain mismatch: " . $data['domain'] . " vs " . $this->domain);
                throw new Exception("This license is not valid for this domain");
            }
            
            // Check if we need to verify with server
            $lastCheck = isset($data['last_check']) ? $data['last_check'] : 0;
            $currentTime = time();
            
            if ($currentTime - $lastCheck > LICENSE_CHECK_INTERVAL) {
                if ($this->debugMode) $this->log("Time for server check: Last check was " . date('Y-m-d H:i:s', $lastCheck));
                return $this->checkWithServer();
            }
            
            if ($this->debugMode) $this->log("Using cached verification (last checked: " . date('Y-m-d H:i:s', $lastCheck) . ")");
            return true;
        } else {
            // No verification file, must check with server
            if ($this->debugMode) $this->log("No verification file, checking with server");
            return $this->checkWithServer();
        }
    }
    
    /**
     * Verify the integrity of the verification file
     * 
     * @return bool
     */
    private function verifyFileIntegrity() {
        $data = include $this->verificationFile;
        if (!isset($data['license_key']) || !isset($data['hash']) || !isset($data['domain'])) {
            if ($this->debugMode) $this->log("Verification file missing required fields");
            return false;
        }
        
        // Generate hash
        $salt = defined('LICENSE_SALT') ? LICENSE_SALT : 'eb_license_system_salt_key_2023';
        $expectedHash = md5($data['license_key'] . $data['domain'] . $salt);
        
        $result = ($expectedHash === $data['hash']);
        
        if ($this->debugMode && !$result) {
            $this->log("Hash mismatch: " . $expectedHash . " vs " . $data['hash']);
        }
        
        return $result;
    }
    
    /**
     * Check license with server
     * 
     * @return bool
     * @throws Exception
     */
    private function checkWithServer() {
        try {
            $postData = [
                'action' => 'verify',
                'license_key' => $this->licenseKey,
                'domain' => $this->domain,
                'ip' => $this->ip,
                'api_key' => API_KEY,
                'product' => $this->productName
            ];
            
            if ($this->debugMode) {
                $this->log("Checking license with server: " . API_URL);
                $this->log("Post data: " . json_encode($postData));
            }
            
            // Use curl if available
            if (function_exists('curl_version')) {
                $ch = curl_init(API_URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Allow HTTP and HTTPS
                
                if ($this->debugMode) {
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    $verbose = fopen('php://temp', 'w+');
                    curl_setopt($ch, CURLOPT_STDERR, $verbose);
                }
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                
                if ($this->debugMode) {
                    $this->log("HTTP Code: " . $httpCode);
                    if (!empty($error)) {
                        $this->log("cURL error: " . $error);
                    }
                    
                    rewind($verbose);
                    $verboseLog = stream_get_contents($verbose);
                    $this->log("cURL log: " . $verboseLog);
                    
                    $this->log("Response: " . $response);
                }
                
                curl_close($ch);
                
                if ($httpCode !== 200) {
                    throw new Exception("API returned HTTP code $httpCode");
                }
            } else {
                // Fallback to file_get_contents
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($postData),
                        'timeout' => 30
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]);
                
                $response = file_get_contents(API_URL, false, $context);
                
                if ($this->debugMode) {
                    $this->log("Using file_get_contents fallback");
                    $this->log("Response: " . $response);
                }
                
                if ($response === false) {
                    throw new Exception("Failed to connect to license server");
                }
            }
            
            // Parse response
            $responseData = json_decode($response, true);
            
            if (!$responseData || !isset($responseData['status'])) {
                throw new Exception("Invalid response from license server");
            }
            
            if ($responseData['status'] === 'success') {
                // Create or update verification file
                $salt = defined('LICENSE_SALT') ? LICENSE_SALT : 'eb_license_system_salt_key_2023';
                
                $data = [
                    'license_key' => $this->licenseKey,
                    'domain' => $this->domain,
                    'status' => 'active',
                    'hash' => md5($this->licenseKey . $this->domain . $salt),
                    'last_check' => time(),
                    'validation_type' => isset($responseData['validation_type']) ? $responseData['validation_type'] : 'automatic',
                    'expires' => isset($responseData['expires']) ? strtotime($responseData['expires']) : (time() + 365 * 86400)
                ];
                
                $content = "<?php\nreturn " . var_export($data, true) . ";\n";
                file_put_contents($this->verificationFile, $content);
                
                if ($this->debugMode) {
                    $this->log("License validated successfully: " . ($responseData['validation_type'] ?? 'automatic'));
                }
                
                return true;
            } else {
                // License is invalid according to server
                if ($this->debugMode) {
                    $this->log("License validation failed: " . ($responseData['message'] ?? 'Unknown error'));
                }
                
                // If there's an existing verification file, mark it as invalid
                if (file_exists($this->verificationFile)) {
                    $data = include $this->verificationFile;
                    $data['status'] = 'inactive';
                    $data['last_check'] = time();
                    
                    $content = "<?php\nreturn " . var_export($data, true) . ";\n";
                    file_put_contents($this->verificationFile, $content);
                }
                
                throw new Exception($responseData['message'] ?? "License validation failed");
            }
        } catch (Exception $e) {
            if ($this->debugMode) {
                $this->log("Exception during server check: " . $e->getMessage());
            }
            
            // Check if we're in grace period
            if (file_exists($this->verificationFile)) {
                $data = include $this->verificationFile;
                
                if (isset($data['last_check']) && (time() - $data['last_check'] < LICENSE_GRACE_PERIOD)) {
                    if ($data['status'] === 'active') {
                        if ($this->debugMode) {
                            $this->log("Server check failed but using grace period");
                        }
                        return true;
                    }
                }
            }
            
            throw $e;
        }
    }
    
    /**
     * Activate the license for this domain
     * 
     * @return bool True if activation was successful
     * @throws Exception If activation fails
     */
    public function activateLicense() {
        if (empty($this->licenseKey)) {
            throw new Exception("License key is missing");
        }
        
        if (empty($this->domain)) {
            throw new Exception("Domain name could not be determined");
        }
        
        $postData = [
            'action' => 'activate',
            'license_key' => $this->licenseKey,
            'domain' => $this->domain,
            'ip' => $this->ip,
            'api_key' => API_KEY,
            'product' => $this->productName
        ];
        
        if ($this->debugMode) {
            $this->log("Activating license with server: " . API_URL);
            $this->log("Post data: " . json_encode($postData));
        }
        
        // Use curl if available
        if (function_exists('curl_version')) {
            $ch = curl_init(API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Allow HTTP and HTTPS
            
            if ($this->debugMode) {
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                $verbose = fopen('php://temp', 'w+');
                curl_setopt($ch, CURLOPT_STDERR, $verbose);
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($this->debugMode) {
                $this->log("HTTP Code: " . $httpCode);
                if (!empty($error)) {
                    $this->log("cURL error: " . $error);
                }
                
                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);
                $this->log("cURL log: " . $verboseLog);
                
                $this->log("Response: " . $response);
            }
            
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception("API returned HTTP code $httpCode");
            }
        } else {
            // Fallback to file_get_contents
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($postData),
                    'timeout' => 30
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $response = file_get_contents(API_URL, false, $context);
            
            if ($this->debugMode) {
                $this->log("Using file_get_contents fallback");
                $this->log("Response: " . $response);
            }
            
            if ($response === false) {
                throw new Exception("Failed to connect to license server");
            }
        }
        
        // Parse response
        $responseData = json_decode($response, true);
        
        if (!$responseData || !isset($responseData['status'])) {
            throw new Exception("Invalid response from license server");
        }
        
        if ($responseData['status'] === 'success') {
            // Create verification file
            $salt = defined('LICENSE_SALT') ? LICENSE_SALT : 'eb_license_system_salt_key_2023';
            
            $data = [
                'license_key' => $this->licenseKey,
                'domain' => $this->domain,
                'status' => 'active',
                'hash' => md5($this->licenseKey . $this->domain . $salt),
                'last_check' => time(),
                'validation_type' => isset($responseData['validation_type']) ? $responseData['validation_type'] : 'automatic',
                'expires' => isset($responseData['expires']) ? strtotime($responseData['expires']) : (time() + 365 * 86400)
            ];
            
            $content = "<?php\nreturn " . var_export($data, true) . ";\n";
            file_put_contents($this->verificationFile, $content);
            
            if ($this->debugMode) {
                $this->log("License activated successfully");
            }
            
            // Create license_check.php for additional protection
            $this->createProtectionFile();
            
            return true;
        } else {
            if ($this->debugMode) {
                $this->log("License activation failed: " . ($responseData['message'] ?? 'Unknown error'));
            }
            
            throw new Exception($responseData['message'] ?? "License activation failed");
        }
    }
    
    /**
     * Deactivate the license for this domain
     * 
     * @return bool True if deactivation was successful
     */
    public function deactivateLicense() {
        if (empty($this->licenseKey)) {
            throw new Exception("License key is missing");
        }
        
        if (empty($this->domain)) {
            throw new Exception("Domain name could not be determined");
        }
        
        $postData = [
            'action' => 'deactivate',
            'license_key' => $this->licenseKey,
            'domain' => $this->domain,
            'ip' => $this->ip,
            'api_key' => API_KEY,
            'product' => $this->productName
        ];
        
        if ($this->debugMode) {
            $this->log("Deactivating license with server: " . API_URL);
            $this->log("Post data: " . json_encode($postData));
        }
        
        // Try to contact the server, but don't fail if server is unreachable
        try {
            // Use curl if available
            if (function_exists('curl_version')) {
                $ch = curl_init(API_URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Short timeout
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                curl_close($ch);
            } else {
                // Fallback to file_get_contents
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($postData),
                        'timeout' => 10
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]);
                
                $response = @file_get_contents(API_URL, false, $context);
            }
            
            if ($this->debugMode && $response !== false) {
                $this->log("Deactivation response: " . $response);
            }
        } catch (Exception $e) {
            if ($this->debugMode) {
                $this->log("Deactivation server request failed: " . $e->getMessage());
            }
        }
        
        // Always delete the verification file locally
        if (file_exists($this->verificationFile)) {
            @unlink($this->verificationFile);
        }
        
        // Remove protection file if it exists
        $protectionFile = __DIR__ . '/license_check.php';
        if (file_exists($protectionFile)) {
            @unlink($protectionFile);
        }
        
        return true;
    }
    
    /**
     * Create a protection file with obfuscated check
     */
    private function createProtectionFile() {
        $protectionFile = __DIR__ . '/license_check.php';
        $obfuscatedCode = $this->getObfuscatedCode();
        file_put_contents($protectionFile, $obfuscatedCode);
        
        return true;
    }
    
    /**
     * Get obfuscated code for protection
     */
    private function getObfuscatedCode() {
        $code = <<<'EOD'
<?php
// License protection file - DO NOT MODIFY
if(!defined('EB_SCRIPT_RUNNING')) { die('Access denied'); }

function eb_verify_license() {
    $v_file = __DIR__ . '/verification.php';
    if(!file_exists($v_file)) {
        die('License verification failed. Please reinstall the script.');
    }
    
    $data = include $v_file;
    if(!isset($data['license_key']) || !isset($data['domain']) || !isset($data['hash']) || !isset($data['status'])) {
        die('License data corrupted. Please reinstall the script.');
    }
    
    // Check if license is marked as inactive
    if($data['status'] !== 'active') {
        die('Your license has been deactivated. Please contact support.');
    }
    
    // Check domain
    $domain = $_SERVER['HTTP_HOST'];
    // Remove www. prefix if present
    $domain = preg_replace('/^www\./i', '', $domain);
    // Remove port number if present
    $domain = preg_replace('/:\d+$/', '', $domain);
    $domain = strtolower(trim($domain));
    
    if($data['domain'] !== '*' && $data['domain'] !== $domain) {
        die('Domain mismatch. This license is not valid for this domain.');
    }
    
    // Verify hash
    $salt = defined('LICENSE_SALT') ? LICENSE_SALT : 'eb_license_system_salt_key_2023';
    $expected_hash = md5($data['license_key'] . $data['domain'] . $salt);
    if($expected_hash !== $data['hash']) {
        die('License verification failed. Hash mismatch.');
    }
    
    // Check with server periodically
    $lastCheck = isset($data['last_check']) ? $data['last_check'] : 0;
    $currentTime = time();
    $checkInterval = defined('LICENSE_CHECK_INTERVAL') ? LICENSE_CHECK_INTERVAL : 86400; // 24 hours
    
    if($currentTime - $lastCheck > $checkInterval) {
        $apiUrl = 'https://eb-admin.rf.gd/api.php';
        $licenseKey = $data['license_key'];
        
        $postData = [
            'action' => 'verify',
            'license_key' => $licenseKey,
            'domain' => $domain,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'api_key' => 'd7x9HgT2pL5vZwK8qY3rS6mN4jF1aE0b',
            'product' => 'default'
        ];
        
        try {
            if (function_exists('curl_version')) {
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                curl_close($ch);
            } else {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($postData),
                        'timeout' => 10
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]);
                
                $response = @file_get_contents($apiUrl, false, $context);
            }
            
            if ($response !== false) {
                $result = json_decode($response, true);
                
                if(isset($result['status']) && $result['status'] === 'success') {
                    // Update last check time and keep status active
                    $data['last_check'] = $currentTime;
                    $content = "<?php\nreturn " . var_export($data, true) . ";\n";
                    file_put_contents($v_file, $content);
                } else {
                    // Server says license is invalid
                    $gracePeriod = defined('LICENSE_GRACE_PERIOD') ? LICENSE_GRACE_PERIOD : 604800; // 7 days
                    if ($currentTime - $lastCheck > $gracePeriod) {
                        // Mark license as inactive
                        $data['status'] = 'inactive';
                        $data['last_check'] = $currentTime;
                        $content = "<?php\nreturn " . var_export($data, true) . ";\n";
                        file_put_contents($v_file, $content);
                        
                        die('Your license is no longer valid. Please contact support.');
                    }
                }
            } else {
                // Server unreachable, grace period check
                $gracePeriod = defined('LICENSE_GRACE_PERIOD') ? LICENSE_GRACE_PERIOD : 604800; // 7 days
                if ($currentTime - $lastCheck > $gracePeriod) {
                    die('License verification failed: Unable to contact license server for an extended period.');
                }
            }
        } catch (Exception $e) {
            // Error connecting to server, grace period check
            $gracePeriod = defined('LICENSE_GRACE_PERIOD') ? LICENSE_GRACE_PERIOD : 604800; // 7 days
            if ($currentTime - $lastCheck > $gracePeriod) {
                die('License verification failed: Server connection error for an extended period.');
            }
        }
    }
    
    return true;
}

// Execute the verification
eb_verify_license();
EOD;
        
        return $code;
    }
    
    /**
     * Log debug messages
     */
    private function log($message) {
        if (!$this->debugMode) return;
        
        $logFile = __DIR__ . '/license_debug.log';
        $timestamp = date('[Y-m-d H:i:s]');
        file_put_contents($logFile, $timestamp . ' ' . $message . PHP_EOL, FILE_APPEND);
    }
}

/**
 * Easy-to-use verification function for inclusion in PHP scripts
 */
function verifyScriptLicense() {
    try {
        $verifier = new LicenseVerifier();
        return $verifier->verifyLicense();
    } catch (Exception $e) {
        die('License Error: ' . $e->getMessage());
    }
}
?>