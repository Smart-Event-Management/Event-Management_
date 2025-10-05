<?php
/**
 * Admin Dashboard Summary Endpoint (usercount.php)
 * Fetches metrics for total events, active/total students, and active/total organizers.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Or use "http://localhost:3000"
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alumnidb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Connection failed: ' . $e->getMessage()
    ]);
    exit;
}

try {
    // This single query gets all counts. "Active" is defined as activity in the last 5 minutes.
    $sql = "SELECT
        (SELECT COUNT(*) FROM event_registration) AS total_events,
        
        (SELECT COUNT(*) FROM students) AS total_students,
        (SELECT COUNT(*) FROM students WHERE last_seen > NOW() - INTERVAL 30 MINUTE) AS active_students,
        
        (SELECT COUNT(*) FROM organizers) AS total_organizers,
        (SELECT COUNT(*) FROM organizers WHERE last_seen > NOW() - INTERVAL 30 MINUTE) AS active_organizers,
        
        (SELECT COUNT(*) FROM admins) AS total_admins
    ";
    
    $stmt = $pdo->query($sql);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Compile Response ---
    $totalUsers = (int)$counts['total_students'] + (int)$counts['total_organizers'];
    
    $response = [
        'success' => true,
        'data' => [
            'total_events' => (int)$counts['total_events'],
            
            // Students Metrics
            'total_students' => (int)$counts['total_students'],
            'active_students' => (int)$counts['active_students'],

            // Organizers Metrics
            'total_organizers' => (int)$counts['total_organizers'],
            'active_organizers' => (int)$counts['active_organizers'],
            
            'total_users' => $totalUsers,
            'total_admins' => (int)$counts['total_admins'],
        ]
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'details' => 'Failed to execute query for summary metrics.'
    ]);
}
?>