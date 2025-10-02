<?php
/**
 * Event Organizer Dashboard - Main API Endpoints
 * Handles CRUD operations for events
 */

// --- UNIVERSAL PATH DEFINITION ---
// This path is reliable for anyone running XAMPP/MAMP who places the project in a subfolder (e.g., /smart/public/posters)
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
    // Route requests based on HTTP method
    switch ($method) {
        case 'GET':
            if (empty($path_segments)) {
                getAllEvents();
            } else {
                $event_id = $path_segments[0];
                if (is_numeric($event_id)) {
                    getEvent($event_id);
                } else {
                    sendError('Invalid event ID format', 400);
                }
            }
            break;
            
        case 'POST':
            createEvent(); 
            break;
            
        case 'PUT':
            if (!empty($path_segments)) {
                $event_id = $path_segments[0];
                if (is_numeric($event_id)) {
                    updateEvent($event_id);
                } else {
                    sendError('Invalid event ID format', 400);
                }
            } else {
                sendError('Event ID required for update', 400);
            }
            break;
            
        case 'DELETE':
            // --- FIX START: Reliable ID retrieval for DELETE method ---
            $event_id = null;
            
            // 1. Try to get ID from query string (most reliable for DELETE method from fetch API)
            if (isset($_GET['id'])) {
                $event_id = $_GET['id'];
            }
            // 2. Fallback to path segment check (original routing logic)
            else if (!empty($path_segments) && is_numeric($path_segments[0])) {
                 $event_id = $path_segments[0];
            }

            if ($event_id !== null) {
                if (is_numeric($event_id)) {
                    deleteEvent($event_id);
                } else {
                    sendError('Invalid event ID format', 400);
                }
            } else {
                // Return the specific error message the frontend is expecting
                sendError('Event ID required for deletion', 400);
            }
            break;
            // --- FIX END ---
            
        default:
            sendError('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    logError('Unexpected error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    sendError('Internal server error', 500, 'An unexpected error occurred');
}

/**
 * Get all events from database
 */
function getAllEvents() {
    global $pdo;
    
    try {
        $sql = "SELECT 
                        id,
                        Event_name as event_name,
                        Time as time,
                        Date as date,
                        Venue as venue,
                        Department as department,
                        Poster_name as poster_name,
                        Event_links as event_links,
                        First_prizes as first_prizes,
                        Second_prizes as second_prizes,
                        Third_prizes as third_prizes,
                        created_at
                        FROM event_registration 
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
                        id,
                        Event_name as event_name,
                        Time as time,
                        Date as date,
                        Venue as venue,
                        Department as department,
                        Poster_name as poster_name,
                        Event_links as event_links,
                        First_prizes as first_prizes,
                        Second_prizes as second_prizes,
                        Third_prizes as third_prizes,
                        created_at
                    FROM event_registration 
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
            
            sendSuccess($event, 'Event retrieved successfully');
        } else {
            sendError('Event not found', 404);
        }
        
    } catch (PDOException $e) {
        logError('Database error in getEvent', ['id' => $id, 'error' => $e->getMessage()]);
        sendError('Failed to retrieve event', 500, 'Database query failed');
    }
}

/**
 * Create new event (HANDLES FILE UPLOAD)
 */
function createEvent() {
    global $pdo;
    
    // --- 1. Get Text Inputs ---
    $input = getReliableInput(); 
    
    // --- 2. Handle File Upload (Poster) ---
    $poster_name = null;
    $poster_file = $_FILES['poster'] ?? null;
    
    // Using the ABSOLUTE PATH constant defined at the top of the file
    $upload_dir = REACT_POSTERS_DIR; 
    $destination_path = null; // Initialize destination_path for cleanup logic

    // Check if the poster file was successfully uploaded by the client
    if ($poster_file && $poster_file['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $poster_file['tmp_name'];
        $file_original_name = basename($poster_file['name']);
        $file_extension = pathinfo($file_original_name, PATHINFO_EXTENSION);
        
        // Sanitize and create a unique filename (e.g., event_name_timestamp.ext)
        $event_name_safe = $input['event_name'] ?? 'event'; 
        $unique_filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", $event_name_safe) . '_' . time() . '.' . $file_extension;
        $destination_path = $upload_dir . '/' . $unique_filename;
        
        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            // Attempt to create the directory if it doesn't exist
            if (!mkdir($upload_dir, 0777, true)) {
                 sendError('Server Setup Error', 500, 'Upload directory could not be created. Check folder permissions.');
            }
        }
        
        // Check permissions before moving
        if (!is_writable($upload_dir)) {
             sendError('Permission Error', 500, 'The poster folder is not writable (Permission denied).');
        }

        if (move_uploaded_file($file_tmp_name, $destination_path)) {
            $poster_name = $unique_filename;
        } else {
            sendError('Failed to upload poster file.', 500, 'Could not move file to destination. Check upload size limits.');
        }
    } else {
        // Handle case where file is missing or upload failed for a reason other than UPLOAD_ERR_OK
        $upload_error_msg = 'Poster file is required for the event or upload failed.';
        if ($poster_file && $poster_file['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_error_msg = 'Upload failed with error code: ' . $poster_file['error'];
        }
        sendError($upload_error_msg, 400);
    }

    // Ensure the poster name is included in the input for database insertion
    $input['poster_name'] = $poster_name; 

    // Validate required fields
    $required_fields = ['event_name', 'time', 'date', 'venue', 'department', 'poster_name'];
    $missing_fields = validateRequiredFields($input, $required_fields);
    
    if (!empty($missing_fields)) {
        // If validation fails, attempt to delete the uploaded file to clean up
        if ($poster_name && $destination_path && file_exists($destination_path)) {
            unlink($destination_path);
        }
        sendError('Missing required fields', 400, [
            'missing_fields' => $missing_fields,
            'required_fields' => $required_fields
        ]);
    }
    
    // Validate date format
    if (!validateDate($input['date'])) {
        sendError('Invalid date format. Please use YYYY-MM-DD format.', 400);
    }
    
    // Validate time format
    if (!validateTime($input['time'])) {
        sendError('Invalid time format. Please use HH:MM format.', 400);
    }
    
    // --- 3. Insert Data into Database ---
    try {
        $sql = "INSERT INTO event_registration (
                    Event_name, Time, Date, Venue, Department, Poster_name, 
                    Event_links, First_prizes, Second_prizes, Third_prizes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $input['event_name'],
            $input['time'],
            $input['date'],
            $input['venue'],
            $input['department'],
            $input['poster_name'],
            $input['event_links'] ?? '',
            (int)($input['first_prizes'] ?? 0),
            (int)($input['second_prizes'] ?? 0),
            (int)($input['third_prizes'] ?? 0)
        ]);
        
        if ($result) {
            $event_id = $pdo->lastInsertId();
            
            // Return the created event data (fetches full details)
            getEvent($event_id);
        } else {
            sendError('Failed to create event', 500, 'Database insert failed');
        }
        
    } catch (PDOException $e) {
        // If database insert fails, attempt to delete the uploaded file
        if ($poster_name && $destination_path && file_exists($destination_path)) {
            unlink($destination_path);
        }
        logError('Database error in createEvent', ['input' => $input, 'error' => $e->getMessage()]);
        
        if ($e->getCode() == 23000) {
            sendError('Event creation failed', 400, 'Duplicate entry or constraint violation');
        } else {
            sendError('Failed to create event', 500, 'Database error occurred');
        }
    }
}

