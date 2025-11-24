<?php
include "../config/db.php";
session_start();

// Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header("Location: ../public/login.php");
    exit;
}

$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($blog_id <= 0) die("Invalid Blog ID.");

// Fetch blog and its images
$stmt = $conn->prepare("
    SELECT b.id, b.title, b.content, b.status, b.created_at,
           GROUP_CONCAT(bi.image_path) AS images
    FROM blogs b
    LEFT JOIN blog_images bi ON b.id = bi.blog_id
    WHERE b.user_id = ? AND b.id = ?
    GROUP BY b.id
");
$stmt->bind_param("ii", $user_id, $blog_id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$blog) die("Blog not found or permission denied.");

include "../seller/seller_header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Blog - Seller Dashboard</title>
<link rel="stylesheet" href="/public/css/view-blog.css">
</head>
<body>

<main class="form-box" style="position: relative;">

    <button 
        style="
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #555;
        "
        onclick="window.location.href='../public/seller-dashboard.php'"
        title="Close"
    >&times;</button>

    <h1 class="title">View Blog</h1>

    <section class="form-columns">

        <div class="form-column">
            <div class="form-row">
                <label>Status</label>
                <input type="text" value="<?= ucfirst($blog['status']) ?>" readonly>
            </div>

            <div class="form-row">
                <label>Created On</label>
                <input type="text" value="<?= date('F j, Y', strtotime($blog['created_at'])) ?>" readonly>
            </div>

            <div class="form-row">
                <label>Content</label>
                <textarea rows="6" readonly><?= htmlspecialchars($blog['content'] ?? 'No content') ?></textarea>
            </div>
        </div>

        <div class="form-column">
            <fieldset>
                <legend class="text-lg font-semibold mb-3">Images</legend>
                <div class="images-grid">
                    <?php
                    $images = explode(',', $blog['images'] ?? '');
                    foreach ($images as $img):
                        if ($img): ?>
                            <div class="image-wrapper">
                                <img src="<?= htmlspecialchars($img) ?>" class="blog-img" onclick="openLightbox('<?= htmlspecialchars($img) ?>')" />
                            </div>
                    <?php endif; endforeach; ?>
                </div>
            </fieldset>
        </div>

    </section>



</main>

<!-- LIGHTBOX -->
<div id="lightboxModal" class="lightbox" style="display:none;" onclick="closeLightbox()">
    <button class="close-btn" onclick="closeLightbox(event)">&times;</button>
    <img id="lightboxImg" class="lightbox-img" />
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxModal').style.display = 'flex';
}
function closeLightbox(e) {
    if (e) e.stopPropagation();
    document.getElementById('lightboxModal').style.display = 'none';
}
</script>

</body>
</html>
