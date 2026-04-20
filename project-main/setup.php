<?php
// Automatic Database Setup Script
echo "<h2>🚀 Student Event Management System - Database Setup</h2>";

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'student_events';

try {
    // First, connect without specifying database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database '$dbname' created/verified</p>";
    
    // Connect to the specific database
    $pdo->exec("USE $dbname");
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            student_id VARCHAR(20) UNIQUE,
            phone VARCHAR(15),
            role ENUM('student', 'admin') DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Users table created</p>";
    
    // Create events table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            event_id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            time TIME NOT NULL,
            venue VARCHAR(200) NOT NULL,
            organizer VARCHAR(100) NOT NULL,
            max_participants INT DEFAULT 50,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
        )
    ");
    echo "<p>✅ Events table created</p>";
    
    // Create registrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registrations (
            reg_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            event_id INT,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('registered', 'cancelled') DEFAULT 'registered',
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
            UNIQUE KEY unique_registration (user_id, event_id)
        )
    ");
    echo "<p>✅ Registrations table created</p>";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@university.edu'");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        // Insert default admin user (password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
            ->execute(['Admin', 'admin@university.edu', $adminPassword]);
        echo "<p>✅ Default admin user created (Email: admin@university.edu, Password: admin123)</p>";
    } else {
        echo "<p>ℹ️ Admin user already exists</p>";
    }
    
    // Insert sample events if they don't exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        $sampleEvents = [
            ['Web Development Workshop', 'Learn modern web development with HTML, CSS, and JavaScript', '2025-11-20', '10:00:00', 'Computer Lab A', 'CS Department', 30],
            ['AI/ML Hackathon', '24-hour hackathon focusing on artificial intelligence and machine learning', '2025-11-25', '09:00:00', 'Innovation Hub', 'Tech Society', 50],
            ['Career Fair 2025', 'Meet with top employers and explore career opportunities', '2025-12-01', '13:00:00', 'Main Auditorium', 'Career Services', 200],
            ['Database Design Seminar', 'Advanced database design principles and best practices', '2025-11-18', '14:00:00', 'Lecture Hall B', 'Database Club', 40],
            ['Mobile App Development', 'Introduction to mobile app development for Android and iOS', '2025-12-05', '09:30:00', 'Computer Lab C', 'Mobile Dev Society', 25]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO events (title, description, date, time, venue, organizer, max_participants, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        
        foreach ($sampleEvents as $event) {
            $stmt->execute($event);
        }
        
        echo "<p>✅ Sample events created (" . count($sampleEvents) . " events)</p>";
    } else {
        echo "<p>ℹ️ Events already exist in database</p>";
    }
    
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p><strong>Your system is now ready to use:</strong></p>";
    echo "<ul>";
    echo "<li><a href='index.php'>🏠 Go to Homepage</a></li>";
    echo "<li><a href='register.php'>📝 Student Registration</a></li>";
    echo "<li><a href='login.php'>🔐 Student Login</a></li>";
    echo "<li><a href='admin/login.php'>👨‍💼 Admin Login</a></li>";
    echo "</ul>";
    echo "<p><strong>Admin Credentials:</strong><br>";
    echo "Email: admin@university.edu<br>";
    echo "Password: admin123</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Setup Failed</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common Solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running (Apache + MySQL)</li>";
    echo "<li>Check if MySQL is running on port 3306</li>";
    echo "<li>Verify MySQL username/password (default: root with no password)</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0d6efd; }
p { margin: 10px 0; }
ul { margin: 10px 0; }
a { color: #0d6efd; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>