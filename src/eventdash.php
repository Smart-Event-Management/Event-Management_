<?php
/**
 * Event Organizer Dashboard - Main API Endpoints
 * Handles CRUD operations for events
 */

require_once 'config.php';

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
            if (!empty($path_segments)) {
                $event_id = $path_segments[0];
                if (is_numeric($event_id)) {
                    deleteEvent($event_id);
                } else {
                    sendError('Invalid event ID format', 400);
                }
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
                    Third_prizes as third_prizes
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
                    Third_prizes as third_prizes
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
 * Create new event
 */
function createEvent() {
    global $pdo;
    
    $input = getJsonInput();
    
    // Validate required fields
    $required_fields = ['event_name', 'time', 'date', 'venue', 'department', 'poster_name'];
    $missing_fields = validateRequiredFields($input, $required_fields);
    
    if (!empty($missing_fields)) {
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
    
    try {
        $sql = "INSERT INTO event_registration (
                    Event_name, Time, Date, Venue, Department, Poster_name, 
                    Event_links, First_prizes, Second_prizes, Third_prizes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
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
            
            // Return the created event
            getEvent($event_id);
        } else {
            sendError('Failed to create event', 500, 'Database insert failed');
        }
        
    } catch (PDOException $e) {
        logError('Database error in createEvent', ['input' => $input, 'error' => $e->getMessage()]);
        
        // Handle specific database errors
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
    
    $input = getJsonInput();
    
    if (empty($input)) {
        sendError('No data provided for update', 400);
    }
    
    try {
        // Check if event exists
        $check_sql = "SELECT id FROM event_registration WHERE id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id]);
        
        if (!$check_stmt->fetch()) {
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
        $check_sql = "SELECT Event_name FROM event_registration WHERE id = ?";
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