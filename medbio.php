<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$is_admin = is_admin();

// Get study entries (shared - all users can see all entries)
$query = "SELECT se.*, u.name as user_name FROM study_entries se JOIN users u ON se.user_id = u.id ORDER BY se.entry_date DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$study_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get flashcards (shared - all users can see all flashcards)
$query = "SELECT f.*, u.name as user_name FROM flashcards f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$flashcards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lab entries (shared - all users can see all lab entries)
$query = "SELECT le.*, u.name as user_name FROM lab_entries le JOIN users u ON le.user_id = u.id ORDER BY le.entry_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$lab_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get resources (shared - all users can see all resources)
$query = "SELECT r.*, u.name as user_name FROM resources r JOIN users u ON r.user_id = u.id ORDER BY r.uploaded_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get to-do items (shared - all users can see all todos)
$query = "SELECT t.*, u.name as user_name FROM todo_items t JOIN users u ON t.user_id = u.id ORDER BY t.position_order ASC, t.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$todo_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
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
            
            // All users can edit any flashcard (shared access)
            $query = "UPDATE flashcards SET front_text = :front_text, back_text = :back_text, category = :category, difficulty = :difficulty WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':front_text', $front_text);
            $stmt->bindParam(':back_text', $back_text);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':difficulty', $difficulty);
            $stmt->execute();
            break;
            
        case 'delete_flashcard':
            $id = $_POST['id'];
            // All users can delete any flashcard (shared access)
            $query = "DELETE FROM flashcards WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            break;
            
        case 'add_lab_entry':
            $title = sanitize_input($_POST['title']);
            $content = sanitize_input($_POST['content']);
            $tags = sanitize_input($_POST['tags']);
            $entry_date = $_POST['entry_date'];
            
            $query = "INSERT INTO lab_entries (user_id, title, content, tags, entry_date) VALUES (:user_id, :title, :content, :tags, :entry_date)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':tags', $tags);
            $stmt->bindParam(':entry_date', $entry_date);
            $stmt->execute();
            break;
            
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
            
        case 'toggle_todo':
            $todo_id = $_POST['todo_id'];
            // All users can toggle any todo (shared access)
            $query = "UPDATE todo_items SET is_completed = NOT is_completed WHERE id = :todo_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':todo_id', $todo_id);
            $stmt->execute();
            break;
            
        case 'delete_todo':
            $todo_id = $_POST['todo_id'];
            // All users can delete any todo (shared access)
            $query = "DELETE FROM todo_items WHERE id = :todo_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':todo_id', $todo_id);
            $stmt->execute();
            break;
            
        case 'upload_resource':
            if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
                $file = $_FILES['resource_file'];
                $tags = sanitize_input($_POST['tags']);
                
                // Create uploads/resources directory if it doesn't exist
                $upload_dir = 'uploads/resources/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Get file info
                    $original_name = $file['name'];
                    $file_size = $file['size'];
                    $file_type = $file['type'];
                    
                    // Insert into database
                    $query = "INSERT INTO resources (user_id, filename, original_name, file_path, file_type, file_size, tags) VALUES (:user_id, :filename, :original_name, :file_path, :file_type, :file_size, :tags)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->bindParam(':filename', $new_filename);
                    $stmt->bindParam(':original_name', $original_name);
                    $stmt->bindParam(':file_path', $upload_path);
                    $stmt->bindParam(':file_type', $file_type);
                    $stmt->bindParam(':file_size', $file_size);
                    $stmt->bindParam(':tags', $tags);
                    $stmt->execute();
                }
            }
            break;
            
        case 'delete_resource':
            $resource_id = $_POST['resource_id'];
            
            // First get the file path to delete the physical file (all users can delete any resource)
            $query = "SELECT file_path FROM resources WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $resource_id);
            $stmt->execute();
            $resource = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resource && file_exists($resource['file_path'])) {
                unlink($resource['file_path']);
            }
            
            // Delete from database (all users can delete any resource - shared access)
            $query = "DELETE FROM resources WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $resource_id);
            $stmt->execute();
            break;
            
        case 'delete_study_entry':
            $id = $_POST['id'];
            // All users can delete any study entry (shared access)
            $query = "DELETE FROM study_entries WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            break;
            
        case 'delete_lab_entry':
            $id = $_POST['id'];
            // All users can delete any lab entry (shared access)
            $query = "DELETE FROM lab_entries WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            break;
    }
    
    header('Location: medbio.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Everything Medbio - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="medbio-container">
            <div class="page-header">
                <h1>Everything MedBio</h1>
                <p>Your comprehensive medical study companion</p>
            </div>
            <section class="tool-launcher">
                <div class="info-grid">
                    <div class="info-card launcher" onclick="openModal('todoModal')">
                        <h3>Study To-Do List</h3>
                        <p>Add a study task</p>
                    </div>
                    <div class="info-card launcher" onclick="openModal('studyModal')">
                        <h3>Study Tracker</h3>
                        <p>Log study hours</p>
                    </div>
                    <div class="info-card launcher" onclick="openModal('flashcardModal')">
                        <h3>Flashcard Generator</h3>
                        <p>Create a flashcard</p>
                    </div>
                    <div class="info-card launcher" onclick="openModal('labModal')">
                        <h3>Lab Notebook</h3>
                        <p>Add lab entry</p>
                    </div>
                    <div class="info-card launcher" onclick="openModal('resourceModal')">
                        <h3>Resource Vault</h3>
                        <p>Upload a resource</p>
                    </div>
                </div>
            </section>
            <!-- To-Do List -->
            <section class="todo-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3>Study To-Do List</h3>
                        <p>Manage your study tasks and assignments</p>
                    </div>
                    <div class="tool-content">
                        
                        <div class="todo-list">
                            <?php if (empty($todo_items)): ?>
                                <p class="empty-state">No tasks yet. Add your first task above!</p>
                            <?php else: ?>
                                <?php foreach ($todo_items as $item): ?>
                                    <div class="todo-item <?php echo $item['is_completed'] ? 'completed' : ''; ?>">
                                        <div class="todo-content">
                                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                            <?php if ($item['description']): ?>
                                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                            <?php endif; ?>
                                            <div class="todo-meta">
                                                <span class="category"><?php echo $item['category']; ?></span>
                                                <?php if ($item['due_date']): ?>
                                                    <span class="due-date">Due: <?php echo format_date($item['due_date']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="todo-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_todo">
                                                <input type="hidden" name="todo_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn-icon" title="Toggle completion">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                <input type="hidden" name="action" value="delete_todo">
                                                <input type="hidden" name="todo_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn-icon" title="Delete task">
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
            
            <!-- Study Tracker -->
            <section class="study-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3>Study Tracker</h3>
                        <p>Log your study hours and track progress</p>
                    </div>
                    <div class="tool-content">
                        
                        
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
                                            <?php if (isset($entry['user_name'])): ?>
                                                <p style="font-size: 0.85rem; opacity: 0.7;">by <?php echo htmlspecialchars($entry['user_name']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($entry['task_description']): ?>
                                                <p class="entry-description"><?php echo htmlspecialchars($entry['task_description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="entry-actions" style="display: flex; gap: 0.5rem;">
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this study entry?')">
                                                <input type="hidden" name="action" value="delete_study_entry">
                                                <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" class="btn-icon" title="Delete entry">
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
            
            <!-- Flashcards -->
            <section class="flashcard-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3>Flashcard Generator</h3>
                        <p>Create and review medical flashcards</p>
                    </div>
                    <div class="tool-content">
                        
                        
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
                                        <?php if (isset($card['user_name'])): ?>
                                            <div style="font-size: 0.75rem; opacity: 0.7; margin-bottom: 0.25rem;">by <?php echo htmlspecialchars($card['user_name']); ?></div>
                                        <?php endif; ?>
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
            
            <!-- Lab Notebook -->
            <section class="lab-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3>Lab Notebook</h3>
                        <p>Digital space for lab observations and protocols</p>
                    </div>
                    <div class="tool-content">
                        
                        
                        <div class="lab-entries">
                            <h4>Recent Lab Entries</h4>
                            <?php if (empty($lab_entries)): ?>
                                <p class="empty-state">No lab entries yet. Start documenting your lab work!</p>
                            <?php else: ?>
                                <?php foreach ($lab_entries as $entry): ?>
                                    <div class="lab-entry">
                                        <div class="entry-header">
                                            <h5><?php echo htmlspecialchars($entry['title']); ?></h5>
                                            <span class="entry-date"><?php echo format_date($entry['entry_date']); ?></span>
                                        </div>
                                        <?php if (isset($entry['user_name'])): ?>
                                            <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.5rem;">by <?php echo htmlspecialchars($entry['user_name']); ?></p>
                                        <?php endif; ?>
                                        <div class="entry-content">
                                            <p><?php echo nl2br(htmlspecialchars($entry['content'])); ?></p>
                                        </div>
                                        <?php if ($entry['tags']): ?>
                                            <div class="entry-tags">
                                                <?php foreach (explode(',', $entry['tags']) as $tag): ?>
                                                    <span class="tag"><?php echo trim($tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="entry-actions" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this lab entry?')">
                                                <input type="hidden" name="action" value="delete_lab_entry">
                                                <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" class="btn-icon" title="Delete entry">
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
            
            <!-- Resource Vault -->
            <section class="resource-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3>Resource Vault</h3>
                        <p>Secure upload for lecture slides, notes, and PDFs</p>
                    </div>
                    <div class="tool-content">
                        
                        
                        <div class="resource-search">
                            <input type="text" id="resource-search" placeholder="Search resources by tags..." onkeyup="searchResources(this.value)">
                        </div>
                        
                        <div class="resource-list">
                            <h4>All Resources</h4>
                            <?php if (empty($resources)): ?>
                                <p class="empty-state">No resources uploaded yet. Start building your study vault!</p>
                            <?php else: ?>
                                <?php foreach ($resources as $resource): ?>
                                    <div class="resource-item" data-tags="<?php echo strtolower($resource['tags']); ?>">
                                        <?php if (isset($resource['user_name'])): ?>
                                            <div style="font-size: 0.75rem; opacity: 0.7; margin-bottom: 0.25rem;">by <?php echo htmlspecialchars($resource['user_name']); ?></div>
                                        <?php endif; ?>
                                        <div class="resource-icon">
                                            <?php
                                            $file_extension = strtolower(pathinfo($resource['original_name'], PATHINFO_EXTENSION));
                                            $icon_class = 'fas fa-file';
                                            
                                            switch ($file_extension) {
                                                case 'pdf':
                                                    $icon_class = 'fas fa-file-pdf';
                                                    break;
                                                case 'doc':
                                                case 'docx':
                                                    $icon_class = 'fas fa-file-word';
                                                    break;
                                                case 'ppt':
                                                case 'pptx':
                                                    $icon_class = 'fas fa-file-powerpoint';
                                                    break;
                                                case 'txt':
                                                    $icon_class = 'fas fa-file-alt';
                                                    break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif':
                                                    $icon_class = 'fas fa-file-image';
                                                    break;
                                                default:
                                                    $icon_class = 'fas fa-file';
                                            }
                                            ?>
                                            <i class="<?php echo $icon_class; ?>"></i>
                                        </div>
                                        <div class="resource-info">
                                            <h5><?php echo htmlspecialchars($resource['original_name']); ?></h5>
                                            <p>Uploaded: <?php echo time_ago($resource['uploaded_at']); ?></p>
                                            <div class="resource-tags">
                                                <?php foreach (explode(',', $resource['tags']) as $tag): ?>
                                                    <span class="tag"><?php echo trim($tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="resource-actions">
                                            <a href="<?php echo $resource['file_path']; ?>" target="_blank" class="btn-icon" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button class="btn-icon" onclick="deleteResource(<?php echo $resource['id']; ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <!-- Modals: Quick forms -->
    <div id="todoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add To-Do</h3>
                <span class="close" onclick="closeModal('todoModal')">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_todo">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Lab">Lab</option>
                        <option value="School">School</option>
                        <option value="Personal">Personal</option>
                        <option value="Relationship">Relationship</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Optional"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('todoModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>

    <div id="studyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Log Study</h3>
                <span class="close" onclick="closeModal('studyModal')">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_study_entry">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" required>
                </div>
                <div class="form-group">
                    <label>Hours</label>
                    <input type="number" name="hours" step="0.5" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="task_description" placeholder="What did you study?"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('studyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Study</button>
                </div>
            </form>
        </div>
    </div>

    <div id="flashcardModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Flashcard</h3>
                <span class="close" onclick="closeModal('flashcardModal')">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_flashcard">
                <div class="form-group">
                    <label>Front</label>
                    <input type="text" name="front_text" required>
                </div>
                <div class="form-group">
                    <label>Back</label>
                    <input type="text" name="back_text" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" required>
                </div>
                <div class="form-group">
                    <label>Difficulty</label>
                    <select name="difficulty" required>
                        <option value="Easy">Easy</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('flashcardModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Card</button>
                </div>
            </form>
        </div>
    </div>

    <div id="labModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Lab Entry</h3>
                <span class="close" onclick="closeModal('labModal')">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_lab_entry">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Tags</label>
                    <input type="text" name="tags" placeholder="Biochemistry, Protocol" required>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="4" required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('labModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Entry</button>
                </div>
            </form>
        </div>
    </div>

    <div id="resourceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Resource</h3>
                <span class="close" onclick="closeModal('resourceModal')">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" class="modal-form">
                <input type="hidden" name="action" value="upload_resource">
                <div class="form-group">
                    <label>File</label>
                    <input type="file" name="resource_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif" required>
                </div>
                <div class="form-group">
                    <label>Tags</label>
                    <input type="text" name="tags" placeholder="Biochem, Anatomy" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('resourceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Modal helpers
        function openModal(id) {
            var m = document.getElementById(id);
            if (m) m.style.display = 'block';
        }
        function closeModal(id) {
            var m = document.getElementById(id);
            if (m) m.style.display = 'none';
        }
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
            ['todoModal','studyModal','flashcardModal','labModal','resourceModal'].forEach(function(id){
                var m = document.getElementById(id);
                if (event.target === m) {
                    m.style.display = 'none';
                }
            });
        }
        
        // Resource management functions
        function deleteResource(resourceId) {
            if (confirm('Are you sure you want to delete this resource?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_resource">
                    <input type="hidden" name="resource_id" value="${resourceId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Resource search functionality
        function searchResources(query) {
            const resources = document.querySelectorAll('.resource-item');
            const searchTerm = query.toLowerCase();
            
            resources.forEach(resource => {
                const tags = resource.getAttribute('data-tags');
                if (tags.includes(searchTerm) || searchTerm === '') {
                    resource.style.display = 'flex';
                } else {
                    resource.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
