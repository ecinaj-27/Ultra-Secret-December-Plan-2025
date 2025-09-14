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
        $spotify_embed = $_POST['spotify_embed'] ?? '';
        
        // Handle image upload (not required for songs)
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_path = upload_file($_FILES['image'], 'uploads/media/');
        }
        
        $query = "INSERT INTO media_items (title, type, description, rating, external_link, spotify_embed, image_path) VALUES (:title, :type, :description, :rating, :external_link, :spotify_embed, :image_path)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':external_link', $external_link);
        $stmt->bindParam(':spotify_embed', $spotify_embed);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->execute();
        
        header('Location: media.php?success=1');
        exit();
    }
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
                                placeholder="Paste the Spotify embed code here..."></textarea>
                            <small class="form-help">
                                <i class="fas fa-info-circle"></i>
                                To get the embed code: Go to Spotify → Right-click on song → Share → Embed → Copy the code
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
                        <img id="playlist-cover" src="assets/images/playlist-default.jpg" alt="Our Playlist" onclick="openPlaylist()">
                        <div class="playlist-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <div class="playlist-info">
                        <h3 id="playlist-title">Our Love Songs</h3>
                        <p id="playlist-description">Songs that remind us of each other</p>
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
                            <input type="text" id="playlist_title" name="playlist_title" value="Our Love Songs">
                        </div>
                        <div class="form-group">
                            <label for="playlist_description">Description</label>
                            <textarea id="playlist_description" name="playlist_description">Songs that remind us of each other</textarea>
                        </div>
                        <div class="form-group">
                            <label for="playlist_link">Playlist Link</label>
                            <input type="url" id="playlist_link" name="playlist_link" placeholder="https://open.spotify.com/playlist/...">
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
                                    
                                    <?php if ($song['spotify_embed']): ?>
                                        <div class="spotify-embed">
                                            <?php echo $song['spotify_embed']; ?>
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
        
        // Playlist form toggle
        function togglePlaylistForm() {
            const form = document.getElementById('playlist-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        // Open playlist
        function openPlaylist() {
            const playlistLink = document.getElementById('playlist-link')?.value || '#';
            if (playlistLink && playlistLink !== '#') {
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
