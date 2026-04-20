<?php
session_start();
require_once '../includes/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
try {
    // Total events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events");
    $stmt->execute();
    $total_events = $stmt->fetchColumn();
    
    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();
    
    // Total registrations
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE status = 'registered'");
    $stmt->execute();
    $total_registrations = $stmt->fetchColumn();
    
    // Upcoming events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE date >= CURDATE()");
    $stmt->execute();
    $upcoming_events = $stmt->fetchColumn();
    
    // Recent events with registration counts
    $stmt = $pdo->prepare("
        SELECT e.*, COUNT(r.reg_id) as registration_count
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'registered'
        GROUP BY e.event_id
        ORDER BY e.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_events = $stmt->fetchAll();
    
    // Recent registrations
    $stmt = $pdo->prepare("
        SELECT r.registration_date, u.name, u.email, e.title
        FROM registrations r
        JOIN users u ON r.user_id = u.user_id
        JOIN events e ON r.event_id = e.event_id
        WHERE r.status = 'registered'
        ORDER BY r.registration_date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_registrations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $total_events = $total_users = $total_registrations = $upcoming_events = 0;
    $recent_events = $recent_registrations = [];
}

$page_title = 'Admin Dashboard - Student Event Management System';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_event.php">Add Event</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">View Site</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Admin Dashboard</h2>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $total_events; ?></h4>
                                <p class="mb-0">Total Events</p>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">📅</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $total_users; ?></h4>
                                <p class="mb-0">Registered Students</p>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">👥</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $total_registrations; ?></h4>
                                <p class="mb-0">Total Registrations</p>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">📝</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $upcoming_events; ?></h4>
                                <p class="mb-0">Upcoming Events</p>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">⏰</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Events -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Events</h5>
                        <a href="add_event.php" class="btn btn-primary btn-sm">Add New Event</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_events)): ?>
                            <p class="text-muted text-center py-3">No events found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Event Title</th>
                                            <th>Date</th>
                                            <th>Registrations</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_events as $event): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($event['venue']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo date('M j, Y', strtotime($event['date'])); ?><br>
                                                    <small class="text-muted"><?php echo date('g:i A', strtotime($event['time'])); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $event['registration_count']; ?>/<?php echo $event['max_participants']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit_event.php?id=<?php echo $event['event_id']; ?>" 
                                                           class="btn btn-outline-primary">Edit</a>
                                                        <a href="delete_event.php?id=<?php echo $event['event_id']; ?>" 
                                                           class="btn btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Registrations -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Registrations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_registrations)): ?>
                            <p class="text-muted text-center py-3">No registrations found.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_registrations as $registration): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><?php echo htmlspecialchars($registration['name']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($registration['title']); ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('M j', strtotime($registration['registration_date'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>