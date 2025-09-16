<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$payload = json_decode(file_get_contents('php://input'), true);
$post_id = isset($payload['post_id']) ? (int)$payload['post_id'] : 0;
if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Ensure a record exists, then mark read
    $stmt = $db->prepare("INSERT IGNORE INTO post_reads (user_id, post_id, is_read) VALUES (:uid, :pid, 0)");
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->bindParam(':pid', $post_id);
    $stmt->execute();

    $upd = $db->prepare("UPDATE post_reads SET is_read = 1, read_at = NOW() WHERE user_id = :uid AND post_id = :pid");
    $upd->bindParam(':uid', $_SESSION['user_id']);
    $upd->bindParam(':pid', $post_id);
    $upd->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false]);
}
?>


