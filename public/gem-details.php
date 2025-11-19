<?php
include __DIR__ . '/../config/db.php'; 
session_start();

$gem_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$res = mysqli_query($conn, "SELECT * FROM gems WHERE id='$gem_id' AND status='approved'");
if(mysqli_num_rows($res) == 0){
    die("Gem not found");
}
$gem = mysqli_fetch_assoc($res);

// Increment views
mysqli_query($conn, "UPDATE gems SET views = views + 1 WHERE id='$gem_id'");

// Fetch gem images
$images_res = mysqli_query($conn, "SELECT * FROM gem_images WHERE gem_id='$gem_id'");
$images = [];
while($row = mysqli_fetch_assoc($images_res)){
    if(file_exists('/' . $row['image_path'])){
        $images[] = '/' . $row['image_path'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gem Details - GemMarketplace</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

<header class="bg-white shadow">
  <div class="container mx-auto flex justify-between items-center p-4">
    <a href="#" class="text-2xl font-bold text-gray-800">GemMarketplace</a>
    <nav class="space-x-4">
      <a href="#" class="text-gray-600 hover:text-gray-800">Home</a>
      <a href="#" class="text-gray-600 hover:text-gray-800">Browse Gems</a>
      <a href="#" class="text-gray-600 hover:text-gray-800">Login</a>
    </nav>
  </div>
</header>

<section class="container mx-auto mt-10 px-4 grid grid-cols-1 md:grid-cols-2 gap-6">
  <!-- Gem Images -->
  <div>
    <?php $mainImage = !empty($images) ? $images[0] : 'https://via.placeholder.com/600x500'; ?>
    <img src="<?php echo htmlspecialchars($mainImage); ?>" id="main-gem-image" class="rounded mb-4 w-full object-cover" alt="Gem Main">

    <div class="grid grid-cols-3 gap-2">
      <?php foreach($images as $img): ?>
        <img src="<?php echo htmlspecialchars($img); ?>" 
             class="rounded cursor-pointer gem-thumb" 
             alt="Gem Thumbnail"
             onclick="document.getElementById('main-gem-image').src='<?php echo $img; ?>'">
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Gem Details -->
  <div class="bg-white shadow rounded p-6">
    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($gem['title']); ?></h1>
    <p class="text-gray-600 mb-4">
        <?php echo htmlspecialchars($gem['carat']); ?> Carat • 
        <?php echo htmlspecialchars($gem['color']); ?> • 
        <?php echo htmlspecialchars($gem['clarity']); ?>
    </p>
    <p class="text-blue-600 font-semibold text-2xl mb-4">Rs <?php echo number_format($gem['price'],2); ?></p>
    
    <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 mb-4">Contact Seller</button>
    <button class="w-full border border-blue-600 text-blue-600 py-2 rounded hover:bg-blue-50">Add to Wishlist</button>

    <hr class="my-4">

    <h2 class="font-bold mb-2">Gem Certificate</h2>
    <?php if(!empty($gem['certificate']) && file_exists('../' . $gem['certificate'])): ?>
        <a href="<?php echo '../' . htmlspecialchars($gem['certificate']); ?>" class="text-blue-600 underline" target="_blank">View Certification PDF</a>
    <?php else: ?>
        <p>N/A</p>
    <?php endif; ?>

</div>
</section>

</body>
</html>
