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
                    <li><a href="#our-story" class="about-nav-link">Our Story</a></li>
                    <li><a href="#anniversary-letter" class="about-nav-link">Anniversary Letter</a></li>
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
            
            <!-- Our Story Section -->
            <section id="our-story" class="about-section">
                <div class="section-content">
                    <div class="section-header">
                        <h2><i class="fas fa-book-heart"></i> Our Story</h2>
                        <?php if ($is_admin): ?>
                            <button class="btn btn-secondary" onclick="toggleEditForm('our-story')">
                                <i class="fas fa-edit"></i>
                                Edit Story
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="story-content">
                        <?php if ($our_story): ?>
                            <div class="story-text">
                                <?php echo nl2br(htmlspecialchars($our_story['content'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="story-text">
                                <p>Our journey started on a beautiful spring day. What began as a simple conversation quickly turned into something magical. We knew from the start that this was something special.</p>
                                <p>Through the challenges of that year, we found strength in each other. Every video call, every text message, every moment we shared brought us closer together.</p>
                                <p>We supported each other through studies, career changes, and personal growth. Every achievement was celebrated together, every setback was faced as a team.</p>
                                <p>As we continue to grow together, this website serves as a testament to our love and a tool to help us navigate both our relationship and our individual journeys.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_admin): ?>
                    <!-- Edit Form -->
                    <div id="our-story-edit" class="edit-form" style="display: none;">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_content">
                            <input type="hidden" name="content_key" value="our_story">
                            <div class="form-group">
                                <label for="our_story_title">Title</label>
                                <input type="text" id="our_story_title" name="title" value="<?php echo htmlspecialchars($our_story['title'] ?? 'Our Story'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="our_story_content">Story Content</label>
                                <textarea id="our_story_content" name="content" rows="10"><?php echo htmlspecialchars($our_story['content'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Story</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEditForm('our-story')">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Anniversary Letter Section -->
            <section id="anniversary-letter" class="about-section">
                <div class="section-content">
                    <div class="section-header">
                        <h2><i class="fas fa-envelope-heart"></i> Anniversary Letter</h2>
                        <?php if ($is_admin): ?>
                            <button class="btn btn-secondary" onclick="toggleEditForm('anniversary-letter')">
                                <i class="fas fa-edit"></i>
                                Edit Letter
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                    // Check if current date is December 17, 2025 or later
                    $unlock_date = new DateTime('2025-12-17');
                    $current_date = new DateTime();
                    $is_unlocked = $current_date >= $unlock_date;
                    ?>
                    
                    <div class="letter-container">
                        <?php if ($is_unlocked): ?>
                            <div class="letter-content">
                                <div class="letter-date">March 20, 2025</div>
                                <div class="letter-text">
                                    <?php if ($anniversary_letter): ?>
                                        <?php echo nl2br(htmlspecialchars($anniversary_letter['content'])); ?>
                                    <?php else: ?>
                                        <p>My Dearest Love,</p>
                                        
                                        <p>As I sit here writing this letter, I'm filled with overwhelming gratitude for the incredible journey we've shared together. Five years ago, you walked into my life and changed everything in the most beautiful way possible.</p>
                                        
                                        <p>Every day with you is a gift. Your laughter is the melody that brightens my darkest moments, your kindness is the light that guides me through challenges, and your love is the foundation that makes everything possible.</p>
                                        
                                        <p>I created this digital space as a tribute to us - to our memories, our dreams, and our future together. It's a place where I can express my love for you in ways that words alone cannot capture. Every feature, every design choice, every line of code was written with you in mind.</p>
                                        
                                        <p>As you pursue your medical studies, know that I'm here cheering you on every step of the way. Your dedication inspires me, your intelligence amazes me, and your compassion for others fills me with pride.</p>
                                        
                                        <p>Here's to many more years of love, laughter, and adventures together. Here's to our past, our present, and all the beautiful tomorrows we'll share.</p>
                                        
                                        <p>With all my love,</p>
                                        <p class="signature">Your devoted partner</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="letter-locked">
                                <div class="lock-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <h3>This letter is locked</h3>
                                <p>The anniversary letter will be unlocked on <strong>December 17, 2025</strong></p>
                                <div class="countdown-timer">
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="days-remaining"><?php echo $unlock_date->diff($current_date)->days; ?></span>
                                        <span class="countdown-label">Days</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="hours-remaining"><?php echo $unlock_date->diff($current_date)->h; ?></span>
                                        <span class="countdown-label">Hours</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="minutes-remaining"><?php echo $unlock_date->diff($current_date)->i; ?></span>
                                        <span class="countdown-label">Minutes</span>
                                    </div>
                                </div>
                                <p class="unlock-message">Until our special day arrives...</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_admin): ?>
                    <!-- Edit Form -->
                    <div id="anniversary-letter-edit" class="edit-form" style="display: none;">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_content">
                            <input type="hidden" name="content_key" value="anniversary_letter">
                            <div class="form-group">
                                <label for="anniversary_letter_title">Title</label>
                                <input type="text" id="anniversary_letter_title" name="title" value="<?php echo htmlspecialchars($anniversary_letter['title'] ?? 'Anniversary Letter'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="anniversary_letter_content">Letter Content</label>
                                <textarea id="anniversary_letter_content" name="content" rows="15"><?php echo htmlspecialchars($anniversary_letter['content'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Letter</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEditForm('anniversary-letter')">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
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
