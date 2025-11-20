<?php
session_start();
include "../config/db.php";

// Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$msg = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid gem ID.");
}

$gem_id = intval($_GET['id']);

// Fetch gem details (including description)
$stmt = $conn->prepare("SELECT * FROM gems WHERE id=? AND seller_id=?");
$stmt->bind_param("ii", $gem_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$gem = $result->fetch_assoc();
$stmt->close();

if (!$gem) {
    die("Gem not found or you don't have permission to edit it.");
}

// Fetch gem images
$images = [];
$stmtImg = $conn->prepare("SELECT id, image_path FROM gem_images WHERE gem_id=?");
$stmtImg->bind_param("i", $gem_id);
$stmtImg->execute();
$resImg = $stmtImg->get_result();
while ($row = $resImg->fetch_assoc()) {
    $images[] = $row;
}
$stmtImg->close();

// Directories
$cert_dir = "../public/uploads/certificates/";
$gems_dir = "../public/uploads/gems/";

if (!is_dir($cert_dir)) mkdir($cert_dir, 0777, true);
if (!is_dir($gems_dir)) mkdir($gems_dir, 0777, true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $type = trim($_POST['type']);
    $carat = trim($_POST['carat']);
    $color = trim($_POST['color']);
    $clarity = trim($_POST['clarity']);
    $origin = trim($_POST['origin']);
    $description = trim($_POST['description']); // NEW
    $price = floatval($_POST['price']);
    $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;

    // Update gem info including description
    $stmt = $conn->prepare("
        UPDATE gems 
        SET title=?, type=?, carat=?, color=?, clarity=?, origin=?, description=?, price=?, is_negotiable=?
        WHERE id=? AND seller_id=?
    ");

    $stmt->bind_param(
        "ssssssssdiii",
        $title,
        $type,
        $carat,
        $color,
        $clarity,
        $origin,
        $description,
        $price,
        $is_negotiable,
        $gem_id,
        $seller_id
    );

    $stmt->execute();
    $stmt->close();

    // Remove selected images
    if (!empty($_POST['remove_images'])) {
        foreach ($_POST['remove_images'] as $img_id) {

            $stmtDel = $conn->prepare("SELECT image_path FROM gem_images WHERE id=? AND gem_id=?");
            $stmtDel->bind_param("ii", $img_id, $gem_id);
            $stmtDel->execute();
            $res = $stmtDel->get_result();

            if ($res->num_rows) {
                $row = $res->fetch_assoc();
                if (file_exists($row['image_path'])) unlink($row['image_path']);

                $stmtDel2 = $conn->prepare("DELETE FROM gem_images WHERE id=?");
                $stmtDel2->bind_param("i", $img_id);
                $stmtDel2->execute();
                $stmtDel2->close();
            }

            $stmtDel->close();
        }
    }

    // Upload new images
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            $filename = time() . "_" . basename($_FILES['images']['name'][$i]);
            $targetFile = $gems_dir . $filename;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $stmtImg = $conn->prepare("INSERT INTO gem_images (gem_id, image_path) VALUES (?, ?)");
                $stmtImg->bind_param("is", $gem_id, $targetFile);
                $stmtImg->execute();
                $stmtImg->close();
            }
        }
    }

    // Replace certificate
    if (!empty($_FILES['certificate']['name'])) {

        if (!empty($gem['certificate']) && file_exists($gem['certificate'])) {
            unlink($gem['certificate']);
        }

        $certFile = time() . "_" . basename($_FILES['certificate']['name']);
        $targetCert = $cert_dir . $certFile;

        if (move_uploaded_file($_FILES['certificate']['tmp_name'], $targetCert)) {
            $stmtCert = $conn->prepare("UPDATE gems SET certificate=? WHERE id=? AND seller_id=?");
            $stmtCert->bind_param("sii", $targetCert, $gem_id, $seller_id);
            $stmtCert->execute();
            $stmtCert->close();
        }
    }

    $msg = "Gem updated successfully!";
    header("Location: edit-gem.php?id=" . $gem_id . "&msg=" . urlencode($msg));
    exit;
}

