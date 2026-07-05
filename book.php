<?php

include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    echo "<div class='alert alert-warning text-center'>Please <a href='login.php'>login as a customer</a> to make a booking.</div>";
    include 'includes/footer.php'; exit();
}

$cid = isset($_GET['cleaner']) ? (int)$_GET['cleaner'] : 0;
$c   = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE id=$cid AND role='cleaner' AND verified=1"
));

if (!$c) {
    echo "<div class='alert alert-danger text-center'>Cleaner not found.</div>";
    include 'includes/footer.php'; exit();
}

$services = mysqli_query($conn, "SELECT * FROM services");
$error = ""; $success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sid     = (int)$_POST['service_id'];
    $date    = $_POST['date'];
    $time    = $_POST['time'];
    $address = trim($_POST['address']);

    if (empty($date) || empty($time) || empty($address)) {
        $error = "Please fill in all fields.";
    } elseif ($date < date('Y-m-d')) {
        $error = "Please choose today or a future date.";
    } else {
        $price  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT price FROM services WHERE id=$sid"))['price'];
        $me     = $_SESSION['user_id'];
        $addr   = mysqli_real_escape_string($conn, $address);
        mysqli_query($conn,
            "INSERT INTO bookings (customer_id,cleaner_id,service_id,date,time,address,amount,status)
             VALUES ($me,$cid,$sid,'$date','$time','$addr',$price,'pending')"
        );
        $success = true;
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">

    <!-- Cleaner mini-card -->
    <div class="d-flex align-items-center gap-3 mb-4 bg-white p-3 rounded shadow-sm">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&size=60&background=343a40&color=fff&rounded=true" alt="">
        <div>
            <h6 class="mb-0"><?= htmlspecialchars($c['name']) ?></h6>
            <small class="verified"><i class="bi bi-check-circle-fill"></i> Verified Cleaner</small>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success text-center">
        <i class="bi bi-check-circle fs-2"></i><br>
        <strong>Booking Submitted!</strong><br>
        The cleaner will confirm your request soon.<br>
        <a href="my-bookings.php" class="btn btn-dark-main mt-2">View My Bookings</a>
    </div>
    <?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card p-4">
        <h5 class="mb-4">Book a Cleaner</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Select Service</label>
                <select name="service_id" class="form-select" required>
                    <?php while ($s = mysqli_fetch_assoc($services)): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?> — TZS <?= number_format($s['price']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Address</label>
                <input type="text" name="address" class="form-control" placeholder="e.g. Kinondoni, Dar es Salaam" required>
            </div>
            <button type="submit" class="btn btn-dark-main w-100">Confirm Booking Request</button>
        </form>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
