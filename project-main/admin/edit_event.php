<?php
session_start();
require_once '../includes/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error_message = '';
$success_message = '';

if (!$event_id) {
    header('Location: dashboard.php');
    exit();
}

// Get event data
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: dashboard.php');
    exit();
}

if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = trim($_POST['venue']);
    $organizer = trim($_POST['organizer']);
    $max_participants = (int)$_POST['max_participants'];
    
    // Server-side validation
    if (empty($title) || empty($description) || empty($date) || empty($time) || empty($venue) || empty($organizer)) {
        $error_message = 'All fields are required';
    } elseif ($max_participants < 1) {
        $error_message = 'Maximum participants must be at least 1';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE events 
                SET title = ?, description = ?, date = ?, time = ?, venue = ?, organizer = ?, max_participants = ?
                WHERE event_id = ?
            ");
            $stmt->execute([$title, $description, $date, $time, $venue, $organizer, $max_participants, $event_id]);
            
            $success_message = 'Event updated successfully!';
            
            // Refresh event data
            $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch();
        } catch (PDOException $e) {
            $error_message = 'Failed to update event. Please try again.';
        }
    }
}

$page_title = 'Edit Event - Admin Panel';
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
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
                <h2 class="mb-4">Edit Event</h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="eventForm" data-validate="true">
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($event['title']); ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Event Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Event Date *</label>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?php echo htmlspecialchars($event['date']); ?>" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="time" class="form-label">Event Time *</label>
                                        <input type="time" class="form-control" id="time" name="time" 
                                               value="<?php echo htmlspecialchars($event['time']); ?>" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="venue" class="form-label">Venue *</label>
                                <input type="text" class="form-control" id="venue" name="venue" 
                                       value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="organizer" class="form-label">Organizer *</label>
                                        <input type="text" class="form-control" id="organizer" name="organizer" 
                                               value="<?php echo htmlspecialchars($event['organizer']); ?>" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="max_participants" class="form-label">Maximum Participants *</label>
                                        <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                               min="1" max="1000" 
                                               value="<?php echo htmlspecialchars($event['max_participants']); ?>" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>