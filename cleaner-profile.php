<?php

include 'includes/header.php';
include 'config/db.php';

$cid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get cleaner info
$c = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE id=$cid AND role='cleaner' AND verified=1 AND status='active'"
));

if (!$c) {
    echo "<div class='alert alert-danger text-center'>Cleaner not found.</div>";
    include 'includes/footer.php'; exit();
}

// Rating info
$r   = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT AVG(rating) as avg, COUNT(*) as total FROM reviews WHERE cleaner_id=$cid"
));
$avg   = $r['avg'] ? round($r['avg'], 1) : 0;
$total = $r['total'];
$stars = str_repeat('★', (int)round($avg)) . str_repeat('☆', 5 - (int)round($avg));

// Completed jobs
$jobs = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as n FROM bookings WHERE cleaner_id=$cid AND status='completed'"
))['n'];

// All reviews for this cleaner
$reviews = mysqli_query($conn,
    "SELECT rv.*, u.name as customer_name
     FROM reviews rv
     JOIN users u ON rv.customer_id = u.id
     WHERE rv.cleaner_id = $cid
     ORDER BY rv.created_at DESC"
);
?>

<div class="row justify-content-center">
  <div class="col-md-8">

    <!-- CLEANER PROFILE CARD -->
    <div class="card p-4 mb-4 text-center">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&size=120&background=343a40&color=fff&rounded=true"
             class="cleaner-photo mx-auto mb-3" style="width:120px;height:120px;" alt="<?= $c['name'] ?>">
        <h4 class="mb-0"><?= htmlspecialchars($c['name']) ?></h4>
        <small class="verified mb-2"><i class="bi bi-check-circle-fill"></i> Verified Cleaner</small>
        <div class="stars fs-4 mb-1"><?= $stars ?></div>
        <p class="text-muted mb-1"><?= $avg ?> / 5 stars</p>
        <p class="text-muted small"><?= $total ?> reviews &nbsp;|&nbsp; <?= $jobs ?> jobs completed</p>
        <a href="book.php?cleaner=<?= $cid ?>" class="btn btn-dark-main mt-2 px-4">
            <i class="bi bi-calendar-check"></i> Book This Cleaner
        </a>
    </div>

    <!-- REVIEWS SECTION -->
    <div class="card p-4">
        <h5 class="mb-3"><i class="bi bi-chat-left-text"></i> Customer Reviews</h5>

        <?php if (mysqli_num_rows($reviews) == 0): ?>
            <p class="text-muted">No reviews yet for this cleaner.</p>
        <?php endif; ?>

        <?php while ($rv = mysqli_fetch_assoc($reviews)): ?>
        <div class="border-bottom pb-3 mb-3">
            <strong><?= htmlspecialchars($rv['customer_name']) ?></strong>
            <span class="stars ms-2">
                <?= str_repeat('★', $rv['rating']) . str_repeat('☆', 5 - $rv['rating']) ?>
            </span>
            <br>
            <?php if ($rv['comment']): ?>
                <p class="text-muted mb-1"><?= htmlspecialchars($rv['comment']) ?></p>
            <?php endif; ?>
            <small class="text-muted"><?= date('d M Y', strtotime($rv['created_at'])) ?></small>
        </div>
        <?php endwhile; ?>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
