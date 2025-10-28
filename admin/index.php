<?php 
require '../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark">
    <div class="container"><span class="navbar-brand">Admin Panel</span>
        <a href="../logout.php" class="btn btn-outline-light">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="add-tour.php" class="list-group-item list-group-item-action">Add Tour</a>
                <a href="bookings.php" class="list-group-item list-group-item-action">Bookings</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h5>Total Tours</h5>
                    <p><?= $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn() ?></p>
                    <h5>Total Bookings</h5>
                    <p><?= $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn() ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>