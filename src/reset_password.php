<?php
/**
 * Password Reset Endpoint
 * Updates user password with proper hashing after username verification
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ]);
    exit();
}

// Include database configuration
require_once 'database_config.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!$input || !isset($input['username']) || !isset($input['role']) || !isset($input['newPassword'])) {
        throw new Exception('Username, role, and new password are required');
    }
    
    $username = trim($input['username']);
    $role = trim($input['role']);
    $newPassword = $input['newPassword'];
    
    // Validate input data
    if (empty($username)) {
        throw new Exception('Username cannot be empty');
    }
    
    if (empty($role)) {
        throw new Exception('Role must be specified');
    }
    
    if (empty($newPassword)) {
        throw new Exception('New password cannot be empty');
    }
    
    // Password strength validation
    if (strlen($newPassword) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    // Optional: Add more password complexity requirements
    if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)/', $newPassword)) {
        throw new Exception('Password must contain at least one letter and one number');
    }
    
    // Validate role
    if (!isValidRole($role)) {
        throw new Exception('Invalid role specified');
    }
    
    // Get appropriate table name
    $tableName = getRoleTable($role);
    
    // First, verify that the username exists (security check)
    $checkStmt = $conn->prepare("SELECT id, username FROM `$tableName` WHERE username = ? LIMIT 1");
    
    if (!$checkStmt) {
        throw new Exception('Database query preparation failed');
    }
    
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        throw new Exception('Username not found for the specified role');
    }
    
    $user = $checkResult->fetch_assoc();
    $userId = $user['id'];
    $checkStmt->close();
    
    // Hash the new password securely
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    if (!$hashedPassword) {
        throw new Exception('Password hashing failed');
    }
    
    // Update the password in the database
    $updateStmt = $conn->prepare("UPDATE `$tableName` SET password = ?, updated_at = NOW() WHERE id = ? AND username = ?");
    
    if (!$updateStmt) {
        throw new Exception('Password update query preparation failed');
    }
    
    $updateStmt->bind_param("sis", $hashedPassword, $userId, $username);
    
    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            // Log successful password reset
            error_log("Password reset successful for user: $username, role: $role, IP: " . $_SERVER['REMOTE_ADDR']);
            
            // Optional: Log to a separate security audit table
            logPasswordReset($conn, $userId, $tableName, $_SERVER['REMOTE_ADDR']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password updated successfully! You can now login with your new password.'
            ]);
        } else {
            throw new Exception('Password update failed - no changes made');
        }
    } else {
        throw new Exception('Database update failed: ' . $updateStmt->error);
    }
    
    $updateStmt->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Password reset error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    closeDatabaseConnection();
}

/**
 * Function to log password reset attempts for security audit
 * @param mysqli $conn - Database connection
 * @param int $userId - User ID
 * @param string $tableName - Table name where user exists
 * @param string $ipAddress - IP address of the request
 */
function logPasswordReset($conn, $userId, $tableName, $ipAddress) {
    try {
        // Create audit log entry (optional - create this table if you want audit trail)
        $auditStmt = $conn->prepare("INSERT INTO password_reset_logs (user_id, table_name, ip_address, reset_time) VALUES (?, ?, ?, NOW())");
        
        if ($auditStmt) {
            $auditStmt->bind_param("iss", $userId, $tableName, $ipAddress);
            $auditStmt->execute();
            $auditStmt->close();
        }
    } catch (Exception $e) {
        // Don't fail the main operation if audit logging fails
        error_log("Audit logging failed: " . $e->getMessage());
    }
}
?>