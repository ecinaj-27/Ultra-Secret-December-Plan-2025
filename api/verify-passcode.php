<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Only allow logged-in users to verify their own passcode
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$passcode = $input['passcode'] ?? '';

if ($passcode === '') {
    echo json_encode(['success' => false, 'error' => 'Missing passcode']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare('SELECT passcode FROM users WHERE id = :id');
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['success' => false]);
        exit();
    }
    $ok = password_verify($passcode, $user['passcode']);
    echo json_encode(['success' => $ok]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}


