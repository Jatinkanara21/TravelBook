<?php 
require 'config.php'; 

// Fetch all tours to display on the home page
$stmt = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC");
$tours = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelBook – Explore the World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 120px 0;
            text-align: center;
        }
        .hero h1 { font-size: 3.5rem; font-weight: 600; }
        .hero p { font-size: 1.2rem; max-width: 700px; margin: 0 auto 20px; }
        .tour-card { transition: transform 0.3s; }
        .tour-card:hover { transform: translateY(-8px); }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">TravelBook</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-outline-light ms-2 px-3" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Discover Your Next Adventure</h1>
        <p>Book exclusive travel packages with ease. Explore destinations, enjoy comfort, and create memories.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-primary btn-lg">Get Started</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary btn-lg">View Tours</a>
        <?php endif; ?>
    </div>
</section>

<!-- Tours Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Popular Destinations</h2>
        <?php if (empty($tours)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No tours available at the moment. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tours as $tour): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card tour-card h-100 shadow-sm">
                            <?php if ($tour['image']): ?>
                                <img src="<?= htmlspecialchars($tour['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($tour['title']) ?>" style="height:200px; object-fit:cover;">
                            <?php else: ?>
                                <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="height:200px;">
                                    <h5>No Image</h5>
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($tour['title']) ?></h5>
                                <p class="card-text flex-grow-1 text-muted">
                                    <?= strlen($tour['description']) > 100 
                                        ? htmlspecialchars(substr($tour['description'], 0, 100)) . '...' 
                                        : htmlspecialchars($tour['description']) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="h5 mb-0 text-primary">$<?= number_format($tour['price'], 2) ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($tour['duration']) ?></small>
                                </div>
                                <a href="<?php echo isset($_SESSION['user_id']) ? 'book.php?tour_id=' . $tour['id'] : 'login.php'; ?>" 
                                   class="btn btn-primary mt-3">
                                    <?php echo isset($_SESSION['user_id']) ? 'Book Now' : 'Login to Book'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p>&copy; <?= date('Y') ?> TravelBook. All rights reserved. | Dark Mode Powered</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>