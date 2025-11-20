<?php
include "../config/db.php";
session_start();

// Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
  header("Location: ../public/login.php");
  exit;
}

$msg = "";

if (isset($_POST['submit'])) {

  $seller_id = $_SESSION['user_id'];
  $title = $_POST['title'];
  $type = $_POST['type'];
  $carat = $_POST['carat'];
  $color = $_POST['color'];
  $clarity = $_POST['clarity'];
  $origin = $_POST['origin'];
  $description = $_POST['description']; // <-- NEW
  $price = $_POST['price'];

  // Set directories (inside public folder)
  $cert_dir = "../public/uploads/certificates/";
  $gems_dir = "../public/uploads/gems/";

  // Create directories if they don't exist
  if (!is_dir($cert_dir)) mkdir($cert_dir, 0777, true);
  if (!is_dir($gems_dir)) mkdir($gems_dir, 0777, true);

  // Upload certificate
  $cert_name = time() . "_" . basename($_FILES['certificate']['name']);
  $cert_path = $cert_dir . $cert_name;

  if (!move_uploaded_file($_FILES['certificate']['tmp_name'], $cert_path)) {
    $msg = "Failed to upload certificate.";
  } else {

    // INSERT QUERY UPDATED WITH DESCRIPTION
    $stmt = $conn->prepare("INSERT INTO gems 
      (seller_id, title, type, carat, color, clarity, origin, description, certificate, price)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
      "issdsssssd",
      $seller_id,
      $title,
      $type,
      $carat,
      $color,
      $clarity,
      $origin,
      $description,
      $cert_path,
      $price
    );

    $stmt->execute();
    $gem_id = $stmt->insert_id;
    $stmt->close();

    // Upload gem images
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
      $img_name = time() . "_" . basename($_FILES['images']['name'][$key]);
      $img_path = $gems_dir . $img_name;

      if (move_uploaded_file($tmp, $img_path)) {
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

  <!-- Link your external CSS -->
  <link rel="stylesheet" href="../public/css/add-gem.css">

</head>

<body>

  <div class="form-box">

    <h1 class="title">Add New Gem</h1>

    <?php if ($msg): ?>
      <div class="alert-success">
        <?php echo $msg; ?>
      </div>
    <?php endif; ?>

    <form action="#" method="POST" enctype="multipart/form-data">

      <div class="form-columns">
        <div class="form-column">

          <div class="form-row">
            <label>Gem Title</label>
            <input type="text" name="title" required>
          </div>

          <div class="form-row">
            <label>Type</label>
            <input type="text" name="type" required>
          </div>

          <div class="form-row">
            <label>Carat</label>
            <input type="number" step="0.01" name="carat" required>
          </div>

          <div class="form-row">
            <label>Color</label>
            <input type="text" name="color" required>
          </div>

        </div>

        <div class="form-column">

          <div class="form-row">
            <label>Clarity</label>
            <input type="text" name="clarity" required>
          </div>

          <div class="form-row">
            <label>Origin</label>
            <input type="text" name="origin" required>
          </div>

          <div class="form-row">
            <label>Price</label>
            <input type="number" name="price" required>
          </div>

          <div class="form-row">
            <label>Certificate PDF</label>
            <input type="file" name="certificate" accept=".pdf" required>
          </div>

        </div>
      </div>
      <br>

      <div class="form-row">
        <label>Description</label>
        <textarea name="description" rows="5" required></textarea>
      </div>
      <br>

      <div class="form-row">
        <label>Gem Images</label>
        <input type="file" name="images[]" multiple accept="image/*" required>
      </div>
      <br>

      <div class="button-row">
        <button type="button" class="back-btn" aria-label="Go back to dashboard"
          onclick="window.location.href='../public/seller-dashboard.php'">
          Back
        </button>
        <button type="submit" name="submit" class="submit-btn">Add Gem</button>
      </div>

    </form>
  </div>

</body>

</html>
