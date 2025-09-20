<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $passcode = $_POST['passcode'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($passcode, $user['passcode'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        header('Location: home.php');
        exit();
    } else {
        $error_message = 'Invalid username or passcode';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/passcode.css">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="passcode-body">
    <div class="passcode-container">
        <!-- Background blur effect -->
        <div class="background-blur"></div>
        
        <!-- Main content -->
        <div class="passcode-content">
            <!-- Username Selection -->
            <div class="username-section" id="username-section">
                <h2>Select User</h2>
                <div class="user-list" id="user-list">
                    <!-- Users will be loaded here via JavaScript -->
                </div>
                <div class="username-input">
                    <input type="text" id="username-input" placeholder="Enter username" style="display: none;">
                    <button type="button" class="btn-other-user" onclick="showUsernameInput()">
                        <i class="fas fa-user-plus"></i>
                        Other User
                    </button>
                </div>
            </div>
            
            <!-- Passcode Entry -->
            <div class="passcode-section" id="passcode-section" style="display: none;">
                <div class="user-info">
                    <div class="user-avatar">
                        <img id="selected-user-avatar" src="" alt="User">
                    </div>
                    <h2 id="selected-username">Username</h2>
                    <button type="button" class="btn-change-user" onclick="changeUser()">
                        <i class="fas fa-arrow-left"></i>
                        Change User
                    </button>
                </div>
                
                <div class="passcode-display">
                    <div class="passcode-dots">
                        <div class="dot" id="dot-1"></div>
                        <div class="dot" id="dot-2"></div>
                        <div class="dot" id="dot-3"></div>
                        <div class="dot" id="dot-4"></div>
                        <div class="dot" id="dot-5"></div>
                        <div class="dot" id="dot-6"></div>
                    </div>
                </div>
                
                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="passcode-hint">
                    <i class="fas fa-lightbulb"></i>
                    <span id="passcode-hint-text">Hint: Anniversary Date</span>
                </div>
            </div>
            
            <!-- Keypad -->
            <div class="keypad" id="keypad">
                <div class="keypad-row">
                    <button class="key" data-number="1">
                        <span class="number">1</span>
                    </button>
                    <button class="key" data-number="2">
                        <span class="number">2</span>
                        <span class="letters">ABC</span>
                    </button>
                    <button class="key" data-number="3">
                        <span class="number">3</span>
                        <span class="letters">DEF</span>
                    </button>
                </div>
                <div class="keypad-row">
                    <button class="key" data-number="4">
                        <span class="number">4</span>
                        <span class="letters">GHI</span>
                    </button>
                    <button class="key" data-number="5">
                        <span class="number">5</span>
                        <span class="letters">JKL</span>
                    </button>
                    <button class="key" data-number="6">
                        <span class="number">6</span>
                        <span class="letters">MNO</span>
                    </button>
                </div>
                <div class="keypad-row">
                    <button class="key" data-number="7">
                        <span class="number">7</span>
                        <span class="letters">PQRS</span>
                    </button>
                    <button class="key" data-number="8">
                        <span class="number">8</span>
                        <span class="letters">TUV</span>
                    </button>
                    <button class="key" data-number="9">
                        <span class="number">9</span>
                        <span class="letters">WXYZ</span>
                    </button>
                </div>
                <div class="keypad-row">
                    <button class="key key-empty"></button>
                    <button class="key" data-number="0">
                        <span class="number">0</span>
                    </button>
                    <button class="key key-delete" id="delete-key">
                        <i class="fas fa-delete-left"></i>
                    </button>
                </div>
            </div>
            
            <!-- Alternative Login -->
            <div class="alternative-login">
                <a href="index.php" class="alt-login-link">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <!-- Hidden form for submission -->
    <form id="login-form" method="POST" style="display: none;">
        <input type="hidden" id="form-username" name="username">
        <input type="hidden" id="form-passcode" name="passcode">
    </form>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="assets/js/passcode.js"></script>
</body>
</html>