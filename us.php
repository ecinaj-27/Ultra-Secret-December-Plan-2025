<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$is_admin = is_admin();

// Get site content
$query = "SELECT * FROM site_content WHERE content_key IN ('our_story', 'anniversary_letter')";
$stmt = $db->prepare($query);
$stmt->execute();
$site_content = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to associative array
$content = [];
foreach ($site_content as $item) {
    $content[$item['content_key']] = $item;
}

// Handle form submissions for admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_content') {
        $content_key = $_POST['content_key'];
        $title = sanitize_input($_POST['title']);
        $content_text = sanitize_input($_POST['content']);
        
        $query = "UPDATE site_content SET title = :title, content = :content, updated_by = :user_id WHERE content_key = :content_key";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content_text);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':content_key', $content_key);
        $stmt->execute();
        
        header('Location: us.php?updated=1');
        exit();
    }
}

// Get timeline events
$query = "SELECT * FROM timeline_events ORDER BY event_date ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$timeline_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get locations
$query = "SELECT * FROM locations ORDER BY visit_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Us - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="us-container">
            <div class="page-header">
                <h1>Our Story</h1>
                <p>Every moment, every memory, every place we've been together</p>
                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success">Content updated successfully!</div>
                <?php endif; ?>
            </div>
            
            <!-- Admin Content Editing -->
            <?php if ($is_admin): ?>
            <section class="admin-content-section">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Admin Content Management</h2>
                    <p>Edit our story and anniversary letter</p>
                </div>
                
                <div class="content-editing-tabs">
                    <button class="tab-btn active" onclick="switchTab('our-story')">Our Story</button>
                    <button class="tab-btn" onclick="switchTab('anniversary-letter')">Anniversary Letter</button>
                </div>
                
                <!-- Our Story Editor -->
                <div id="our-story-tab" class="content-tab active">
                    <form method="POST" class="content-form">
                        <input type="hidden" name="action" value="update_content">
                        <input type="hidden" name="content_key" value="our_story">
                        <div class="form-group">
                            <label for="story_title">Title</label>
                            <input type="text" id="story_title" name="title" 
                                   value="<?php echo htmlspecialchars($content['our_story']['title'] ?? 'Our Story'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="story_content">Our Story</label>
                            <textarea id="story_content" name="content" rows="10" required><?php echo htmlspecialchars($content['our_story']['content'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Our Story
                        </button>
                    </form>
                </div>
                
                <!-- Anniversary Letter Editor -->
                <div id="anniversary-letter-tab" class="content-tab">
                    <form method="POST" class="content-form">
                        <input type="hidden" name="action" value="update_content">
                        <input type="hidden" name="content_key" value="anniversary_letter">
                        <div class="form-group">
                            <label for="letter_title">Title</label>
                            <input type="text" id="letter_title" name="title" 
                                   value="<?php echo htmlspecialchars($content['anniversary_letter']['title'] ?? 'Anniversary Letter'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="letter_content">Anniversary Letter</label>
                            <textarea id="letter_content" name="content" rows="15" required><?php echo htmlspecialchars($content['anniversary_letter']['content'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Anniversary Letter
                        </button>
                    </form>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Relationship Timeline -->
            <section class="timeline-section">
                <div class="section-header">
                    <h2><i class="fas fa-heart"></i> Our Timeline</h2>
                    <p>Key moments in our journey together</p>
                </div>
                
                <div class="timeline-container">
                    <div class="timeline-line"></div>
                    <div class="timeline-dots">
                        <?php foreach ($timeline_events as $index => $event): ?>
                            <div class="timeline-dot" 
                                 data-date="<?php echo format_date($event['event_date']); ?>"
                                 data-title="<?php echo htmlspecialchars($event['title']); ?>"
                                 data-description="<?php echo htmlspecialchars($event['description'] ?? ''); ?>"
                                 data-caption="<?php echo htmlspecialchars($event['caption'] ?? ''); ?>"
                                 data-image="<?php echo htmlspecialchars($event['image_path'] ?? ''); ?>"
                                 style="left: <?php echo ($index / (count($timeline_events) - 1)) * 100; ?>%">
                                <div class="dot-inner"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            
            <!-- Places We've Been -->
            <section class="map-section">
                <div class="section-header">
                    <h2><i class="fas fa-map-marker-alt"></i> Places We've Been</h2>
                    <p>Click on a location to see our memories there</p>
                </div>
                
                <div class="locations-grid">
                    <?php foreach ($locations as $location): ?>
                        <div class="location-card" 
                             data-name="<?php echo htmlspecialchars($location['name']); ?>"
                             data-description="<?php echo htmlspecialchars($location['description'] ?? ''); ?>"
                             data-caption="<?php echo htmlspecialchars($location['caption'] ?? ''); ?>"
                             data-image="<?php echo htmlspecialchars($location['image_path'] ?? ''); ?>"
                             data-date="<?php echo format_date($location['visit_date']); ?>">
                            <div class="location-image">
                                <?php if ($location['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($location['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($location['name']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="location-info">
                                <h3><?php echo htmlspecialchars($location['name']); ?></h3>
                                <?php if ($location['description']): ?>
                                    <p><?php echo htmlspecialchars($location['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($location['visit_date']): ?>
                                    <div class="visit-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo format_date($location['visit_date']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Memory Stats -->
            <section class="stats-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-pie"></i> Our Journey in Numbers</h2>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($timeline_events); ?></div>
                        <div class="stat-label">Special Moments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($locations); ?></div>
                        <div class="stat-label">Places Visited</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo get_anniversary_countdown(); ?></div>
                        <div class="stat-label">Days Until Anniversary</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">∞</div>
                        <div class="stat-label">Love</div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Polaroid Popup for Timeline -->
    <div id="polaroid-popup" class="polaroid-popup">
        <div class="polaroid-content">
            <span class="polaroid-close">&times;</span>
            <div class="polaroid-frame">
                <div class="polaroid-image">
                    <img id="polaroid-img" src="" alt="">
                </div>
                <div class="polaroid-caption">
                    <div id="polaroid-title"></div>
                    <div id="polaroid-date"></div>
                    <div id="polaroid-description"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lightbox for Location Cards -->
    <div id="location-lightbox" class="location-lightbox">
        <div class="location-lightbox-content">
            <span class="location-lightbox-close">&times;</span>
            <div class="location-lightbox-image">
                <img id="location-lightbox-img" src="" alt="">
            </div>
            <div class="location-lightbox-info">
                <h3 id="location-lightbox-title"></h3>
                <div id="location-lightbox-date"></div>
                <p id="location-lightbox-description"></p>
                <div id="location-lightbox-caption"></div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.content-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Timeline dots functionality
            const timelineDots = document.querySelectorAll('.timeline-dot');
            const polaroidPopup = document.getElementById('polaroid-popup');
            const polaroidClose = document.querySelector('.polaroid-close');
            
            timelineDots.forEach(dot => {
                dot.addEventListener('click', function() {
                    const title = this.dataset.title;
                    const date = this.dataset.date;
                    const description = this.dataset.description;
                    const caption = this.dataset.caption;
                    const image = this.dataset.image;
                    
                    document.getElementById('polaroid-title').textContent = title;
                    document.getElementById('polaroid-date').textContent = date;
                    document.getElementById('polaroid-description').textContent = description;
                    
                    const polaroidImg = document.getElementById('polaroid-img');
                    if (image) {
                        polaroidImg.src = image;
                        polaroidImg.style.display = 'block';
                    } else {
                        polaroidImg.style.display = 'none';
                    }
                    
                    polaroidPopup.style.display = 'flex';
                });
            });
            
            // Close polaroid popup
            polaroidClose.addEventListener('click', function() {
                polaroidPopup.style.display = 'none';
            });
            
            polaroidPopup.addEventListener('click', function(e) {
                if (e.target === polaroidPopup) {
                    polaroidPopup.style.display = 'none';
                }
            });
            
            // Location cards functionality
            const locationCards = document.querySelectorAll('.location-card');
            const locationLightbox = document.getElementById('location-lightbox');
            const locationLightboxClose = document.querySelector('.location-lightbox-close');
            
            locationCards.forEach(card => {
                card.addEventListener('click', function() {
                    const name = this.dataset.name;
                    const description = this.dataset.description;
                    const caption = this.dataset.caption;
                    const image = this.dataset.image;
                    const date = this.dataset.date;
                    
                    document.getElementById('location-lightbox-title').textContent = name;
                    document.getElementById('location-lightbox-description').textContent = description;
                    document.getElementById('location-lightbox-caption').textContent = caption;
                    document.getElementById('location-lightbox-date').textContent = date;
                    
                    const locationImg = document.getElementById('location-lightbox-img');
                    if (image) {
                        locationImg.src = image;
                        locationImg.style.display = 'block';
                    } else {
                        locationImg.style.display = 'none';
                    }
                    
                    locationLightbox.style.display = 'flex';
                });
            });
            
            // Close location lightbox
            locationLightboxClose.addEventListener('click', function() {
                locationLightbox.style.display = 'none';
            });
            
            locationLightbox.addEventListener('click', function(e) {
                if (e.target === locationLightbox) {
                    locationLightbox.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
