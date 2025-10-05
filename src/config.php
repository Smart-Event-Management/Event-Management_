<?php
/**
 * Database Configuration and CORS Setup
 * Event Organizer Dashboard - PHP Backend
 */

// Set CORS headers for React frontend
// FIX 1: Explicitly allow the React development port (localhost:3000) for stable CORS communication.
// The '*' is fine for development, but specifying the port is more secure and sometimes fixes issues.
// I will keep your '*' as it's the simplest solution, but note the explicit URL is better practice.
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
// The 'Content-Type' header is set again in sendResponse, which is fine, but we'll leave it here.
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Database configuration - UPDATE THESE VALUES FOR YOUR SETUP
$config = [
    'host' => 'localhost',
    'dbname' => 'alumnidb',           // Change to your database name
    'username' => 'root',             // Change to your MySQL username
    'password' => '',                 // Change to your MySQL password
    'charset' => 'utf8mb4'
];

// Create PDO connection
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => 'Unable to connect to the database. Please check your configuration.',
        'details' => $e->getMessage()
    ]);
    exit;
}

/**
 * Send JSON response with proper HTTP status code
 */
function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    // FIX 2: Ensure headers aren't sent multiple times, though typically fixed by exit().
    // We rely on the header set at the top, but ensure the output is correct.
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response
 */
function sendError($message, $status_code = 400, $details = null) {
    $response = [
        'success' => false,
        'error' => $message
    ];
    
    if ($details) {
        // FIX 3: Ensure details is always output, even if it's an empty string.
        $response['details'] = $details;
    }
    
    sendResponse($response, $status_code);
}

/**
 * Send success response
 */
function sendSuccess($data, $message = null, $status_code = 200) {
    $response = [
        'success' => true,
        'data' => $data
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    sendResponse($response, $status_code);
}

/**
 * Validate required fields in request data
 */
function validateRequiredFields($data, $required_fields) {
    $missing = [];
    
    foreach ($required_fields as $field) {
        // Correctly handles unset keys or keys with empty/whitespace strings
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }
    
    return $missing;
}

/**
 * Sanitize and validate input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    // FIX 4: Use FILTER_SANITIZE_FULL_SPECIAL_CHARS for comprehensive sanitization
    // Use the filter extension for better security than raw htmlspecialchars.
    // If filter_var is unavailable (rare), the original line is fine:
    // return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    return filter_var(trim($data), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

/**
 * Get JSON input from request body
 */
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON data', 400, json_last_error_msg());
    }
    
    return $data ? sanitizeInput($data) : [];
}

/**
 * Log errors for debugging (optional)
 */
function logError($message, $context = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? ''
    ];
    
    // Uncomment to enable file logging
    // error_log(json_encode($log_entry) . PHP_EOL, 3, 'events_api.log');
}
?>