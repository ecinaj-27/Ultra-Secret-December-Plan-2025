<?php
// Ensure user data is available
if (!isset($user) && isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    require_once 'includes/functions.php';
    $user = get_user_data($_SESSION['user_id']);
}

// Ensure post_reads table exists for unread tracking
$__db_for_nav = null;
try {
    if (!class_exists('Database')) {
        require_once 'config/database.php';
    }
    $__db_for_nav = (new Database())->getConnection();
    $__db_for_nav->exec("CREATE TABLE IF NOT EXISTS post_reads ( id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, post_id INT NOT NULL, is_read BOOLEAN DEFAULT FALSE, read_at TIMESTAMP NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_user_post (user_id, post_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE )");
} catch (Exception $e) { /* ignore */ }

// Fetch scheduled letters due today or earlier for pop-up (shown once per session per letter)
$scheduledPopupLetters = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT id, title, content, scheduled_date FROM scheduled_letters WHERE (is_sent = 0 OR is_sent IS NULL) AND scheduled_date <= CURDATE() ORDER BY scheduled_date ASC");
    $stmt->execute();
    $scheduledPopupLetters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create posts for any due letters that don't already have a corresponding post
    foreach ($scheduledPopupLetters as $letter) {
        try {
            // Does a matching post already exist?
            $check = $db->prepare("SELECT id FROM posts WHERE type = 'love_letter' AND title = :title AND content = :content LIMIT 1");
            $check->bindParam(':title', $letter['title']);
            $check->bindParam(':content', $letter['content']);
            $check->execute();
            $existingPost = $check->fetch(PDO::FETCH_ASSOC);
            $postId = $existingPost ? (int)$existingPost['id'] : 0;
            if (!$postId) {
                // Insert as a public post attributed to current user if logged in, else user_id = 1
                $authorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
                $ins = $db->prepare("INSERT INTO posts (user_id, title, content, type, is_public) VALUES (:user_id, :title, :content, 'love_letter', 1)");
                $ins->bindParam(':user_id', $authorId);
                $ins->bindParam(':title', $letter['title']);
                $ins->bindParam(':content', $letter['content']);
                $ins->execute();
                $postId = (int)$db->lastInsertId();
            }

            // Mark letter as sent so we don't recreate
            $upd = $db->prepare("UPDATE scheduled_letters SET is_sent = 1 WHERE id = :id");
            $upd->bindParam(':id', $letter['id']);
            $upd->execute();

            // Ensure unread record exists for current user
            if (isset($_SESSION['user_id']) && $__db_for_nav) {
                $uid = (int)$_SESSION['user_id'];
                $reads = $__db_for_nav->prepare("INSERT IGNORE INTO post_reads (user_id, post_id, is_read) VALUES (:uid, :pid, 0)");
                $reads->bindParam(':uid', $uid);
                $reads->bindParam(':pid', $postId);
                $reads->execute();
            }
        } catch (Exception $ie) { /* ignore per-letter errors */ }
    }
} catch (Exception $e) {
    // fail silently
}
?>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Left Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <a href="home.php">
                <i class="fas fa-heart"></i>
                <span>WebCza</span>
            </a>
        </div>
        <button class="sidebar-close" id="sidebar-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <!-- User button moved to top of nav -->
        <div class="sidebar-user">
            <div class="user-dropdown">
                <button class="user-button">
                    <img src="<?php echo isset($user) ? ($user['profile_picture'] ?: 'assets/images/default-avatar.png') : 'assets/images/default-avatar.png'; ?>" alt="Profile" class="user-avatar">
                    <span><?php echo isset($user) ? htmlspecialchars($user['name']) : 'User'; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                    <?php if (isset($user) && is_admin()): ?>
                    <div class="dropdown-divider"></div>
                    <a href="tools.php" class="dropdown-item admin-dropdown-item">
                        <i class="fas fa-crown"></i>
                        Content Management
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="home.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="medbio.php" class="nav-link">
                    <i class="fas fa-graduation-cap"></i>
                    <span> Everything MedBio</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="about.php" class="nav-link">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="openMoreLock(event)">
                    <i class="fas fa-ellipsis-h"></i>
                    <span>More</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- Dark Mode Toggle -->
    <div class="sidebar-footer" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
        <button class="nav-link" id="dark-mode-toggle" style="background: none; border: none; width: 100%; text-align: left; cursor: pointer; color: inherit;">
            <i class="fas fa-moon" id="dark-mode-icon"></i>
            <span id="dark-mode-text">Dark Mode</span>
        </button>
    </div>
    
    <!-- Logout pinned at bottom -->
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>
<div id="moreLockModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Unlock More</h3>
            <span class="close" onclick="closeMoreLock()">&times;</span>
        </div>
        <form class="modal-form" onsubmit="return submitMoreLock(event)">
            <div class="form-group">
                <label>Enter Passcode</label>
                <input type="password" id="more_passcode" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeMoreLock()">Cancel</button>
                <button type="submit" class="btn btn-primary">Unlock</button>
            </div>
        </form>
        <div id="more-links" style="display:none;padding:0 1.5rem 1rem;">
            <a href="how-i-see-her.php" class="dropdown-item"><i class="fas fa-eye"></i> How I See Her</a>
            <a href="media.php" class="dropdown-item"><i class="fas fa-film"></i> Movies / Songs</a>
            <a href="anniversary-letter.php" class="dropdown-item"><i class="fas fa-envelope-heart"></i> Anniversary Letter</a>
            <a href="us.php" class="dropdown-item"><i class="fas fa-users"></i> Us</a>
            <a href="photo-booth.php" class="dropdown-item"><i class="fas fa-camera"></i> Photo Booth</a>
            <a href="art-wall.php" class="dropdown-item"><i class="fas fa-palette"></i> Art Wall</a>
        </div>
    </div>
