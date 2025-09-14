<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$user = get_user_data($_SESSION['user_id']);
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password_hint = sanitize_input($_POST['password_hint']);
    
    // Handle passcode change
    $new_passcode = $_POST['new_passcode'];
    $confirm_passcode = $_POST['confirm_passcode'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Update basic info
        $query = "UPDATE users SET name = :name, email = :email, password_hint = :password_hint";
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':password_hint' => $password_hint
        ];
        
        // Update passcode if provided
        if (!empty($new_passcode) && !empty($confirm_passcode)) {
            if ($new_passcode === $confirm_passcode) {
                $hashed_passcode = password_hash($new_passcode, PASSWORD_DEFAULT);
                $query .= ", passcode = :passcode";
                $params[':passcode'] = $hashed_passcode;
            } else {
                throw new Exception('Passcodes do not match');
            }
        }
        
        $query .= " WHERE id = :user_id";
        $params[':user_id'] = $_SESSION['user_id'];
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $upload_result = upload_file($_FILES['profile_picture'], 'uploads/profiles/');
            if ($upload_result) {
                $update_query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':profile_picture', $upload_result);
                $update_stmt->bindParam(':user_id', $_SESSION['user_id']);
                $update_stmt->execute();
            }
        }
        
        $db->commit();
        $success_message = 'Profile updated successfully!';
        
        // Refresh user data
        $user = get_user_data($_SESSION['user_id']);
        $_SESSION['name'] = $user['name'];
        
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <h1>Profile Settings</h1>
                <p>Manage your account information</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-content">
                <div class="profile-card">
                    <div class="profile-picture-section">
                        <div class="profile-picture">
                            <img src="<?php echo $user['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                 alt="Profile Picture" id="profile-preview">
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('profile_picture').click()">
                            <i class="fas fa-camera"></i>
                            Change Photo
                        </button>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="profile-form">
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i>
                                Full Name
                            </label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-at"></i>
                                Username
                            </label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small>Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password_hint">
                                <i class="fas fa-lightbulb"></i>
                                Password Hint
                            </label>
                            <input type="text" id="password_hint" name="password_hint" 
                                   value="<?php echo htmlspecialchars($user['password_hint']); ?>" required>
                        </div>
                        
                        <div class="passcode-section">
                            <h3>Change Passcode</h3>
                            <div class="form-group">
                                <label for="new_passcode">
                                    <i class="fas fa-lock"></i>
                                    New Passcode
                                </label>
                                <input type="password" id="new_passcode" name="new_passcode">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_passcode">
                                    <i class="fas fa-lock"></i>
                                    Confirm New Passcode
                                </label>
                                <input type="password" id="confirm_passcode" name="confirm_passcode">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-save"></i>
                            Update Profile
                        </button>
                    </form>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-card">
                        <i class="fas fa-calendar-heart"></i>
                        <h3>Member Since</h3>
                        <p><?php echo format_date($user['created_at']); ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-edit"></i>
                        <h3>Last Updated</h3>
                        <p><?php echo time_ago($user['updated_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="assets/js/main.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
