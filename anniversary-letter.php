<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Fetch anniversary letter content
$stmt = $db->prepare("SELECT * FROM site_content WHERE content_key = 'anniversary_letter' LIMIT 1");
$stmt->execute();
$anniversary_letter = $stmt->fetch(PDO::FETCH_ASSOC);

// Unlock logic
$unlock_date = new DateTime('2025-12-17');
$current_date = new DateTime();
$is_unlocked = $current_date >= $unlock_date;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anniversary Letter - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <main class="main-content">
        <div class="about-container">
            <div class="page-header">
                <h1>Anniversary Letter</h1>
                <p>A special letter for our special day</p>
            </div>

            <div class="letter-container">
                <?php if ($is_unlocked): ?>
                    <div class="letter-content">
                        <div class="letter-date">March 20, 2025</div>
                        <div class="letter-text">
                            <?php if ($anniversary_letter): ?>
                                <?php echo nl2br(htmlspecialchars($anniversary_letter['content'])); ?>
                            <?php else: ?>
                                <p>My Dearest Love,</p>
                                <p>The letter will be placed here.</p>
                                <p class="signature">Your devoted partner</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="letter-locked">
                        <div class="lock-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>This letter is locked</h3>
                        <p>The anniversary letter will be unlocked on <strong>December 17, 2025</strong></p>
                        <div class="countdown-timer">
                            <div class="countdown-item">
                                <span class="countdown-number" id="days-remaining"><?php echo $unlock_date->diff($current_date)->days; ?></span>
                                <span class="countdown-label">Days</span>
                            </div>
                            <div class="countdown-item">
                                <span class="countdown-number" id="hours-remaining"><?php echo $unlock_date->diff($current_date)->h; ?></span>
                                <span class="countdown-label">Hours</span>
                            </div>
                            <div class="countdown-item">
                                <span class="countdown-number" id="minutes-remaining"><?php echo $unlock_date->diff($current_date)->i; ?></span>
                                <span class="countdown-label">Minutes</span>
                            </div>
                        </div>
                        <p class="unlock-message">Until our special day arrives...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <script>
        function updateCountdown() {
            const unlockDate = new Date('2025-12-17T00:00:00');
            const now = new Date();
            const timeDiff = unlockDate - now;
            if (timeDiff > 0) {
                const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                const d = document.getElementById('days-remaining');
                const h = document.getElementById('hours-remaining');
                const m = document.getElementById('minutes-remaining');
                if (d) d.textContent = days;
                if (h) h.textContent = hours;
                if (m) m.textContent = minutes;
            } else {
                location.reload();
            }
        }
        setInterval(updateCountdown, 60000);
        updateCountdown();
    </script>
</body>
</html>


