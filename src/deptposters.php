<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
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
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include_once '../config/database.php';

class EventsAPI {
    private $conn;
    private $table_name = "event_registration";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getEventsByDepartment() {
        // Get all events grouped by department
        $query = "SELECT id, Event_name, Department, Poster_name 
                  FROM " . $this->table_name . " 
                  WHERE Poster_name IS NOT NULL AND Poster_name != ''
                  ORDER BY Department ASC, Event_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $departments = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $department_name = $row['Department'];
            
            // Create department array if it doesn't exist
            if (!isset($departments[$department_name])) {
                $departments[$department_name] = array(
                    "department_name" => $department_name,
                    "events" => array()
                );
            }
            // Add event to department
            $event_item = array(
                "id" => $row['id'],
                "event_name" => $row['Event_name'],
                "poster_name" => $row['Poster_name']
            );
            
            array_push($departments[$department_name]["events"], $event_item);
        }
        
        // Convert associative array to indexed array
        $result = array();
        $result["Departments"] = array_values($departments);
        
        return $result;
    }
    public function getAllDepartments() {
        $query = "SELECT DISTINCT Department 
                  FROM " . $this->table_name . " 
                  WHERE Poster_name IS NOT NULL AND Poster_name != ''
                  ORDER BY Department ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $departments = array();
        $departments["departments"] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($departments["departments"], $row['department']);
        }
        
        return $departments;
    }
}
// Initialize database connection
$database = new Database();
$db = $database->getConnection();

if($db != null) {
    $events = new EventsAPI($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if($method == 'GET') {
        // Check what type of request
        if(isset($_GET['departments_only'])) {
            // Get list of departments only
            $result = $events->getAllDepartments();
            http_response_code(200);
            echo json_encode($result);
        } else {
            // Get all events grouped by department (Instagram-style feed)
            $result = $events->getEventsByDepartment();
            http_response_code(200);
            echo json_encode($result);
        }
    } else {
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
}
?>