if (isset($_GET['msg'])) $msg = $_GET['msg'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gem</title>
    <link rel="stylesheet" href="/public/css/edit-gem.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 p-6">

    <main class="form-box">
        <h1 class="text-3xl font-bold mb-6 text-center text-pink-600">Edit Gem</h1>

        <?php if ($msg): ?>
            <div class="message-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <section class="form-columns">

                <div class="form-column">
                    <div class="form-row">
                        <label>Gem Title</label>
                        <input name="title" type="text" value="<?= htmlspecialchars($gem['title']) ?>" required />
                    </div>

                    <div class="form-row">
                        <label>Type</label>
                        <input name="type" type="text" value="<?= htmlspecialchars($gem['type']) ?>" required />
                    </div>

                    <div class="form-row">
                        <label>Carat</label>
                        <input name="carat" type="number" step="0.01" value="<?= htmlspecialchars($gem['carat']) ?>" required />
                    </div>

                    <div class="form-row mt-6">
                        <label>Price</label>
                        <input name="price" type="number" step="0.01" value="<?= htmlspecialchars($gem['price']) ?>" required />
                    </div>

                                        <!-- DESCRIPTION FIELD -->
                    <div class="form-row">
                        <label>Description</label>
                        <textarea name="description" rows="4" required><?= htmlspecialchars($gem['description']) ?></textarea>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-row">
                        <label>Color</label>
                        <input name="color" type="text" value="<?= htmlspecialchars($gem['color']) ?>" required />
                    </div>

                    <div class="form-row">
                        <label>Clarity</label>
                        <input name="clarity" type="text" value="<?= htmlspecialchars($gem['clarity']) ?>" required />
                    </div>

                    <div class="form-row">
                        <label>Origin</label>
                        <input name="origin" type="text" value="<?= htmlspecialchars($gem['origin']) ?>" required />
                    </div>

                                        <div class="checkbox-row">
                        <input name="is_negotiable" type="checkbox" <?= $gem['is_negotiable'] ? 'checked' : '' ?> />
                        <label>Negotiable</label>
                    </div>

                                <div class="form-row">
                <label>Add New Images</label>
                <input name="images[]" type="file" multiple accept="image/*" />
            </div>




                </div>
            </section>

            <fieldset>
                <legend class="text-lg font-semibold mb-3">Existing Images</legend>
                <div class="images-grid">
                    <?php foreach ($images as $img): ?>
                        <div class="image-wrapper">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="Gem image" onclick="openLightbox('<?= htmlspecialchars($img['image_path']) ?>')" />
                            <label>
                                <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img['id']) ?>" /> Remove
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>



            <fieldset class="mt-6">
                <legend class="text-lg font-semibold mb-3">Certificate</legend>

                <?php if (!empty($gem['certificate'])):
                    $ext = strtolower(pathinfo($gem['certificate'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <div class="certificate-preview">
                            <img src="<?= htmlspecialchars($gem['certificate']) ?>" alt="Certificate Image Preview" />
                        </div>
                    <?php else: ?>
                        <p><a href="<?= htmlspecialchars($gem['certificate']) ?>" target="_blank">View Certificate PDF</a></p>
                <?php endif; endif; ?>

                <div class="form-row mt-3">
                    <label>Upload New Certificate</label>
                    <input name="certificate" type="file" accept="image/*,application/pdf" />
                </div>
            </fieldset>

            <div class="button-row">
                <button type="button" class="back-btn" onclick="window.location.href='../public/seller-dashboard.php'">Back</button>
                <button type="submit" class="submit-btn">Save Changes</button>
            </div>
        </form>
    </main>

    <div id="lightboxModal" class="lightbox" style="display:none;" onclick="closeLightbox()">
        <button class="close-btn" onclick="closeLightbox(event)">&times;</button>
        <img id="lightboxImg" class="lightbox-img" />
    </div>

    <script>
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightboxModal').style.display = 'flex';
        }

        function closeLightbox(event) {
            if (event) event.stopPropagation();
            document.getElementById('lightboxModal').style.display = 'none';
        }
    </script>

</body>

</html>
