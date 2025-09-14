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
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-heart"></i>
                <h2>Join Us</h2>
                <p>Create your account</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-at"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="passcode">
                        <i class="fas fa-lock"></i>
                        Passcode
                    </label>
                    <input type="password" id="passcode" name="passcode" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_passcode">
                        <i class="fas fa-lock"></i>
                        Confirm Passcode
                    </label>
                    <input type="password" id="confirm_passcode" name="confirm_passcode" required>
                </div>
                
                <div class="form-group">
                    <label for="password_hint">
                        <i class="fas fa-lightbulb"></i>
                        Password Hint
                    </label>
                    <input type="text" id="password_hint" name="password_hint" value="Anniversary Date" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
                <a href="index.php" class="back-home">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
