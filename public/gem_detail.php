<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) die("Gem ID missing");
$gemId = intval($_GET['id']);

/* Fetch Main Gem */
$res = $conn->query("
    SELECT g.*, u.full_name AS seller_name
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.id = $gemId AND g.status='approved'
");
$gem = $res->fetch_assoc();
if (!$gem) die("Gem not found");

/* Fetch Gem Images */
$imagesRes = $conn->query("SELECT image_path FROM gem_images WHERE gem_id = $gemId");
$images = [];
while ($row = $imagesRes->fetch_assoc()) {
    $images[] = $row['image_path'];
}

/* User Wishlist */
$inWishlist = false;
$wishlistGemIds = [];
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $wlRes = $conn->query("SELECT gem_id FROM wishlist WHERE user_id=$uid");
    while($row = $wlRes->fetch_assoc()) $wishlistGemIds[] = $row['gem_id'];
    if(in_array($gemId, $wishlistGemIds)) $inWishlist = true;
}

/* Fetch Related Gems */
$relatedGems = [];
$relatedIds = [];
$relatedRes = $conn->query("
    SELECT g.*, u.full_name AS seller_name
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.status='approved'
      AND g.id != $gemId
      AND g.type = '".$conn->real_escape_string($gem['type'])."'
    ORDER BY g.created_at DESC
    LIMIT 4
");
while($g = $relatedRes->fetch_assoc()){
    $relatedGems[] = $g;
    $relatedIds[] = $g['id'];
}

/* Related Gem Images */
$relatedImages = [];
if(!empty($relatedIds)){
    $ids = implode(',', array_map('intval', $relatedIds));
    $imgRes = $conn->query("SELECT gem_id, image_path FROM gem_images WHERE gem_id IN ($ids) GROUP BY gem_id");
    while($img = $imgRes->fetch_assoc()){
        $relatedImages[$img['gem_id']] = $img['image_path'];
    }
}

include __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($gem['title']) ?> - Gem Details</title>
    <link rel="stylesheet" href="css/gem-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<section class="gem-detail-section">
    <div class="container">

        <!-- Left: Images -->
        <div class="gem-images">
            <div class="main-image">
                <img id="currentImage" src="<?= htmlspecialchars($images[0] ?? 'https://via.placeholder.com/500x500?text=No+Image') ?>" alt="<?= htmlspecialchars($gem['title']) ?>">
            </div>
            <div class="thumbnails">
                <?php foreach($images as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" class="thumb" alt="Gem Image">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Details -->
        <div class="gem-details">
            <h1><?= htmlspecialchars($gem['title']) ?></h1>
            <p><strong>Carat:</strong> <?= htmlspecialchars($gem['carat']) ?></p>
            <p><strong>Color:</strong> <?= htmlspecialchars($gem['color']) ?></p>
            <p><strong>Certification:</strong> <?= !empty($gem['certificate']) ? 'Certified' : 'Uncertified' ?></p>
            <p><strong>Price:</strong> Rs <?= number_format($gem['price'],2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?></p>
            <p><strong>Seller:</strong> <?= htmlspecialchars($gem['seller_name']) ?></p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($gem['description'])) ?></p>

            <?php if(isset($_SESSION['user_id'])): ?>
                <form action="checkout.php" method="POST" style="margin-top:20px;">
                    <input type="hidden" name="gem_id" value="<?= $gem['id'] ?>">
                    <button type="submit" class="buy-now">
                        <i class="fa fa-credit-card"></i> Buy Now
                    </button>
                </form>
            <?php else: ?>
                <button class="login" onclick="alert('Please login to buy this gem.')">
                    <i class="fa fa-lock"></i> Login to Buy
                </button>
            <?php endif; ?>

            <!-- Wishlist
            <button id="wishlistBtn" class="wishlist-btn <?= $inWishlist ? 'active' : '' ?>" data-gem-id="<?= $gem['id'] ?>" title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                <i class="fa fa-heart"></i>
            </button> -->
        </div>

    </div>
</section>

<!-- Related Gems -->
<?php if(!empty($relatedGems)): ?>
<section class="related-gems">
    <h2>Related Gems</h2>
    <div class="latest-grid">
        <?php foreach($relatedGems as $g):
            $imgPath = $relatedImages[$g['id']] ?? "https://via.placeholder.com/400x500?text=No+Image";
            $hasCert = !empty($g['certificate']);
            $inWl = isset($_SESSION['user_id']) && in_array($g['id'], $wishlistGemIds);
        ?>
        <div class="card">
            <div class="card-image">
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($g['title']) ?>">
                <div class="card-overlay">
                    <button class="wishlist-btn <?= $inWl?'active':'' ?>" data-gem-id="<?= $g['id'] ?>" title="<?= $inWl?'Remove from wishlist':'Add to wishlist' ?>">
                        <i class="fa fa-heart"></i>
                    </button>
                    <a href="gem_detail.php?id=<?= $g['id'] ?>"><i class="fa fa-eye"></i></a>
                </div>
            </div>
            <div class="card-content">
                <h3><?= htmlspecialchars($g['title']) ?></h3>
                <p class="card-meta">
                    <?= htmlspecialchars($g['carat']) ?> Carat &bull; <?= htmlspecialchars($g['color']) ?> &bull;
                    <?= $hasCert ? '<span class="certified">Certified</span>' : '<span class="uncertified">Uncertified</span>' ?>
                </p>
                <p class="price">Rs <?= number_format($g['price'],2) ?> <?= $g['is_negotiable']?'(Negotiable)':'' ?></p>
                <p class="seller">Seller: <?= htmlspecialchars($g['seller_name']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>

<script>
// Image thumbnail click
document.querySelectorAll('.thumb').forEach(t => {
    t.addEventListener('click', () => document.getElementById('currentImage').src = t.src);
});

// Wishlist toggle
document.querySelectorAll('.wishlist-btn').forEach(button => {
    button.addEventListener('click', () => {
        const gemId = button.getAttribute('data-gem-id');
        const loggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
        if (!loggedIn) {
            alert('Please login to add to wishlist.');
            return;
        }
        const isActive = button.classList.contains('active');
        const url = isActive ? 'wishlist-remove.php' : 'wishlist-add.php';

        fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `gem_id=${encodeURIComponent(gemId)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                button.classList.toggle('active');
                button.setAttribute('title', button.classList.contains('active') ? 'Remove from wishlist' : 'Add to wishlist');
            } else {
                alert(data.message || 'Something went wrong.');
            }
        })
        .catch(() => alert('Error updating wishlist'));
    });
});
</script>

</body>
</html>
