<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get active compliments from database
    $query = "SELECT content FROM custom_compliments WHERE is_active = 1 ORDER BY RAND()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $compliments = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // If no database compliments, fall back to hardcoded
    if (empty($compliments)) {
        $compliments = [
            "Your smile could light up the darkest room.",
            "You have the most beautiful eyes I've ever seen.",
            "Your laugh is my favorite sound in the world.",
            "You make everything better just by being you.",
            "Your kindness touches everyone around you.",
            "You are incredibly intelligent and wise.",
            "Your creativity never ceases to amaze me.",
            "You have the most caring heart.",
            "Your strength inspires me every day.",
            "You are absolutely perfect just as you are.",
            "Your presence makes any place feel like home.",
            "You have the most beautiful soul I've ever encountered.",
            "Your love has changed my life in the best way possible.",
            "You are my greatest adventure and my safest place.",
            "Your beauty radiates from within and illuminates everything around you."
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['compliments' => $compliments]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
