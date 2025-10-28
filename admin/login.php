<!-- Same as user login.php but redirect to admin/index.php -->
<?php 
require '../config.php';
if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';
        header("Location: index.php");
        exit;
    } else {
        echo '<div class="alert alert-danger">Invalid Admin Login</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Login</title></head>
<body>
<div class="container mt-5">
    <h3 class="text-center">Admin Login</h3>
    <form method="POST" class="col-md-4 mx-auto">
        <input type="email" name="email" class="form-control mb-2" placeholder="admin@travel.com" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="password" required>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>
