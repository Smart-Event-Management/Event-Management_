<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Config
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "alumnidb";

define('IMAGE_BASE_URL', 'http://localhost/events/images/');

$tableName = "event_registration";
$imageColumn = "Poster_name";
$titleColumn = "Event_name";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Validate table existence
if (!$conn->query("SHOW TABLES LIKE '{$tableName}'")->num_rows) {
    http_response_code(500);
    echo json_encode(['error' => "Table '{$tableName}' not found"]);
    exit;
}

// Validate image column
if (!$conn->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$imageColumn}'")->num_rows) {
    http_response_code(500);
    echo json_encode(['error' => "Column '{$imageColumn}' not found"]);
    exit;
}

// Fetch columns
$colsRes = $conn->query("SHOW COLUMNS FROM `{$tableName}`");
$cols = [];
while ($r = $colsRes->fetch_assoc()) $cols[] = $r['Field'];

if (!in_array($titleColumn, $cols)) {
    foreach ($cols as $c) {
        if (stripos($c, 'name') !== false || stripos($c, 'title') !== false) { 
            $titleColumn = $c; 
            break; 
        }
    }
    if (!in_array($titleColumn, $cols)) $titleColumn = $cols[0];
}

$idCol = in_array('id', $cols) ? 'id' : $cols[0];

// ✅ Fetch latest poster per department
$sql = "SELECT e.$idCol AS id, e.$titleColumn AS title, e.$imageColumn AS image, e.Department
        FROM $tableName e
        INNER JOIN (
            SELECT Department, MAX($idCol) AS max_id
            FROM $tableName
            GROUP BY Department
        ) latest 
        ON e.Department = latest.Department AND e.$idCol = latest.max_id
        ORDER BY e.$idCol DESC";

$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $raw = trim((string)$row['image']);
    if ($raw === "") {
        $imageUrl = IMAGE_BASE_URL . 'placeholder.png';
    } elseif (preg_match('#^https?://#i', $raw)) {
        $imageUrl = $raw;
    } else {
        $imageUrl = IMAGE_BASE_URL . basename($raw);
    }
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'department' => $row['department'],
        'image' => $imageUrl
    ];
}

echo json_encode($events, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
$conn->close();
?>
