<?php
include "config/db.php";
session_start();

$user_id = $_SESSION['user_id'];
$gem_id = $_GET['gem_id'];

$res = mysqli_query($conn,"SELECT * FROM wishlist WHERE user_id='$user_id' AND gem_id='$gem_id'");
if(mysqli_num_rows($res)>0){
    mysqli_query($conn,"DELETE FROM wishlist WHERE user_id='$user_id' AND gem_id='$gem_id'");
} else {
    mysqli_query($conn,"INSERT INTO wishlist(user_id,gem_id) VALUES ('$user_id','$gem_id')");
}

header("Location: wishlist.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wishlist - GemMarketplace</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

<section class="container mx-auto mt-10 px-4">
  <h1 class="text-2xl font-bold mb-6">Your Wishlist</h1>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    
    <div class="bg-white shadow rounded overflow-hidden">
      <img src="https://via.placeholder.com/400x300" alt="Gem" class="w-full h-48 object-cover">
      <div class="p-4">
        <h3 class="font-bold text-lg mb-1">Red Ruby</h3>
        <p class="text-gray-600 mb-2">1.5 Carat • Red • Certified</p>
        <p class="text-blue-600 font-semibold">$1,200</p>
        <button class="mt-3 w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">Remove</button>
      </div>
    </div>

    <!-- Repeat gem cards -->

  </div>
</section>

</body>
</html>
