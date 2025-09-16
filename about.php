<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$is_admin = is_admin();

// Handle content updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_content') {
        $content_key = sanitize_input($_POST['content_key']);
        $title = sanitize_input($_POST['title']);
        $content = sanitize_input($_POST['content']);
        
        $query = "INSERT INTO site_content (content_key, title, content, updated_by) 
                  VALUES (:content_key, :title, :content, :updated_by)
                  ON DUPLICATE KEY UPDATE 
                  title = VALUES(title), 
                  content = VALUES(content), 
                  updated_by = VALUES(updated_by)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':content_key', $content_key);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':updated_by', $_SESSION['user_id']);
        $stmt->execute();
        
        header('Location: about.php?success=1');
        exit();
    }
}

// Get site content
$query = "SELECT * FROM site_content WHERE content_key IN ('our_story', 'anniversary_letter')";
$stmt = $db->prepare($query);
$stmt->execute();
$content_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$our_story = null;
$anniversary_letter = null;

foreach ($content_data as $content) {
    if ($content['content_key'] === 'our_story') {
        $our_story = $content;
    } elseif ($content['content_key'] === 'anniversary_letter') {
        $anniversary_letter = $content;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="about-container">
            <div class="page-header">
                <h1>About Our Secret Place</h1>
                <p>Discover the story behind this digital sanctuary</p>
            </div>
            
            <!-- Sub-navigation -->
            <nav class="about-nav">
                <ul class="about-nav-list">
                    <li><a href="#website-info" class="about-nav-link active">Website Info</a></li>
                </ul>
            </nav>
            
            <!-- Website Info Section -->
            <section id="website-info" class="about-section active">
                <div class="section-content">
                    <h2><i class="fas fa-info-circle"></i> Website Information</h2>
                    <div class="info-grid">
                        <div class="info-card">
                            <i class="fas fa-heart"></i>
                            <h3>Purpose</h3>
                            <p>This website is a digital space created to celebrate our relationship, store our memories, and provide tools to support both our personal connection and academic journey.</p>
                        </div>
                        
                        <div class="info-card">
                            <i class="fas fa-shield-alt"></i>
                            <h3>Privacy</h3>
                            <p>All your data is securely stored and encrypted. This is our private space, accessible only to us, where we can share our most personal thoughts and memories.</p>
                        </div>
                        
                        <div class="info-card">
                            <i class="fas fa-mobile-alt"></i>
                            <h3>Accessibility</h3>
                            <p>Designed to work seamlessly on all devices - from your phone during study breaks to your laptop for longer sessions. Always accessible, always there.</p>
                        </div>
                        
                        <div class="info-card">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Study Support</h3>
                            <p>Includes specialized tools for medical studies - flashcards, study tracking, lab notebooks, and resource management to help you succeed academically.</p>
                        </div>
                    </div>
                    
                    <div class="features-list">
                        <h3>Key Features</h3>
                        <ul>
                            <li><i class="fas fa-check"></i> Anniversary countdown and relationship timeline</li>
                            <li><i class="fas fa-check"></i> Personal notes and love letters storage</li>
                            <li><i class="fas fa-check"></i> Interactive map of places we've visited</li>
                            <li><i class="fas fa-check"></i> Movies and music playlist management</li>
                            <li><i class="fas fa-check"></i> Study tracker and medical resources</li>
                            <li><i class="fas fa-check"></i> Art wall for creative expressions</li>
                            <li><i class="fas fa-check"></i> Wish jar for future plans</li>
                            <li><i class="fas fa-check"></i> Love letter scheduler</li>
                        </ul>
                    </div>
                </div>
            </section>
            
        </div>
    </main>
    
    <script src="assets/js/main.js"></script>
    <script>
        // About page navigation
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.about-nav-link');
            const sections = document.querySelectorAll('.about-section');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links and sections
                    navLinks.forEach(l => l.classList.remove('active'));
                    sections.forEach(s => s.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);
                    if (targetSection) {
                        targetSection.classList.add('active');
                    }
                });
            });
        });
        
        // Toggle edit forms
        function toggleEditForm(section) {
            const editForm = document.getElementById(section + '-edit');
            if (editForm) {
                editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
            }
        }
        
        // Live countdown timer for locked letter
        function updateCountdown() {
            const unlockDate = new Date('2025-12-17T00:00:00');
            const now = new Date();
            const timeDiff = unlockDate - now;
            
            if (timeDiff > 0) {
                const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                
                const daysEl = document.getElementById('days-remaining');
                const hoursEl = document.getElementById('hours-remaining');
                const minutesEl = document.getElementById('minutes-remaining');
                
                if (daysEl) daysEl.textContent = days;
                if (hoursEl) hoursEl.textContent = hours;
                if (minutesEl) minutesEl.textContent = minutes;
            } else {
                // Letter is unlocked, reload page to show content
                location.reload();
            }
        }
        
        // Update countdown every minute
        setInterval(updateCountdown, 60000);
        
        // Initial countdown update
        updateCountdown();
    </script>
</body>
</html>
