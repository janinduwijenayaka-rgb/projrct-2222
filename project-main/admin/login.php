<?php
session_start();
require_once '../includes/db.php';

// Check if admin is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                // Enhanced debugging
                if (!$user) {
                    $error_message = 'No admin user found with email: ' . htmlspecialchars($email);
                } else {
                    $error_message = 'Password verification failed for admin user';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Student Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="card-title">Admin Login</h3>
                            <p class="text-muted">Event Management System</p>
                        </div>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="adminLoginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Login as Admin</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p><a href="../index.php">Back to Main Site</a></p>
                            <p><a href="../login.php">Student Login</a></p>
                            <p><small><a href="create_admin.php" class="text-muted">Create New Admin User</a></small></p>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <strong>Demo Admin:</strong><br>
                                Email: admin@university.edu<br>
                                Password: admin123<br><br>
                                <a href="test_admin.php" class="text-decoration-none">🔧 Troubleshoot Admin Login</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>