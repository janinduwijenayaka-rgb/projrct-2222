<?php
// Database connection test
require_once 'includes/db.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    echo "<p>✅ Database connection successful!</p>";
    
    // Check if tables exist
    $tables = ['users', 'events', 'registrations'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        
        if ($stmt->fetch()) {
            echo "<p>✅ Table '$table' exists</p>";
            
            // Show table structure
            $stmt = $pdo->prepare("DESCRIBE $table");
            $stmt->execute();
            $columns = $stmt->fetchAll();
            
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>❌ Table '$table' does not exist!</p>";
        }
    }
    
    // Test a simple query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p>Users table has {$result['count']} records</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>