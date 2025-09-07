<?php
/**
 * Handles fetching event details by clicking on a poster/button
 */

// Enable CORS for React frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
class DatabaseConfig {
    private $host = "localhost";        
    private $username = "root"; 
    private $password = "";                
    private $database = "alumnidb";      
    private $connection;

    public function connect() {
        try {
            $this->connection = new mysqli(
                $this->host, 
                $this->username, 
                $this->password, 
                $this->database
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8");
            return $this->connection;
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ]);
            exit();
        }
    }
    public function getConnection() {
        return $this->connection;
    }
}

// Event API class
class EventAPI {
    private $db;
    
    public function __construct() {
        $database = new DatabaseConfig();
        $this->db = $database->connect();
    }
     /**
     * Get event details by ID
     */
    public function getEventById($eventId) {
        try {
            // Validate input
            if (empty($eventId)) {
                return [
                    'success' => false,
                    'message' => 'Invalid event ID provided'
                ];
            }
            
            // Prepare SQL query to prevent SQL injection
            $query = "SELECT id, Event_name, Time, Date, Venue, Department,Poster_name,Event_links 
                     FROM event_registration 
                     WHERE id = ? 
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $eventId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $event = $result->fetch_assoc();
                
                // Format the response for React frontend
                return [
                    'success' => true,
                    'message' => 'Event found successfully',
                    'data' => [
                        'id' => $event['id'],
                        'eventName' => $event['Event_name'],
                        'time' => $event['Time'],
                        'date' => $event['Date'],
                        'venue' => $event['Venue'],
                        'department' => $event['Department'],
                        'poster' => $event['Poster_name'],
                        'link' => $event['Event_links']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Event not found',
                    'data' => null
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching event details',
                'error' => $e->getMessage()
            ];
        }
    }
}
// Main API router
function handleRequest() {
    $method = $_SERVER['REQUEST_METHOD'];
    $eventAPI = new EventAPI();
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get specific event by ID
                $eventId = $_GET['id'];
                error_log("DEBUG: Received ID -> " . $eventId);
                $response = $eventAPI->getEventById($eventId);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Event ID is required. Use ?id=EVENT_ID'
                ];
            }
            break;
            
        case 'POST':
            // Handle POST request with JSON body
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['id'])) {
                $eventId = (int)$input['id'];
                $response = $eventAPI->getEventById($eventId);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Event ID is required in request body'
                ];
            }
            break;
            
        default:
            http_response_code(405);
            $response = [
                'success' => false,
                'message' => 'Method not allowed. Use GET or POST'
            ];
            break;
    }
    // Set appropriate HTTP response code
    if (!$response['success']) {
        if (strpos($response['message'], 'not found') !== false) {
            http_response_code(404);
        } elseif (strpos($response['message'], 'Invalid') !== false || strpos($response['message'], 'required') !== false) {
            http_response_code(400);
        } else {
            http_response_code(500);
        }
    }
    
    // Return JSON response
    echo json_encode($response, JSON_PRETTY_PRINT);
}

// Execute the API
handleRequest();
?>