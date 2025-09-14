<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$is_admin = is_admin();

// Get art items
$query = "SELECT * FROM art_items ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$art_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle art upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_art') {
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $story = sanitize_input($_POST['story']);
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_path = upload_file($_FILES['image'], 'uploads/art_wall/');
            
            if ($image_path) {
                $query = "INSERT INTO art_items (title, description, image_path, story) VALUES (:title, :description, :image_path, :story)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':image_path', $image_path);
                $stmt->bindParam(':story', $story);
                $stmt->execute();
                
                header('Location: art-wall.php?uploaded=1');
                exit();
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
    <title>Art Wall - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .art-wall-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .upload-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 3rem;
            border: 1px solid #e1e5e9;
        }
        
        .upload-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }
        
        .masonry-grid {
            column-count: 4;
            column-gap: 2rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 1200px) {
            .masonry-grid {
                column-count: 3;
            }
        }
        
        @media (max-width: 768px) {
            .masonry-grid {
                column-count: 2;
            }
        }
        
        @media (max-width: 480px) {
            .masonry-grid {
                column-count: 1;
            }
        }
        
        .art-item {
            break-inside: avoid;
            margin-bottom: 2rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .art-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .art-image {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .art-item:hover .art-image {
            transform: scale(1.05);
        }
        
        .art-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 107, 107, 0.9), rgba(255, 107, 107, 0.7));
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .art-item:hover .art-overlay {
            opacity: 1;
        }
        
        .art-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .art-description {
            font-size: 1rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .art-story {
            font-size: 0.9rem;
            font-style: italic;
            line-height: 1.4;
            max-height: 100px;
            overflow-y: auto;
        }
        
        .art-info {
            padding: 1.5rem;
        }
        
        .art-title-static {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .art-description-static {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
            font-size: 1.1rem;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ccc;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn:hover {
            background: #ff5252;
        }
        
        .btn-primary {
            background: #ff6b6b;
        }
        
        .btn-primary:hover {
            background: #ff5252;
        }
        
        /* Art Popup Styles */
        .art-popup {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .art-popup-content {
            background: white;
            border-radius: 12px;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .art-popup-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            z-index: 10001;
            background: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .art-popup-close:hover {
            color: #ff6b6b;
        }
        
        .art-popup-image {
            width: 100%;
            max-height: 60vh;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
        }
        
        .art-popup-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }
        
        .art-popup-info {
            padding: 2rem;
        }
        
        .art-popup-info h3 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .art-popup-info div:first-of-type {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .art-popup-info p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .art-popup-info div:last-child {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #ff6b6b;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="art-wall-container">
            <div class="page-header">
                <h1><i class="fas fa-palette"></i> Art Wall</h1>
                <p>A digital gallery of creative expressions and heartfelt drawings</p>
                <?php if (isset($_GET['uploaded'])): ?>
                    <div class="alert alert-success">Artwork uploaded successfully!</div>
                <?php endif; ?>
            </div>
            
            <?php if ($is_admin): ?>
            <div class="upload-section">
                <h2><i class="fas fa-plus"></i> Add New Drawing</h2>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="action" value="add_art">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" placeholder="Give your artwork a title..." required>
                    </div>
                    <div class="form-group">
                        <label for="image">Upload Artwork</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Describe your artwork..." rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="story">Sweet Message/Story</label>
                        <textarea id="story" name="story" placeholder="Write a sweet message or story behind this artwork..." rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Add to Art Wall
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="masonry-grid">
                <?php if (empty($art_items)): ?>
                    <div class="empty-state">
                        <i class="fas fa-palette"></i>
                        <h3>No artwork yet</h3>
                        <p><?php echo $is_admin ? 'Upload your first drawing above!' : 'Artwork will appear here once uploaded.'; ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($art_items as $art): ?>
                        <div class="art-item" 
                             data-title="<?php echo htmlspecialchars($art['title']); ?>"
                             data-description="<?php echo htmlspecialchars($art['description'] ?? ''); ?>"
                             data-story="<?php echo htmlspecialchars($art['story'] ?? ''); ?>"
                             data-image="<?php echo htmlspecialchars($art['image_path']); ?>"
                             data-date="<?php echo format_date($art['created_at']); ?>"
                             onclick="showArtPopup(this)">
                            <img src="<?php echo htmlspecialchars($art['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($art['title']); ?>" class="art-image">
                            
                            <div class="art-overlay">
                                <div class="art-title"><?php echo htmlspecialchars($art['title']); ?></div>
                                <?php if ($art['description']): ?>
                                    <div class="art-description"><?php echo htmlspecialchars($art['description']); ?></div>
                                <?php endif; ?>
                                <?php if ($art['story']): ?>
                                    <div class="art-story"><?php echo htmlspecialchars($art['story']); ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="art-info">
                                <div class="art-title-static"><?php echo htmlspecialchars($art['title']); ?></div>
                                <?php if ($art['description']): ?>
                                    <div class="art-description-static"><?php echo htmlspecialchars($art['description']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Art Popup -->
    <div id="art-popup" class="art-popup">
        <div class="art-popup-content">
            <span class="art-popup-close">&times;</span>
            <div class="art-popup-image">
                <img id="art-popup-img" src="" alt="">
            </div>
            <div class="art-popup-info">
                <h3 id="art-popup-title"></h3>
                <div id="art-popup-date"></div>
                <p id="art-popup-description"></p>
                <div id="art-popup-story"></div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Art popup functionality
        function showArtPopup(artItem) {
            const title = artItem.dataset.title;
            const description = artItem.dataset.description;
            const story = artItem.dataset.story;
            const image = artItem.dataset.image;
            const date = artItem.dataset.date;
            
            document.getElementById('art-popup-title').textContent = title;
            document.getElementById('art-popup-description').textContent = description;
            document.getElementById('art-popup-story').textContent = story;
            document.getElementById('art-popup-date').textContent = date;
            
            const popupImg = document.getElementById('art-popup-img');
            popupImg.src = image;
            popupImg.alt = title;
            
            document.getElementById('art-popup').style.display = 'flex';
        }
        
        // Close art popup
        document.querySelector('.art-popup-close').addEventListener('click', function() {
            document.getElementById('art-popup').style.display = 'none';
        });
        
        document.getElementById('art-popup').addEventListener('click', function(e) {
            if (e.target === document.getElementById('art-popup')) {
                document.getElementById('art-popup').style.display = 'none';
            }
        });
    </script>
</body>
</html>
