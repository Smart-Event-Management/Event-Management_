<?php
/**
 * Event Organizer Dashboard - Main API Endpoints
 * Handles CRUD operations for events
 */

// --- UNIVERSAL PATH DEFINITION ---
// Keeping your original path as requested.
define('REACT_POSTERS_DIR', $_SERVER['DOCUMENT_ROOT'] . '/posters');
// --- END UNIVERSAL PATH ---


require_once 'config.php'; // Assuming this file sets up CORS headers and utility functions

// Define utility function for reliable input retrieval (Unchanged, but necessary for context)
function getReliableInput() {
    $method = $_SERVER['REQUEST_METHOD'];
    $data = [];

    if ($method === 'POST' || $method === 'PUT') {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($content_type, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } elseif (strpos(strtolower($content_type), 'multipart/form-data') !== false || !empty($_POST)) {
            $data = $_POST;
        } else {
            parse_str(file_get_contents('php://input'), $data);
        }
    }
    
    // Check for JSON decoding errors only if raw input was attempted
    if ($method === 'POST' || $method === 'PUT') {
        if (json_last_error() !== JSON_ERROR_NONE && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
             // Use the sendError function defined in config.php
             sendError('Invalid JSON data', 400, json_last_error_msg());
        }
    }

    return $data ? sanitizeInput($data) : [];
}


// Get request method and parse URL path
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove script name from path and get path segments
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace(dirname($script_name), '', $path);
$path_segments = array_filter(explode('/', trim($path, '/')));

// Remove events_api.php from segments if present
$path_segments = array_filter($path_segments, function($segment) {
    return $segment !== 'eventdash.php';
});

$path_segments = array_values($path_segments); // Re-index array

