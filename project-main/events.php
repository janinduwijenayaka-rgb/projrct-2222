<?php
session_start();
require_once 'includes/db.php';

$page_title = 'Events - Student Event Management System';

// Handle event registration
if ($_POST && isset($_POST['register_event']) && isset($_SESSION['user_id'])) {
    $event_id = (int)$_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check if already registered
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'registered'");
        $stmt->execute([$user_id, $event_id]);
        
        if ($stmt->fetchColumn() == 0) {
            // Check if event is full
            $stmt = $pdo->prepare("
                SELECT e.max_participants, COUNT(r.reg_id) as registered_count 
                FROM events e 
                LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'registered'
                WHERE e.event_id = ? 
                GROUP BY e.event_id
            ");
            $stmt->execute([$event_id]);
            $event_info = $stmt->fetch();
            
            if ($event_info['registered_count'] < $event_info['max_participants']) {
                // Register user
                $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $event_id]);
                $success_message = 'Successfully registered for the event!';
            } else {
                $error_message = 'Sorry, this event is full.';
            }
        } else {
            $error_message = 'You are already registered for this event.';
        }
    } catch (PDOException $e) {
        $error_message = 'Registration failed: ' . $e->getMessage();
    }
}

// Get events with registration info
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$organizer_filter = isset($_GET['organizer']) ? $_GET['organizer'] : '';

$where_conditions = ["e.date >= CURDATE()"];
$params = [isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0];

if ($search) {
    $where_conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.organizer LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($date_filter) {
    $where_conditions[] = "e.date = ?";
    $params[] = $date_filter;
}

if ($organizer_filter) {
    $where_conditions[] = "e.organizer = ?";
    $params[] = $organizer_filter;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    $sql = "
        SELECT e.*, 
               COUNT(r.reg_id) as registered_count,
               CASE WHEN ur.user_id IS NOT NULL THEN 1 ELSE 0 END as user_registered
        FROM events e 
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'registered'
        LEFT JOIN registrations ur ON e.event_id = ur.event_id AND ur.user_id = ? AND ur.status = 'registered'
        WHERE $where_clause
        GROUP BY e.event_id 
        ORDER BY e.date ASC, e.time ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    
    // Get unique organizers for filter
    $stmt = $pdo->prepare("SELECT DISTINCT organizer FROM events WHERE date >= CURDATE() ORDER BY organizer");
    $stmt->execute();
    $organizers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $events = [];
    $organizers = [];
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">University Events</h2>
            
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
    
    <!-- Search and Filters -->
    <div class="filter-section mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search Events</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Search by title, description, or organizer..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label">Filter by Date</label>
                <input type="date" class="form-control" id="date" name="date" 
                       value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <div class="col-md-3">
                <label for="organizer" class="form-label">Filter by Organizer</label>
                <select class="form-select" id="organizer" name="organizer">
                    <option value="">All Organizers</option>
                    <?php foreach ($organizers as $organizer): ?>
                        <option value="<?php echo htmlspecialchars($organizer); ?>"
                                <?php echo $organizer_filter === $organizer ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($organizer); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="events.php" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
    
    <!-- Events List -->
    <div class="row">
        <?php if (empty($events)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <h4>No events found</h4>
                    <p class="text-muted">Try adjusting your search criteria or check back later for new events.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 event-card" data-date="<?php echo $event['date']; ?>" 
                         data-organizer="<?php echo htmlspecialchars($event['organizer']); ?>">
                        <div class="card-body d-flex flex-column">
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
                            
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>
                                <?php if (strlen($event['description']) > 120) echo '...'; ?>
                            </p>
                            
                            <div class="event-meta mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <strong>📅 Date:</strong><br>
                                            <?php echo date('M j, Y', strtotime($event['date'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <strong>🕐 Time:</strong><br>
                                            <?php echo date('g:i A', strtotime($event['time'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <strong>📍 Venue:</strong><br>
                                            <?php echo htmlspecialchars($event['venue']); ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <strong>👥 Organizer:</strong><br>
                                            <?php echo htmlspecialchars($event['organizer']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <span class="registration-count"><?php echo $event['registered_count']; ?></span>/<?php echo $event['max_participants']; ?> registered
                                </small>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($event['user_registered']): ?>
                                        <button class="btn btn-success btn-sm" disabled>
                                            ✓ Registered
                                        </button>
                                    <?php elseif ($event['registered_count'] >= $event['max_participants']): ?>
                                        <button class="btn btn-danger btn-sm" disabled>
                                            Event Full
                                        </button>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;" class="registration-form"
                                              onsubmit="return confirmRegistration(this, '<?php echo htmlspecialchars($event['title']); ?>')">
                                            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                            <button type="submit" name="register_event" class="btn btn-primary btn-sm register-btn"
                                                    data-event-id="<?php echo $event['event_id']; ?>"
                                                    data-event-title="<?php echo htmlspecialchars($event['title']); ?>">
                                                Register Now
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary btn-sm">
                                        Login to Register
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Enhanced registration functionality
function confirmRegistration(form, eventTitle) {
    const confirmed = confirm(`Are you sure you want to register for this event: ${eventTitle}?`);
    
    if (confirmed) {
        const button = form.querySelector('.register-btn');
        // Show loading state
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Registering...';
        button.disabled = true;
        
        // Let form submit normally - you can uncomment the AJAX version below for instant feedback
        return true;
        
        // AJAX Version (uncomment to use):
        /*
        const eventId = button.getAttribute('data-event-id');
        
        fetch('ajax/register_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                event_id: eventId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.innerHTML = '✓ Registered';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                button.disabled = true;
                
                // Update registration count
                const countElement = form.closest('.card').querySelector('.registration-count');
                if (countElement) {
                    countElement.textContent = data.new_count;
                }
                
                // Show success message
                showToast(data.message, 'success');
                
            } else {
                button.innerHTML = 'Register Now';
                button.disabled = false;
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            button.innerHTML = 'Register Now';
            button.disabled = false;
            showToast('Network error. Please try again.', 'danger');
        });
        
        return false; // Prevent form submission
        */
    }
    
    return confirmed;
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll to success/error messages
    const alertElements = document.querySelectorAll('.alert');
    if (alertElements.length > 0) {
        alertElements[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            alertElements.forEach(alert => {
                if (alert.classList.contains('alert-dismissible')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    }
});
</script>

<?php include 'includes/footer.php'; ?>