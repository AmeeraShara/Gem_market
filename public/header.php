<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="css/header.css">

<header>
  <div class="container header-container">
    <a href="#" class="logo">Gem</a>

    <nav class="header-nav">
      <a href="index.php">Home</a>
      <a href="blog.php">Blog</a>
      <a href="browse_gems.php">Browse Gems</a>

      <?php if (isset($_SESSION['user_id'])): ?>
          <a href="wishlist.php">Wishlist</a>
          <a href="logout.php">Logout</a>
      <?php else: ?>
          <a href="login.php">Login</a>
      <?php endif; ?>
    </nav>

  </div>
</header>
