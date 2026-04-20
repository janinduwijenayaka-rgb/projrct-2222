<?php
session_start();
require_once '../includes/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to register for events']);
    exit();
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$event_id = (int)$input['event_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if already registered
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'registered'");
    $stmt->execute([$user_id, $event_id]);
    
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already registered for this event']);
        exit();
    }
    
    // Check if event exists and get info
    $stmt = $pdo->prepare("
        SELECT e.title, e.max_participants, COUNT(r.reg_id) as registered_count 
        FROM events e 
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'registered'
        WHERE e.event_id = ? 
        GROUP BY e.event_id
    ");
    $stmt->execute([$event_id]);
    $event_info = $stmt->fetch();
    
    if (!$event_info) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit();
    }
    
    if ($event_info['registered_count'] >= $event_info['max_participants']) {
        echo json_encode(['success' => false, 'message' => 'Sorry, this event is full']);
        exit();
    }
    
    // Register user
    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $event_id]);
    
    // Get new registration count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND status = 'registered'");
    $stmt->execute([$event_id]);
    $new_count = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully registered for ' . $event_info['title'] . '!',
        'new_count' => $new_count,
        'event_title' => $event_info['title']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
}
?>