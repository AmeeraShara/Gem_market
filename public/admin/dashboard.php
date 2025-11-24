<?php
session_start();
include 'admin_header.php';

// Restrict access to logged-in admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard - GemMarketplace</title>
<link rel="stylesheet" href="css/admin-dashboard.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<section class="dashboard-hero">
  <div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
    <p>Use the navigation bar above to manage sellers, listings, blogs, and user accounts.</p>
    
    <a href="approve-sellers.php" class="btn-primary"><i class="fa fa-user-check"></i> Approve Sellers</a>
    <a href="approve-listings.php" class="btn-primary"><i class="fa fa-gem"></i> Approve Listings</a>
    <a href="blog_approve.php" class="btn-primary"><i class="fa fa-pencil"></i> Approve Blogs</a>
    <a href="dashboard.php" class="btn-primary"><i class="fa fa-users"></i> Manage Users</a>
  </div>
</section>

</body>
</html>
