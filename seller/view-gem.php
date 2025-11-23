<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

if (!isset($_GET['id'])) {
    die("No gem ID provided.");
}

$gem_id = intval($_GET['id']);
$seller_id = $_SESSION['user_id'];

// Fetch gem details
$stmt = $conn->prepare("SELECT * FROM gems WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $gem_id, $seller_id);
$stmt->execute();
$gem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$gem) {
    die("Gem not found or you do not have permission.");
}

// Fetch images
$stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id = ?");
$stmtImg->bind_param("i", $gem_id);
$stmtImg->execute();
$images = $stmtImg->get_result();
$imgArray = [];
while ($img = $images->fetch_assoc()) $imgArray[] = $img['image_path'];
$stmtImg->close();

// Fetch videos
$stmtVdo = $conn->prepare("SELECT video_path FROM gem_videos WHERE gem_id = ?");
$stmtVdo->bind_param("i", $gem_id);
$stmtVdo->execute();
$videos = $stmtVdo->get_result();
$vdoArray = [];
while ($v = $videos->fetch_assoc()) $vdoArray[] = $v['video_path'];
$stmtVdo->close();
?>

<link rel="stylesheet" href="../public/css/view-gem.css">

<div class="form-box" style="position: relative;">

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
    
    <div class="title"><?= htmlspecialchars($gem['title']) ?> Details</div>

    <div class="form-columns">
        <div class="form-column">
            <div class="form-row"><label>Type:</label><input type="text" value="<?= htmlspecialchars($gem['type']) ?>" readonly></div>
            <div class="form-row"><label>Carat:</label><input type="text" value="<?= htmlspecialchars($gem['carat']) ?>" readonly></div>
            <div class="form-row"><label>Color:</label><input type="text" value="<?= htmlspecialchars($gem['color']) ?>" readonly></div>
            <div class="form-row"><label>Clarity:</label><input type="text" value="<?= htmlspecialchars($gem['clarity']) ?>" readonly></div>
            <div class="form-row"><label>Origin:</label><input type="text" value="<?= htmlspecialchars($gem['origin']) ?>" readonly></div>
        </div>

        <div class="form-column">
            <div class="form-row"><label>Price:</label><input type="text" value="Rs <?= number_format($gem['price'], 2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?>" readonly></div>
            <div class="form-row"><label>Description:</label><textarea rows="4" readonly><?= htmlspecialchars($gem['description']) ?></textarea></div>
<div class="form-row">
    <label>Certificate:</label>
    <?php if (!empty($gem['certificate'])): 
        $certPath = $gem['certificate'];
        $ext = strtolower(pathinfo($certPath, PATHINFO_EXTENSION));
        // Convert server path to full URL
        $certUrl = 'http://localhost:3000/' . ltrim(str_replace('../', '', $certPath), '/');

        if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
            <img src="<?= htmlspecialchars($certUrl) ?>" style="width:100px; cursor:pointer;" onclick="window.open('<?= htmlspecialchars($certUrl) ?>','_blank')">
        <?php elseif ($ext === 'pdf'): ?>
            <a href="<?= htmlspecialchars($certUrl) ?>" target="_blank">View Certificate PDF</a>
        <?php else: ?>
            N/A
        <?php endif; ?>
    <?php else: ?>
        N/A
    <?php endif; ?>
</div>



        </div>
    </div>

    <div class="form-row">
        <label>Images:</label>
        <div>
            <?php foreach ($imgArray as $img): ?>
                <img src="<?= htmlspecialchars($img) ?>" style="width:80px; cursor:pointer; margin:5px;" onclick="window.open('<?= htmlspecialchars($img) ?>','_blank')">
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-row">
        <label>Videos:</label>
        <div>
            <?php foreach ($vdoArray as $vdo): ?>
                <video src="<?= htmlspecialchars($vdo) ?>" controls style="width:200px; display:block; margin-bottom:10px;"></video>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="button-row">
        <button class="back-btn" onclick="window.location.href='gem-listings.php'">Back to Dashboard</button>
    </div>
</div>
