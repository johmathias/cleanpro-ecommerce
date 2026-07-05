<?php
include 'includes/header.php';
include 'config/db.php';
$cat = isset($_GET['cat']) ? $_GET['cat'] : 'all';
?>

<h3 class="section-title">Our Cleaning Services</h3>

<!-- FILTER BUTTONS — centered -->
<div class="text-center mb-4">
    <a href="?cat=all"    class="btn btn-sm me-1 <?= $cat=='all'    ? 'btn-dark-main' : 'btn-outline-secondary' ?>">All</a>
    <a href="?cat=home"   class="btn btn-sm me-1 <?= $cat=='home'   ? 'btn-dark-main' : 'btn-outline-secondary' ?>">Home</a>
    <a href="?cat=office" class="btn btn-sm me-1 <?= $cat=='office' ? 'btn-dark-main' : 'btn-outline-secondary' ?>">Office</a>
    <a href="?cat=deep"   class="btn btn-sm      <?= $cat=='deep'   ? 'btn-dark-main' : 'btn-outline-secondary' ?>">Deep Clean</a>
</div>

<!-- SERVICES LIST -->
<?php
$where = ($cat != 'all') ? "WHERE category='$cat'" : "";
$svcs  = mysqli_query($conn, "SELECT * FROM services $where");
?>
<div class="row g-4 mb-5">
<?php while ($s = mysqli_fetch_assoc($svcs)):
    $img = $s['category']=='home'
    ? "/cleanpro/images/home-cleaning.jpg"
    : ($s['category']=='office'
        ? "/cleanpro/images/office-cleaning.jpg"
        : "/cleanpro/images/deep-cleaning.jpg");
?>
    <div class="col-md-4">
        <div class="card h-100">
            <img src="<?= $img ?>" class="card-img-top" alt="<?= $s['name'] ?>">
            <div class="card-body text-center">
                <span class="badge bg-secondary mb-2"><?= ucfirst($s['category']) ?></span>
                <h5><?= $s['name'] ?></h5>
                <p class="text-muted small"><?= $s['description'] ?></p>
                <p class="text-muted small"><i class="bi bi-clock"></i> <?= $s['hours'] ?> hours</p>
                <p class="fw-bold fs-5" style="color:var(--dark);">TZS <?= number_format($s['price']) ?></p>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>

<!-- AVAILABLE CLEANERS -->
<h3 class="section-title">Available Verified Cleaners</h3>
<div class="row g-4">
<?php
$cleaners = mysqli_query($conn, "SELECT * FROM users WHERE role='cleaner' AND verified=1 AND status='active'");
if (mysqli_num_rows($cleaners) == 0): ?>
    <div class="col-12 text-center text-muted">No verified cleaners available yet.</div>
<?php endif;
while ($c = mysqli_fetch_assoc($cleaners)):
    $r   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT AVG(rating) as avg, COUNT(*) as total FROM reviews WHERE cleaner_id={$c['id']}"));
    $avg = $r['avg'] ? round($r['avg'],1) : 0;
    $tot = $r['total'];
    $stars = str_repeat('★', (int)round($avg)) . str_repeat('☆', 5 - (int)round($avg));
?>
    <div class="col-md-3">
        <div class="card text-center p-3 h-100">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&size=95&background=343a40&color=fff&rounded=true"
                 class="cleaner-photo mx-auto mb-2" alt="<?= $c['name'] ?>">
            <h6 class="mb-0"><?= htmlspecialchars($c['name']) ?></h6>
            <small class="verified"><i class="bi bi-check-circle-fill"></i> Verified</small>
            <div class="stars my-1"><?= $stars ?></div>
            <small class="text-muted"><?= $avg ?>/5 (<?= $tot ?> reviews)</small>
            <a href="cleaner-profile.php?id=<?= $c['id'] ?>" class="btn btn-dark-main btn-sm mt-3">
                View Profile &amp; Book
            </a>
        </div>
    </div>
<?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>
