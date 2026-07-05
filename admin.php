<?php
include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); exit();
}

$msg = "";

// ── Handle all POST actions 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Approve a cleaner
    if (isset($_POST['approve'])) {
        $uid = (int)$_POST['uid'];
        mysqli_query($conn, "UPDATE users SET verified=1 WHERE id=$uid");
        $msg = "Cleaner approved successfully.";
    }

    // Reject a cleaner
    if (isset($_POST['reject'])) {
        $uid = (int)$_POST['uid'];
        mysqli_query($conn, "UPDATE users SET verified=0 WHERE id=$uid");
        $msg = "Cleaner rejected.";
    }

    // Suspend a user account
    if (isset($_POST['suspend'])) {
        $uid = (int)$_POST['uid'];
        mysqli_query($conn, "UPDATE users SET status='suspended' WHERE id=$uid");
        $msg = "User account suspended.";
    }

    // Reactivate a user account
    if (isset($_POST['activate'])) {
        $uid = (int)$_POST['uid'];
        mysqli_query($conn, "UPDATE users SET status='active' WHERE id=$uid");
        $msg = "User account reactivated.";
    }

    // Add a new service
    if (isset($_POST['add_service'])) {
        $n = mysqli_real_escape_string($conn, $_POST['sname']);
        $d = mysqli_real_escape_string($conn, $_POST['sdesc']);
        $p = (float)$_POST['sprice'];
        $h = (float)$_POST['shours'];
        $c = mysqli_real_escape_string($conn, $_POST['scat']);
        mysqli_query($conn, "INSERT INTO services (name,description,price,hours,category) VALUES ('$n','$d',$p,$h,'$c')");
        $msg = "New service added.";
    }

    // Edit an existing service
    if (isset($_POST['edit_service'])) {
        $id = (int)$_POST['sid'];
        $n  = mysqli_real_escape_string($conn, $_POST['sname']);
        $d  = mysqli_real_escape_string($conn, $_POST['sdesc']);
        $p  = (float)$_POST['sprice'];
        $h  = (float)$_POST['shours'];
        $c  = mysqli_real_escape_string($conn, $_POST['scat']);
        mysqli_query($conn, "UPDATE services SET name='$n',description='$d',price=$p,hours=$h,category='$c' WHERE id=$id");
        $msg = "Service updated.";
    }

    // Remove a service
    if (isset($_POST['delete_service'])) {
        $id = (int)$_POST['sid'];
        mysqli_query($conn, "DELETE FROM services WHERE id=$id");
        $msg = "Service removed.";
    }

    header("Location: admin.php?msg=" . urlencode($msg)); exit();
}

if (isset($_GET['msg'])) $msg = $_GET['msg'];

// Counts for the dashboard
$total_users    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as n FROM users WHERE role!='admin'"))['n'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as n FROM bookings"))['n'];
$pending_clean  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as n FROM users WHERE role='cleaner' AND verified=0"))['n'];
$total_payments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as t FROM payments"))['t'];
?>

<h3 class="section-title">Admin Panel</h3>

<?php if ($msg): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Dashboard stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h2><?= $total_users ?></h2>
            <small class="text-muted">Total Users</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h2><?= $total_bookings ?></h2>
            <small class="text-muted">Total Bookings</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h2 class="<?= $pending_clean > 0 ? 'text-warning' : '' ?>"><?= $pending_clean ?></h2>
            <small class="text-muted">Pending Approvals</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h2>TZS <?= number_format($total_payments ?? 0) ?></h2>
            <small class="text-muted">Total Payments</small>
        </div>
    </div>
</div>

<!-- TABS -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab1">
        Verify Cleaners <?php if ($pending_clean): ?><span class="badge bg-warning text-dark"><?= $pending_clean ?></span><?php endif; ?>
    </a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab2">Manage Services</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab3">Manage Users</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab4">All Bookings</a></li>
</ul>

<div class="tab-content">

