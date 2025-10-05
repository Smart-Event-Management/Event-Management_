<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// --- DEBUGGING START ---
$log_file = 'debug_log.txt';
// Clear the log file for each new request to keep it clean. Remove this line if you want to see history.
file_put_contents($log_file, "--- New Activity Update Request ---\n"); 
// --- DEBUGGING END ---


$data = json_decode(file_get_contents("php://input"));

// Log the raw data received from React
file_put_contents($log_file, "1. Received raw data: " . file_get_contents("php://input") . "\n", FILE_APPEND);

if (!$data || !isset($data->role) || !isset($data->id)) {
    $error_msg = 'Invalid role or ID provided.';
    file_put_contents($log_file, "ERROR: " . $error_msg . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $error_msg]);
    exit;
}

$role = $data->role;
$id = $data->id;
$table_name = '';

file_put_contents($log_file, "2. Decoded Role: '{$role}', ID: '{$id}'\n", FILE_APPEND);

// Determine the correct table based on the user's role
switch (strtolower($role)) { // Use strtolower for safety
    case 'student':
        $table_name = 'students';
        break;
    case 'organizer':
        $table_name = 'organizers';
        break;
    default:
        $error_msg = 'Unknown role.';
        file_put_contents($log_file, "ERROR: " . $error_msg . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => $error_msg]);
        exit;
}

file_put_contents($log_file, "3. Determined table: '{$table_name}'\n", FILE_APPEND);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alumnidb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // NOTE: This assumes the primary key column in your students/organizers table is named 'id'.
    // If it's named 'student_id' or 'organizer_id', you must change it here.
    $sql = "UPDATE {$table_name} SET last_seen = NOW() WHERE id = ?";
    
    file_put_contents($log_file, "4. Preparing SQL: \"{$sql}\"\n", FILE_APPEND);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    file_put_contents($log_file, "5. SUCCESS: Database updated successfully.\n", FILE_APPEND);
    echo json_encode(['success' => true, 'message' => 'Activity updated for ' . $role]);

} catch (PDOException $e) {
    // Log the specific database error
    file_put_contents($log_file, "DATABASE ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>