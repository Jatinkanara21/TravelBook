<?php 
require '../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); 
    exit;
}

// Filters
$status_filter = $_GET['status'] ?? '';
$date_filter   = $_GET['date'] ?? '';
$search        = trim($_GET['search'] ?? '');

// Build Query
$sql = "SELECT b.*, u.name AS user_name, t.title AS tour_title 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN tours t ON b.tour_id = t.id 
        WHERE 1=1";
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'confirmed', 'cancelled'])) {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}
if ($date_filter) {
    $sql .= " AND DATE(b.booking_date) = ?";
    $params[] = $date_filter;
}
if ($search) {
    $sql .= " AND (u.name LIKE ? OR t.title LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY b.created_at DESC";

// Pagination
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$count_stmt = $pdo->prepare(str_replace('b.*, u.name', 'COUNT(*)', $sql));
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$sql .= " LIMIT ? OFFSET ?";

// Prepare statement first
$stmt = $pdo->prepare($sql);

// Execute with explicit types: PDO::PARAM_INT
$stmt->bindParam(1, $per_page, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);

// Bind other params (if any)
$param_index = 3;
foreach ($params as $param) {
    $stmt->bindValue($param_index++, $param);
}

$stmt->execute();
$bookings = $stmt->fetchAll();

// Update Status
if ($_POST['action'] ?? '' === 'update_status') {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    if (in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        $upd = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $upd->execute([$new_status, $booking_id]);
        header("Location: bookings.php?" . http_build_query($_GET));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card { background: #1e1e1e; border: 1px solid #333; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .table { background: #1e1e1e; color: #e0e0e0; }
        .table th { background: #2c2c2c; border-color: #444; font-weight: 600; }
        .table td { border-color: #444; vertical-align: middle; }
        .status-badge { font-size: 0.8rem; padding: 0.35em 0.65em; }
        .filter-card { background: #252525; }
        .search-input { max-width: 250px; }
        .pagination .page-link { background: #2c2c2c; border-color: #444; color: #e0e0e0; }
        .pagination .page-item.active .page-link { background: #0d6efd; border-color: #0d6efd; }
        .pagination .page-link:hover { background: #0d6efd; color: #fff; }
    </style>
</head>
<body class="bg-dark text-light">

<div class="container mt-4">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-light me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h3 class="mb-0">Manage Bookings</h3>
    </div>

    <!-- Filters -->
    <div class="card filter-card p-3 mb-4">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label text-muted mb-1">Search</label>
                <div class="input-group search-input">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           value="<?= htmlspecialchars($search) ?>" placeholder="User or Tour">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" <?= $status_filter=='pending'?'selected':'' ?>>Pending</option>
                    <option value="confirmed" <?= $status_filter=='confirmed'?'selected':'' ?>>Confirmed</option>
                    <option value="cancelled" <?= $status_filter=='cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted mb-1">Date</label>
                <input type="date" name="date" class="form-control" value="<?= $date_filter ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="bookings.php" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No bookings found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Tour</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Booked On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $i => $b): ?>
                                <tr>
                                    <td><?= $offset + $i + 1 ?></td>
                                    <td><?= htmlspecialchars($b['user_name']) ?></td>
                                    <td><?= htmlspecialchars($b['tour_title']) ?></td>
                                    <td><?= date('M j, Y', strtotime($b['booking_date'])) ?></td>
                                    <td>
                                        <span class="badge status-badge 
                                            <?= $b['status']=='confirmed' ? 'bg-success' : 
                                               ($b['status']=='cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                            <?= ucfirst($b['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y H:i', strtotime($b['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="form-select form-select-sm">
                                                <option value="pending" <?= $b['status']=='pending'?'selected':'' ?>>Pending</option>
                                                <option value="confirmed" <?= $b['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                                                <option value="cancelled" <?= $b['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="p-3">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">Previous</a>
                            </li>
                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            for ($p = $start; $p <= $end; $p++): ?>
                                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">Next</a>
                            </li>
                        </ul>
                        <div class="text-center text-muted small mt-2">
                            Page <?= $page ?> of <?= $total_pages ?> | Total: <?= $total ?> bookings
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>