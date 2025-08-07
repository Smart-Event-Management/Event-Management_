<?php
// Set CORS headers to allow requests from your React application
header("Access-Control-Allow-Origin: *"); // Allows all origins, for development only. In production, specify your React app's domain.
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request (important for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection parameters
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password (empty)
$dbname = "alumnidb"; // The database you created

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get the raw POST data
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Validate input data
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
    exit();
}

// Extract and sanitize data
$username = filter_var($data['username'] ?? '', FILTER_SANITIZE_STRING);
$rollNo = filter_var($data['rollNo'] ?? '', FILTER_SANITIZE_STRING); // Only for students
$department = filter_var($data['department'] ?? '', FILTER_SANITIZE_STRING);
$yearOfGraduation = filter_var($data['yearOfGraduation'] ?? '', FILTER_SANITIZE_NUMBER_INT);
$role = filter_var($data['role'] ?? '', FILTER_SANITIZE_STRING);
$password = $data['password'] ?? ''; // Password will be hashed, no direct sanitization needed here

// Basic validation (more robust validation should be done on the client-side as well)
if (empty($username) || empty($department) || empty($yearOfGraduation) || empty($role) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

if ($role === 'student' && empty($rollNo)) {
    echo json_encode(["status" => "error", "message" => "Roll number is required for students."]);
    exit();
}

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = null; // Initialize statement variable

try {
    // Determine which table to insert into based on the role
    switch ($role) {
        case 'student':
            // Check if roll number already exists for students
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE roll_no = ?");
            $check_stmt->bind_param("s", $rollNo);
            $check_stmt->execute();
            $check_stmt->store_result();
            if ($check_stmt->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "Roll number already registered."]);
                $check_stmt->close();
                $conn->close();
                exit();
            }
            $check_stmt->close();

            $sql = "INSERT INTO students (username, roll_no, department, year_of_graduation, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssis", $username, $rollNo, $department, $yearOfGraduation, $hashed_password);
            break;

        case 'admin':
            $sql = "INSERT INTO admins (username, department, year_of_graduation, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ssis", $username, $department, $yearOfGraduation, $hashed_password);
            break;

        case 'organizer':
            $sql = "INSERT INTO organizers (username, department, year_of_graduation, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ssis", $username, $department, $yearOfGraduation, $hashed_password);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid role specified."]);
            exit();
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account created successfully!"]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
} finally {
    // Close statement and connection
    if ($stmt) {
        $stmt->close();
    }
    $conn->close();
}
?>
