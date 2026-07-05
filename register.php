<?php
include 'includes/header.php';
include 'config/db.php';

$error = ""; $success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pass  = $_POST['password'];
    $role  = $_POST['role'];

    if (empty($name) || empty($email) || empty($phone) || empty($pass)) {
        $error = "Please fill in all fields.";
    } elseif ($role == 'cleaner' && (!isset($_FILES['document']) || $_FILES['document']['error'] != 0)) {
        $error = "Cleaners must upload an ID document (National ID or Passport).";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered.";
        } else {
            $hashed  = password_hash($pass, PASSWORD_DEFAULT);
            $doc_path = "";

            // Handle cleaner document upload
            if ($role == 'cleaner') {
                $ext     = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','pdf'];
                if (!in_array($ext, $allowed)) {
                    $error = "ID document must be JPG, PNG, or PDF.";
                } else {
                    $fname = "id_" . time() . "." . $ext;
                    move_uploaded_file($_FILES['document']['tmp_name'], "uploads/" . $fname);
                    $doc_path = "uploads/" . $fname;
                }
            }

            if (empty($error)) {
                $doc = mysqli_real_escape_string($conn, $doc_path);
                mysqli_query($conn,
                    "INSERT INTO users (name,email,password,phone,role,verified,status,document)
                     VALUES ('$name','$email','$hashed','$phone','$role',0,'active','$doc')"
                );
                $msg = $role == 'cleaner'
                    ? "Account created! Wait for admin to approve your document before you can receive bookings."
                    : "Account created! You can now login.";
                $success = $msg;
            }
        }
    }
}
?>

<div class="form-box">
    <h4 class="text-center mb-4"><i class="bi bi-person-plus"></i> Create Account</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?> <a href="login.php">Login &rarr;</a></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">I am registering as:</label>
            <select name="role" id="roleSelect" class="form-select" onchange="toggleDoc()">
                <option value="customer">Customer — I want to book cleaners</option>
                <option value="cleaner">Cleaner — I want to offer services</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Amina Juma" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="yourname@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number (M-Pesa)</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 0712345678" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Choose a password" required>
        </div>

        <!-- Only visible when Cleaner is chosen -->
        <div class="mb-3" id="docBox" style="display:none;">
            <label class="form-label">Upload ID Document <span class="text-danger">*</span></label>
            <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            <small class="text-muted">Required for cleaners. JPG, PNG, or PDF only. Admin will review and approve.</small>
        </div>

        <button type="submit" class="btn btn-dark-main w-100">Register</button>
    </form>
    <p class="text-center mt-3 text-muted small">Already have an account? <a href="login.php">Login here</a></p>
</div>

<script>
function toggleDoc() {
    var role = document.getElementById('roleSelect').value;
    document.getElementById('docBox').style.display = role === 'cleaner' ? 'block' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
