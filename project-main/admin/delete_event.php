<?php
session_start();
require_once '../includes/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    header('Location: dashboard.php');
    exit();
}

try {
    // Check if event exists
    $stmt = $pdo->prepare("SELECT title FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: dashboard.php?error=Event not found');
        exit();
    }
    
    // Delete related registrations first
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
    $stmt->execute([$event_id]);
    
    // Delete the event
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    
    header('Location: dashboard.php?success=Event deleted successfully');
    exit();
    
} catch (PDOException $e) {
    header('Location: dashboard.php?error=Failed to delete event');
    exit();
}
?>