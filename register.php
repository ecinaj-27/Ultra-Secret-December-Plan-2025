<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $passcode = $_POST['passcode'];
    $confirm_passcode = $_POST['confirm_passcode'];
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password_hint = sanitize_input($_POST['password_hint']);
    
    // Validation
    if ($passcode !== $confirm_passcode) {
        $error_message = 'Passcodes do not match';
    } elseif (strlen($passcode) < 6) {
        $error_message = 'Passcode must be at least 6 characters long';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if username already exists
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error_message = 'Username already exists';
        } else {
            // Create new user
            $hashed_passcode = password_hash($passcode, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, passcode, name, email, password_hint) VALUES (:username, :passcode, :name, :email, :password_hint)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':passcode', $hashed_passcode);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hint', $password_hint);
            
            if ($stmt->execute()) {
                $success_message = 'Account created successfully! You can now login.';
            } else {
                $error_message = 'Error creating account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/passcode.css">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="passcode-body">
    <div id="slideshow-container">
        <div class="slideshow-image image-1"></div>
        <div class="slideshow-image image-2"></div>
        <div class="slideshow-image image-3"></div>
        <div class="slideshow-image image-4"></div>
    </div>
    <div class="passcode-container">
        <div class="background-blur"></div>
        <div class="passcode-content">
            <div class="username-section">
                <h2><i class="fas fa-heart"></i> Join Us</h2>
                <p style="margin-bottom: 1.5rem; opacity: 0.9;">Create your account</p>
                
                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="error-message" style="background: rgba(212, 237, 218, 0.3); border-color: #c3e6cb; color: #155724;">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="username-input">
                    <input type="text" id="name" name="name" placeholder="Full Name" required style="width: 100%; padding: 0.75rem; background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 10px; color: #333; font-size: 1rem; margin-bottom: 1rem;">
                    
                    <input type="text" id="username" name="username" placeholder="Username" required style="width: 100%; padding: 0.75rem; background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 10px; color: #333; font-size: 1rem; margin-bottom: 1rem;">
                    
                    <input type="email" id="email" name="email" placeholder="Email" required style="width: 100%; padding: 0.75rem; background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 10px; color: #333; font-size: 1rem; margin-bottom: 1rem;">
                    
                    <input type="password" id="passcode" name="passcode" placeholder="Passcode (min 6 characters)" required style="width: 100%; padding: 0.75rem; background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 10px; color: #333; font-size: 1rem; margin-bottom: 1rem;">
                    
                    <input type="password" id="confirm_passcode" name="confirm_passcode" placeholder="Confirm Passcode" required style="width: 100%; padding: 0.75rem; background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 10px; color: #333; font-size: 1rem; margin-bottom: 1rem;">
                    
                    <input type="text" id="password_hint" name="password_hint" value="Anniversary Date" placeholder="Password Hint" required style="width: 100%; padding: 0.75rem; background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 10px; color: #333; font-size: 1rem; margin-bottom: 1.5rem;">
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; background: #ff6b6b; color: white; border: none; border-radius: 25px; cursor: pointer; font-size: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>
                
                <div class="alternative-login" style="text-align: center; margin-top: 1rem;">
                    <a href="login.php" class="alt-login-link" style="color: white; text-decoration: none; font-size: 0.9rem;">
                        <i class="fas fa-sign-in-alt"></i>
                        Already have an account? Sign in here
                    </a>
                    <br>
                    <a href="index.php" class="alt-login-link" style="color: white; text-decoration: none; font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/passcode.js"></script>
</body>
</html>
