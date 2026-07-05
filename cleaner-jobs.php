<?php

include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'cleaner') {
    header("Location: login.php"); exit();
}
$me = $_SESSION['user_id'];

// Handle Accept / Decline / Complete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bid    = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    if ($action == 'accept')   mysqli_query($conn,"UPDATE bookings SET status='confirmed'  WHERE id=$bid AND cleaner_id=$me");
    if ($action == 'decline')  mysqli_query($conn,"UPDATE bookings SET status='cancelled'  WHERE id=$bid AND cleaner_id=$me");
    if ($action == 'complete') mysqli_query($conn,"UPDATE bookings SET status='completed'  WHERE id=$bid AND cleaner_id=$me");
    header("Location: cleaner-jobs.php"); exit();
}

// Check if verified
$me_info = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=$me"));

// Stats
$r      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT AVG(rating) as avg, COUNT(*) as total FROM reviews WHERE cleaner_id=$me"));
$avg    = $r['avg'] ? round($r['avg'],1) : 0;
$done   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as n FROM bookings WHERE cleaner_id=$me AND status='completed'"))['n'];

// Bookings
$jobs   = mysqli_query($conn,
    "SELECT b.*, s.name as service, u.name as customer, u.phone as cphone
     FROM bookings b
     JOIN services s ON b.service_id = s.id
     JOIN users u    ON b.customer_id = u.id
     WHERE b.cleaner_id = $me
     ORDER BY FIELD(b.status,'pending','confirmed','completed','cancelled'), b.date ASC"
);
?>

<h3 class="section-title">My Jobs Dashboard</h3>

<?php if (!$me_info['verified']): ?>
<div class="alert alert-warning text-center">
    <i class="bi bi-hourglass-split fs-3 d-block mb-2"></i>
    <strong>Your account is waiting for admin approval.</strong><br>
    The admin is reviewing your ID document. Once approved, customers can book you.
</div>
<?php else: ?>

<!-- STATS -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h2><?= mysqli_num_rows(mysqli_query($conn,"SELECT id FROM bookings WHERE cleaner_id=$me AND status='pending'")) ?></h2>
            <small class="text-muted">New Requests</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h2><?= mysqli_num_rows(mysqli_query($conn,"SELECT id FROM bookings WHERE cleaner_id=$me AND status='confirmed'")) ?></h2>
            <small class="text-muted">Upcoming Jobs</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h2><?= $done ?></h2>
            <small class="text-muted">Completed</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h2 class="stars"><?= $avg > 0 ? $avg.'★' : '—' ?></h2>
            <small class="text-muted">Your Rating</small>
        </div>
    </div>
</div>

<!-- JOBS LIST -->
<?php if (mysqli_num_rows($jobs) == 0): ?>
    <div class="alert alert-info text-center">No bookings yet. Customers can find and book you on the Services page.</div>
<?php else: ?>
<div class="row g-3 mb-5">
<?php while ($j = mysqli_fetch_assoc($jobs)): ?>
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0"><?= $j['service'] ?></h6>
                <span class="badge-<?= $j['status'] ?>"><?= ucfirst($j['status']) ?></span>
            </div>
            <p class="mb-1 text-muted small"><i class="bi bi-person"></i> <?= htmlspecialchars($j['customer']) ?> — <?= $j['cphone'] ?></p>
            <p class="mb-1 small"><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($j['date'])) ?> at <?= date('H:i', strtotime($j['time'])) ?></p>
            <p class="mb-1 small"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($j['address']) ?></p>
            <p class="fw-bold mb-2" style="color:var(--dark);">TZS <?= number_format($j['amount']) ?></p>

            <?php if ($j['status'] == 'pending'): ?>
            <div class="d-flex gap-2">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="booking_id" value="<?= $j['id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <button class="btn btn-success btn-sm">✔ Accept</button>
                </form>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="booking_id" value="<?= $j['id'] ?>">
                    <input type="hidden" name="action" value="decline">
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Decline this booking?')">✖ Decline</button>
                </form>
            </div>
            <?php elseif ($j['status'] == 'confirmed'): ?>
            <form method="POST">
                <input type="hidden" name="booking_id" value="<?= $j['id'] ?>">
                <input type="hidden" name="action" value="complete">
                <button class="btn btn-sm btn-dark-main" onclick="return confirm('Mark as completed?')">✔ Mark as Complete</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<!-- Show cleaner's ratings and reviews -->
<h4 class="section-title">My Ratings &amp; Reviews</h4>
<?php
$reviews = mysqli_query($conn,
    "SELECT rv.*, u.name as cname
     FROM reviews rv
     JOIN users u ON rv.customer_id = u.id
     WHERE rv.cleaner_id = $me
     ORDER BY rv.created_at DESC"
);
?>
<?php if (mysqli_num_rows($reviews) == 0): ?>
    <div class="alert alert-info text-center">No reviews yet. Reviews appear here after customers rate your work.</div>
<?php else: ?>
<div class="row g-3">
<?php while ($rv = mysqli_fetch_assoc($reviews)): ?>
    <div class="col-md-6">
        <div class="card p-3">
            <div class="d-flex justify-content-between">
                <strong><?= htmlspecialchars($rv['cname']) ?></strong>
                <span class="stars"><?= str_repeat('★',$rv['rating']).str_repeat('☆',5-$rv['rating']) ?></span>
            </div>
            <?php if ($rv['comment']): ?>
                <p class="text-muted mb-1 mt-1"><?= htmlspecialchars($rv['comment']) ?></p>
            <?php endif; ?>
            <small class="text-muted"><?= date('d M Y', strtotime($rv['created_at'])) ?></small>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
