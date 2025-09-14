<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect to home if already logged in
if (is_logged_in()) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="landing-page">
    <div class="landing-container">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-heart"></i>
                <h1>Our Secret Place</h1>
                <p>A digital space for us</p>
            </div>
        </div>
        
        <div class="auth-buttons">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i>
                Register
            </a>
        </div>
        
        <div class="landing-features">
            <div class="feature">
                <i class="fas fa-calendar-heart"></i>
                <h3>Anniversary Tracker</h3>
                <p>Never miss a special moment</p>
            </div>
            <div class="feature">
                <i class="fas fa-images"></i>
                <h3>Memory Lane</h3>
                <p>Cherish every moment together</p>
            </div>
            <div class="feature">
                <i class="fas fa-graduation-cap"></i>
                <h3>Study Buddy</h3>
                <p>Track your medical studies</p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
