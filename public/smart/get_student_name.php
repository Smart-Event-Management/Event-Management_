<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

    public function getStudentName($rollNo) { // Changed parameter name to rollNo
        if (empty($rollNo)) {
            return [
                'success' => false,
                'message' => 'Roll number not provided'
            ];
        }

        $query = "SELECT username FROM students WHERE roll_no = :roll_no LIMIT 1"; // Changed :student_id to :roll_no
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':roll_no', $rollNo); // Bind the roll_no parameter
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'success' => true,
                'name' => $row['username']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Student not found.'
            ];
        }
    }
}

function handleRequest() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    $database = new Database();
    $db = $database->getConnection();

    if ($db == null) {
        return; 
    }
    
    $studentAPI = new StudentAPI($db);
    
    // Get the roll_no from the URL
    $rollNo = isset($_GET['roll_no']) ? $_GET['roll_no'] : null;

    $result = $studentAPI->getStudentName($rollNo);
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
}

handleRequest();
?>