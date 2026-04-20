<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'My Events - Student Event Management System';
$user_id = $_SESSION['user_id'];

// Handle event cancellation
if ($_POST && isset($_POST['cancel_registration'])) {
    $event_id = (int)$_POST['event_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE registrations SET status = 'cancelled' WHERE user_id = ? AND event_id = ? AND status = 'registered'");
        $stmt->execute([$user_id, $event_id]);
        
        if ($stmt->rowCount() > 0) {
            $success_message = 'Registration cancelled successfully.';
        } else {
            $error_message = 'Unable to cancel registration.';
        }
    } catch (PDOException $e) {
        $error_message = 'Cancellation failed. Please try again.';
    }
}

// Get user's registered events
try {
    $sql = "
        SELECT e.*, r.registration_date, r.status,
               COUNT(all_reg.reg_id) as total_registered
        FROM events e
        INNER JOIN registrations r ON e.event_id = r.event_id
        LEFT JOIN registrations all_reg ON e.event_id = all_reg.event_id AND all_reg.status = 'registered'
        WHERE r.user_id = ?
        GROUP BY e.event_id, r.reg_id
        ORDER BY 
            CASE WHEN r.status = 'registered' AND e.date >= CURDATE() THEN 1
                 WHEN r.status = 'registered' AND e.date < CURDATE() THEN 2
                 ELSE 3 END,
            e.date ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $registered_events = $stmt->fetchAll();
    
    // Separate events by status
    $upcoming_events = [];
    $past_events = [];
    $cancelled_events = [];
    
    foreach ($registered_events as $event) {
        if ($event['status'] === 'cancelled') {
            $cancelled_events[] = $event;
        } elseif ($event['date'] >= date('Y-m-d')) {
            $upcoming_events[] = $event;
        } else {
            $past_events[] = $event;
        }
    }
    
} catch (PDOException $e) {
    $registered_events = [];
    $upcoming_events = [];
    $past_events = [];
    $cancelled_events = [];
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">My Events</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?php echo count($upcoming_events); ?></h3>
                    <p class="mb-0">Upcoming Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?php echo count($past_events); ?></h3>
                    <p class="mb-0">Past Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?php echo count($cancelled_events); ?></h3>
                    <p class="mb-0">Cancelled</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?php echo count($registered_events); ?></h3>
                    <p class="mb-0">Total Registered</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="eventTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" 
                    type="button" role="tab">
                Upcoming Events (<?php echo count($upcoming_events); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" 
                    type="button" role="tab">
                Past Events (<?php echo count($past_events); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" 
                    type="button" role="tab">
                Cancelled (<?php echo count($cancelled_events); ?>)
            </button>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="eventTabsContent">
        <!-- Upcoming Events -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <div class="mt-4">
                <?php if (empty($upcoming_events)): ?>
                    <div class="text-center py-5">
                        <h4>No upcoming events</h4>
                        <p class="text-muted">You haven't registered for any upcoming events yet.</p>
                        <a href="events.php" class="btn btn-primary">Browse Events</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date & Time</th>
                                    <th>Venue</th>
                                    <th>Organizer</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_events as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(substr($event['description'], 0, 80)); ?>
                                                <?php if (strlen($event['description']) > 80) echo '...'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($event['date'])); ?><br>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($event['time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                        <td><?php echo htmlspecialchars($event['organizer']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo date('M j, Y', strtotime($event['registration_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Registered</span><br>
                                            <small class="text-muted">
                                                <?php echo $event['total_registered']; ?>/<?php echo $event['max_participants']; ?> total
                                            </small>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to cancel your registration?')">
                                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                                <button type="submit" name="cancel_registration" 
                                                        class="btn btn-outline-danger btn-sm">
                                                    Cancel Registration
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Past Events -->
        <div class="tab-pane fade" id="past" role="tabpanel">
            <div class="mt-4">
                <?php if (empty($past_events)): ?>
                    <div class="text-center py-5">
                        <h4>No past events</h4>
                        <p class="text-muted">You haven't attended any events yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date & Time</th>
                                    <th>Venue</th>
                                    <th>Organizer</th>
                                    <th>Attended</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($past_events as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(substr($event['description'], 0, 80)); ?>
                                                <?php if (strlen($event['description']) > 80) echo '...'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($event['date'])); ?><br>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($event['time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                        <td><?php echo htmlspecialchars($event['organizer']); ?></td>
                                        <td>
                                            <span class="badge bg-success">✓ Attended</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Cancelled Events -->
        <div class="tab-pane fade" id="cancelled" role="tabpanel">
            <div class="mt-4">
                <?php if (empty($cancelled_events)): ?>
                    <div class="text-center py-5">
                        <h4>No cancelled events</h4>
                        <p class="text-muted">You haven't cancelled any event registrations.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date & Time</th>
                                    <th>Venue</th>
                                    <th>Organizer</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cancelled_events as $event): ?>
                                    <tr class="table-light">
                                        <td>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(substr($event['description'], 0, 80)); ?>
                                                <?php if (strlen($event['description']) > 80) echo '...'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($event['date'])); ?><br>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($event['time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                        <td><?php echo htmlspecialchars($event['organizer']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">Cancelled</span>
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
</div>

<?php include 'includes/footer.php'; ?>