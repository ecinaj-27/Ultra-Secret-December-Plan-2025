<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

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

// Get lab entries
$query = "SELECT * FROM lab_entries WHERE user_id = :user_id ORDER BY entry_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$lab_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get resources
$query = "SELECT * FROM resources WHERE user_id = :user_id ORDER BY uploaded_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's to-do items
$query = "SELECT * FROM todo_items WHERE user_id = :user_id ORDER BY position_order ASC, created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
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
    <title>MedBio Study Hub - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="medbio-container">
            <div class="page-header">
                <h1><i class="fas fa-graduation-cap"></i> MedBio Study Hub</h1>
                <p>Your comprehensive medical study companion</p>
            </div>
            
            <!-- To-Do List -->
            <section class="todo-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-tasks"></i> Study To-Do List</h3>
                        <p>Manage your study tasks and assignments</p>
                    </div>
                    <div class="tool-content">
                        <div class="todo-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_todo">
                                <div class="form-row">
                                    <input type="text" name="title" placeholder="Task title..." required>
                                    <select name="category" required>
                                        <option value="Lab">Lab</option>
                                        <option value="School">School</option>
                                        <option value="Personal">Personal</option>
                                        <option value="Relationship">Relationship</option>
                                    </select>
                                    <input type="date" name="due_date">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Task
                                    </button>
                                </div>
                                <textarea name="description" placeholder="Task description (optional)"></textarea>
                            </form>
                        </div>
                        
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
                        <h3><i class="fas fa-chart-line"></i> Study Tracker</h3>
                        <p>Log your study hours and track progress</p>
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
            </section>
            
            <!-- Flashcards -->
            <section class="flashcard-section">
                <div class="tool-card">
                    <div class="tool-header">
                        <h3><i class="fas fa-cards-blank"></i> Flashcard Generator</h3>
                        <p>Create and review medical flashcards</p>
                    </div>
                    <div class="tool-content">
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
                                    <div class="flashcard" onclick="flipCard(this)">
                                        <div class="card-front">
                                            <h4><?php echo htmlspecialchars($card['front_text']); ?></h4>
                                        </div>
                                        <div class="card-back">
                                            <h4><?php echo htmlspecialchars($card['back_text']); ?></h4>
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
                        <h3><i class="fas fa-microscope"></i> Lab Notebook</h3>
                        <p>Digital space for lab observations and protocols</p>
                    </div>
                    <div class="tool-content">
                        <div class="lab-form">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="add_lab_entry">
                                <div class="form-row">
                                    <input type="text" name="title" placeholder="Entry title" required>
                                    <input type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Entry
                                    </button>
                                </div>
                                <div class="form-row">
                                    <input type="text" name="tags" placeholder="Tags (e.g., Biochemistry, Lab Protocol)" required>
                                </div>
                                <textarea name="content" placeholder="Lab observations, protocols, results..." rows="4" required></textarea>
                            </form>
                        </div>
                        
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
                        <h3><i class="fas fa-archive"></i> Resource Vault</h3>
                        <p>Secure upload for lecture slides, notes, and PDFs</p>
                    </div>
                    <div class="tool-content">
                        <div class="resource-upload">
                            <form method="POST" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="action" value="upload_resource">
                                <div class="form-row">
                                    <input type="file" name="resource_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt" required>
                                    <input type="text" name="tags" placeholder="Tags (e.g., Biochem, Anatomy)" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i>
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="resource-search">
                            <input type="text" id="resource-search" placeholder="Search resources by tags..." onkeyup="searchResources(this.value)">
                        </div>
                        
                        <div class="resource-list">
                            <h4>Your Resources</h4>
                            <?php if (empty($resources)): ?>
                                <p class="empty-state">No resources uploaded yet. Start building your study vault!</p>
                            <?php else: ?>
                                <?php foreach ($resources as $resource): ?>
                                    <div class="resource-item" data-tags="<?php echo strtolower($resource['tags']); ?>">
                                        <div class="resource-icon">
                                            <i class="fas fa-file-pdf"></i>
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
                                            <a href="<?php echo $resource['file_path']; ?>" target="_blank" class="btn-icon">
                                                <i class="fas fa-download"></i>
                                            </a>
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
    
    <script src="assets/js/main.js"></script>
    <script>
        // Flashcard flip functionality
        function flipCard(card) {
            card.classList.toggle('flipped');
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