try {
    // FIX 1: Extract ID from query string or path segment immediately for routing reliability
    $event_id = null;
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $event_id = $_GET['id'];
    } else if (!empty($path_segments) && is_numeric($path_segments[0])) {
        $event_id = $path_segments[0];
    }

    // Route requests based on HTTP method
    switch ($method) {
        case 'GET':
            if ($event_id !== null) {
                getEvent($event_id);
            } else {
                getAllEvents();
            }
            break;
            
        case 'POST':
            // FIX 2: Use the reliably extracted $event_id to route POST requests
            if ($event_id !== null) {
                updateEvent($event_id); 
            } else {
                createEvent();
            }
            break;
            
        case 'PUT':
            if ($event_id !== null) {
                updateEvent($event_id);
            } else {
                sendError('Event ID required for update', 400);
            }
            break;
            
        case 'DELETE':
            if ($event_id !== null) {
                deleteEvent($event_id);
            } else {
                sendError('Event ID required for deletion', 400);
            }
            break;
            
        default:
            sendError('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    logError('Unexpected error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    sendError('Internal server error', 500, 'An unexpected error occurred');
}

// --- CORE FUNCTIONALITY ---

/**
 * Get all events from database
 */
function getAllEvents() {
    global $pdo;
    
    try {
        $sql = "SELECT 
                    id, Event_name as event_name, Time as time, Date as date, Venue as venue, 
                    Department as department, Poster_name as poster_name, Event_links as event_links, 
                    First_prizes as first_prizes, Second_prizes as second_prizes, 
                    Third_prizes as third_prizes, created_at
                FROM event 
                ORDER BY Date DESC, Time DESC";
        
        $stmt = $pdo->query($sql);
        $events = $stmt->fetchAll();
        
        // Convert numeric strings to integers for prize fields
        foreach ($events as &$event) {
            $event['first_prizes'] = (int)$event['first_prizes'];
            $event['second_prizes'] = (int)$event['second_prizes'];
            $event['third_prizes'] = (int)$event['third_prizes'];
            $event['id'] = (int)$event['id'];
        }
        
        sendSuccess($events, count($events) . ' events retrieved successfully');
        
    } catch (PDOException $e) {
        logError('Database error in getAllEvents', ['error' => $e->getMessage()]);
        sendError('Failed to retrieve events', 500, 'Database query failed');
    }
}

/**
 * Get specific event by ID
 */
function getEvent($id) {
    global $pdo;
    
    try {
        $sql = "SELECT 
                    id, Event_name as event_name, Time as time, Date as date, Venue as venue, 
                    Department as department, Poster_name as poster_name, Event_links as event_links, 
                    First_prizes as first_prizes, Second_prizes as second_prizes, 
                    Third_prizes as third_prizes, created_at
                FROM event
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        if ($event) {
            // Convert numeric strings to integers
            $event['first_prizes'] = (int)$event['first_prizes'];
            $event['second_prizes'] = (int)$event['second_prizes'];
            $event['third_prizes'] = (int)$event['third_prizes'];
            $event['id'] = (int)$event['id'];
            
            // To satisfy the React component mapping when editing
            $event_for_frontend = [
                'id' => $event['id'],
                'eventName' => $event['event_name'],
                'department' => $event['department'],
                'date' => $event['date'],
                'time' => $event['time'],
                'venue' => $event['venue'],
                'poster' => $event['poster_name'],
                'link' => $event['event_links'],
                'First_prizes' => $event['first_prizes'],
                'Second_prizes' => $event['second_prizes'],
                'Third_prizes' => $event['third_prizes'],
                'event_name' => $event['event_name'],
                'event_links' => $event['event_links'],
                'first_prizes' => $event['first_prizes'],
                'second_prizes' => $event['second_prizes'],
                'third_prizes' => $event['third_prizes'],
                'poster_name' => $event['poster_name']
            ];

            sendSuccess($event_for_frontend, 'Event retrieved successfully');
        } else {
            sendError('Event not found', 404);
        }
        
    } catch (PDOException $e) {
        logError('Database error in getEvent', ['id' => $id, 'error' => $e->getMessage()]);
        sendError('Failed to retrieve event', 500, 'Database query failed');
    }
}

/**
 * Create new event (POST request without ID)
 */
function createEvent() {
    global $pdo;
    
    $input = getReliableInput(); 
    $poster_name = null;
    $poster_file = $_FILES['poster'] ?? null;
    $upload_dir = REACT_POSTERS_DIR; 
    $destination_path = null; 

    if ($poster_file && $poster_file['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $poster_file['tmp_name'];
        $file_original_name = basename($poster_file['name']);
        $file_extension = pathinfo($file_original_name, PATHINFO_EXTENSION);
        
        $event_name_safe = $input['event_name'] ?? 'event'; 
        $unique_filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", $event_name_safe) . '_' . time() . '.' . $file_extension;
        $destination_path = $upload_dir . '/' . $unique_filename;
        
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                sendError('Server Setup Error', 500, 'Upload directory could not be created.');
            }
        }
        
        if (!is_writable($upload_dir)) {
            sendError('Permission Error', 500, 'The poster folder is not writable.');
        }

        if (move_uploaded_file($file_tmp_name, $destination_path)) {
            $poster_name = $unique_filename;
        } else {
            sendError('Failed to upload poster file.', 500);
        }
    } else {
        $upload_error_msg = 'Poster file is required for the event.';
        if ($poster_file && $poster_file['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_error_msg = 'Upload failed with error code: ' . $poster_file['error'];
        }
        sendError($upload_error_msg, 400);
    }

    $input['poster_name'] = $poster_name; 

    // Validation remains the same...
    
    try {
        $sql = "INSERT INTO event (
                    Event_name, Time, Date, Venue, Department, Poster_name, 
                    Event_links, First_prizes, Second_prizes, Third_prizes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $input['event_name'], $input['time'], $input['date'], $input['venue'], 
            $input['department'], $input['poster_name'], $input['event_links'] ?? '',
            (int)($input['first_prizes'] ?? 0), (int)($input['second_prizes'] ?? 0),
            (int)($input['third_prizes'] ?? 0)
        ]);
        
        if ($result) {
            $event_id = $pdo->lastInsertId();
            getEvent($event_id);
        } else {
            sendError('Failed to create event', 500, 'Database insert failed');
        }
        
    } catch (PDOException $e) {
        // Error handling remains the same...
    }
}

/**
 * Update existing event (Used by POST with ID or explicit PUT)
 * --- THIS FUNCTION HAS BEEN REPLACED ---
 */
