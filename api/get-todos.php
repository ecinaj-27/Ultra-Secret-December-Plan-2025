<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$date = $_GET['date'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (empty($date) && (empty($start_date) || empty($end_date))) {
    echo json_encode(['success' => false, 'message' => 'Date or date range parameters required']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!empty($date)) {
        // Single date query
        $query = "SELECT * FROM todo_items WHERE user_id = :user_id AND due_date = :date ORDER BY position_order ASC, created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':date', $date);
    } else {
        // Date range query
        $query = "SELECT * FROM todo_items WHERE user_id = :user_id AND due_date BETWEEN :start_date AND :end_date ORDER BY due_date ASC, position_order ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    
    $stmt->execute();
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'todos' => $todos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
