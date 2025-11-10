<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$is_admin = is_admin();

// Handle admin uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_media') {
        $title = sanitize_input($_POST['title']);
        $type = sanitize_input($_POST['type']);
        $description = sanitize_input($_POST['description']);
        $rating = (int)$_POST['rating'];
        $external_link = sanitize_input($_POST['external_link']);
        // Don't sanitize spotify_embed - it contains HTML/iframe code
        $spotify_embed = $_POST['spotify_embed'] ?? '';
        $spotify_embed = trim($spotify_embed);
        
        // Handle image upload (not required for songs)
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_path = upload_file($_FILES['image'], 'uploads/media/');
        }
        
        // Check if spotify_embed column exists
        $has_spotify_column = false;
        try {
            $check_query = "SHOW COLUMNS FROM media_items LIKE 'spotify_embed'";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute();
            $has_spotify_column = $check_stmt->rowCount() > 0;
        } catch (Exception $e) {
            $has_spotify_column = false;
        }
        
        if ($has_spotify_column) {
            $query = "INSERT INTO media_items (title, type, description, rating, external_link, spotify_embed, image_path) VALUES (:title, :type, :description, :rating, :external_link, :spotify_embed, :image_path)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':external_link', $external_link);
            $stmt->bindParam(':spotify_embed', $spotify_embed);
            $stmt->bindParam(':image_path', $image_path);
        } else {
            $query = "INSERT INTO media_items (title, type, description, rating, external_link, image_path) VALUES (:title, :type, :description, :rating, :external_link, :image_path)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':external_link', $external_link);
            $stmt->bindParam(':image_path', $image_path);
        }
        $stmt->execute();
        
        header('Location: media.php?success=1');
        exit();
    }
    
    if ($action === 'edit_media') {
        $id = (int)($_POST['id'] ?? 0);
        $title = sanitize_input($_POST['title']);
        $type = sanitize_input($_POST['type']);
        $description = sanitize_input($_POST['description']);
        $rating = (int)$_POST['rating'];
        $external_link = sanitize_input($_POST['external_link']);
        // Don't sanitize spotify_embed - it contains HTML/iframe code
        $spotify_embed = $_POST['spotify_embed'] ?? '';
        $spotify_embed = trim($spotify_embed);
        
        // Fetch existing image
        $stmt = $db->prepare("SELECT image_path FROM media_items WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        $image_path = $existing ? $existing['image_path'] : null;
        
        if ($type !== 'song') {
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $new_path = upload_file($_FILES['image'], 'uploads/media/');
                if ($new_path) {
                    if ($image_path) { delete_file_if_exists($image_path); }
                    $image_path = $new_path;
                }
            }
        } else {
            $image_path = null; // songs don't use images
        }
        
        // Check if spotify_embed column exists
        $has_spotify_column = false;
        try {
            $check_query = "SHOW COLUMNS FROM media_items LIKE 'spotify_embed'";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute();
            $has_spotify_column = $check_stmt->rowCount() > 0;
        } catch (Exception $e) {
            $has_spotify_column = false;
        }
        
        if ($has_spotify_column) {
            $query = "UPDATE media_items SET title = :title, type = :type, description = :description, rating = :rating, external_link = :external_link, spotify_embed = :spotify_embed, image_path = :image_path WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':external_link', $external_link);
            $stmt->bindParam(':spotify_embed', $spotify_embed);
            $stmt->bindParam(':image_path', $image_path);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "UPDATE media_items SET title = :title, type = :type, description = :description, rating = :rating, external_link = :external_link, image_path = :image_path WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':external_link', $external_link);
            $stmt->bindParam(':image_path', $image_path);
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
        
        header('Location: media.php?updated=1');
        exit();
    }
    
    if ($action === 'delete_media') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT image_path FROM media_items WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing && $existing['image_path']) {
            delete_file_if_exists($existing['image_path']);
        }
        $stmt = $db->prepare("DELETE FROM media_items WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: media.php?deleted=1');
        exit();
    }
    
    if ($action === 'update_playlist') {
        $playlist_title = sanitize_input($_POST['playlist_title'] ?? 'Our Love Songs');
        $playlist_description = sanitize_input($_POST['playlist_description'] ?? 'Songs that remind us of each other');
        $playlist_link = sanitize_input($_POST['playlist_link'] ?? '');
        
        // Handle playlist image upload
        $playlist_image = null;
        if (isset($_FILES['playlist_image']) && $_FILES['playlist_image']['error'] == 0) {
            $playlist_image = upload_file($_FILES['playlist_image'], 'uploads/media/');
        }
        
        // Store playlist data in site_content table
        // Store link in content field as JSON or separated format
        $playlist_data = json_encode([
            'title' => $playlist_title,
            'description' => $playlist_description,
            'link' => $playlist_link,
            'image' => $playlist_image
        ]);
        
        $query = "INSERT INTO site_content (content_key, title, content, updated_by) 
                  VALUES ('playlist', :title, :content, :user_id)
                  ON DUPLICATE KEY UPDATE 
                  title = VALUES(title), 
                  content = VALUES(content),
                  updated_by = VALUES(updated_by)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $playlist_title);
        $stmt->bindParam(':content', $playlist_data);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        header('Location: media.php?playlist_updated=1');
        exit();
    }
}

