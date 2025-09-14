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
                    <a href="how-i-see-her.php" class="nav-link">
                        <i class="fas fa-eye"></i>
                        <span>How I See Her</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="media.php" class="nav-link">
                        <i class="fas fa-film"></i>
                        <span>Movies / Songs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="us.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Us</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="about.php" class="nav-link">
                        <i class="fas fa-info-circle"></i>
                        <span>About</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="photo-booth.php" class="nav-link">
                        <i class="fas fa-camera"></i>
                        <span>Photo Booth</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="art-wall.php" class="nav-link">
                        <i class="fas fa-palette"></i>
                        <span>Art Wall</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="medbio.php" class="nav-link">
                        <i class="fas fa-graduation-cap"></i>
                        <span>For My MedBio Bebe</span>
                    </a>
                </li>
                <li class="nav-item user-nav-item">
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
                            <a href="settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        
        <button class="navbar-toggle" id="navbar-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>
