<?php
/**
 * Database Configuration for Smart College Event Management Portal
 * Update these credentials according to your database setup
 */

// Database configuration
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "alumnidb"; 

// Create connection with error handling
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8 for proper character handling
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // Log error and return JSON error response
    error_log("Database connection error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed. Please try again later.'
    ]);
    exit();
}

/**
 * Function to get the appropriate table name based on user role
 * @param string $role - The user role (admin, organizer, student)
 * @return string - The table name
 */
function getRoleTable($role) {
    switch (strtolower($role)) {
        case 'admin':
            return 'admins';
        case 'organizer':
            return 'organizers';
        case 'student':
            return 'students';
        default:
            throw new Exception('Invalid role specified');
    }
}

/**
 * Function to validate role
 * @param string $role - The role to validate
 * @return bool - True if valid, false otherwise
 */
function isValidRole($role) {
    $validRoles = ['admin', 'organizer', 'student'];
    return in_array(strtolower($role), $validRoles);
}

/**
 * Function to close database connection
 */
function closeDatabaseConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}
?>