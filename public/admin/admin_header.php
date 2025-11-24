<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to logged-in admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="../css/admin-header.css">

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-3" href="admin-dashboard.php">Gem Admin</a>

        <!-- Hamburger Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Menu -->
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto gap-lg-3">

                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="approve-sellers.php">Approve Sellers</a></li>
                <li class="nav-item"><a class="nav-link" href="approve-listings.php">Approve Listings</a></li>
                <li class="nav-item"><a class="nav-link" href="blog_approve.php">Approve Blogs</a></li>
                <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>

            </ul>
        </div>

    </div>
</nav>
