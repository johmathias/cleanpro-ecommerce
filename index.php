<?php
include 'includes/header.php';
include 'config/db.php';
?>

<!-- HERO BANNER -->
<div class="hero">
    <h1 class="fw-bold display-5">Find a Trusted Cleaner Near You</h1>
    <p class="lead mt-2 mb-4">Book verified home and office cleaning in minutes. Pay with M-Pesa.</p>
    <a href="services.php" class="btn btn-light btn-lg me-2">Browse Services</a>
    <a href="register.php" class="btn btn-outline-light btn-lg">Join Free</a>
</div>

<!-- HOW IT WORKS -->
<h3 class="section-title">How CleanPro Works</h3>
<div class="row g-4 mb-5 text-center">
    <div class="col-md-4">
        <div class="card h-100">
            <img src="/cleanpro/images/how1.jpg" class="card-img-top" alt="Search">
            <div class="card-body">
                <h5>1. Search</h5>
                <p class="text-muted">Browse our verified cleaning services and pick what you need.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <img src="/cleanpro/images/how2.jpg" class="card-img-top" alt="Book">
            <div class="card-body">
                <h5>2. Book</h5>
                <p class="text-muted">Choose a cleaner, pick a date and time, enter your address.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <img src="/cleanpro/images/how3.jpg" class="card-img-top" alt="Pay">
            <div class="card-body">
                <h5>3. Pay</h5>
                <p class="text-muted">Pay safely using M-Pesa. No cash. No hassle.</p>
            </div>
        </div>
    </div>
</div>

<!-- SERVICES PREVIEW -->
<h3 class="section-title">Our Services</h3>
<div class="row g-4 mb-5">
<?php
$res = mysqli_query($conn, "SELECT * FROM services LIMIT 3");
while ($s = mysqli_fetch_assoc($res)):
    $img = $s['category']=='home'
    ? "/cleanpro/images/home-cleaning.jpg"
    : ($s['category']=='office'
        ? "/cleanpro/images/office-cleaning.jpg"
        : "/cleanpro/images/deep-cleaning.jpg");
?>
    <div class="col-md-4">
        <div class="card h-100">
            <img src="<?= $img ?>" class="card-img-top" alt="<?= $s['name'] ?>">
            <div class="card-body">
                <h5><?= $s['name'] ?></h5>
                <p class="text-muted small"><?= $s['description'] ?></p>
                <p class="fw-bold" style="color:var(--dark);">TZS <?= number_format($s['price']) ?></p>
                <a href="services.php" class="btn btn-dark-main btn-sm">Book Now</a>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>

<!-- WHY CHOOSE US -->
<div class="bg-white rounded p-4 shadow-sm mb-5">
    <h3 class="section-title">Why Choose CleanPro?</h3>
    <div class="row text-center g-3">
        <div class="col-md-3">
            <i class="bi bi-shield-check" style="font-size:36px;color:#343a40;"></i>
            <h6 class="mt-2">Verified Cleaners</h6>
            <p class="text-muted small">ID-checked before listing</p>
        </div>
        <div class="col-md-3">
            <i class="bi bi-cash-coin" style="font-size:36px;color:#343a40;"></i>
            <h6 class="mt-2">Clear Pricing</h6>
            <p class="text-muted small">Fixed prices, no hidden fees</p>
        </div>
        <div class="col-md-3">
            <i class="bi bi-phone" style="font-size:36px;color:#343a40;"></i>
            <h6 class="mt-2">M-Pesa Payment</h6>
            <p class="text-muted small">Pay safely from your phone</p>
        </div>
        <div class="col-md-3">
            <i class="bi bi-star" style="font-size:36px;color:#343a40;"></i>
            <h6 class="mt-2">Rated Cleaners</h6>
            <p class="text-muted small">Read real customer reviews</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
