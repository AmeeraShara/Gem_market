<?php
session_start();
include "../config/db.php";

// Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate blog ID
if ($blog_id <= 0) die("Invalid Blog ID.");

$msg = isset($_GET['msg']) ? $_GET['msg'] : "";

// Fetch blog
$stmt = $conn->prepare("SELECT * FROM blogs WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $blog_id, $user_id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$blog) die("Blog not found or permission denied.");

// Fetch images
$images = [];
$stmtImg = $conn->prepare("SELECT id, image_path FROM blog_images WHERE blog_id=?");
$stmtImg->bind_param("i", $blog_id);
$stmtImg->execute();
$resImg = $stmtImg->get_result();
while ($row = $resImg->fetch_assoc()) $images[] = $row;
$stmtImg->close();

// Fetch videos
$videos = [];
$stmtVid = $conn->prepare("SELECT id, video_path FROM blog_videos WHERE blog_id=?");
$stmtVid->bind_param("i", $blog_id);
$stmtVid->execute();
$resVid = $stmtVid->get_result();
while ($row = $resVid->fetch_assoc()) $videos[] = $row;
$stmtVid->close();

include "../seller/seller_header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Blog</title>
<link rel="stylesheet" href="../public/css/edit-blog.css">
</head>

<body>

<main class="form-box" style="position: relative;">

<!-- Close Button -->
<button 
    style="position: absolute; top: 10px; right: 10px; 
           background: transparent; border: none; 
           font-size: 24px; cursor: pointer; color: #555;"
    onclick="window.location.href='../public/seller-dashboard.php'"
>&times;</button>

<h1 class="title">Edit Blog</h1>

<?php if ($msg): ?>
    <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form action="update-blog.php" method="POST" enctype="multipart/form-data">

<input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">

<div class="form-column">

    <div class="form-row">
        <label>Blog Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($blog['title']) ?>" required>
    </div>

    <div class="form-row">
        <label>Content</label>
        <textarea name="content" rows="6" required><?= htmlspecialchars($blog['content']) ?></textarea>
    </div>

    <div class="form-row">
        <label>Status</label>
        <select name="status">
            <option value="draft" <?= $blog['status']=="draft" ? "selected" : "" ?>>Draft</option>
            <option value="published" <?= $blog['status']=="published" ? "selected" : "" ?>>Published</option>
        </select>
    </div>

    <!-- Existing Images -->
    <fieldset>
        <legend>Existing Images</legend>
        <div class="images-grid">

        <?php foreach ($images as $img): 
            $img_url = str_replace("../public", "/public", $img['image_path']); 
        ?>
        <div class="image-wrapper">
            <img src="<?= $img_url ?>" onclick="openLightbox('<?= $img_url ?>')">
            <label>
                <input type="checkbox" name="remove_images[]" value="<?= $img['id'] ?>"> Remove
            </label>
        </div>
        <?php endforeach; ?>

        </div>
    </fieldset>

    <!-- Existing Videos -->
    <fieldset>
        <legend>Existing Videos</legend>
        <div class="videos-grid">

        <?php foreach ($videos as $v): 
            $video_url = str_replace("../public", "/public", $v['video_path']); 
        ?>
        <div class="video-wrapper">
            <video width="200" controls>
                <source src="<?= $video_url ?>" type="video/mp4">
            </video>
            <label>
                <input type="checkbox" name="remove_videos[]" value="<?= $v['id'] ?>"> Remove
            </label>
        </div>
        <?php endforeach; ?>

        </div>
    </fieldset>

    <!-- Add new images -->
    <div class="form-row">
        <label>Add New Images</label>
        <input type="file" name="images[]" multiple accept="image/*">
    </div>

    <!-- Add new videos -->
    <div class="form-row">
        <label>Add New Videos</label>
        <input type="file" name="videos[]" multiple accept="video/*">
    </div>

    <div class="button-row">
        <button type="button" class="back-btn" onclick="window.location.href='../public/seller-dashboard.php'">Back</button>
        <button type="submit" class="submit-btn">Save Changes</button>
    </div>

</div>

</form>
</main>

<!-- Lightbox -->
<div id="lightboxModal" class="lightbox" onclick="closeLightbox()">
    <button class="close-btn" onclick="closeLightbox(event)">&times;</button>
    <img id="lightboxImg" class="lightbox-img" />
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxModal').style.display = 'flex';
}
function closeLightbox(e) {
    if(e) e.stopPropagation();
    document.getElementById('lightboxModal').style.display = 'none';
}
</script>

</body>
</html>
