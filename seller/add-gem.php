<?php
include "../config/db.php";
session_start();

// Ensure only sellers can access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: ../public/login.php");
    exit;
}

$msg = "";

if(isset($_POST['submit'])) {

    $seller_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $type = $_POST['type'];
    $carat = $_POST['carat'];
    $color = $_POST['color'];
    $clarity = $_POST['clarity'];
    $origin = $_POST['origin'];
    $price = $_POST['price'];

    // Set directories (inside public folder)
    $cert_dir = "../public/uploads/certificates/";
    $gems_dir = "../public/uploads/gems/";

    // Create directories if they don't exist
    if(!is_dir($cert_dir)) mkdir($cert_dir, 0777, true);
    if(!is_dir($gems_dir)) mkdir($gems_dir, 0777, true);

    // Upload certificate
    $cert_name = time() . "_" . basename($_FILES['certificate']['name']);
    $cert_path = $cert_dir . $cert_name;

    if(!move_uploaded_file($_FILES['certificate']['tmp_name'], $cert_path)){
        $msg = "Failed to upload certificate.";
    } else {
        // Insert gem listing
        $stmt = $conn->prepare("INSERT INTO gems 
            (seller_id, title, type, carat, color, clarity, origin, certificate, price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssssd", $seller_id, $title, $type, $carat, $color, $clarity, $origin, $cert_path, $price);
        $stmt->execute();
        $gem_id = $stmt->insert_id;
        $stmt->close();

        // Upload gem images
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            $img_name = time() . "_" . basename($_FILES['images']['name'][$key]);
            $img_path = $gems_dir . $img_name;

            if(move_uploaded_file($tmp, $img_path)){
                $stmt_img = $conn->prepare("INSERT INTO gem_images (gem_id, image_path) VALUES (?, ?)");
                $stmt_img->bind_param("is", $gem_id, $img_path);
                $stmt_img->execute();
                $stmt_img->close();
            }
        }

        $msg = "Listing added! Await admin approval.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Gem - Seller Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

<!-- NAV BAR -->
<header class="bg-white shadow p-4 mb-6">
  <div class="container mx-auto flex justify-between items-center">
    <h1 class="font-bold text-xl">Seller Dashboard</h1>
    <nav class="space-x-4">
      <a href="dashboard.php" class="text-blue-600 hover:underline">Home</a>
      <a href="my-gems.php" class="text-blue-600 hover:underline">My Gems</a>
      <a href="add-gem.php" class="text-blue-600 hover:underline font-semibold">Add Gem</a>
      <a href="../logout.php" class="text-red-600 hover:underline">Logout</a>
    </nav>
  </div>
</header>

<section class="container mx-auto px-4">
  <div class="bg-white p-6 rounded shadow max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Add New Gem</h1>

    <?php if($msg): ?>
      <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
        <?php echo $msg; ?>
      </div>
    <?php endif; ?>

    <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="text" name="title" placeholder="Gem Title" class="w-full border p-2 rounded" required>
      <input type="text" name="type" placeholder="Type" class="w-full border p-2 rounded" required>
      <input type="number" name="carat" step="0.01" placeholder="Carat" class="w-full border p-2 rounded" required>
      <input type="text" name="color" placeholder="Color" class="w-full border p-2 rounded" required>
      <input type="text" name="clarity" placeholder="Clarity" class="w-full border p-2 rounded" required>
      <input type="text" name="origin" placeholder="Origin" class="w-full border p-2 rounded" required>
      <input type="number" name="price" placeholder="Price" class="w-full border p-2 rounded" required>
      <label class="block font-medium">Certificate PDF</label>
      <input type="file" name="certificate" class="w-full border p-2 rounded" accept=".pdf" required>
      <label class="block font-medium">Gem Images</label>
      <input type="file" name="images[]" multiple class="w-full border p-2 rounded" accept="image/*" required>
      <button type="submit" name="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Add Gem</button>
    </form>
  </div>
</section>

</body>
</html>
