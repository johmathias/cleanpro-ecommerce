<?php
include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php"); exit();
}
$me = $_SESSION['user_id'];

$bookings = mysqli_query($conn,
    "SELECT b.*, s.name as service, u.name as cleaner
     FROM bookings b
     JOIN services s ON b.service_id = s.id
     JOIN users u    ON b.cleaner_id = u.id
     WHERE b.customer_id = $me
     ORDER BY b.created_at DESC"
);
?>

<h3 class="section-title">My Bookings</h3>
<div class="text-center mb-4">
    <a href="services.php" class="btn btn-dark-main btn-sm"><i class="bi bi-plus"></i> Book New Cleaner</a>
</div>

<?php if (mysqli_num_rows($bookings) == 0): ?>
    <div class="alert alert-info text-center">
        You have no bookings yet. <a href="services.php">Book a cleaner now!</a>
    </div>
<?php else: ?>
<div class="table-box">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Service</th><th>Cleaner</th><th>Date &amp; Time</th>
                <th>Amount</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($b = mysqli_fetch_assoc($bookings)):
            $paid = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM payments WHERE booking_id={$b['id']}"));
            $rev  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM reviews  WHERE booking_id={$b['id']}"));
        ?>
        <tr>
            <td><?= $b['service'] ?></td>
            <td><?= $b['cleaner'] ?></td>
            <td><?= date('d M Y', strtotime($b['date'])) ?> at <?= date('H:i', strtotime($b['time'])) ?></td>
            <td>TZS <?= number_format($b['amount']) ?></td>
            <td><span class="badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
            <td>
                <?php if ($b['status']=='confirmed' && !$paid): ?>
                    <a href="pay.php?id=<?= $b['id'] ?>" class="btn btn-success btn-sm">Pay Now</a>
                <?php elseif ($paid): ?>
                    <span class="text-success small">Paid ✓</span>
                <?php endif; ?>
                <?php if ($b['status']=='completed' && !$rev): ?>
                    <a href="review.php?id=<?= $b['id'] ?>" class="btn btn-warning btn-sm ms-1">Leave Review</a>
                <?php elseif ($rev): ?>
                    <span class="text-muted small ms-1">Reviewed ✓</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
