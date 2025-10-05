<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include 'config.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alumnidb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

try {
    // Get count of students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $studentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get count of organizers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM organizers");
    $organizersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get count of events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM event_registration");
    $eventsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get count of admins
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $adminsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Calculate total users (students + organizers)
    $totalUsers = $studentsCount + $organizersCount;
    
    $response = [
        'success' => true,
        'data' => [
            'students' => $studentsCount,
            'organizers' => $organizersCount,
            'events' => $eventsCount,
            'admins' => $adminsCount,
            'totalUsers' => $totalUsers,
            'totalRegistrations' => $eventsCount // Based on event_registration table
        ]
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>