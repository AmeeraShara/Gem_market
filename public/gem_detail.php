<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) die("Gem ID missing");
$gemId = intval($_GET['id']);

/*Fetch Main Gem*/
$res = $conn->query("
    SELECT g.*, u.full_name AS seller_name
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.id = $gemId AND g.status='approved'
");
$gem = $res->fetch_assoc();
if (!$gem) die("Gem not found");

/* Fetch Gem Images*/
$imagesRes = $conn->query("SELECT image_path FROM gem_images WHERE gem_id = $gemId");
$images = [];
while ($row = $imagesRes->fetch_assoc()) {
    $images[] = $row['image_path'];
}

/* User Wishlist*/
$inWishlist = false;
$wishlistGemIds = [];
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $wlRes = $conn->query("SELECT gem_id FROM wishlist WHERE user_id=$uid");
    while($row = $wlRes->fetch_assoc()) $wishlistGemIds[] = $row['gem_id'];
    if(in_array($gemId, $wishlistGemIds)) $inWishlist = true;
}

/* Fetch Related Gems*/
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

/* Related Gem Images*/
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
<html>
<head>
    <title><?= htmlspecialchars($gem['title']) ?> - Gem Details</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/wishlist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .gem-images .thumb:hover { border:2px solid #007BFF; }
        .card-overlay { display:flex; justify-content:flex-end; gap:10px; position:absolute; top:10px; right:10px; }
        .card { position:relative; border:1px solid #eee; border-radius:10px; overflow:hidden; background:#fff; }
        .card img { width:100%; display:block; }
        .card-content { padding:10px; }
        .certified { color:green; font-weight:bold; }
        .uncertified { color:red; font-weight:bold; }
        .wishlist-btn i {
            color: #f9a8d4;
            transition: color 0.3s;
            font-size: 24px;
        }
        .wishlist-btn.active i {
            color: red;
        }
        .wishlist-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            padding: 0;
        }
    </style>
</head>
<body>

<!-- GEM DETAILS SECTION -->
<section class="gem-detail-section" style="padding:40px;">
    <div class="container" style="display:flex; gap:40px; flex-wrap:wrap;">

        <!-- Left: Images -->
        <div class="gem-images" style="flex:1; min-width:300px;">
            <div class="main-image" style="margin-bottom:10px;">
                <img id="currentImage" src="<?= htmlspecialchars($images[0] ?? 'https://via.placeholder.com/500x500?text=No+Image') ?>" style="width:100%; border-radius:10px;" />
            </div>
            <div class="thumbnails" style="display:flex; gap:10px;">
                <?php foreach($images as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" class="thumb" style="width:60px; height:60px; object-fit:cover; cursor:pointer; border-radius:5px;" />
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Details -->
        <div class="gem-details" style="flex:1; min-width:300px;">
            <h1><?= htmlspecialchars($gem['title']) ?></h1>
            <p><strong>Carat:</strong> <?= htmlspecialchars($gem['carat']) ?></p>
            <p><strong>Color:</strong> <?= htmlspecialchars($gem['color']) ?></p>
            <p><strong>Certification:</strong> <?= !empty($gem['certificate']) ? 'Certified' : 'Uncertified' ?></p>
            <p><strong>Price:</strong> Rs <?= number_format($gem['price'],2) ?> <?= $gem['is_negotiable']?'(Negotiable)':'' ?></p>
            <p><strong>Seller:</strong> <?= htmlspecialchars($gem['seller_name']) ?></p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($gem['description'])) ?></p>

            <!-- Wishlist Button 
            <button id="wishlistBtn" 
                    class="wishlist-btn <?= $inWishlist ? 'active' : '' ?>" 
                    data-gem-id="<?= $gem['id'] ?>">
                <i class="fa fa-heart"></i>
            </button>  -->
        </div>

    </div>
</section>

<!-- RELATED GEMS -->
<?php if(!empty($relatedGems)): ?>
<section class="related-gems" style="padding:40px; background:#f9f9f9;">
    <div class="container">
        <h2>Related Gems</h2>
        <div class="latest-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:20px;">
            <?php foreach($relatedGems as $g):
                $imgPath = $relatedImages[$g['id']] ?? "https://via.placeholder.com/400x500?text=No+Image";
                $hasCert = !empty($g['certificate']);
                $inWl = isset($_SESSION['user_id']) && in_array($g['id'], $wishlistGemIds);
            ?>
            <div class="card">
                <div class="card-image">
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($g['title']) ?>" />
                    <div class="card-overlay">
                        <button class="wishlist-btn <?= $inWl?'active':'' ?>" data-gem-id="<?= $g['id'] ?>">
                            <i class="fa fa-heart"></i>
                        </button>
                        <a href="gem_detail.php?id=<?= $g['id'] ?>">
                            <i class="fa fa-eye"></i>
                        </a>
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
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>

<script>
// Image thumbnail click
document.querySelectorAll('.thumb').forEach(t => {
    t.addEventListener('click',()=> document.getElementById('currentImage').src=t.src);
});

// Wishlist toggle (main & related)
document.querySelectorAll('.wishlist-btn').forEach(button => {
  button.addEventListener('click', () => {
    const gemId = button.getAttribute('data-gem-id');

    // Check if user is logged in
    const loggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    if (!loggedIn) {
      alert('Please log in to add to wishlist.');
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
        // Only icon
        button.setAttribute(
          'title',
          button.classList.contains('active') ? 'Remove from wishlist' : 'Add to wishlist'
        );
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