</div>

<!-- Scheduled Letter Popup Modal -->
<div id="letterPopupModal" class="modal" style="display:none;">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="letterPopupTitle"><i class="fas fa-envelope-heart"></i> Letter</h3>
			<span class="close" onclick="closeLetterPopup()">&times;</span>
		</div>
		<div class="modal-body" style="padding: 0 1.5rem 1rem;">
			<p class="letter-date" id="letterPopupDate" style="opacity:.8;margin:.25rem 0 .75rem 0;"></p>
			<div id="letterPopupContent" style="white-space:pre-wrap;line-height:1.6;"></div>
		</div>
		<div class="modal-actions" style="padding: 0 1.5rem 1.25rem;">
			<button type="button" class="btn btn-primary" id="letterPopupNextBtn">Close</button>
		</div>
	</div>
</div>

<script>
function openMoreLock(e){ e.preventDefault(); document.getElementById('moreLockModal').style.display='block'; }
function closeMoreLock(){ document.getElementById('moreLockModal').style.display='none'; document.getElementById('more-links').style.display='none'; }
function submitMoreLock(e){
    e.preventDefault();
    const passcode = document.getElementById('more_passcode').value;
    fetch('api/verify-passcode.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ passcode }) })
      .then(r=>r.json())
      .then(data=>{
        if(data.success){
            document.getElementById('more-links').style.display='block';
        } else {
            alert('Invalid passcode');
        }
      }).catch(()=>alert('Error verifying'));
    return false;
}

// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const mainContent = document.querySelector('.main-content');
    
    // Check if sidebar should be hidden by default
    const sidebarHidden = localStorage.getItem('sidebarHidden') === 'true';
    if (sidebarHidden) {
        sidebar.classList.add('hidden');
        if (mainContent) mainContent.classList.add('sidebar-hidden');
    } else {
        // Hide hamburger when sidebar is open by default
        sidebarToggle.classList.add('hidden');
    }
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('hidden');
        sidebarToggle.classList.toggle('hidden');
        if (mainContent) mainContent.classList.toggle('sidebar-hidden');
        localStorage.setItem('sidebarHidden', sidebar.classList.contains('hidden'));
    });
    
    // Close sidebar
    sidebarClose.addEventListener('click', function() {
        sidebar.classList.add('hidden');
        sidebarToggle.classList.remove('hidden');
        if (mainContent) mainContent.classList.add('sidebar-hidden');
        localStorage.setItem('sidebarHidden', 'true');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.add('hidden');
            sidebarToggle.classList.remove('hidden');
            if (mainContent) mainContent.classList.add('sidebar-hidden');
        }
    });
    
    // Scheduled Letters popup logic
    try {
        const letters = <?php echo json_encode($scheduledPopupLetters, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?> || [];
        const queue = letters.filter(l => !sessionStorage.getItem('letter_shown_' + l.id));
        if (queue.length > 0) {
            const modal = document.getElementById('letterPopupModal');
            const titleEl = document.getElementById('letterPopupTitle');
            const dateEl = document.getElementById('letterPopupDate');
            const contentEl = document.getElementById('letterPopupContent');
            const nextBtn = document.getElementById('letterPopupNextBtn');

            const formatDate = (iso) => {
                try { return new Date(iso).toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' }); } catch(e){ return iso; }
            };

            const showNext = () => {
                if (queue.length === 0) { modal.style.display = 'none'; return; }
                const current = queue.shift();
                titleEl.innerHTML = '<i class="fas fa-envelope-heart"></i> ' + (current.title || 'A Letter For You');
                dateEl.textContent = 'Scheduled for: ' + formatDate(current.scheduled_date);
                contentEl.textContent = current.content || '';
                modal.style.display = 'block';
                sessionStorage.setItem('letter_shown_' + current.id, '1');
            };

            nextBtn.onclick = function(){
                modal.style.display = 'none';
                setTimeout(showNext, 150);
            };

            showNext();
        }
    } catch (e) {
        // ignore popup errors
    }
});

function closeLetterPopup(){
	const modal = document.getElementById('letterPopupModal');
	if (modal) modal.style.display = 'none';
}
</script>
