<?php
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
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
    public function getAllEvents() {
        $query = "SELECT id, Event_name, Time, Date, Venue, Department, poster 
                  FROM " . $this->table_name . " 
                  WHERE poster IS NOT NULL AND poster != ''
                  ORDER BY Date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $events = array();
        $events["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $event_item = array(
                "id" => $row['id'],
                "event_name" => $row['Event_name'],
                "time" => $row['Time'],
                "date" => $row['Date'],
                "venue" => $row['Venue'],
                "department" => $row['Department'],
                "poster" => $row['poster']
            );
            array_push($events["records"], $event_item);
        }
        return $events;
    }
    public function getEventById($id) {
        $query = "SELECT id, Event_name, Time, Date, Venue, Department, poster 
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $event_item = array(
                "id" => $row['id'],
                "event_name" => $row['Event_name'],
                "time" => $row['Time'],
                "date" => $row['Date'],
                "venue" => $row['Venue'],
                "department" => $row['Department'],
                "poster" => $row['poster']
            );
            return $event_item;
        }
        return null;
    }
}
//Initialize database connection
$database = new Database();
$db = $database->getConnection();

if($db != null) {
    $events = new EventsAPI($db);
    
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    if($method == 'GET') {
        // Check if specific event ID is requested
        if(isset($_GET['id'])) {
            $event = $events->getEventById($_GET['id']);
            if($event) {
                http_response_code(200);
                echo json_encode($event);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Event not found."));
            }
        } else {
            // Return all events
            $result = $events->getAllEvents();
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