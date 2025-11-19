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

// Fetch gem details
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

// Set directories
$cert_dir = "../public/uploads/certificates/";
$gems_dir = "../public/uploads/gems/";

// Create directories if they don't exist
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
    $price = floatval($_POST['price']);
    $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;

    // Update gem info
    $stmt = $conn->prepare("
        UPDATE gems 
        SET title=?, type=?, carat=?, color=?, clarity=?, origin=?, price=?, is_negotiable=?
        WHERE id=? AND seller_id=?
    ");
    $stmt->bind_param("ssssssdiii", $title, $type, $carat, $color, $clarity, $origin, $price, $is_negotiable, $gem_id, $seller_id);
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
        // Delete old certificate
        if (!empty($gem['certificate']) && file_exists($gem['certificate'])) unlink($gem['certificate']);

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
    <link rel="stylesheet" href="/public/css/edit-gem.css
">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 p-6">

    <main class="form-box" role="main" aria-labelledby="pageTitle">
        <h1 id="pageTitle" class="text-3xl font-bold mb-6 text-center text-pink-600">Edit Gem</h1>

        <?php if ($msg): ?>
            <div class="message-success" role="alert"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>
            <section class="form-columns" aria-label="Gem details">

                <div class="form-column">
                    <div class="form-row">
                        <label for="title">Gem Title</label>
                        <input id="title" name="title" type="text" value="<?= htmlspecialchars($gem['title']) ?>" required />
                    </div>
                    <div class="form-row">
                        <label for="type">Type</label>
                        <input id="type" name="type" type="text" value="<?= htmlspecialchars($gem['type']) ?>" required />
                    </div>
                    <div class="form-row">
                        <label for="carat">Carat</label>
                        <input id="carat" name="carat" type="number" step="0.01" value="<?= htmlspecialchars($gem['carat']) ?>" required />
                    </div>
                    <div class="form-row mt-6">
                        <label for="price">Price</label>
                        <input id="price" name="price" type="number" step="0.01" value="<?= htmlspecialchars($gem['price']) ?>" required />
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-row">
                        <label for="color">Color</label>
                        <input id="color" name="color" type="text" value="<?= htmlspecialchars($gem['color']) ?>" required />
                    </div>
                    <div class="form-row">
                        <label for="clarity">Clarity</label>
                        <input id="clarity" name="clarity" type="text" value="<?= htmlspecialchars($gem['clarity']) ?>" required />
                    </div>
                    <div class="form-row">
                        <label for="origin">Origin</label>
                        <input id="origin" name="origin" type="text" value="<?= htmlspecialchars($gem['origin']) ?>" required />
                    </div>

                    <div class="checkbox-row">
                        <input id="is_negotiable" name="is_negotiable" type="checkbox" <?= $gem['is_negotiable'] ? 'checked' : '' ?> />
                        <label for="is_negotiable">Negotiable</label>
                    </div>
                </div>
            </section>

            <fieldset>
                <legend class="text-lg font-semibold mb-3">Existing Images</legend>
                <div class="images-grid" aria-live="polite">
                    <?php foreach ($images as $img): ?>
                        <div class="image-wrapper">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="Gem image" onclick="openLightbox('<?= htmlspecialchars($img['image_path']) ?>')" />
                            <label class="remove-label">
                                <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img['id']) ?>" /> Remove
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="form-row">
                <label for="images"> Add New Images</label>
                <input id="images" name="images[]" type="file" multiple accept="image/*" />
            </div>
            <br>
            <fieldset class="mt-6">
                <legend class="text-lg font-semibold mb-3">Certificate</legend>

                <?php if (!empty($gem['certificate'])):
                    $ext = strtolower(pathinfo($gem['certificate'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <div class="certificate-preview">
                            <img src="<?= htmlspecialchars($gem['certificate']) ?>" alt="Certificate Image Preview" />
                        </div>
                    <?php else: ?>
                        <p><a href="<?= htmlspecialchars($gem['certificate']) ?>" target="_blank" rel="noopener noreferrer">View Certificate PDF</a></p>
                <?php endif;
                endif; ?>

                <div class="form-row mt-3">
                    <label for="certificate">Upload New Certificate</label>
                    <input id="certificate" name="certificate" type="file" accept="image/*,application/pdf" />
                </div>
            </fieldset>

            <div class="button-row">
                <button type="button" class="back-btn" aria-label="Go back to dashboard"
                    onclick="window.location.href='../public/seller-dashboard.php'">
                    Back
                </button>

                <button type="submit" class="submit-btn" aria-label="Save changes to gem">
                    Save Changes
                </button>
            </div>

        </form>
    </main>

    <div id="lightboxModal" class="lightbox" role="dialog" aria-modal="true" aria-labelledby="lightboxTitle" style="display:none;" tabindex="-1" onclick="closeLightbox()">
        <button class="close-btn" aria-label="Close image preview" onclick="closeLightbox(event)">&times;</button>
        <img id="lightboxImg" class="lightbox-img" alt="Image preview" />
    </div>

    <script>
        function openLightbox(src) {
            const modal = document.getElementById('lightboxModal');
            const img = document.getElementById('lightboxImg');
            img.src = src;
            modal.style.display = 'flex';
            modal.focus();
        }

        function closeLightbox(event) {
            if (event) event.stopPropagation();
            const modal = document.getElementById('lightboxModal');
            modal.style.display = 'none';
            document.getElementById('lightboxImg').src = '';
        }
    </script>

</body>

</html>