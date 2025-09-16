<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$is_admin = is_admin();

// Get all posts (admin-uploaded content visible to all users)
$query = "SELECT p.*, u.name as author_name, pr.is_read AS has_read
          FROM posts p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN post_reads pr ON pr.post_id = p.id AND pr.user_id = :current_user_id
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':current_user_id', $_SESSION['user_id']);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get random compliment
$compliment = get_random_compliment();

// Handle new post creation (admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    if (!$is_admin) {
        header('Location: how-i-see-her.php?error=unauthorized');
        exit();
    }
    
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $type = sanitize_input($_POST['type']);
    
    $query = "INSERT INTO posts (user_id, title, content, type) VALUES (:user_id, :title, :content, :type)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':type', $type);
    
    if ($stmt->execute()) {
        header('Location: how-i-see-her.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How I See Her - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/masonry-layout@4.2.2/dist/masonry.pkgd.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="how-i-see-her-container">
            <div class="page-header">
                <h1>How I See Her</h1>
                <p>Personal notes, love letters, and all the reasons why she's amazing</p>
            </div>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Note added successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 'unauthorized'): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    You don't have permission to perform this action.
                </div>
            <?php endif; ?>
            
            <!-- Random Compliment Generator -->
            <section class="compliment-section">
                <div class="compliment-card">
                    <div class="compliment-header">
                        <i class="fas fa-heart"></i>
                        <h2>Today's Compliment</h2>
                        <button class="btn btn-secondary" onclick="generateNewCompliment()">
                            <i class="fas fa-refresh"></i>
                            New Compliment
                        </button>
                    </div>
                    <div class="compliment-content">
                        <p id="compliment-text">"<?php echo $compliment; ?>"</p>
                    </div>
                </div>
            </section>
            
            <!-- Add New Post (Admin Only) -->
            <?php if ($is_admin): ?>
            <section class="add-post-section">
                <button class="btn btn-primary" onclick="toggleAddPostForm()">
                    <i class="fas fa-plus"></i>
                    Add New Note
                </button>
                
                <div id="add-post-form" class="add-post-form" style="display: none;">
                    <form method="POST" class="post-form">
                        <input type="hidden" name="action" value="create_post">
                        
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" placeholder="Give your note a title..." required>
                        </div>
                        
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type" required>
                                <option value="note">Personal Note</option>
                                <option value="love_letter">Love Letter</option>
                                <option value="compliment">Compliment</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" rows="6" placeholder="Write your thoughts here..." required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Note
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAddPostForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Posts Masonry Grid -->
            <section class="posts-section">
                <div class="section-header">
                    <h2><i class="fas fa-sticky-note"></i> Notes & Letters</h2>
                    <p>All the beautiful things about her</p>
                </div>
                
                <div class="posts-grid" id="posts-grid">
                    <?php if (empty($posts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-heart-broken"></i>
                            <h3>No notes yet</h3>
                            <p><?php echo $is_admin ? 'Start by adding your first note about her' : 'Notes will appear here once they are added'; ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card post-<?php echo $post['type']; ?>" 
                                 data-id="<?php echo $post['id']; ?>"
                                 data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                 data-content="<?php echo htmlspecialchars($post['content']); ?>"
                                 data-type="<?php echo $post['type']; ?>"
                                 data-author="<?php echo htmlspecialchars($post['author_name']); ?>"
                                 data-date="<?php echo time_ago($post['created_at']); ?>">
                                <div class="post-header">
                                    <h3><?php echo htmlspecialchars($post['title']); ?>
                                        <?php if ($post['type'] === 'love_letter' && (is_null($post['has_read']) || !$post['has_read'])): ?>
                                            <span class="badge badge-unread">Unread</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="post-meta">
                                        <span class="post-type"><?php echo ucfirst(str_replace('_', ' ', $post['type'])); ?></span>
                                        <span class="post-author">by <?php echo htmlspecialchars($post['author_name']); ?></span>
                                        <span class="post-date"><?php echo time_ago($post['created_at']); ?></span>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                </div>
                                <div class="post-expand">
                                    <button class="btn btn-secondary btn-sm" onclick="expandPost(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                        Read More
                                    </button>
                                </div>
                                <?php if ($is_admin): ?>
                                <div class="post-actions">
                                    <button class="btn-icon" onclick="editPost(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" onclick="deletePost(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Post Lightbox for Expanded View -->
    <div id="post-lightbox" class="post-lightbox">
        <div class="post-lightbox-content">
            <span class="post-lightbox-close">&times;</span>
            <div class="post-lightbox-header">
                <h2 id="post-lightbox-title"></h2>
                <div class="post-lightbox-meta">
                    <span id="post-lightbox-type"></span>
                    <span id="post-lightbox-author"></span>
                    <span id="post-lightbox-date"></span>
                </div>
            </div>
            <div class="post-lightbox-body">
                <div id="post-lightbox-content"></div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize masonry layout
        document.addEventListener('DOMContentLoaded', function() {
            const grid = document.getElementById('posts-grid');
            if (grid) {
                new Masonry(grid, {
                    itemSelector: '.post-card',
                    columnWidth: 300,
                    gutter: 20
                });
            }
        });
        
        // Compliment generator
        let compliments = [];
        
        // Load compliments from API
        async function loadCompliments() {
            try {
                const response = await fetch('api/get-compliments.php');
                const data = await response.json();
                compliments = data.compliments || [];
            } catch (error) {
                console.error('Error loading compliments:', error);
                // Fallback to hardcoded compliments
                compliments = [
                    "Your smile could light up the darkest room.",
                    "You have the most beautiful eyes I've ever seen.",
                    "Your laugh is my favorite sound in the world.",
                    "You make everything better just by being you.",
                    "Your kindness touches everyone around you.",
                    "You are incredibly intelligent and wise.",
                    "Your creativity never ceases to amaze me.",
                    "You have the most caring heart.",
                    "Your strength inspires me every day.",
                    "You are absolutely perfect just as you are.",
                    "Your presence makes any place feel like home.",
                    "You have the most beautiful soul I've ever encountered.",
                    "Your love has changed my life in the best way possible.",
                    "You are my greatest adventure and my safest place.",
                    "Your beauty radiates from within and illuminates everything around you."
                ];
            }
        }
        
        function generateNewCompliment() {
            if (compliments.length === 0) {
                // If compliments haven't loaded yet, try to load them
                loadCompliments().then(() => {
                    if (compliments.length > 0) {
                        showRandomCompliment();
                    }
                });
                return;
            }
            showRandomCompliment();
        }
        
        function showRandomCompliment() {
            const complimentText = document.getElementById('compliment-text');
            const randomCompliment = compliments[Math.floor(Math.random() * compliments.length)];
            complimentText.textContent = `"${randomCompliment}"`;
            
            // Add animation
            complimentText.style.transform = 'scale(0.9)';
            setTimeout(() => {
                complimentText.style.transform = 'scale(1)';
            }, 100);
        }
        
        // Load compliments when page loads
        loadCompliments();
        
        // Toggle add post form (admin only)
        function toggleAddPostForm() {
            <?php if ($is_admin): ?>
            const form = document.getElementById('add-post-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            <?php else: ?>
            alert('Only admins can add notes.');
            <?php endif; ?>
        }
        
        // Edit post (placeholder)
        function editPost(postId) {
            alert('Edit functionality coming soon!');
        }
        
        // Delete post (placeholder)
        function deletePost(postId) {
            if (confirm('Are you sure you want to delete this note?')) {
                // Add delete functionality here
                alert('Delete functionality coming soon!');
            }
        }
        
        // Expand post functionality
        async function expandPost(postId) {
            const postCard = document.querySelector(`[data-id="${postId}"]`);
            const lightbox = document.getElementById('post-lightbox');
            
            if (postCard && lightbox) {
                // Populate lightbox with post data
                document.getElementById('post-lightbox-title').textContent = postCard.dataset.title;
                document.getElementById('post-lightbox-type').textContent = postCard.dataset.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                document.getElementById('post-lightbox-author').textContent = `by ${postCard.dataset.author}`;
                document.getElementById('post-lightbox-date').textContent = postCard.dataset.date;
                document.getElementById('post-lightbox-content').innerHTML = postCard.dataset.content.replace(/\n/g, '<br>');
                
                // Show lightbox
                lightbox.style.display = 'flex';

                // Mark love letters as read when opened
                if (postCard.dataset.type === 'love_letter') {
                    try {
                        await fetch('api/mark-post-read.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ post_id: postId })
                        });
                        const badge = postCard.querySelector('.badge-unread');
                        if (badge) badge.remove();
                    } catch (e) { /* ignore */ }
                }
            }
        }
        
        // Close post lightbox
        document.addEventListener('DOMContentLoaded', function() {
            const postLightbox = document.getElementById('post-lightbox');
            const postLightboxClose = document.querySelector('.post-lightbox-close');
            
            if (postLightboxClose) {
                postLightboxClose.addEventListener('click', function() {
                    postLightbox.style.display = 'none';
                });
            }
            
            if (postLightbox) {
                postLightbox.addEventListener('click', function(e) {
                    if (e.target === postLightbox) {
                        postLightbox.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