// Get playlist data
$playlist_data = null;
$playlist_query = "SELECT * FROM site_content WHERE content_key = 'playlist'";
$playlist_stmt = $db->prepare($playlist_query);
$playlist_stmt->execute();
$playlist_result = $playlist_stmt->fetch(PDO::FETCH_ASSOC);
if ($playlist_result && !empty($playlist_result['content'])) {
    $playlist_data = json_decode($playlist_result['content'], true);
    if (!$playlist_data) {
        // Fallback if not JSON
        $playlist_data = [
            'title' => $playlist_result['title'] ?? 'Our Love Songs',
            'description' => $playlist_result['content'] ?? 'Songs that remind us of each other',
            'link' => '',
            'image' => null
        ];
    }
}

// Get media items - ensure spotify_embed column is included
// First check if column exists, if not, add it
try {
    $check_col = "SHOW COLUMNS FROM media_items LIKE 'spotify_embed'";
    $check_stmt = $db->prepare($check_col);
    $check_stmt->execute();
    if ($check_stmt->rowCount() == 0) {
        // Column doesn't exist, add it
        $alter_query = "ALTER TABLE media_items ADD COLUMN spotify_embed TEXT AFTER external_link";
        $db->exec($alter_query);
    }
} catch (Exception $e) {
    // Column might already exist or table doesn't exist
}

