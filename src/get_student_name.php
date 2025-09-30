<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

class Database {
    private $host = "localhost";
    private $db_name = "alumnidb";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $exception->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}

class StudentAPI {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getStudentName($studentId) {
        if (empty($studentId)) {
            return [
                'success' => false,
                'message' => 'Invalid student ID provided'
            ];
        }

        $query = "SELECT name FROM students WHERE student_id = :student_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'success' => true,
                'name' => $row['student_name']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Student not found.'
            ];
        }
    }
}

// Main logic
$database = new Database();
$db = $database->getConnection();

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($db != null) {
    $studentAPI = new StudentAPI($db);
    $studentId = "S12345"; // In a real app, this would come from a session or query parameter

    $result = $studentAPI->getStudentName($studentId);
    if ($result['success']) {
        http_response_code(200);
        $response = $result;
    } else {
        http_response_code(404);
        $response = $result;
    }
} else {
    http_response_code(500);
    $response = ['success' => false, 'message' => 'Database connection failed.'];
}

echo json_encode($response);
?>