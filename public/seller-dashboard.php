<?php
session_start();

// Redirect if not logged in as seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

// Include header (navigation bar)
include "../seller/seller_header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Seller Dashboard</title>
<link rel="stylesheet" href="../public/css/seller-dashboard.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<section class="dashboard-hero">
  <div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Seller') ?>!</h1>
    <p>Use the navigation bar above to access your gems, blogs, and account settings.</p>
    <a href="../seller/add-gem.php" class="btn-primary"><i class="fa fa-plus"></i> Add New Gem</a>
    <a href="../seller/add-blog.php" class="btn-primary"><i class="fa fa-pencil"></i> Add New Blog</a>
  </div>
</section>

</body>
</html>
