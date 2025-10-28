<?php 
require 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php"); exit;
}
$tour_id = $_GET['tour_id'];
$stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->execute([$tour_id]);
$tour = $stmt->fetch();

if (!$tour) { die("Tour not found"); }

if ($_POST) {
    $date = $_POST['date'];
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tour_id, booking_date) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $tour_id, $date]);
    echo '<div class="alert alert-success">Booking confirmed!</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h4>Book: <?= htmlspecialchars($tour['title']) ?></h4></div>
                <div class="card-body">
                    <p><strong>Price:</strong> $<?= $tour['price'] ?></p>
                    <p><strong>Duration:</strong> <?= $tour['duration'] ?></p>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Travel Date</label>
                            <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Confirm Booking</button>
                    </form>
                    <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>