/**
 * Update existing event
 */
function updateEvent($id) {
    global $pdo;
    
    // Check if the request is multipart/form-data (file upload)
    $is_multipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

    // Get input data
    if ($is_multipart) {
        // Use $_POST for form data
        $input = sanitizeInput($_POST);
    } else {
        // Use getJsonInput() for raw JSON data (for PUT/JSON requests)
        $input = getReliableInput(); // Use the reliable getter here
    }
    
    // Handle optional file upload for PUT request
    $new_poster_name = null;
    if ($is_multipart && isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        // Logic similar to createEvent for handling and moving file
        $file_tmp_name = $_FILES['poster']['tmp_name'];
        $file_extension = pathinfo(basename($_FILES['poster']['name']), PATHINFO_EXTENSION);
        
        // Use a safe, unique filename
        $event_name_safe = sanitizeInput($input['event_name'] ?? 'event'); 
        $unique_filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", $event_name_safe) . '_update_' . time() . '.' . $file_extension;
        $upload_dir = REACT_POSTERS_DIR; // Use absolute path constant
        $destination_path = $upload_dir . '/' . $unique_filename;

        // Ensure directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($file_tmp_name, $destination_path)) {
            $new_poster_name = $unique_filename;
            $input['poster_name'] = $new_poster_name; // Add to input array for database update
        } else {
            sendError('Failed to update poster file.', 500);
        }
    }
    
    if (empty($input)) {
        sendError('No data provided for update', 400);
    }
    
    try {
        // Check if event exists
        $check_sql = "SELECT id, Poster_name FROM event_registration WHERE id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id]);
        $existing_event = $check_stmt->fetch();
        
        if (!$existing_event) {
            sendError('Event not found', 404);
        }
        
        // Build dynamic update query
        $update_fields = [];
        $values = [];
        
        $field_mapping = [
            'event_name' => 'Event_name',
            'time' => 'Time',
            'date' => 'Date',
            'venue' => 'Venue',
            'department' => 'Department',
            'poster_name' => 'Poster_name',
            'event_links' => 'Event_links',
            'first_prizes' => 'First_prizes',
            'second_prizes' => 'Second_prizes',
            'third_prizes' => 'Third_prizes'
        ];
        
        foreach ($field_mapping as $input_field => $db_field) {
            if (isset($input[$input_field])) {
                $update_fields[] = "$db_field = ?";
                
                if (in_array($input_field, ['first_prizes', 'second_prizes', 'third_prizes'])) {
                    $values[] = (int)$input[$input_field];
                } else {
                    $values[] = $input[$input_field];
                }
            }
        }
        
        if (empty($update_fields)) {
            sendError('No valid fields provided for update', 400);
        }
        
        // Add updated_at timestamp
        $update_fields[] = "updated_at = NOW()";
        $values[] = $id; // for WHERE clause
        
        $sql = "UPDATE event_registration SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($values);
        
        if ($result) {
            // Return updated event
            getEvent($id);
        } else {
            sendError('Failed to update event', 500, 'Database update failed');
        }
        
    } catch (PDOException $e) {
        logError('Database error in updateEvent', ['id' => $id, 'input' => $input, 'error' => $e->getMessage()]);
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
        $check_sql = "SELECT Event_name, Poster_name FROM event_registration WHERE id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id]);
        $event = $check_stmt->fetch();
        
        if (!$event) {
            sendError('Event not found', 404);
        }
        
        // Delete the event
        $delete_sql = "DELETE FROM event_registration WHERE id = ?";
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