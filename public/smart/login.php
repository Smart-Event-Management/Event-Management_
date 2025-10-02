<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

// Handle preflight OPTIONS request (important for CORS)
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
    // Return a generic error message for security
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Get JSON data from React
$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? ''; // This is the roll_no for students
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

// --- CRITICAL CHANGE: Select the actual name column ('username' in students table) ---
// We select the ID, the encrypted password, and the username (which is the student's name).
$stmt = $conn->prepare("SELECT id, username, password FROM $table WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Verify password
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Use password_verify if passwords are hashed (recommended)
    // NOTE: If passwords are NOT hashed, use: $password === $row['password']
    if (password_verify($password, $row['password'])) {
        
        // --- CRITICAL CHANGE: Return the username field as 'name' and the roll_no field as 'rollNo' ---
        echo json_encode([
            "success" => true,
            "message" => "$role login successful",
            "rollNo" => $row['username'], // Roll number is the 'username' field in the database
            "name" => $row['username'],   // The student's name is also the 'username' field
            "user" => [
                "id" => $row['id'],
                "rollNo" => $row['username'],
                "name" => $row['username'], 
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