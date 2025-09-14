<?php
// Common functions for the website

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_user_data($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_anniversary_countdown() {
    // Anniversary is December 17th, 2025
    $anniversary = new DateTime('2025-12-17');
    $today = new DateTime();
    
    // Calculate days until anniversary
    $interval = $today->diff($anniversary);
    
    // If anniversary has passed, return 0
    if ($today > $anniversary) {
        return 0;
    }
    
    return $interval->days;
}

function get_random_quote() {
    $quotes = [
        "You are my today and all of my tomorrows.",
        "In all the world, there is no heart for me like yours.",
        "I love you not only for what you are, but for what I am when I am with you.",
        "You make me want to be a better person.",
        "Every love story is beautiful, but ours is my favorite.",
        "I have found the one whom my soul loves.",
        "You are my sunshine on a cloudy day.",
        "I love you more than words can express.",
        "You are the reason I believe in love.",
        "My heart is and always will be yours."
    ];
    
    return $quotes[array_rand($quotes)];
}

function get_random_compliment() {
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
        "You are absolutely perfect just as you are."
    ];
    
    return $compliments[array_rand($compliments)];
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

function upload_file($file, $upload_dir = 'uploads/') {
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }
    
    return false;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function get_user_role_data($user_id = null) {
    if (!$user_id) {
        if (!is_logged_in()) return null;
        $user_id = $_SESSION['user_id'];
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT r.* FROM roles r 
              JOIN users u ON u.role_id = r.id 
              WHERE u.id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function is_admin() {
    if (!is_logged_in()) return false;
    
    $role_data = get_user_role_data();
    return $role_data && $role_data['name'] === 'admin';
}

function is_moderator() {
    if (!is_logged_in()) return false;
    
    $role_data = get_user_role_data();
    return $role_data && in_array($role_data['name'], ['admin', 'moderator']);
}

function get_user_role() {
    if (!is_logged_in()) return 'guest';
    
    $role_data = get_user_role_data();
    return $role_data ? $role_data['name'] : 'user';
}

function get_user_role_display() {
    if (!is_logged_in()) return 'Guest';
    
    $role_data = get_user_role_data();
    return $role_data ? $role_data['display_name'] : 'User';
}

function has_permission($required_role) {
    if (!is_logged_in()) return false;
    
    $role_data = get_user_role_data();
    if (!$role_data) return false;
    
    $user_level = $role_data['level'];
    
    // Get required role level
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT level FROM roles WHERE name = :role_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':role_name', $required_role);
    $stmt->execute();
    
    $required_role_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$required_role_data) return false;
    
    return $user_level >= $required_role_data['level'];
}

function has_specific_permission($permission) {
    if (!is_logged_in()) return false;
    
    $role_data = get_user_role_data();
    if (!$role_data || !$role_data['permissions']) return false;
    
    $permissions = json_decode($role_data['permissions'], true);
    return isset($permissions[$permission]) && $permissions[$permission] === true;
}
?>
