<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get all users (you might want to limit this in production)
    $query = "SELECT id, username, name, profile_picture, password_hint FROM users ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Remove sensitive data
    foreach ($users as &$user) {
        unset($user['passcode']);
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load users: ' . $e->getMessage()
    ]);
}
?>
