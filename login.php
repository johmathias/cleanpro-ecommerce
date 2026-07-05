<?php
include 'includes/header.php';
include 'config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $res  = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($pass, $user['password'])) {
        if ($user['status'] == 'suspended') {
            $error = "Your account has been suspended. Please contact admin.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] == 'admin')    { header("Location: admin.php");       exit(); }
            if ($user['role'] == 'cleaner')  { header("Location: cleaner-jobs.php");exit(); }
            if ($user['role'] == 'customer') { header("Location: my-bookings.php"); exit(); }
        }
    } else {
        $error = "Wrong email or password. Please try again.";
    }
}
?>

<div class="form-box">
    <h4 class="text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Login</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="yourname@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Your password" required>
        </div>
        <button type="submit" class="btn btn-dark-main w-100">Login</button>
    </form>
    <p class="text-center mt-3 text-muted small">No account? <a href="register.php">Register here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
