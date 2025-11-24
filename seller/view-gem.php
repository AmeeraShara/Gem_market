<?php
session_start();
include "../config/db.php";

// Ensure seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid gem ID.");
}

$gem_id = intval($_GET['id']);

// Fetch gem details
$stmt = $conn->prepare("SELECT * FROM gems WHERE id=? AND seller_id=?");
$stmt->bind_param("ii", $gem_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$gem = $result->fetch_assoc();
$stmt->close();

if (!$gem) {
    die("Gem not found or unauthorized access.");
}

// Fetch gem images
$images = [];
$stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id=?");
$stmtImg->bind_param("i", $gem_id);
$stmtImg->execute();
$resImg = $stmtImg->get_result();
while ($row = $resImg->fetch_assoc()) {
    $images[] = $row['image_path'];
}
$stmtImg->close();

// Fetch gem videos
$videos = [];
$stmtVdo = $conn->prepare("SELECT video_path FROM gem_videos WHERE gem_id=?");
$stmtVdo->bind_param("i", $gem_id);
$stmtVdo->execute();
$resVdo = $stmtVdo->get_result();
while ($row = $resVdo->fetch_assoc()) {
    $videos[] = $row['video_path'];
}
$stmtVdo->close();
 include "../seller/seller_header.php"; 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Gem</title>

    <!-- Use EXACT same CSS as EDIT page -->
    <link rel="stylesheet" href="/public/css/view-gem.css">
</head>

<body class="bg-gray-50 p-6">

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

        <h1 class="text-3xl font-bold mb-6 text-center text-pink-600">View Gem</h1>

        <section class="form-columns">

            <div class="form-column">
                <div class="form-row">
                    <label>Gem Title</label>
                    <input type="text" value="<?= htmlspecialchars($gem['title']) ?>" readonly />
                </div>

                <div class="form-row">
                    <label>Type</label>
                    <input type="text" value="<?= htmlspecialchars($gem['type']) ?>" readonly />
                </div>

                <div class="form-row mt-6">
                    <label>Price</label>
                    <input type="text" value="Rs <?= number_format($gem['price'], 2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?>" readonly />
                </div>

                        <div class="form-row">
                    <label>Description</label>
                    <textarea rows="4" readonly><?= htmlspecialchars($gem['description']) ?></textarea>
                </div>
            </div>

            <div class="form-column">
                <div class="form-row">
                    <label>Color</label>
                    <input type="text" value="<?= htmlspecialchars($gem['color']) ?>" readonly />
                </div>

                <div class="form-row">
                    <label>Origin</label>
                    <input type="text" value="<?= htmlspecialchars($gem['origin']) ?>" readonly />
                </div>

                                <div class="form-row">
                    <label>Carat</label>
                    <input type="number" value="<?= htmlspecialchars($gem['carat']) ?>" readonly />
                </div>

                <div class="form-row">
                    <label>Clarity</label>
                    <input type="text" value="<?= htmlspecialchars($gem['clarity']) ?>" readonly />
                </div>

        
            </div>


        </section>

        <!-- IMAGES -->
        <fieldset>
            <legend class="text-lg font-semibold mb-3">Images</legend>
            <div class="images-grid">
                <?php foreach ($images as $img): ?>
                    <div class="image-wrapper">
                        <img src="<?= htmlspecialchars($img) ?>" onclick="openLightbox('<?= htmlspecialchars($img) ?>')" />
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- VIDEOS -->
        <fieldset class="mt-6">
            <legend class="text-lg font-semibold mb-3">Videos</legend>
            <div class="videos-grid">
                <?php foreach ($videos as $vdo): ?>
                    <div class="video-wrapper">
                        <video width="200" controls>
                            <source src="<?= htmlspecialchars($vdo) ?>" type="video/mp4">
                        </video>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- CERTIFICATE -->
        <fieldset class="mt-6">
            <legend class="text-lg font-semibold mb-3">Certificate</legend>

            <?php if (!empty($gem['certificate'])):
                $ext = strtolower(pathinfo($gem['certificate'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                    <div class="certificate-preview">
                        <img src="<?= htmlspecialchars($gem['certificate']) ?>" />
                    </div>
                <?php else: ?>
                    <p><a href="<?= htmlspecialchars($gem['certificate']) ?>" target="_blank">View Certificate PDF</a></p>
                <?php endif;
            else: ?>
                <p>No Certificate Uploaded</p>
            <?php endif; ?>

        </fieldset>

        <div class="button-row mt-6">
            <button type="button" class="back-btn" onclick="window.location.href='../public/seller-dashboard.php'">Back</button>
        </div>

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
