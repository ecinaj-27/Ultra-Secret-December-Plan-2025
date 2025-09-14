<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$is_admin = is_admin();

// Get user's to-do items
$query = "SELECT * FROM todo_items WHERE user_id = :user_id ORDER BY position_order ASC, created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$todo_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get scheduled letters (admin only)
$scheduled_letters = [];
if ($is_admin) {
    $query = "SELECT * FROM scheduled_letters ORDER BY scheduled_date ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $scheduled_letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get art items
$query = "SELECT * FROM art_items ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$art_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get wish jar items
$query = "SELECT * FROM wish_items WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$wish_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get study entries
$query = "SELECT * FROM study_entries WHERE user_id = :user_id ORDER BY entry_date DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$study_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get flashcards
$query = "SELECT * FROM flashcards WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$flashcards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get timeline events and locations for admin
$timeline_events = [];
$locations = [];
$custom_compliments = [];
$custom_inspirations = [];
if ($is_admin) {
    $query = "SELECT * FROM timeline_events ORDER BY event_date ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $timeline_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $query = "SELECT * FROM locations ORDER BY visit_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get custom content
    $custom_compliments = get_all_compliments();
    $custom_inspirations = get_all_inspirations();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_todo':
            $title = sanitize_input($_POST['title']);
            $description = sanitize_input($_POST['description']);
            $category = sanitize_input($_POST['category']);
            $due_date = $_POST['due_date'];
            
            $query = "INSERT INTO todo_items (user_id, title, description, category, due_date) VALUES (:user_id, :title, :description, :category, :due_date)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':due_date', $due_date);
            $stmt->execute();
            break;
            
        case 'add_wish':
            $title = sanitize_input($_POST['title']);
            $description = sanitize_input($_POST['description']);
            $category = sanitize_input($_POST['category']);
            
            $query = "INSERT INTO wish_items (user_id, title, description, category) VALUES (:user_id, :title, :description, :category)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->execute();
            break;
            
        case 'add_study_entry':
            $subject = sanitize_input($_POST['subject']);
            $hours = $_POST['hours'];
            $task_description = sanitize_input($_POST['task_description']);
            $entry_date = $_POST['entry_date'];
            
            $query = "INSERT INTO study_entries (user_id, subject, hours_studied, task_description, entry_date) VALUES (:user_id, :subject, :hours, :description, :entry_date)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':hours', $hours);
            $stmt->bindParam(':description', $task_description);
            $stmt->bindParam(':entry_date', $entry_date);
            $stmt->execute();
            break;
            
        case 'add_timeline_event':
            if ($is_admin) {
                $title = sanitize_input($_POST['title']);
                $description = sanitize_input($_POST['description']);
                $event_date = $_POST['event_date'];
                $caption = sanitize_input($_POST['caption']);
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_path = upload_file($_FILES['image'], 'uploads/timeline/');
                }
                
                $query = "INSERT INTO timeline_events (title, description, event_date, caption, image_path) VALUES (:title, :description, :event_date, :caption, :image_path)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':event_date', $event_date);
                $stmt->bindParam(':caption', $caption);
                $stmt->bindParam(':image_path', $image_path);
                $stmt->execute();
            }
            break;
            
        case 'add_location':
            if ($is_admin) {
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $caption = sanitize_input($_POST['caption']);
                $latitude = $_POST['latitude'];
                $longitude = $_POST['longitude'];
                $visit_date = $_POST['visit_date'];
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_path = upload_file($_FILES['image'], 'uploads/locations/');
                }
                
                $query = "INSERT INTO locations (name, description, caption, latitude, longitude, visit_date, image_path) VALUES (:name, :description, :caption, :latitude, :longitude, :visit_date, :image_path)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':caption', $caption);
                $stmt->bindParam(':latitude', $latitude);
                $stmt->bindParam(':longitude', $longitude);
                $stmt->bindParam(':visit_date', $visit_date);
                $stmt->bindParam(':image_path', $image_path);
                $stmt->execute();
            }
            break;
            
        case 'add_love_letter':
            if ($is_admin) {
                $title = sanitize_input($_POST['title']);
                $content = sanitize_input($_POST['content']);
                $scheduled_date = $_POST['scheduled_date'];
                
                $query = "INSERT INTO love_letters (title, content, scheduled_date, created_by) VALUES (:title, :content, :scheduled_date, :created_by)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':scheduled_date', $scheduled_date);
                $stmt->bindParam(':created_by', $_SESSION['user_id']);
                $stmt->execute();
            }
            break;
            
        case 'add_art':
            if ($is_admin) {
                $title = sanitize_input($_POST['title']);
                $description = sanitize_input($_POST['description']);
                $story = sanitize_input($_POST['story']);
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_path = upload_file($_FILES['image'], 'uploads/art_wall/');
                }
                
                if ($image_path) {
                    $query = "INSERT INTO art_items (title, description, image_path, story) VALUES (:title, :description, :image_path, :story)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':image_path', $image_path);
                    $stmt->bindParam(':story', $story);
                    $stmt->execute();
                }
            }
            break;
            
        case 'toggle_todo':
            $todo_id = $_POST['todo_id'];
            $query = "UPDATE todo_items SET is_completed = NOT is_completed WHERE id = :todo_id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':todo_id', $todo_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            break;
            
        case 'delete_todo':
            $todo_id = $_POST['todo_id'];
            $query = "DELETE FROM todo_items WHERE id = :todo_id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':todo_id', $todo_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            break;
            
        // Custom content management (admin only)
        case 'add_custom_compliment':
            if ($is_admin) {
                $content = sanitize_input($_POST['content']);
                add_custom_compliment($content, $_SESSION['user_id']);
            }
            break;
            
        case 'add_custom_inspiration':
            if ($is_admin) {
                $content = sanitize_input($_POST['content']);
                add_custom_inspiration($content, $_SESSION['user_id']);
            }
            break;
            
        case 'toggle_compliment':
            if ($is_admin) {
                $id = $_POST['id'];
                $is_active = $_POST['is_active'] === '1';
                toggle_compliment_status($id, $is_active);
            }
            break;
            
        case 'toggle_inspiration':
            if ($is_admin) {
                $id = $_POST['id'];
                $is_active = $_POST['is_active'] === '1';
                toggle_inspiration_status($id, $is_active);
            }
            break;
            
        case 'delete_compliment':
            if ($is_admin) {
                $id = $_POST['id'];
                delete_custom_compliment($id);
            }
            break;
            
        case 'delete_inspiration':
            if ($is_admin) {
                $id = $_POST['id'];
                delete_custom_inspiration($id);
            }
            break;
            
        // Flashcard management
        case 'add_flashcard':
            $front_text = sanitize_input($_POST['front_text']);
            $back_text = sanitize_input($_POST['back_text']);
            $category = sanitize_input($_POST['category']);
            $difficulty = sanitize_input($_POST['difficulty']);
            
            $query = "INSERT INTO flashcards (user_id, front_text, back_text, category, difficulty) VALUES (:user_id, :front_text, :back_text, :category, :difficulty)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':front_text', $front_text);
            $stmt->bindParam(':back_text', $back_text);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':difficulty', $difficulty);
            $stmt->execute();
            break;
            
        case 'edit_flashcard':
            $id = $_POST['id'];
            $front_text = sanitize_input($_POST['front_text']);
            $back_text = sanitize_input($_POST['back_text']);
            $category = sanitize_input($_POST['category']);
            $difficulty = sanitize_input($_POST['difficulty']);
            
            $query = "UPDATE flashcards SET front_text = :front_text, back_text = :back_text, category = :category, difficulty = :difficulty WHERE id = :id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':front_text', $front_text);
            $stmt->bindParam(':back_text', $back_text);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':difficulty', $difficulty);
            $stmt->execute();
            break;
            
        case 'delete_flashcard':
            $id = $_POST['id'];
            $query = "DELETE FROM flashcards WHERE id = :id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            break;
    }
    
    header('Location: tools.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="tools-container">
            <div class="page-header">
                <h1>Tools & Utilities</h1>
                <p>Everything you need to manage our relationship and your studies</p>
            </div>
            
            <!-- Admin Tools Section -->
            <?php if ($is_admin): ?>
            <section class="admin-tools">
                <h2><i class="fas fa-crown"></i> Admin Tools</h2>
                
                <!-- Love Letter Scheduler -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-envelope-heart"></i> Love Letter Scheduler</h3>
                        <p>Schedule love letters to be delivered on special dates</p>
                    </div>
                    <div class="tool-content">
                        <div class="letter-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_love_letter">
                                <div class="form-row">
                                    <input type="text" name="title" placeholder="Letter title..." required>
                                    <input type="date" name="scheduled_date" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Schedule Letter
                                    </button>
                                </div>
                                <textarea name="content" placeholder="Write your love letter here..." rows="6" required></textarea>
                            </form>
                        </div>
                        
                        <div class="scheduled-letters">
                            <h4>Scheduled Letters</h4>
                            <?php if (empty($scheduled_letters)): ?>
                                <p class="empty-state">No letters scheduled</p>
                            <?php else: ?>
                                <?php foreach ($scheduled_letters as $letter): ?>
                                    <div class="letter-item">
                                        <div class="letter-info">
                                            <h5><?php echo htmlspecialchars($letter['title']); ?></h5>
                                            <p class="letter-date">Scheduled for: <?php echo format_date($letter['scheduled_date']); ?></p>
                                            <p class="letter-preview"><?php echo htmlspecialchars(substr($letter['content'], 0, 100)) . '...'; ?></p>
                                        </div>
                                        <div class="letter-status">
                                            <?php if ($letter['is_sent']): ?>
                                                <span class="status sent">Sent</span>
                                            <?php else: ?>
                                                <span class="status pending">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline Management -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-heart"></i> Timeline Events</h3>
                        <p>Manage relationship timeline events and photos</p>
                    </div>
                    <div class="tool-content">
                        <div class="timeline-form">
                            <form method="POST" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="action" value="add_timeline_event">
                                <div class="form-row">
                                    <input type="text" name="title" placeholder="Event title..." required>
                                    <input type="date" name="event_date" required>
                                    <input type="file" name="image" accept="image/*">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Event
                                    </button>
                                </div>
                                <div class="form-row">
                                    <textarea name="description" placeholder="Event description..."></textarea>
                                    <textarea name="caption" placeholder="Polaroid caption..."></textarea>
                                </div>
                            </form>
                        </div>
                        
                        <div class="timeline-list">
                            <h4>Timeline Events</h4>
                            <?php if (empty($timeline_events)): ?>
                                <p class="empty-state">No timeline events yet. Add your first event above!</p>
                            <?php else: ?>
                                <?php foreach ($timeline_events as $event): ?>
                                    <div class="timeline-item-admin">
                                        <div class="timeline-preview">
                                            <?php if ($event['image_path']): ?>
                                                <img src="<?php echo htmlspecialchars($event['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($event['title']); ?>">
                                            <?php else: ?>
                                                <div class="placeholder-image">
                                                    <i class="fas fa-heart"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="timeline-info">
                                            <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                                            <p class="timeline-date"><?php echo format_date($event['event_date']); ?></p>
                                            <?php if ($event['description']): ?>
                                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                            <?php endif; ?>
                                            <?php if (isset($event['caption']) && $event['caption']): ?>
                                                <p class="timeline-caption"><em><?php echo htmlspecialchars($event['caption']); ?></em></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="timeline-actions">
                                            <button class="btn-icon" onclick="editTimelineEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon" onclick="deleteTimelineEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Location Management -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Locations</h3>
                        <p>Manage places you've visited together</p>
                    </div>
                    <div class="tool-content">
                        <div class="location-form">
                            <form method="POST" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="action" value="add_location">
                                <div class="form-row">
                                    <input type="text" name="name" placeholder="Location name..." required>
                                    <input type="date" name="visit_date" required>
                                    <input type="file" name="image" accept="image/*">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Location
                                    </button>
                                </div>
                                <div class="form-row">
                                    <input type="text" name="latitude" placeholder="Latitude (optional)">
                                    <input type="text" name="longitude" placeholder="Longitude (optional)">
                                </div>
                                <div class="form-row">
                                    <textarea name="description" placeholder="Location description..."></textarea>
                                    <textarea name="caption" placeholder="Memory caption..."></textarea>
                                </div>
                            </form>
                        </div>
                        
                        <div class="location-list">
                            <h4>Locations</h4>
                            <?php if (empty($locations)): ?>
                                <p class="empty-state">No locations yet. Add your first location above!</p>
                            <?php else: ?>
                                <?php foreach ($locations as $location): ?>
                                    <div class="location-item-admin">
                                        <div class="location-preview">
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
                                            <h5><?php echo htmlspecialchars($location['name']); ?></h5>
                                            <p class="location-date"><?php echo format_date($location['visit_date']); ?></p>
                                            <?php if ($location['description']): ?>
                                                <p><?php echo htmlspecialchars($location['description']); ?></p>
                                            <?php endif; ?>
                                            <?php if (isset($location['caption']) && $location['caption']): ?>
                                                <p class="location-caption"><em><?php echo htmlspecialchars($location['caption']); ?></em></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="location-actions">
                                            <button class="btn-icon" onclick="editLocation(<?php echo $location['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon" onclick="deleteLocation(<?php echo $location['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Content Management -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-heart"></i> Custom Compliments</h3>
                        <p>Manage personalized compliments for the "How I See Her" page</p>
                    </div>
                    <div class="tool-content">
                        <div class="content-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_custom_compliment">
                                <div class="form-row">
                                    <textarea name="content" placeholder="Add a new compliment..." required></textarea>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Compliment
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="content-list">
                            <h4>Compliments</h4>
                            <?php if (empty($custom_compliments)): ?>
                                <p class="empty-state">No custom compliments yet. Add your first one above!</p>
                            <?php else: ?>
                                <?php foreach ($custom_compliments as $compliment): ?>
                                    <div class="content-item">
                                        <div class="content-text">
                                            <p><?php echo htmlspecialchars($compliment['content']); ?></p>
                                            <small class="content-meta">
                                                Added <?php echo time_ago($compliment['created_at']); ?>
                                                <?php if ($compliment['is_active']): ?>
                                                    <span class="status active">Active</span>
                                                <?php else: ?>
                                                    <span class="status inactive">Inactive</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="content-actions">
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="action" value="toggle_compliment">
                                                <input type="hidden" name="id" value="<?php echo $compliment['id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $compliment['is_active'] ? '0' : '1'; ?>">
                                                <button type="submit" class="btn-icon" title="<?php echo $compliment['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $compliment['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this compliment?')">
                                                <input type="hidden" name="action" value="delete_compliment">
                                                <input type="hidden" name="id" value="<?php echo $compliment['id']; ?>">
                                                <button type="submit" class="btn-icon" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Daily Inspirations Management -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-quote-left"></i> Daily Inspirations</h3>
                        <p>Manage motivational quotes for the home page</p>
                    </div>
                    <div class="tool-content">
                        <div class="content-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_custom_inspiration">
                                <div class="form-row">
                                    <textarea name="content" placeholder="Add a new daily inspiration..." required></textarea>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Inspiration
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="content-list">
                            <h4>Daily Inspirations</h4>
                            <?php if (empty($custom_inspirations)): ?>
                                <p class="empty-state">No custom inspirations yet. Add your first one above!</p>
                            <?php else: ?>
                                <?php foreach ($custom_inspirations as $inspiration): ?>
                                    <div class="content-item">
                                        <div class="content-text">
                                            <p><?php echo htmlspecialchars($inspiration['content']); ?></p>
                                            <small class="content-meta">
                                                Added <?php echo time_ago($inspiration['created_at']); ?>
                                                <?php if ($inspiration['is_active']): ?>
                                                    <span class="status active">Active</span>
                                                <?php else: ?>
                                                    <span class="status inactive">Inactive</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="content-actions">
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="action" value="toggle_inspiration">
                                                <input type="hidden" name="id" value="<?php echo $inspiration['id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $inspiration['is_active'] ? '0' : '1'; ?>">
                                                <button type="submit" class="btn-icon" title="<?php echo $inspiration['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $inspiration['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this inspiration?')">
                                                <input type="hidden" name="action" value="delete_inspiration">
                                                <input type="hidden" name="id" value="<?php echo $inspiration['id']; ?>">
                                                <button type="submit" class="btn-icon" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>
            
            
            <!-- Art Wall -->
            <section class="art-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-palette"></i> Art Wall</h3>
                        <p>Digital gallery of creative expressions</p>
                        <a href="art-wall.php" class="view-all">View Full Gallery</a>
                    </div>
                    <div class="tool-content">
                        <?php if ($is_admin): ?>
                        <div class="art-form">
                            <form method="POST" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="action" value="add_art">
                                <div class="form-row">
                                    <input type="text" name="title" placeholder="Artwork title..." required>
                                    <input type="file" name="image" accept="image/*" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Artwork
                                    </button>
                                </div>
                                <div class="form-row">
                                    <textarea name="description" placeholder="Description..."></textarea>
                                    <textarea name="story" placeholder="Sweet message or story..."></textarea>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <div class="art-grid">
                            <?php if (empty($art_items)): ?>
                                <p class="empty-state">No artwork yet. <?php echo $is_admin ? 'Upload your first piece above!' : 'Artwork will appear here once uploaded.'; ?></p>
                            <?php else: ?>
                                <?php foreach (array_slice($art_items, 0, 6) as $art): ?>
                                    <div class="art-item">
                                        <div class="art-image">
                                            <?php if ($art['image_path']): ?>
                                                <img src="<?php echo htmlspecialchars($art['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($art['title']); ?>">
                                            <?php else: ?>
                                                <div class="placeholder-image">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="art-info">
                                            <h4><?php echo htmlspecialchars($art['title']); ?></h4>
                                            <?php if ($art['description']): ?>
                                                <p><?php echo htmlspecialchars($art['description']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($art['story']): ?>
                                                <div class="art-story">
                                                    <i class="fas fa-quote-left"></i>
                                                    <p><?php echo htmlspecialchars($art['story']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($art_items) > 6): ?>
                                    <div class="view-more">
                                        <a href="art-wall.php" class="btn btn-secondary">
                                            <i class="fas fa-eye"></i>
                                            View All Artwork
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- MedBio Study Tools -->
            <section class="study-section">
                <h2><i class="fas fa-graduation-cap"></i> MedBio Study Tools</h2>
                
                <!-- Study Tracker -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-chart-line"></i> Study Tracker</h3>
                        <p>Track your study hours and progress</p>
                    </div>
                    <div class="tool-content">
                        <div class="study-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_study_entry">
                                <div class="form-row">
                                    <input type="text" name="subject" placeholder="Subject (e.g., Biochemistry)" required>
                                    <input type="number" name="hours" step="0.5" placeholder="Hours studied" required>
                                    <input type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Log Study
                                    </button>
                                </div>
                                <textarea name="task_description" placeholder="What did you study? (optional)"></textarea>
                            </form>
                        </div>
                        
                        <div class="study-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo array_sum(array_column($study_entries, 'hours_studied')); ?></div>
                                <div class="stat-label">Total Hours</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($study_entries); ?></div>
                                <div class="stat-label">Study Sessions</div>
                            </div>
                        </div>
                        
                        <div class="recent-studies">
                            <h4>Recent Study Sessions</h4>
                            <?php if (empty($study_entries)): ?>
                                <p class="empty-state">No study sessions logged yet</p>
                            <?php else: ?>
                                <?php foreach ($study_entries as $entry): ?>
                                    <div class="study-entry">
                                        <div class="entry-info">
                                            <h5><?php echo htmlspecialchars($entry['subject']); ?></h5>
                                            <p><?php echo $entry['hours_studied']; ?> hours on <?php echo format_date($entry['entry_date']); ?></p>
                                            <?php if ($entry['task_description']): ?>
                                                <p class="entry-description"><?php echo htmlspecialchars($entry['task_description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Flashcards -->
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-cards-blank"></i> Flashcard Generator</h3>
                        <p>Create and review medical flashcards</p>
                    </div>
                    <div class="tool-content">
                        <!-- Add Flashcard Form -->
                        <div class="flashcard-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_flashcard">
                                <div class="form-row">
                                    <input type="text" name="front_text" placeholder="Front (e.g., What is ATP?)" required>
                                    <input type="text" name="back_text" placeholder="Back (e.g., Adenosine Triphosphate)" required>
                                </div>
                                <div class="form-row">
                                    <input type="text" name="category" placeholder="Category (e.g., Biochemistry)" required>
                                    <select name="difficulty" required>
                                        <option value="Easy">Easy</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="Hard">Hard</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Card
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="flashcard-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($flashcards); ?></div>
                                <div class="stat-label">Total Cards</div>
                            </div>
                        </div>
                        
                        <?php if (empty($flashcards)): ?>
                            <p class="empty-state">No flashcards created yet. Start creating your medical study cards!</p>
                        <?php else: ?>
                            <div class="flashcard-grid">
                                <?php foreach ($flashcards as $card): ?>
                                    <div class="flashcard-container">
                                        <div class="flashcard" onclick="flipCard(this)">
                                            <div class="card-front">
                                                <h4><?php echo htmlspecialchars($card['front_text']); ?></h4>
                                                <div class="card-category"><?php echo htmlspecialchars($card['category']); ?></div>
                                            </div>
                                            <div class="card-back">
                                                <h4><?php echo htmlspecialchars($card['back_text']); ?></h4>
                                                <div class="card-difficulty"><?php echo htmlspecialchars($card['difficulty']); ?></div>
                                            </div>
                                        </div>
                                        <div class="flashcard-actions">
                                            <button class="btn-icon" onclick="editFlashcard(<?php echo $card['id']; ?>, '<?php echo htmlspecialchars($card['front_text'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($card['back_text'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($card['category'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($card['difficulty'], ENT_QUOTES); ?>')" title="Edit Card">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon" onclick="deleteFlashcard(<?php echo $card['id']; ?>)" title="Delete Card">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Flashcard flip functionality
        function flipCard(card) {
            card.classList.toggle('flipped');
        }
        
        // Flashcard management functions
        function editFlashcard(id, frontText, backText, category, difficulty) {
            const modal = document.getElementById('editFlashcardModal');
            if (!modal) {
                createEditModal();
            }
            
            document.getElementById('edit_card_id').value = id;
            document.getElementById('edit_front_text').value = frontText;
            document.getElementById('edit_back_text').value = backText;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_difficulty').value = difficulty;
            
            document.getElementById('editFlashcardModal').style.display = 'block';
        }
        
        function deleteFlashcard(id) {
            if (confirm('Are you sure you want to delete this flashcard?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_flashcard">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function createEditModal() {
            const modal = document.createElement('div');
            modal.id = 'editFlashcardModal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit Flashcard</h3>
                        <span class="close" onclick="closeEditModal()">&times;</span>
                    </div>
                    <form method="POST" class="modal-form">
                        <input type="hidden" name="action" value="edit_flashcard">
                        <input type="hidden" name="id" id="edit_card_id">
                        <div class="form-group">
                            <label>Front Text:</label>
                            <input type="text" name="front_text" id="edit_front_text" required>
                        </div>
                        <div class="form-group">
                            <label>Back Text:</label>
                            <input type="text" name="back_text" id="edit_back_text" required>
                        </div>
                        <div class="form-group">
                            <label>Category:</label>
                            <input type="text" name="category" id="edit_category" required>
                        </div>
                        <div class="form-group">
                            <label>Difficulty:</label>
                            <select name="difficulty" id="edit_difficulty" required>
                                <option value="Easy">Easy</option>
                                <option value="Medium">Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        function closeEditModal() {
            document.getElementById('editFlashcardModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editFlashcardModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
        
        // Todo management functions
        function toggleTodo(todoId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_todo">
                <input type="hidden" name="todo_id" value="${todoId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteTodo(todoId) {
            if (confirm('Are you sure you want to delete this task?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_todo">
                    <input type="hidden" name="todo_id" value="${todoId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function fulfillWish(wishId) {
            if (confirm('Mark this wish as fulfilled?')) {
                // Add AJAX call to fulfill wish
                alert('Fulfill functionality coming soon!');
            }
        }
        
        function deleteWish(wishId) {
            if (confirm('Are you sure you want to delete this wish?')) {
                // Add AJAX call to delete wish
                alert('Delete functionality coming soon!');
            }
        }
        
        // Timeline management functions
        function editTimelineEvent(eventId) {
            alert('Edit timeline event functionality coming soon!');
        }
        
        function deleteTimelineEvent(eventId) {
            if (confirm('Are you sure you want to delete this timeline event?')) {
                // Add AJAX call to delete timeline event
                alert('Delete timeline event functionality coming soon!');
            }
        }
        
        // Location management functions
        function editLocation(locationId) {
            alert('Edit location functionality coming soon!');
        }
        
        function deleteLocation(locationId) {
            if (confirm('Are you sure you want to delete this location?')) {
                // Add AJAX call to delete location
                alert('Delete location functionality coming soon!');
            }
        }
    </script>
</body>
</html>
