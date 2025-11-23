<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="css/header.css">

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-3" href="seller-dashboard.php">Seller Dashboard</a>

        <!-- Hamburger Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Menu -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto gap-lg-3">

                <li class="nav-item"><a class="nav-link" href="seller-dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../seller/add-gem.php">Add New Gem</a></li>
                <li class="nav-item"><a class="nav-link" href="browse_gems.php">Browse Gems</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>

            </ul>
        </div>

    </div>
</nav>
