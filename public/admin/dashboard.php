<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - GemMarketplace</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

<!-- Navigation Bar -->
<nav class="bg-white shadow">
  <div class="container mx-auto px-4 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-6">
      <a href="admin-dashboard.php" class="text-xl font-bold text-gray-800 hover:text-blue-600">GemMarketplace Admin</a>
      <a href="approve-sellers.php" class="text-gray-700 hover:text-blue-600">Approve Sellers</a>
      <a href="approve-listings.php" class="text-gray-700 hover:text-blue-600">Approve Listings</a>
    </div>
    <div>
      <a href="../logout.php" class="text-red-600 hover:underline">Logout</a>
    </div>
  </div>
</nav>

<!-- Main Content -->
<section class="container mx-auto mt-6 px-4">
  <h1 class="text-2xl font-bold mb-4">Welcome, Admin!</h1>
  <p class="text-gray-700">Use the navigation bar above to manage sellers and listings.</p>
</section>

</body>
</html>
