<?php
session_start();
require_once 'includes/db.php';

$page_title = 'Home - Student Event Management System';
include 'includes/header.php';

// Get upcoming events
try {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               COUNT(r.reg_id) as registered_count,
               CASE WHEN ur.user_id IS NOT NULL THEN 1 ELSE 0 END as user_registered
        FROM events e 
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'registered'
        LEFT JOIN registrations ur ON e.event_id = ur.event_id AND ur.user_id = ? AND ur.status = 'registered'
        WHERE e.date >= CURDATE() 
        GROUP BY e.event_id 
        ORDER BY e.date ASC 
        LIMIT 3
    ");
    $stmt->execute([isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0]);
    $upcoming_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $upcoming_events = [];
}
?>

<!-- Hero Section -->
<section class="hero bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Student Event Management</h1>
                <p class="lead">Discover, register, and manage your participation in university events seamlessly.</p>
                <div class="mt-4">
                    <a href="events.php" class="btn btn-light btn-lg me-3">Browse Events</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-outline-light btn-lg">Join Now</a>
                    <?php else: ?>
                        <a href="event_details.php" class="btn btn-outline-light btn-lg">My Events</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <div class="hero-icon">
                        <i class="fas fa-calendar-alt fa-8x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Choose Our Platform?</h2>
            <p class="lead text-muted">Streamlined event management for students</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            📅
                        </div>
                        <h5>Easy Registration</h5>
                        <p class="text-muted">Quick and simple event registration process with instant confirmation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            🎯
                        </div>
                        <h5>Event Discovery</h5>
                        <p class="text-muted">Find events that match your interests with our advanced filtering system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="feature-icon bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            📊
                        </div>
                        <h5>Track Participation</h5>
                        <p class="text-muted">Monitor your event history and manage your upcoming registrations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<?php if (!empty($upcoming_events)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Upcoming Events</h2>
            <p class="lead text-muted">Don't miss out on these exciting events</p>
        </div>
        <div class="row">
            <?php foreach ($upcoming_events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 event-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <?php if ($event['user_registered']): ?>
                                    <span class="badge bg-success">Registered</span>
                                <?php elseif ($event['registered_count'] >= $event['max_participants']): ?>
                                    <span class="badge bg-danger">Full</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Available</span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text text-muted"><?php echo substr(htmlspecialchars($event['description']), 0, 100) . '...'; ?></p>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Date:</strong> <?php echo date('M j, Y', strtotime($event['date'])); ?><br>
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['time'])); ?><br>
                                    <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?><br>
                                    <strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizer']); ?>
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo $event['registered_count']; ?>/<?php echo $event['max_participants']; ?> registered
                                </small>
                                <a href="events.php?id=<?php echo $event['event_id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="events.php" class="btn btn-primary">View All Events</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>