function updateEvent($id) {
    global $pdo;

    // --- 1. Fetch existing event to get the current poster name ---
    $stmt = $pdo->prepare("SELECT Poster_name FROM event WHERE id = ?");
    $stmt->execute([$id]);
    $existing_event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing_event) {
        sendError('Event not found', 404);
    }
    
    // Initialize the poster name with the current one from the database. This is our default.
    $poster_name_to_save = $existing_event['Poster_name'];
    $upload_dir = REACT_POSTERS_DIR;

    // --- 2. Check if a NEW file was actually uploaded ---
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        // A new file was provided, so we process it.
        $file_tmp_name = $_FILES['poster']['tmp_name'];
        $file_original_name = basename($_FILES['poster']['name']);
        $file_extension = pathinfo($file_original_name, PATHINFO_EXTENSION);
        
        // Sanitize and create a unique filename for the new file
        $event_name_safe = $_POST['event_name'] ?? 'event';
        $unique_filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", $event_name_safe) . '_update_' . time() . '.' . $file_extension;
        $destination_path = $upload_dir . '/' . $unique_filename;

        // Move the new file to the destination
        if (move_uploaded_file($file_tmp_name, $destination_path)) {
            // If move is successful, set the new name to be saved in the DB
            $poster_name_to_save = $unique_filename;

            // And delete the OLD physical file from the server
            $old_poster_path = $upload_dir . '/' . $existing_event['Poster_name'];
            if ($existing_event['Poster_name'] && file_exists($old_poster_path)) {
                unlink($old_poster_path);
            }
        } else {
            sendError('Failed to upload new poster file.', 500);
        }
    } 
    // --- If no new file was uploaded, we use the name sent from the form, which is the old name ---
    elseif (isset($_POST['poster_name'])) {
        $poster_name_to_save = $_POST['poster_name'];
    }
    
    // --- 3. Get the rest of the text data from $_POST ---
    $input = sanitizeInput($_POST);
    
    // --- 4. Update the database with the determined data ---
    try {
        $sql = "UPDATE event SET 
                    Event_name = ?, Department = ?, Date = ?, Time = ?, Venue = ?, 
                    Poster_name = ?, Event_links = ?, First_prizes = ?, 
                    Second_prizes = ?, Third_prizes = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['event_name'] ?? '',
            $input['department'] ?? '',
            $input['date'] ?? '',
            $input['time'] ?? '',
            $input['venue'] ?? '',
            $poster_name_to_save, // Use the determined poster name
            $input['event_links'] ?? null,
            (int)($input['first_prizes'] ?? 0),
            (int)($input['second_prizes'] ?? 0),
            (int)($input['third_prizes'] ?? 0),
            $id
        ]);

        getEvent($id); // Send back the updated event data

    } catch (PDOException $e) {
        logError('Database error in updateEvent', ['id' => $id, 'error' => $e->getMessage()]);
        sendError('Failed to update event', 500, 'Database error occurred');
    }
}


/**
 * Delete event
 */
function deleteEvent($id) {
    global $pdo;
    
    try {
        // Check if event exists first
        $check_sql = "SELECT Event_name, Poster_name FROM event WHERE id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id]);
        $event = $check_stmt->fetch();
        
        if (!$event) {
            sendError('Event not found', 404);
        }
        
        // Delete the event from the database
        $delete_sql = "DELETE FROM event WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $result = $delete_stmt->execute([$id]);
        
        if ($result && $delete_stmt->rowCount() > 0) {
            // Optional: Delete the physical poster file
            $poster_file_path = REACT_POSTERS_DIR . '/' . $event['Poster_name'];
            if ($event['Poster_name'] && file_exists($poster_file_path)) {
                unlink($poster_file_path);
            }
            
            sendSuccess([
                'id' => (int)$id,
                'deleted_event' => $event['Event_name']
            ], 'Event deleted successfully');
        } else {
            sendError('Failed to delete event', 500, 'Database delete failed');
        }
        
    } catch (PDOException $e) {
        logError('Database error in deleteEvent', ['id' => $id, 'error' => $e->getMessage()]);
        sendError('Failed to delete event', 500, 'Database error occurred');
    }
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate time format (HH:MM)
 */
function validateTime($time) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
}
?>