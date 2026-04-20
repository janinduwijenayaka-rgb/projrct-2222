<?php
session_start();
require_once 'includes/db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error_message = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error_message = 'Login failed. Please try again.';
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Student Login</h3>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                        <p><a href="index.php">Back to Home</a></p>
                        <p><a href="admin/login.php">Admin Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>