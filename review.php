<?php

include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php"); exit();
}

$bid = (int)$_GET['id'];
$me  = $_SESSION['user_id'];

$b = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT b.*, u.name as cleaner, u.id as cleaner_id
     FROM bookings b JOIN users u ON b.cleaner_id = u.id
     WHERE b.id=$bid AND b.customer_id=$me AND b.status='completed'"
));

if (!$b) {
    echo "<div class='alert alert-danger text-center'>Booking not found.</div>";
    include 'includes/footer.php'; exit();
}

$already = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM reviews WHERE booking_id=$bid"));
if ($already) {
    echo "<div class='alert alert-info text-center'>You already reviewed this booking. <a href='my-bookings.php'>Back</a></div>";
    include 'includes/footer.php'; exit();
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating  = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, trim($_POST['comment']));
    $cid     = $b['cleaner_id'];
    mysqli_query($conn,
        "INSERT INTO reviews (booking_id,customer_id,cleaner_id,rating,comment)
         VALUES ($bid,$me,$cid,$rating,'$comment')"
    );
    $success = true;
}
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
        <h5 class="text-center mb-1">Leave a Review</h5>
        <p class="text-center text-muted mb-4">How was <strong><?= htmlspecialchars($b['cleaner']) ?></strong>?</p>

        <?php if ($success): ?>
        <div class="text-center">
            <div style="font-size:50px;">⭐</div>
            <h5>Thank you for your review!</h5>
            <a href="my-bookings.php" class="btn btn-dark-main mt-2">Back to My Bookings</a>
        </div>
        <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Your Rating</label>
                <div class="d-flex gap-3 flex-wrap">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="rating" value="<?= $i ?>" id="s<?= $i ?>" required>
                        <label class="form-check-label stars" for="s<?= $i ?>">★ <?= $i ?></label>
                    </div>
                <?php endfor; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Comment (optional)</label>
                <textarea name="comment" class="form-control" rows="3"
                    placeholder="e.g. The cleaner was on time and did a great job!"></textarea>
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold">Submit Review</button>
            <a href="my-bookings.php" class="btn btn-outline-secondary w-100 mt-2">Skip</a>
        </form>
        <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
