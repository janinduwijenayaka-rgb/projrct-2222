<?php
// Create Admin User Script
require_once '../includes/db.php';

$success = false;
$message = '';

if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($name) && !empty($email) && !empty($password)) {
        try {
            // Check if admin already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetchColumn() > 0) {
                $message = 'User with this email already exists!';
            } else {
                // Create admin user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$name, $email, $hashedPassword]);
                
                $success = true;
                $message = 'Admin user created successfully!';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $message = 'All fields are required!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Create Admin User</h3>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <?php if ($success): ?>
                                    <br><br>
                                    <a href="login.php" class="btn btn-success btn-sm">Go to Admin Login</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Admin Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($name ?? 'Admin'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? 'admin@university.edu'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter admin password" required>
                                <small class="form-text text-muted">Recommended: admin123</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Create Admin User</button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <p><a href="login.php">Back to Admin Login</a></p>
                            <p><a href="../index.php">Back to Main Site</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>