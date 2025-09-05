<?php
/**
 * Event Poster API - Fixed Version
 * Fetches latest event posters per department
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Configuration
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "alumnidb";

// Image Configuration
define('IMAGE_BASE_URL', 'http://localhost/event/Event_posters/');

// Table Configuration
$tableName = "event_registration";
$imageColumn = "Poster_name";
$titleColumn = "Event_name";
try {
    // Create database connection with charset
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Set charset to prevent encoding issues
    $conn->set_charset("utf8");

    // Validate table existence
    $tableCheck = $conn->prepare("SHOW TABLES LIKE ?");
    $tableCheck->bind_param("s", $tableName);
    $tableCheck->execute();
    $tableResult = $tableCheck->get_result();
    
    if ($tableResult->num_rows == 0) {
        throw new Exception("Table '{$tableName}' not found");
    }
    $tableCheck->close();

    // Get all columns from the table
    $colsQuery = $conn->prepare("SHOW COLUMNS FROM `{$tableName}`");
    $colsQuery->execute();
    $colsResult = $colsQuery->get_result();
    
    $cols = [];
    while ($colRow = $colsResult->fetch_assoc()) {
        $cols[] = $colRow['Field'];
    }
    $colsQuery->close();

    // Validate required columns exist
    if (!in_array($imageColumn, $cols)) {
        throw new Exception("Image column '{$imageColumn}' not found in table");
    }

    // Find title column (use Event_name or find alternative)
    if (!in_array($titleColumn, $cols)) {
        foreach ($cols as $col) {
            if (stripos($col, 'name') !== false || stripos($col, 'title') !== false) { 
                $titleColumn = $col; 
                break; 
            }
        }
        if (!in_array($titleColumn, $cols)) {
            $titleColumn = $cols[1] ?? $cols[0]; // Use second column or first if only one exists
        }
    }
    // Find ID column
    $idCol = 'id';
    if (!in_array('id', $cols)) {
        $idCol = $cols[0]; // Use first column as ID if 'id' doesn't exist
    }

    // Validate Department column exists
    if (!in_array('Department', $cols)) {
        throw new Exception("Department column not found in table");
    }

    // Build and execute the main query
    $sql = "SELECT 
                e.`{$idCol}` AS id, 
                e.`{$titleColumn}` AS title, 
                e.`{$imageColumn}` AS image, 
                e.`Department` AS department
            FROM `{$tableName}` e
            INNER JOIN (
                SELECT `Department`, MAX(`{$idCol}`) AS max_id
                FROM `{$tableName}`
                WHERE `Department` IS NOT NULL AND `Department` != ''
                GROUP BY `Department`
            ) latest 
            ON e.`Department` = latest.`Department` AND e.`{$idCol}` = latest.max_id
            WHERE e.`{$imageColumn}` IS NOT NULL
            ORDER BY e.`{$idCol}` DESC";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception('Query execution failed: ' . $conn->error);
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Handle image URL construction
        $rawImage = trim((string)$row['image']);
        
        if (empty($rawImage)) {
            $imageUrl = IMAGE_BASE_URL . 'placeholder.png';
        } elseif (preg_match('#^https?://#i', $rawImage)) {
            // Already a full URL
            $imageUrl = $rawImage;
        } else {
            // Local image file
            $cleanFileName = basename($rawImage);
            $imageUrl = IMAGE_BASE_URL . $cleanFileName;
        }

        $events[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'] ?? 'Untitled Event',
            'department' => $row['department'] ?? 'Unknown Department',
            'image' => $imageUrl
        ];
    }

    // Return successful response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Events fetched successfully',
        'count' => count($events),
        'data' => $events
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Handle all errors consistently
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ], JSON_PRETTY_PRINT);
} finally {
    // Always close the database connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>