// Get media items
$query = "SELECT * FROM media_items ORDER BY rating DESC, created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$media_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by type
$movies = array_filter($media_items, function($item) { return $item['type'] === 'movie'; });
$songs = array_filter($media_items, function($item) { return $item['type'] === 'song'; });
$series = array_filter($media_items, function($item) { return $item['type'] === 'series'; });
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies & Songs - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="media-container">
            <div class="page-header">
                <h1>Movies & Songs</h1>
                <p>Our favorite films, series, and music to enjoy together</p>
                <?php if ($is_admin): ?>
                    <button class="btn btn-primary" onclick="toggleAdminForm()">
                        <i class="fas fa-plus"></i>
                        Add Media
                    </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['playlist_updated'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Playlist updated successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Media added successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Media updated successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Media deleted successfully!
                </div>
            <?php endif; ?>
            
            <?php if ($is_admin): ?>
            <!-- Admin Upload Form -->
            <div id="admin-form" class="admin-form" style="display: none;">
                <div class="form-container">
                    <h3><i class="fas fa-upload"></i> Add New Media</h3>
                    <form method="POST" enctype="multipart/form-data" class="media-form">
                        <input type="hidden" name="action" value="add_media">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select id="type" name="type" required>
                                    <option value="movie">Movie</option>
                                    <option value="series">Series</option>
                                    <option value="song">Song</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rating">Rating (1-5)</label>
                                <select id="rating" name="rating" required>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="external_link">External Link (Optional)</label>
                                <input type="url" id="external_link" name="external_link" placeholder="https://...">
                            </div>
                        </div>
                        
                        <!-- Spotify Embed Field (only for songs) -->
                        <div class="form-group" id="spotify-embed-group" style="display: none;">
                            <label for="spotify_embed">Spotify Embed Code</label>
                            <textarea id="spotify_embed" name="spotify_embed" rows="4" 
                                placeholder='Paste the full iframe code here, e.g.: &lt;iframe data-testid="embed-iframe" style="border-radius:12px" src="https://open.spotify.com/embed/track/..." width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"&gt;&lt;/iframe&gt;'></textarea>
                            <small class="form-help">
                                <i class="fas fa-info-circle"></i>
                                To get the embed code: Go to Spotify → Right-click on song → Share → Embed → Copy the full iframe code
                            </small>
                        </div>
                        
                        <div class="form-group" id="image-upload-group">
                            <label for="image">Cover Image</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <small class="form-help" id="image-help">
                                Upload a cover image for this media item
                            </small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Add Media
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAdminForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Movies Section -->
            <section class="media-section">
                <div class="section-header">
                    <h2><i class="fas fa-film"></i> Movies We Love</h2>
                    <p>Films that bring us closer together</p>
                </div>
                
                <div class="media-grid">
                    <?php if (empty($movies)): ?>
                        <div class="empty-state">
                            <i class="fas fa-video"></i>
                            <h3>No movies added yet</h3>
                            <p>Add your favorite films to watch together</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($movies as $movie): ?>
                            <div class="media-card polaroid">
                                <div class="polaroid-image">
                                    <?php if ($movie['image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($movie['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-film"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $movie['rating'] ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="polaroid-caption">
                                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                                    <?php if ($movie['description']): ?>
                                        <p><?php echo htmlspecialchars($movie['description']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($movie['external_link']): ?>
                                        <a href="<?php echo htmlspecialchars($movie['external_link']); ?>" 
                                           target="_blank" class="watch-link">
                                            <i class="fas fa-play"></i>
                                            Watch Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_admin): ?>
                                <div class="admin-actions">
                                    <button class="btn-icon" onclick="openEditMedia(<?php echo $movie['id']; ?>, '<?php echo htmlspecialchars($movie['title'], ENT_QUOTES); ?>', 'movie', '<?php echo htmlspecialchars($movie['description'], ENT_QUOTES); ?>', <?php echo (int)$movie['rating']; ?>, '<?php echo htmlspecialchars($movie['external_link'], ENT_QUOTES); ?>', '')"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" onclick="deleteMedia(<?php echo $movie['id']; ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Series Section -->
            <section class="media-section">
                <div class="section-header">
                    <h2><i class="fas fa-tv"></i> Series We Binge</h2>
                    <p>Shows we marathon together</p>
                </div>
                
                <div class="media-grid">
                    <?php if (empty($series)): ?>
                        <div class="empty-state">
                            <i class="fas fa-tv"></i>
                            <h3>No series added yet</h3>
                            <p>Add your favorite shows to binge together</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($series as $show): ?>
                            <div class="media-card polaroid">
                                <div class="polaroid-image">
                                    <?php if ($show['image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($show['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($show['title']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-tv"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $show['rating'] ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="polaroid-caption">
                                    <h3><?php echo htmlspecialchars($show['title']); ?></h3>
                                    <?php if ($show['description']): ?>
                                        <p><?php echo htmlspecialchars($show['description']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($show['external_link']): ?>
                                        <a href="<?php echo htmlspecialchars($show['external_link']); ?>" 
                                           target="_blank" class="watch-link">
                                            <i class="fas fa-play"></i>
                                            Watch Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_admin): ?>
                                <div class="admin-actions">
                                    <button class="btn-icon" onclick="openEditMedia(<?php echo $show['id']; ?>, '<?php echo htmlspecialchars($show['title'], ENT_QUOTES); ?>', 'series', '<?php echo htmlspecialchars($show['description'], ENT_QUOTES); ?>', <?php echo (int)$show['rating']; ?>, '<?php echo htmlspecialchars($show['external_link'], ENT_QUOTES); ?>', '')"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" onclick="deleteMedia(<?php echo $show['id']; ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Music Section -->
            <section class="media-section">
                <div class="section-header">
                    <h2><i class="fas fa-music"></i> Our Playlist</h2>
                    <p>Songs that remind us of each other</p>
                    <?php if ($is_admin): ?>
                        <button class="btn btn-secondary" onclick="togglePlaylistForm()">
                            <i class="fas fa-edit"></i>
                            Edit Playlist
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Embedded Playlist Card -->
                <div class="playlist-card">
                    <div class="playlist-image">
                        <img id="playlist-cover" src="<?php echo !empty($playlist_data['image']) ? htmlspecialchars($playlist_data['image']) : 'assets/images/playlist-default.jpg'; ?>" alt="Our Playlist" onclick="openPlaylist()">
                        <div class="playlist-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <div class="playlist-info">
                        <h3 id="playlist-title"><?php echo htmlspecialchars($playlist_data['title'] ?? 'Our Love Songs'); ?></h3>
                        <p id="playlist-description"><?php echo htmlspecialchars($playlist_data['description'] ?? 'Songs that remind us of each other'); ?></p>
                        <button class="playlist-btn" onclick="openPlaylist()">
                            <i class="fas fa-external-link-alt"></i>
                            Open Playlist
                        </button>
                    </div>
                </div>
                
                <?php if ($is_admin): ?>
                <!-- Playlist Edit Form -->
                <div id="playlist-form" class="playlist-edit-form" style="display: none;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_playlist">
                        <div class="form-group">
                            <label for="playlist_title">Playlist Title</label>
                            <input type="text" id="playlist_title" name="playlist_title" value="<?php echo htmlspecialchars($playlist_data['title'] ?? 'Our Love Songs'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="playlist_description">Description</label>
                            <textarea id="playlist_description" name="playlist_description"><?php echo htmlspecialchars($playlist_data['description'] ?? 'Songs that remind us of each other'); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="playlist_link">Playlist Link</label>
                            <input type="url" id="playlist_link" name="playlist_link" value="<?php echo htmlspecialchars($playlist_data['link'] ?? ''); ?>" placeholder="https://open.spotify.com/playlist/...">
                        </div>
                        <div class="form-group">
                            <label for="playlist_image">Cover Image</label>
                            <input type="file" id="playlist_image" name="playlist_image" accept="image/*">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Playlist</button>
                            <button type="button" class="btn btn-secondary" onclick="togglePlaylistForm()">Cancel</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                
                <div class="playlist-container">
                    <div class="playlist-header">
                        <div class="playlist-info">
                            <h3>Love Songs Playlist</h3>
                            <p><?php echo count($songs); ?> songs that make us smile</p>
                        </div>
                    </div>
                    
                    <div class="spotify-tracks">
                        <?php if (empty($songs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-music"></i>
                                <h3>No songs added yet</h3>
                                <p>Add your favorite songs using Spotify embed codes</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($songs as $song): ?>
                                <div class="spotify-track-card">
                                    <div class="track-header">
                                        <h4><?php echo htmlspecialchars($song['title']); ?></h4>
                                        <div class="track-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $song['rating'] ? 'filled' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($song['description']): ?>
                                        <p class="track-description"><?php echo htmlspecialchars($song['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Get spotify_embed from database - check all possible keys
                                    $embed_code = '';
                                    if (isset($song['spotify_embed']) && $song['spotify_embed'] !== null && $song['spotify_embed'] !== '') {
                                        $embed_code = $song['spotify_embed'];
                                    }
                                    
                                    // Clean and prepare embed code
                                    $embed_code = trim($embed_code);
                                    
                                    if (!empty($embed_code)): ?>
                                        <div class="spotify-embed">
                                            <?php 
                                            // Decode any HTML entities that might have been encoded
                                            $embed_code = html_entity_decode($embed_code, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            
                                            // Strip any slashes that might have been added
                                            $embed_code = stripslashes($embed_code);
                                            
                                            // Check if it's already a complete iframe
                                            if (stripos($embed_code, '<iframe') !== false) {
                                                // It's already a full iframe code - output directly without any escaping
                                                echo $embed_code;
                                            } else if (stripos($embed_code, 'spotify.com') !== false || stripos($embed_code, 'open.spotify.com') !== false) {
                                                // It's a Spotify URL - extract track/album/playlist ID
                                                if (preg_match('/spotify\.com\/(track|album|playlist)\/([a-zA-Z0-9]+)/', $embed_code, $matches)) {
                                                    $type = $matches[1];
                                                    $id = $matches[2];
                                                    // Extract query params if present
                                                    $query_params = '';
                                                    if (preg_match('/\?([^"]+)/', $embed_code, $qmatches)) {
                                                        $query_params = '?' . $qmatches[1];
                                                    }
                                                    echo '<iframe data-testid="embed-iframe" style="border-radius:12px" src="https://open.spotify.com/embed/' . $type . '/' . $id . $query_params . '" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
                                                } else {
                                                    // Use as direct URL
                                                    echo '<iframe data-testid="embed-iframe" style="border-radius:12px" src="' . htmlspecialchars($embed_code, ENT_QUOTES, 'UTF-8') . '" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
                                                }
                                            } else {
                                                // Unknown format - try to create iframe anyway
                                                echo '<iframe data-testid="embed-iframe" style="border-radius:12px" src="' . htmlspecialchars($embed_code, ENT_QUOTES, 'UTF-8') . '" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-embed">
                                            <i class="fas fa-music"></i>
                                            <p>No Spotify embed available</p>
                                            <?php if ($song['external_link']): ?>
                                                <a href="<?php echo htmlspecialchars($song['external_link']); ?>" 
                                                   target="_blank" class="external-link">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    Open in Music App
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($is_admin): ?>
                                    <div class="admin-actions">
                                        <button class="btn-icon" onclick="openEditMedia(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars($song['title'], ENT_QUOTES); ?>', 'song', '<?php echo htmlspecialchars($song['description'], ENT_QUOTES); ?>', <?php echo (int)$song['rating']; ?>, '<?php echo htmlspecialchars($song['external_link'], ENT_QUOTES); ?>', `<?php echo str_replace('`', '\`', $song['spotify_embed'] ?? ''); ?>`)"><i class="fas fa-edit"></i></button>
                                        <button class="btn-icon" onclick="deleteMedia(<?php echo $song['id']; ?>)"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Polaroid hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const polaroids = document.querySelectorAll('.polaroid');
            
            polaroids.forEach(polaroid => {
                polaroid.addEventListener('mouseenter', function() {
                    this.style.transform = 'rotate(0deg) scale(1.05)';
                });
                
                polaroid.addEventListener('mouseleave', function() {
                    this.style.transform = 'rotate(0deg) scale(1)';
                });
            });
        });
        
        // Admin form toggle
        function toggleAdminForm() {
            const form = document.getElementById('admin-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        // Media edit/delete
        function ensureEditModal() {
            if (document.getElementById('editMediaModal')) return;
            const modal = document.createElement('div');
            modal.id = 'editMediaModal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit Media</h3>
                        <span class="close" onclick="closeEditMedia()">&times;</span>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="modal-form">
                        <input type="hidden" name="action" value="edit_media">
                        <input type="hidden" name="id" id="edit_media_id">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" id="edit_media_title" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" id="edit_media_type" required>
                                <option value="movie">Movie</option>
                                <option value="series">Series</option>
                                <option value="song">Song</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="edit_media_description" rows="3"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Rating</label>
                                <select name="rating" id="edit_media_rating" required>
                                    <option value="5">5</option>
                                    <option value="4">4</option>
                                    <option value="3">3</option>
                                    <option value="2">2</option>
                                    <option value="1">1</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>External Link</label>
                                <input type="url" name="external_link" id="edit_media_link">
                            </div>
                        </div>
                        <div class="form-group" id="edit_media_spotify_group" style="display:none;">
                            <label>Spotify Embed Code</label>
                            <textarea name="spotify_embed" id="edit_media_spotify" rows="4"></textarea>
                        </div>
                        <div class="form-group" id="edit_media_image_group">
                            <label>Replace Image</label>
                            <input type="file" name="image" accept="image/*">
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeEditMedia()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>`;
            document.body.appendChild(modal);
        }
        function openEditMedia(id, title, type, description, rating, link, spotify) {
            ensureEditModal();
            document.getElementById('edit_media_id').value = id;
            document.getElementById('edit_media_title').value = title;
            document.getElementById('edit_media_type').value = type;
            document.getElementById('edit_media_description').value = description;
            document.getElementById('edit_media_rating').value = String(rating);
            document.getElementById('edit_media_link').value = link || '';
            const spotifyGroup = document.getElementById('edit_media_spotify_group');
            if (type === 'song') {
                spotifyGroup.style.display = 'block';
                document.getElementById('edit_media_spotify').value = spotify || '';
                document.getElementById('edit_media_image_group').style.display = 'none';
            } else {
                spotifyGroup.style.display = 'none';
                document.getElementById('edit_media_spotify').value = '';
                document.getElementById('edit_media_image_group').style.display = 'block';
            }
            document.getElementById('editMediaModal').style.display = 'block';
        }
        function closeEditMedia() {
            const modal = document.getElementById('editMediaModal');
            if (modal) modal.style.display = 'none';
        }
        function deleteMedia(id) {
            if (!confirm('Delete this media item?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_media">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Playlist form toggle
        function togglePlaylistForm() {
            const form = document.getElementById('playlist-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        // Open playlist
        function openPlaylist() {
            const playlistLink = document.getElementById('playlist_link')?.value || '<?php echo htmlspecialchars($playlist_data['link'] ?? '', ENT_QUOTES); ?>';
            if (playlistLink && playlistLink !== '') {
                window.open(playlistLink, '_blank');
            } else {
                alert('Playlist link not set. Admin can add a link in the edit form.');
            }
        }
        
        // Handle form field visibility based on media type
        function handleMediaTypeChange() {
            const typeSelect = document.getElementById('type');
            const spotifyGroup = document.getElementById('spotify-embed-group');
            const imageGroup = document.getElementById('image-upload-group');
            const imageHelp = document.getElementById('image-help');
            
            if (typeSelect.value === 'song') {
                spotifyGroup.style.display = 'block';
                imageGroup.style.display = 'none';
            } else {
                spotifyGroup.style.display = 'none';
                imageGroup.style.display = 'block';
                imageHelp.textContent = 'Upload a cover image for this media item';
            }
        }
        
        // Initialize form on page load
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                typeSelect.addEventListener('change', handleMediaTypeChange);
                handleMediaTypeChange(); // Set initial state
            }
        });
        
        // Playlist functionality
        function playAll() {
            alert('Play all functionality would integrate with your preferred music service!');
        }
        
        function playSong(songTitle) {
            alert(`Playing: ${songTitle}`);
        }
    </script>
</body>
</html>
