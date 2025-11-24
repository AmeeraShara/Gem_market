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
  $description = $_POST['description'];
  $price = $_POST['price'];

  // Set directories (inside public folder)
  $cert_dir = "../public/uploads/certificates/";
  $gems_dir = "../public/uploads/gems/";
  $videos_dir = "../public/uploads/videos/";

  // Create directories if they don't exist
  if (!is_dir($cert_dir)) mkdir($cert_dir, 0777, true);
  if (!is_dir($gems_dir)) mkdir($gems_dir, 0777, true);
  if (!is_dir($videos_dir)) mkdir($videos_dir, 0777, true);

  // Upload certificate
  $cert_name = time() . "_" . basename($_FILES['certificate']['name']);
  $cert_path = $cert_dir . $cert_name;

  if (!move_uploaded_file($_FILES['certificate']['tmp_name'], $cert_path)) {
    $msg = "Failed to upload certificate.";
  } else {

    // INSERT GEM RECORD
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
    if (!empty($_FILES['images']['tmp_name'])) {
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
    }

    // Upload gem videos
    if (!empty($_FILES['videos']['tmp_name'])) {
      foreach ($_FILES['videos']['tmp_name'] as $key => $tmp) {
        $vid_name = time() . "_" . basename($_FILES['videos']['name'][$key]);
        $vid_path = $videos_dir . $vid_name;

        if (move_uploaded_file($tmp, $vid_path)) {
          $stmt_vid = $conn->prepare("INSERT INTO gem_videos (gem_id, video_path) VALUES (?, ?)");
          $stmt_vid->bind_param("is", $gem_id, $vid_path);
          $stmt_vid->execute();
          $stmt_vid->close();
        }
      }
    }

    $msg = "Listing added! Await admin approval.";
  }
}

 include "../seller/seller_header.php"; 

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Gem - Seller Dashboard</title>
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
            <select name="type" required>
              <option value="">Select Type</option>
              <option value="diamond">Diamond</option>
              <option value="ruby">Ruby</option>
              <option value="sapphire">Sapphire</option>
              <option value="emerald">Emerald</option>
              <option value="topaz">Topaz</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="form-row">
            <label>Carat</label>
            <input type="number" step="0.01" name="carat" required>
          </div>

          <div class="form-row">
            <label>Color</label>
            <select name="color" required>
              <option value="">Select Color</option>
              <option value="white">White</option>
              <option value="yellow">Yellow</option>
              <option value="blue">Blue</option>
              <option value="red">Red</option>
              <option value="green">Green</option>
              <option value="pink">Pink</option>
              <option value="purple">Purple</option>
              <option value="orange">Orange</option>
              <option value="other">Other</option>
            </select>
          </div>

        </div>

        <div class="form-column">

          <div class="form-row">
            <label>Clarity</label>
            <select name="clarity" required>
              <option value="">Select Clarity</option>
              <option value="flawless">Flawless (FL)</option>
              <option value="internally_flawless">Internally Flawless (IF)</option>
              <option value="very_very_slight_inclusions">Very Very Slight Inclusions (VVS1, VVS2)</option>
              <option value="very_slight_inclusions">Very Slight Inclusions (VS1, VS2)</option>
              <option value="slight_inclusions">Slight Inclusions (SI1, SI2)</option>
              <option value="included">Included (I1, I2, I3)</option>
            </select>
          </div>

          <div class="form-row">
            <label>Origin</label>
            <select name="origin" required>
              <option value="">Select Origin</option>
              <option value="sri_lanka">Sri Lanka</option>
              <option value="india">India</option>
              <option value="brazil">Brazil</option>
              <option value="africa">Africa</option>
              <option value="australia">Australia</option>
              <option value="other">Other</option>
            </select>
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

      <div class="form-row">
        <label>Gem Videos (optional)</label>
        <input type="file" name="videos[]" multiple accept="video/*">
      </div>
      <br>

      <div class="button-row">
        <button type="button" class="back-btn" onclick="window.location.href='../public/seller-dashboard.php'">Back</button>
        <button type="submit" name="submit" class="submit-btn">Add Gem</button>
      </div>

    </form>
  </div>

</body>

</html>
