<?php
session_start();
require_once 'includes/db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $student_id = trim($_POST['student_id']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Server-side validation
    if (empty($name) || empty($email) || empty($student_id) || empty($password)) {
        $error_message = 'All required fields must be filled';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } else {
        try {
            // Check if email or student_id already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR student_id = ?");
            $stmt->execute([$email, $student_id]);
            
            if ($stmt->fetchColumn() > 0) {
                $error_message = 'Email or Student ID already exists';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, student_id, phone, password, role) VALUES (?, ?, ?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $student_id, $phone, $hashed_password]);
                
                $success_message = 'Registration successful! You can now login.';
                // Clear form data
                $name = $email = $student_id = $phone = '';
            }
        } catch (PDOException $e) {
            // For debugging - in production, you'd log this and show a generic message
            $error_message = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$page_title = 'Register - Student Event Management System';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Student Registration</h3>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <br><a href="login.php" class="alert-link">Click here to login</a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="registerForm" data-validate="true">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID *</label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" 
                                           value="<?php echo htmlspecialchars($student_id ?? ''); ?>" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">At least 6 characters</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                        <p><a href="index.php">Back to Home</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
