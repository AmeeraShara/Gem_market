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
<link rel="stylesheet" href="../css/header.css">

<header>
  <div class="header-container">
    <!-- LOGO -->
    <a href="admin-dashboard.php" class="logo">Gem Admin</a>

    <!-- HAMBURGER BUTTON FOR MOBILE -->
    <button class="menu-toggle" aria-label="Toggle Menu">&#9776;</button>

    <!-- NAVIGATION -->
    <nav class="header-nav">
      <a href="dashboard.php">Dashboard</a>
      <a href="approve-sellers.php">Approve Sellers</a>
      <a href="approve-listings.php">Approve Listings</a>
      <a href="blog_approve.php">Approve Blogs</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </div>
</header>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const headerNav = document.querySelector('.header-nav');

    menuToggle.addEventListener('click', () => {
      headerNav.classList.toggle('active');
    });
  });
</script>
