<?php
// Common functions for the website

function sanitize_input($data) {
    $data = trim($data);
    // Only strip slashes if magic quotes are enabled (PHP < 5.4)
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }
    // Don't use htmlspecialchars here - it converts special characters
    // We'll escape when displaying, not when storing
    return $data;
}

// Function to sanitize for display (use this when outputting to HTML)
function sanitize_for_display($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
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
    // Try to get from database first
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT content FROM custom_inspirations WHERE is_active = 1 ORDER BY RAND() LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['content'])) {
            return $result['content'];
        }
    } catch (Exception $e) {
        // Fall back to hardcoded if database fails
    }
    
    // Fallback to hardcoded quotes
    $quotes = [
        "That's all for today, folks!",
 
    ];
    
    return $quotes[array_rand($quotes)];
}

function get_random_compliment() {
    // Try to get from database first
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT content FROM custom_compliments WHERE is_active = 1 ORDER BY RAND() LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['content'])) {
            return $result['content'];
        }
    } catch (Exception $e) {
        // Fall back to hardcoded if database fails
    }
    
    // Fallback to hardcoded compliments
    $compliments = [
        "That's all for today, folks!"
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

function delete_file_if_exists($file_path) {
    if (!$file_path) return false;
    try {
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
    } catch (Exception $e) {
        return false;
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

// New functions for managing custom content
function get_all_compliments() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM custom_compliments ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function get_all_inspirations() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM custom_inspirations ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function add_custom_compliment($content, $user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO custom_compliments (content, created_by) VALUES (:content, :user_id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function add_custom_inspiration($content, $user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO custom_inspirations (content, created_by) VALUES (:content, :user_id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function toggle_compliment_status($id, $is_active) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE custom_compliments SET is_active = :is_active WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function toggle_inspiration_status($id, $is_active) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE custom_inspirations SET is_active = :is_active WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function delete_custom_compliment($id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "DELETE FROM custom_compliments WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function delete_custom_inspiration($id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "DELETE FROM custom_inspirations WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}
?>
