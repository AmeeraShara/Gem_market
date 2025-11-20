<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

$gem_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch gem
$stmt = $conn->prepare("SELECT * FROM gems WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $gem_id, $_SESSION['user_id']);
$stmt->execute();
$gem = $stmt->get_result()->fetch_assoc();

if (!$gem) {
    echo "Gem not found!";
    exit;
}

// Fetch gem images
$stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id = ?");
$stmtImg->bind_param("i", $gem_id);
$stmtImg->execute();
$images = $stmtImg->get_result();

// Fetch gem videos
$stmtVdo = $conn->prepare("SELECT video_path FROM gem_videos WHERE gem_id = ?");
$stmtVdo->bind_param("i", $gem_id);
$stmtVdo->execute();
$videos = $stmtVdo->get_result();
?>

<h2><?= htmlspecialchars($gem['title']) ?></h2>
<p><strong>Type:</strong> <?= htmlspecialchars($gem['type']) ?></p>
<p><strong>Carat:</strong> <?= htmlspecialchars($gem['carat']) ?></p>
<p><strong>Color:</strong> <?= htmlspecialchars($gem['color']) ?></p>
<p><strong>Clarity:</strong> <?= htmlspecialchars($gem['clarity']) ?></p>
<p><strong>Origin:</strong> <?= htmlspecialchars($gem['origin']) ?></p>
<p><strong>Price:</strong> Rs <?= number_format($gem['price'],2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?></p>
<p><strong>Status:</strong> <?= ucfirst($gem['status']) ?></p>

<!-- Certificate -->
<p><strong>Certificate:</strong>
<?php 
if(!empty($gem['certificate'])) {
    $certPath = "../uploads/certificates/" . basename($gem['certificate']);
    $ext = strtolower(pathinfo($certPath, PATHINFO_EXTENSION));
    if(in_array($ext, ['jpg','jpeg','png','gif'])) {
        echo "<img src='$certPath' style='width:100px; height:100px; object-fit:cover; cursor:pointer;' onclick=\"window.open('$certPath','_blank')\">";
    } else {
        echo "<a href='$certPath' target='_blank'>View PDF</a>";
    }
} else echo "N/A";
?>
</p>

<!-- Images -->
<p><strong>Images:</strong></p>
<?php while($img = $images->fetch_assoc()): 
    $imgPath = "../uploads/gems/" . basename($img['image_path']);
?>
    <img src="<?= $imgPath ?>" style="width:100px; height:100px; object-fit:cover; margin:5px; cursor:pointer;" 
         onclick="window.open('<?= $imgPath ?>','_blank')">
<?php endwhile; ?>

<!-- Videos -->
<?php if($videos->num_rows > 0): ?>
    <p><strong>Videos:</strong></p>
    <?php while($vdo = $videos->fetch_assoc()):
        $vdoPath = "../uploads/gem_videos/" . basename($vdo['video_path']);
    ?>
        <video src="<?= $vdoPath ?>" controls style="width:200px; height:120px; object-fit:cover; margin:5px;"></video>
    <?php endwhile; ?>
<?php endif; ?>
