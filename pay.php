<?php

include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php"); exit();
}

$bid = (int)$_GET['id'];
$me  = $_SESSION['user_id'];

$b = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT b.*, s.name as service, u.name as cleaner
     FROM bookings b
     JOIN services s ON b.service_id = s.id
     JOIN users u    ON b.cleaner_id = u.id
     WHERE b.id=$bid AND b.customer_id=$me AND b.status='confirmed'"
));

if (!$b) {
    echo "<div class='alert alert-warning text-center'>Booking not found or not yet confirmed by the cleaner.</div>";
    include 'includes/footer.php'; exit();
}

$already = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM payments WHERE booking_id=$bid"));
if ($already) {
    echo "<div class='alert alert-success text-center'>This booking is already paid. <a href='my-bookings.php'>Go to My Bookings</a></div>";
    include 'includes/footer.php'; exit();
}

$success = false; $code = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    // Simulate M-Pesa — in a real system this calls Daraja STK Push API
    $code  = "MP" . strtoupper(substr(md5(time()), 0, 8));
    $amt   = $b['amount'];
    $ph    = mysqli_real_escape_string($conn, $phone);
    mysqli_query($conn,
        "INSERT INTO payments (booking_id,mpesa_code,amount,phone) VALUES ($bid,'$code',$amt,'$ph')"
    );
    $success = true;
}
?>

<div class="row justify-content-center">
  <div class="col-md-5">

    <!-- Summary -->
    <div class="card p-4 mb-4">
        <h6 class="text-muted mb-3">Payment Summary</h6>
        <table class="table table-borderless table-sm mb-0">
            <tr><td class="text-muted">Service</td><td><strong><?= $b['service'] ?></strong></td></tr>
            <tr><td class="text-muted">Cleaner</td><td><?= $b['cleaner'] ?></td></tr>
            <tr><td class="text-muted">Date</td><td><?= date('d M Y', strtotime($b['date'])) ?> at <?= date('H:i', strtotime($b['time'])) ?></td></tr>
            <tr class="border-top">
                <td><strong>Total</strong></td>
                <td><strong class="fs-5" style="color:var(--dark);">TZS <?= number_format($b['amount']) ?></strong></td>
            </tr>
        </table>
    </div>

    <?php if ($success): ?>
    <div class="card p-4 text-center">
        <div style="font-size:54px;">✅</div>
        <h5 class="mt-2">Payment Successful!</h5>
        <p class="text-muted">M-Pesa Transaction Code: <strong><?= $code ?></strong></p>
        <a href="my-bookings.php" class="btn btn-dark-main">Go to My Bookings</a>
    </div>
    <?php else: ?>
    <div class="card p-4">
        <h6><i class="bi bi-phone"></i> Pay with M-Pesa</h6>
        <p class="text-muted small">Enter your M-Pesa number. You will receive a prompt on your phone to enter your PIN.</p>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">M-Pesa Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. 0712345678" required>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold">
                Pay TZS <?= number_format($b['amount']) ?> Now
            </button>
        </form>
        <p class="text-center text-muted small mt-3">🔒 Powered by Safaricom Daraja API (Sandbox)</p>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