<!-- TAB 1: Verify Cleaners -->
<div class="tab-pane fade show active" id="tab1">
    <h5 class="mb-3">Cleaners &amp; Verification</h5>
    <?php
    $cleaners = mysqli_query($conn,"SELECT * FROM users WHERE role='cleaner' ORDER BY verified ASC, id DESC");
    if (mysqli_num_rows($cleaners) == 0): ?>
        <div class="alert alert-info">No cleaners registered yet.</div>
    <?php else: ?>
    <div class="row g-3">
    <?php while ($c = mysqli_fetch_assoc($cleaners)): ?>
        <div class="col-md-6">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-0"><?= htmlspecialchars($c['name']) ?></h6>
                        <small class="text-muted"><?= $c['email'] ?> | <?= $c['phone'] ?></small>
                    </div>
                    <?php if ($c['verified']): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                </div>

                <!-- Show the uploaded ID document -->
                <?php if ($c['document']): ?>
                <div class="mb-2">
                    <small class="text-muted">ID Document:</small><br>
                    <?php
                    $ext = strtolower(pathinfo($c['document'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png'])): ?>
                        <img src="/cleanpro/<?= $c['document'] ?>" style="max-height:80px;border-radius:6px;border:1px solid #ddd;" alt="ID">
                    <?php else: ?>
                        <a href="/cleanpro/<?= $c['document'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-pdf"></i> View PDF Document
                        </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <small class="text-muted d-block mb-2">No document uploaded.</small>
                <?php endif; ?>

                <!-- Approve / Reject buttons -->
                <div class="d-flex gap-2 flex-wrap">
                    <?php if (!$c['verified']): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="uid" value="<?= $c['id'] ?>">
                        <button name="approve" class="btn btn-success btn-sm">✔ Approve</button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="uid" value="<?= $c['id'] ?>">
                        <button name="reject" class="btn btn-danger btn-sm">✖ Reject</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="uid" value="<?= $c['id'] ?>">
                        <button name="reject" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove approval?')">Revoke Approval</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<!-- TAB 2: Manage Services -->
<div class="tab-pane fade" id="tab2">
    <div class="row g-4">

        <!-- Add new service -->
        <div class="col-md-4">
            <div class="card p-3">
                <h6 class="mb-3">Add New Service</h6>
                <form method="POST">
                    <div class="mb-2"><input type="text"   name="sname"  class="form-control form-control-sm" placeholder="Service Name" required></div>
                    <div class="mb-2"><input type="text"   name="sdesc"  class="form-control form-control-sm" placeholder="Description" required></div>
                    <div class="mb-2"><input type="number" name="sprice" class="form-control form-control-sm" placeholder="Price (TZS)" required></div>
                    <div class="mb-2"><input type="number" name="shours" step="0.5" class="form-control form-control-sm" placeholder="Hours" required></div>
                    <div class="mb-2">
                        <select name="scat" class="form-select form-select-sm" required>
                            <option value="home">Home</option>
                            <option value="office">Office</option>
                            <option value="deep">Deep Clean</option>
                        </select>
                    </div>
                    <button name="add_service" class="btn btn-dark-main btn-sm w-100">Add Service</button>
                </form>
            </div>
        </div>

        <!-- Existing services with Edit and Delete -->
        <div class="col-md-8">
            <h6 class="mb-3">Current Services</h6>
            <?php $svcs = mysqli_query($conn,"SELECT * FROM services"); ?>
            <?php while ($sv = mysqli_fetch_assoc($svcs)): ?>
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?= htmlspecialchars($sv['name']) ?></strong>
                        <span class="badge bg-secondary ms-2"><?= $sv['category'] ?></span><br>
                        <small class="text-muted"><?= htmlspecialchars($sv['description']) ?></small><br>
                        <small>TZS <?= number_format($sv['price']) ?> &nbsp;|&nbsp; <?= $sv['hours'] ?> hrs</small>
                    </div>
                    <!-- Delete button -->
                    <form method="POST" class="d-inline ms-2">
                        <input type="hidden" name="sid" value="<?= $sv['id'] ?>">
                        <button name="delete_service" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this service?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                <!-- Edit form (collapsed by default) -->
                <div class="mt-2">
                    <button class="btn btn-outline-secondary btn-sm" type="button"
                        data-bs-toggle="collapse" data-bs-target="#edit<?= $sv['id'] ?>">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <div class="collapse mt-2" id="edit<?= $sv['id'] ?>">
                        <form method="POST" class="row g-2">
                            <input type="hidden" name="sid" value="<?= $sv['id'] ?>">
                            <div class="col-6"><input type="text"   name="sname"  class="form-control form-control-sm" value="<?= htmlspecialchars($sv['name']) ?>" required></div>
                            <div class="col-6"><input type="text"   name="sdesc"  class="form-control form-control-sm" value="<?= htmlspecialchars($sv['description']) ?>" required></div>
                            <div class="col-4"><input type="number" name="sprice" class="form-control form-control-sm" value="<?= $sv['price'] ?>" required></div>
                            <div class="col-4"><input type="number" name="shours" step="0.5" class="form-control form-control-sm" value="<?= $sv['hours'] ?>" required></div>
                            <div class="col-4">
                                <select name="scat" class="form-select form-select-sm">
                                    <option value="home"   <?= $sv['category']=='home'   ?'selected':'' ?>>Home</option>
                                    <option value="office" <?= $sv['category']=='office' ?'selected':'' ?>>Office</option>
                                    <option value="deep"   <?= $sv['category']=='deep'   ?'selected':'' ?>>Deep</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button name="edit_service" class="btn btn-dark-main btn-sm">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- TAB 3: Manage Users  -->
<div class="tab-pane fade" id="tab3">
    <h5 class="mb-3">All Users (Customers &amp; Cleaners)</h5>
    <?php
    $users = mysqli_query($conn,"SELECT * FROM users WHERE role != 'admin' ORDER BY role, name ASC");
    ?>
    <div class="table-box">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= $u['email'] ?></td>
                <td><?= $u['phone'] ?></td>
                <td><span class="badge bg-secondary"><?= ucfirst($u['role']) ?></span></td>
                <td>
                    <?php if ($u['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Suspended</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($u['status'] == 'active'): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                        <button name="suspend" class="btn btn-warning btn-sm"
                            onclick="return confirm('Suspend this account?')">Suspend</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                        <button name="activate" class="btn btn-success btn-sm">Reactivate</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- TAB 4: All Bookings -->
<div class="tab-pane fade" id="tab4">
    <h5 class="mb-3">All Bookings</h5>
    <?php
    $bks = mysqli_query($conn,
        "SELECT b.*, s.name as service, c.name as customer, cl.name as cleaner
         FROM bookings b
         JOIN services s ON b.service_id = s.id
         JOIN users c    ON b.customer_id = c.id
         JOIN users cl   ON b.cleaner_id  = cl.id
         ORDER BY b.created_at DESC"
    );
    ?>
    <div class="table-box">
        <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Service</th><th>Customer</th><th>Cleaner</th><th>Date</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php while ($bk = mysqli_fetch_assoc($bks)): ?>
            <tr>
                <td><?= $bk['service'] ?></td>
                <td><?= $bk['customer'] ?></td>
                <td><?= $bk['cleaner'] ?></td>
                <td><?= date('d M Y', strtotime($bk['date'])) ?></td>
                <td>TZS <?= number_format($bk['amount']) ?></td>
                <td><span class="badge-<?= $bk['status'] ?>"><?= ucfirst($bk['status']) ?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</div><!-- end tab-content -->

<?php include 'includes/footer.php'; ?>