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
<link rel="stylesheet" href="../css/admin-header.css">

<header class="navbar">
  <div class="header-container">
    <!-- LOGO -->
    <a href="admin-dashboard.php" class="navbar-brand">Gem Admin</a>

    <!-- HAMBURGER BUTTON FOR MOBILE ONLY -->
    <button class="navbar-toggler" aria-label="Toggle Menu">&#9776;</button>

    <!-- NAVIGATION -->
    <nav class="navbar-collapse">
      <ul class="navbar-nav">
        <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
        <li class="nav-item"><a href="approve-sellers.php" class="nav-link">Approve Sellers</a></li>
        <li class="nav-item"><a href="approve-listings.php" class="nav-link">Approve Listings</a></li>
        <li class="nav-item"><a href="blog_approve.php" class="nav-link">Approve Blogs</a></li>
        <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
      </ul>
    </nav>
  </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    menuToggle.addEventListener('click', () => {
        navbarCollapse.classList.toggle('active');
    });
});
</script>
