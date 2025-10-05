<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "alumnidb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? '';

if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit();
}

$tableMap = [
    "Admin" => "admins",
    "Organizer" => "organizers",
    "Student" => "students"
];

if (!array_key_exists($role, $tableMap)) {
    echo json_encode(["success" => false, "message" => "Invalid role selected"]);
    exit();
}

$table = $tableMap[$role];

// --- THIS IS THE FIX ---
// Use one simple query that works for all tables.
$sql = "SELECT id, username, password FROM $table WHERE username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['password'])) {
        
        // The display name will now always be the username.
        $displayName = $row['username'];

        // This response now works for all roles.
        echo json_encode([
            "success" => true,
            "message" => "$role login successful",
            "userData" => [
                "userId" => $row['id'],
                "userName" => $displayName,
                "role" => $role
            ]
        ]);

    } else {
        echo json_encode(["success" => false, "message" => "Incorrect password"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>