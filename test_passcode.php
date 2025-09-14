<?php
// Test script to diagnose passcode authentication issues
require_once 'config/database.php';

echo "<h2>Passcode Authentication Test</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if the passcode field exists
    $query = "DESCRIBE users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Database Schema Check:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if there are any users
    $query = "SELECT id, username, name, created_at FROM users LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Existing Users:</h3>";
    if (empty($users)) {
        echo "<p>No users found in database.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['name'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test passcode verification if users exist
    if (!empty($users)) {
        echo "<h3>Passcode Field Test:</h3>";
        $test_user = $users[0];
        $query = "SELECT passcode FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $test_user['id']);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            if (isset($user_data['passcode'])) {
                echo "<p style='color: green;'>✓ Passcode field exists and contains data</p>";
                echo "<p>Passcode hash length: " . strlen($user_data['passcode']) . " characters</p>";
            } else {
                echo "<p style='color: red;'>✗ Passcode field is missing or empty</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Could not retrieve user data</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="login.php">Go to Login</a> | <a href="register.php">Go to Register</a></p>
