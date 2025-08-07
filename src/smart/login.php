<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "alumnidb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Get JSON data from React
$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? '';

// Validate input
if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit();
}

// Table mapping
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

// Prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, username, password FROM $table WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Verify password
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Use password_verify if passwords are hashed
    if (password_verify($password, $row['password'])) {
        echo json_encode([
            "success" => true,
            "message" => "$role login successful",
            "user" => [
                "id" => $row['id'],
                "username" => $row['username'],
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
