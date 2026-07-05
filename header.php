<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CleanPro Tanzania</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/cleanpro/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background:#343a40;">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="/cleanpro/index.php">
        <i class="bi bi-stars me-1"></i>CleanPro
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="/cleanpro/services.php">Services</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] == 'customer'): ?>
                <li class="nav-item"><a class="nav-link" href="/cleanpro/my-bookings.php">My Bookings</a></li>
            <?php elseif ($_SESSION['role'] == 'cleaner'): ?>
                <li class="nav-item"><a class="nav-link" href="/cleanpro/cleaner-jobs.php">My Jobs</a></li>
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="/cleanpro/admin.php">Admin Panel</a></li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link text-warning" href="/cleanpro/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout (<?= htmlspecialchars($_SESSION['name']) ?>)
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/cleanpro/login.php">Login</a></li>
            <li class="nav-item ms-2">
                <a class="btn btn-outline-light btn-sm px-3" href="/cleanpro/register.php">Register</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">