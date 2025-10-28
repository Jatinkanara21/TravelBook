<?php 
require '../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); 
    exit;
}

$success = $error = '';

// Handle form submission
if ($_POST) {
    $title       = trim($_POST['title']);
    $description = $_POST['description'];
    $price       = floatval($_POST['price']);
    $duration    = trim($_POST['duration']);
    $image_path  = '';

    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $filename = 'tour_' . time() . '.' . $ext;
            $upload_dir = '../uploads/tours/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $image_path = 'uploads/tours/' . $filename;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image. Use JPG/PNG/WEBP (max 5MB).";
        }
    }

    if (!$error && $title && $price > 0 && $duration) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tours (title, description, price, duration, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $price, $duration, $image_path]);
            $success = "Tour added successfully!";
            $_POST = [];
        } catch (Exception $e) {
            $error = "Database error.";
        }
    } else {
        $error = $error ?: "Please fill all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tour | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card { background: #1e1e1e; border: 1px solid #333; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .form-label { font-weight: 500; color: #ccc; }
        .image-preview { max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px dashed #444; margin-top: 10px; display: none; }
        .upload-area {
            border: 2px dashed #555;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            background: #252525;
            cursor: pointer;
        }
        .upload-area:hover { border-color: #0d6efd; background: #2a2a2a; }
        .upload-area.dragover { border-color: #0d6efd; background: #1a2530; }
        .toolbar {
            background: #2c2c2c;
            border: 1px solid #444;
            border-radius: 8px 8px 0 0;
            padding: 8px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .toolbar button {
            background: #333;
            color: #fff;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .toolbar button:hover { background: #0d6efd; }
        #description {
            min-height: 180px;
            resize: vertical;
            background: #2c2c2c;
            color: #fff;
            border: 1px solid #444;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
    </style>
</head>
<body class="bg-dark text-light">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="index.php" class="btn btn-outline-light me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="mb-0">Add New Tour</h3>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" id="tourForm">
                        <!-- Title -->
                        <div class="mb-4">
                            <label class="form-label">Tour Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" 
                                   value="<?= $_POST['title'] ?? '' ?>" required 
                                   placeholder="e.g. Bali Paradise Escape">
                        </div>

                        <!-- Description with Simple Toolbar -->
                        <div class="mb-4">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <div class="toolbar">
                                <button type="button" onclick="formatText('bold')"><i class="fas fa-bold"></i></button>
                                <button type="button" onclick="formatText('italic')"><i class="fas fa-italic"></i></button>
                                <button type="button" onclick="formatText('underline')"><i class="fas fa-underline"></i></button>
                                <button type="button" onclick="formatText('insertunorderedlist')"><i class="fas fa-list-ul"></i></button>
                                <button type="button" onclick="formatText('insertorderedlist')"><i class="fas fa-list-ol"></i></button>
                                <button type="button" onclick="formatText('undo')"><i class="fas fa-undo"></i></button>
                            </div>
                            <textarea name="description" id="description" class="form-control" required 
                                      placeholder="Describe the tour..."><?= $_POST['description'] ?? '' ?></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label class="form-label">Tour Image (JPG, PNG, WEBP - Max 5MB)</label>
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Drop image here or click to select</p>
                                <input type="file" name="image" id="imageInput" accept="image/*" class="d-none">
                            </div>
                            <div class="mt-3 text-center">
                                <img id="imagePreview" class="image-preview" alt="Preview">
                            </div>
                        </div>

                        <!-- Price & Duration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="price" class="form-control" 
                                           value="<?= $_POST['price'] ?? '' ?>" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration <span class="text-danger">*</span></label>
                                <input type="text" name="duration" class="form-control" 
                                       value="<?= $_POST['duration'] ?? '' ?>" required 
                                       placeholder="e.g. 5 Days / 4 Nights">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid d-md-flex justify-content-md-end gap-2">
                            <a href="index.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Add Tour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple Text Formatting
    function formatText(command) {
        document.execCommand(command, false, null);
        document.getElementById('description').focus();
    }

    // Image Upload Preview & Drag/Drop
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    uploadArea.addEventListener('click', () => imageInput.click());
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            imageInput.files = e.dataTransfer.files;
            previewImage(file);
        }
    });

    imageInput.addEventListener('change', () => {
        if (imageInput.files[0]) previewImage(imageInput.files[0]);
    });

    function previewImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
</script>
</body>
</html>