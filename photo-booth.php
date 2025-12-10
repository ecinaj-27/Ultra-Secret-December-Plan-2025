<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$is_admin = is_admin();

// Get photo booth images
$query = "SELECT * FROM photo_booth ORDER BY uploaded_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'upload_photo') {
        $caption = sanitize_input($_POST['caption']);
        
        // Handle image upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $image_path = upload_file($_FILES['photo'], 'uploads/photo_booth/');
            
            if ($image_path) {
                $query = "INSERT INTO photo_booth (user_id, image_path, caption) VALUES (:user_id, :image_path, :caption)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':image_path', $image_path);
                $stmt->bindParam(':caption', $caption);
                $stmt->execute();
                
                header('Location: photo-booth.php?uploaded=1');
                exit();
            }
        }
    } elseif ($action === 'edit_photo') {
        $id = (int)($_POST['id'] ?? 0);
        $caption = sanitize_input($_POST['caption'] ?? '');
        // optional image replacement
        $stmt = $db->prepare("SELECT image_path FROM photo_booth WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        $image_path = $existing ? $existing['image_path'] : null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $new_path = upload_file($_FILES['photo'], 'uploads/photo_booth/');
            if ($new_path) {
                if ($image_path) { delete_file_if_exists($image_path); }
                $image_path = $new_path;
            }
        }
        $query = "UPDATE photo_booth SET caption = :caption, image_path = :image_path WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':caption', $caption);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: photo-booth.php?updated=1');
        exit();
    } elseif ($action === 'delete_photo') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT image_path FROM photo_booth WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing && $existing['image_path']) {
            delete_file_if_exists($existing['image_path']);
        }
        $stmt = $db->prepare("DELETE FROM photo_booth WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: photo-booth.php?deleted=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Booth - Our Secret Place</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .photo-booth-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .upload-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 3rem;
            border: 1px solid #e1e5e9;
        }
        
        .upload-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 4px;
            margin-top: 2rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            background: white;
            padding: 4px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .photo-item {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
            background: white;
            border: 2px solid white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .photo-item:hover {
            transform: scale(1.02);
            z-index: 10;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .photo-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
            color: white;
            padding: 0.8rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            font-size: 0.8rem;
        }
        
        .photo-item:hover .photo-overlay {
            transform: translateY(0);
        }
        
        .photo-caption {
            font-size: 0.8rem;
            margin-bottom: 0.3rem;
            font-weight: 500;
            line-height: 1.2;
        }
        
        .photo-date {
            font-size: 0.7rem;
            opacity: 0.8;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
            font-size: 1.1rem;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ccc;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn:hover {
            background: #ff5252;
        }
        
        .btn-primary {
            background: #ff6b6b;
        }
        
        .btn-primary:hover {
            background: #ff5252;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .photo-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(2, 1fr);
                max-width: 600px;
                gap: 3px;
                padding: 3px;
            }
        }
        
        @media (max-width: 480px) {
            .photo-grid {
                grid-template-columns: 1fr;
                grid-template-rows: repeat(3, 1fr);
                max-width: 300px;
                gap: 2px;
                padding: 2px;
            }
        }
        
        /* Handle empty grid slots */
        .photo-item.empty {
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 2rem;
        }
        
        .photo-item.empty i {
            opacity: 0.3;
        }

        /* Photo capture section */
        .capture-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 3rem;
            border: 1px solid #e1e5e9;
        }

        .capture-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: #0f172a;
        }

        .capture-header h2 {
            margin: 0;
        }

        .capture-help {
            color: #4b5563;
            margin: 0 0 1.25rem 0;
        }

        .capture-grid {
            display: grid;
            grid-template-columns: minmax(320px, 1fr) 1fr;
            gap: 1.5rem;
            align-items: center;
        }

        .capture-preview {
            position: relative;
            background: #0b1224;
            border-radius: 12px;
            overflow: hidden;
            min-height: 260px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05), 0 10px 30px rgba(0,0,0,0.15);
        }

        .capture-preview video,
        .capture-preview canvas {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .capture-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #d1d5db;
            gap: 0.5rem;
            background: linear-gradient(145deg, rgba(15,23,42,0.85), rgba(15,23,42,0.65));
            text-align: center;
            padding: 1rem;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .capture-placeholder.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .capture-placeholder i {
            font-size: 2rem;
        }

        .capture-controls {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .capture-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .capture-actions .btn {
            margin: 0;
        }

        .capture-actions .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .capture-status {
            color: #374151;
            font-size: 0.95rem;
        }

        .capture-status.success {
            color: #047857;
        }

        .capture-status.error {
            color: #b91c1c;
        }

        @media (max-width: 900px) {
            .capture-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="page-flower-bg">
    <?php include 'includes/navbar.php'; ?>
    
    <main class="main-content">
        <div class="photo-booth-container">
            <div class="page-header">
                <h1><i class="fas fa-camera"></i> Photo Booth</h1>
                <p>Capture and share our precious moments together</p>
                <?php if (isset($_GET['uploaded'])): ?>
                    <div class="alert alert-success">Photo uploaded successfully!</div>
                <?php endif; ?>
            </div>

            <div class="capture-section">
                <div class="capture-header">
                    <i class="fas fa-camera-retro"></i>
                    <h2>Take a Photo</h2>
                </div>
                <p class="capture-help">Turn on your camera, snap a photo, and we will save it straight into the Photo Booth.</p>
                <div class="capture-grid">
                    <div class="capture-preview">
                        <video id="capture-video" autoplay playsinline muted></video>
                        <canvas id="capture-canvas" style="display:none;"></canvas>
                        <div class="capture-placeholder" id="capture-placeholder">
                            <i class="fas fa-video"></i>
                            <span>Start the camera to preview</span>
                        </div>
                    </div>
                    <div class="capture-controls">
                        <div class="form-group">
                            <label for="capture-caption">Caption (optional)</label>
                            <textarea id="capture-caption" rows="3" placeholder="Add a caption for this snap..."></textarea>
                        </div>
                        <div class="capture-actions">
                            <button type="button" class="btn btn-secondary" id="start-camera-btn">
                                <i class="fas fa-play"></i> Start Camera
                            </button>
                            <button type="button" class="btn btn-primary" id="capture-photo-btn" disabled>
                                <i class="fas fa-circle"></i> Capture
                            </button>
                            <button type="button" class="btn btn-secondary" id="retake-photo-btn" disabled>
                                <i class="fas fa-undo"></i> Retake
                            </button>
                            <button type="button" class="btn btn-primary" id="save-photo-btn" disabled>
                                <i class="fas fa-cloud-upload-alt"></i> Save to Booth
                            </button>
                        </div>
                        <div class="capture-status" id="capture-status">Camera is off.</div>
                    </div>
                </div>
            </div>
            
            <?php if ($is_admin): ?>
            <div class="upload-section">
                <h2><i class="fas fa-upload"></i> Upload New Photo</h2>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="action" value="upload_photo">
                    <div class="form-group">
                        <label for="photo">Select Photo</label>
                        <input type="file" id="photo" name="photo" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="caption">Caption</label>
                        <textarea id="caption" name="caption" placeholder="Write a caption for this photo..." rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Upload Photo
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="photo-grid">
                <?php if (empty($photos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-camera"></i>
                        <h3>No photos yet</h3>
                        <p><?php echo $is_admin ? 'Upload your first photo above!' : 'Photos will appear here once uploaded.'; ?></p>
                    </div>
                <?php else: ?>
                    <?php 
                    // Create a 3x3 grid (9 slots total)
                    $grid_slots = 9;
                    $photo_count = count($photos);
                    
                    // Fill the grid with photos and empty slots
                    for ($i = 0; $i < $grid_slots; $i++): 
                        if ($i < $photo_count): 
                            $photo = $photos[$i];
                    ?>
                        <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                            <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" 
                                 alt="Photo" class="photo-image">
                            <div class="photo-overlay">
                                <div class="photo-caption">
                                    <?php echo htmlspecialchars($photo['caption']); ?>
                                </div>
                                <div class="photo-date">
                                    <?php echo format_date($photo['uploaded_at']); ?>
                                </div>
                            </div>
                            <?php if ($is_admin): ?>
                            <div class="admin-actions" style="position:absolute;top:8px;right:8px;display:flex;gap:6px;z-index:5;">
                                <button class="btn-icon" title="Edit" onclick="openEditPhoto(<?php echo $photo['id']; ?>, `<?php echo htmlspecialchars($photo['caption'], ENT_QUOTES); ?>`)" style="background:#fff;border:1px solid #e1e5e9;border-radius:6px;padding:6px;cursor:pointer;"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon" title="Delete" onclick="deletePhoto(<?php echo $photo['id']; ?>)" style="background:#fff;border:1px solid #e1e5e9;border-radius:6px;padding:6px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="photo-item empty">
                            <i class="fas fa-plus"></i>
                        </div>
                    <?php 
                        endif;
                    endfor; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Photo Lightbox -->
    <div id="photo-lightbox" class="photo-lightbox">
        <div class="photo-lightbox-content">
            <span class="photo-lightbox-close">&times;</span>
            <div class="photo-lightbox-image">
                <img id="lightbox-img" src="" alt="">
            </div>
            <div class="photo-lightbox-info">
                <div id="lightbox-caption"></div>
                <div id="lightbox-date"></div>
            </div>
        </div>
    </div>
    
    <!-- Edit Photo Modal -->
    <div id="editPhotoModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Photo</h3>
                <span class="close" onclick="closeEditPhoto()">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" class="modal-form">
                <input type="hidden" name="action" value="edit_photo">
                <input type="hidden" name="id" id="edit_photo_id">
                <div class="form-group">
                    <label>Caption</label>
                    <textarea name="caption" id="edit_photo_caption" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Replace Photo (optional)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditPhoto()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Photo lightbox functionality
        document.addEventListener('DOMContentLoaded', function() {
            const photoItems = document.querySelectorAll('.photo-item:not(.empty)');
            const lightbox = document.getElementById('photo-lightbox');
            const lightboxClose = document.querySelector('.photo-lightbox-close');
            const lightboxImg = document.getElementById('lightbox-img');
            const lightboxCaption = document.getElementById('lightbox-caption');
            const lightboxDate = document.getElementById('lightbox-date');
            
            photoItems.forEach(item => {
                item.addEventListener('click', function() {
                    const img = this.querySelector('.photo-image');
                    const caption = this.querySelector('.photo-caption').textContent;
                    const date = this.querySelector('.photo-date').textContent;
                    
                    lightboxImg.src = img.src;
                    lightboxCaption.textContent = caption;
                    lightboxDate.textContent = date;
                    
                    lightbox.style.display = 'flex';
                });
            });
            
            // Close lightbox
            lightboxClose.addEventListener('click', function() {
                lightbox.style.display = 'none';
            });
            
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox) {
                    lightbox.style.display = 'none';
                }
            });
        });
        
        function openEditPhoto(id, caption) {
            document.getElementById('edit_photo_id').value = id;
            document.getElementById('edit_photo_caption').value = caption || '';
            document.getElementById('editPhotoModal').style.display = 'block';
        }
        function closeEditPhoto() {
            const modal = document.getElementById('editPhotoModal');
            if (modal) modal.style.display = 'none';
        }
        function deletePhoto(id) {
            if (!confirm('Delete this photo?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_photo">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Photo capture controls
        (function initPhotoCapture() {
            const video = document.getElementById('capture-video');
            const canvas = document.getElementById('capture-canvas');
            const placeholder = document.getElementById('capture-placeholder');
            const captionInput = document.getElementById('capture-caption');
            const startBtn = document.getElementById('start-camera-btn');
            const captureBtn = document.getElementById('capture-photo-btn');
            const retakeBtn = document.getElementById('retake-photo-btn');
            const saveBtn = document.getElementById('save-photo-btn');
            const statusEl = document.getElementById('capture-status');

            if (!video || !canvas || !startBtn || !captureBtn || !retakeBtn || !saveBtn) return;

            let stream = null;
            let capturedDataUrl = '';

            function updateStatus(message, type = '') {
                if (!statusEl) return;
                statusEl.textContent = message;
                statusEl.className = 'capture-status' + (type ? ` ${type}` : '');
            }

            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    video.srcObject = stream;
                    video.style.display = 'block';
                    canvas.style.display = 'none';
                    captureBtn.disabled = false;
                    retakeBtn.disabled = true;
                    saveBtn.disabled = true;
                    capturedDataUrl = '';
                    placeholder.classList.add('hidden');
                    updateStatus('Camera is on. Click Capture when ready.');
                } catch (error) {
                    updateStatus('Camera is blocked. Please allow access.', 'error');
                    if (typeof showNotification === 'function') {
                        showNotification('Camera access was blocked. Please enable it to take a photo.', 'error');
                    }
                }
            }

            function capturePhoto() {
                if (!stream) {
                    updateStatus('Start the camera first.', 'error');
                    return;
                }
                const trackSettings = stream.getVideoTracks()[0]?.getSettings?.() || {};
                canvas.width = trackSettings.width || video.videoWidth || 640;
                canvas.height = trackSettings.height || video.videoHeight || 480;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                capturedDataUrl = canvas.toDataURL('image/jpeg', 0.92);

                canvas.style.display = 'block';
                video.style.display = 'none';
                captureBtn.disabled = true;
                retakeBtn.disabled = false;
                saveBtn.disabled = false;
                updateStatus('Preview ready. Save it or retake if needed.');
            }

            function retakePhoto() {
                capturedDataUrl = '';
                canvas.style.display = 'none';
                video.style.display = 'block';
                captureBtn.disabled = false;
                retakeBtn.disabled = true;
                saveBtn.disabled = true;
                updateStatus('Camera is on. Click Capture when ready.');
            }

            async function savePhoto() {
                if (!capturedDataUrl) {
                    updateStatus('Take a photo before saving.', 'error');
                    return;
                }
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';
                updateStatus('Saving photo...', '');

                try {
                    const response = await fetch('api/photo-booth-capture.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            image: capturedDataUrl,
                            caption: captionInput ? captionInput.value : ''
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        updateStatus('Saved! Reloading...', 'success');
                        if (typeof showNotification === 'function') {
                            showNotification('Photo saved to the Photo Booth!', 'success');
                        }
                        setTimeout(() => window.location.reload(), 700);
                    } else {
                        const message = data.message || 'Unable to save photo.';
                        updateStatus(message, 'error');
                        if (typeof showNotification === 'function') {
                            showNotification(message, 'error');
                        }
                        saveBtn.disabled = false;
                    }
                } catch (err) {
                    updateStatus('Network error while saving photo.', 'error');
                    if (typeof showNotification === 'function') {
                        showNotification('Network error while saving photo.', 'error');
                    }
                    saveBtn.disabled = false;
                } finally {
                    saveBtn.textContent = 'Save to Booth';
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
                video.srcObject = null;
                placeholder.classList.remove('hidden');
                captureBtn.disabled = true;
                retakeBtn.disabled = true;
                saveBtn.disabled = true;
                updateStatus('Camera is off.');
            }

            startBtn.addEventListener('click', startCamera);
            captureBtn.addEventListener('click', capturePhoto);
            retakeBtn.addEventListener('click', retakePhoto);
            saveBtn.addEventListener('click', savePhoto);
            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
    
    <style>
        .photo-lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .photo-lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(12, 7, 7, 0.5);
        }
        
        .photo-lightbox-close {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 2rem;
            color: white;
            cursor: pointer;
            z-index: 1;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        
        .photo-lightbox-close:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        
        .photo-lightbox-image {
            max-height: 70vh;
            overflow: hidden;
        }
        
        .photo-lightbox-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        
        .photo-lightbox-info {
            padding: 1.5rem;
            background: white !important;
            max-height: 20vh;
            overflow-y: auto;
            overflow-x: hidden;
            color: #000 !important;
        }
        
        .photo-lightbox-info div:first-child {
            font-size: 1.1rem;
            font-weight: 500;
            color: #000 !important;
            margin-bottom: 0.5rem;
            word-wrap: break-word;
        }
    
        .photo-lightbox-info div:last-child {
            font-size: 0.9rem;
            color: #666 !important;
        }
        
        #lightbox-caption {
            color: #000 !important;
        }
        
        #lightbox-date {
            color: #666 !important;
        }
        
        /* Custom scrollbar for lightbox info */
        .photo-lightbox-info::-webkit-scrollbar {
            width: 8px;
        }
        
        .photo-lightbox-info::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .photo-lightbox-info::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .photo-lightbox-info::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</body>
</html>
