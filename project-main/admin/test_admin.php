<?php
// Admin User Test Script
require_once '../includes/db.php';

echo "<h2>🔍 Admin User Debugging</h2>";

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT user_id, name, email, role, created_at FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    echo "<h3>Admin Users in Database:</h3>";
    if (empty($admins)) {
        echo "<p>❌ No admin users found!</p>";
        
        // Create admin user
        echo "<h4>Creating default admin user...</h4>";
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute(['Admin', 'admin@university.edu', $adminPassword]);
        
        echo "<p>✅ Admin user created successfully!</p>";
        echo "<p><strong>Login Credentials:</strong></p>";
        echo "<ul>";
        echo "<li>Email: admin@university.edu</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
        
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['name']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . $admin['role'] . "</td>";
            echo "<td>" . $admin['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test password verification
    if (!empty($admins)) {
        $admin = $admins[0];
        echo "<h3>Password Test:</h3>";
        
        // Test with admin123
        $testPassword = 'admin123';
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute([$admin['email']]);
        $storedHash = $stmt->fetchColumn();
        
        if (password_verify($testPassword, $storedHash)) {
            echo "<p>✅ Password 'admin123' verification: <strong>SUCCESS</strong></p>";
        } else {
            echo "<p>❌ Password 'admin123' verification: <strong>FAILED</strong></p>";
            
            // Recreate admin with correct password
            echo "<h4>Fixing admin password...</h4>";
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$newHash, $admin['email']]);
            echo "<p>✅ Admin password updated!</p>";
        }
    }
    
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎯 Try Admin Login Now:</h3>";
    echo "<p><a href='login.php' style='background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    echo "<p><strong>Credentials:</strong><br>";
    echo "Email: admin@university.edu<br>";
    echo "Password: admin123</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure to run the setup script first: <a href='../setup.php'>setup.php</a></p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #0d6efd; }
table { margin: 20px 0; }
th { background-color: #0d6efd; color: white; }
tr:nth-child(even) { background-color: #f8f9fa; }
</style>