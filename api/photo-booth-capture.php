<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload || empty($payload['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image received.']);
    exit;
}

$caption = sanitize_input($payload['caption'] ?? '');
$imageData = $payload['image'];

if (!preg_match('/^data:image\/(png|jpe?g);base64,(.+)$/', $imageData, $matches)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
    exit;
}

$extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
$binaryData = base64_decode($matches[2]);

if ($binaryData === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Could not decode image data.']);
    exit;
}

$uploadDir = '../uploads/photo_booth/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filename = uniqid('', true) . '.' . $extension;
$fullPath = $uploadDir . $filename;
$dbPath = 'uploads/photo_booth/' . $filename;

if (file_put_contents($fullPath, $binaryData) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save image to disk.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("INSERT INTO photo_booth (user_id, image_path, caption) VALUES (:user_id, :image_path, :caption)");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':image_path', $dbPath);
    $stmt->bindParam(':caption', $caption);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'image_path' => $dbPath
    ]);
} catch (Exception $e) {
    @unlink($fullPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save photo.']);
}
?>

