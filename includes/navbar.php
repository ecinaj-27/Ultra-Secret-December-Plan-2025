<?php
// Ensure user data is available
if (!isset($user) && isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    require_once 'includes/functions.php';
    $user = get_user_data($_SESSION['user_id']);
}
?>

<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a href="home.php">
                <i class="fas fa-heart"></i>
                <span>WebCza</span>
            </a>
        </div>
        
        <div class="navbar-menu">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="home.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="medbio.php" class="nav-link">
                        <i class="fas fa-graduation-cap"></i>
                        <span>For My MedBio Bebe</span>
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
        
        <div class="navbar-user">
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
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
        
        <button class="navbar-toggle" id="navbar-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
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
</script>
