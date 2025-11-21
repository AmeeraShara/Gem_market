<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="css/header.css">

<header>
  <div class="header-container">
    <!-- LOGO -->
    <a href="index.php" class="logo">Gem</a>

    <!-- HAMBURGER BUTTON FOR MOBILE -->
    <button class="menu-toggle" aria-label="Toggle Menu">&#9776;</button>

    <!-- NAVIGATION -->
    <nav class="header-nav">
      <a href="index.php">Home</a>
      <a href="blog.php">Blog</a>
      <a href="browse_gems.php">Browse Gems</a>

      <?php if (isset($_SESSION['user_id'])): ?>
          <a href="logout.php">Logout</a>
      <?php else: ?>
          <a href="login.php">Login</a>
      <?php endif; ?>
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
