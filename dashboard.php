<?php 
require 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php"); exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC");
$tours = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">TravelBook</a>
        <div class="ms-auto">
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></h2>
    <div class="row">
        <?php foreach ($tours as $tour): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($tour['image']): ?>
                        <img src="<?= $tour['image'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary" style="height:200px;"></div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($tour['title']) ?></h5>
                        <p class="card-text flex-grow-1"><?= htmlspecialchars($tour['description']) ?></p>
                        <p><strong>$<?= $tour['price'] ?></strong> | <?= $tour['duration'] ?></p>
                        <a href="book.php?tour_id=<?= $tour['id'] ?>" class="btn btn-primary mt-auto">Book Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>