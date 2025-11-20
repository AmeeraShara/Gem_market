<?php
session_start();
include __DIR__ . '/../config/db.php';

$where = ["g.status='approved'"];

// Filters
if(!empty($_GET['type'])) $where[] = "g.type='". $conn->real_escape_string($_GET['type']) ."'";
if(!empty($_GET['carat'])) $where[] = "g.carat=". floatval($_GET['carat']);
if(!empty($_GET['color'])) $where[] = "g.color='". $conn->real_escape_string($_GET['color']) ."'";
if(!empty($_GET['cert'])) {
    if($_GET['cert'] === 'yes') $where[] = "g.certificate IS NOT NULL AND g.certificate != ''";
    else $where[] = "(g.certificate IS NULL OR g.certificate = '')";
}
if(!empty($_GET['location'])) $where[] = "g.origin LIKE '%". $conn->real_escape_string($_GET['location']) ."%'";

$sql = "SELECT g.*, u.full_name AS seller_name
        FROM gems g
        JOIN users u ON g.seller_id=u.id
        WHERE ". implode(" AND ", $where) ."
        ORDER BY g.created_at DESC
        LIMIT 50";

$res = $conn->query($sql);
$gemIds = [];
$gems = [];
while($row = $res->fetch_assoc()){
    $gems[] = $row;
    $gemIds[] = $row['id'];
}

// Fetch images
$imagesByGem = [];
if(!empty($gemIds)){
    $ids = implode(',', array_map('intval', $gemIds));
    $imgRes = $conn->query("SELECT gem_id, image_path FROM gem_images WHERE gem_id IN ($ids) GROUP BY gem_id");
    while($img = $imgRes->fetch_assoc()) $imagesByGem[$img['gem_id']] = $img['image_path'];
}

// Wishlist
$wishlistGemIds = [];
if(isset($_SESSION['user_id'])){
    $userId = intval($_SESSION['user_id']);
    $resWishlist = $conn->query("SELECT gem_id FROM wishlist WHERE user_id=$userId");
    while($row=$resWishlist->fetch_assoc()) $wishlistGemIds[]=$row['gem_id'];
}

// Output HTML for gem cards
foreach($gems as $gem){
    $imgPath = $imagesByGem[$gem['id']] ?? "https://via.placeholder.com/400x500?text=No+Image";
    $hasCert = !empty($gem['certificate']);
    $inWishlist = in_array($gem['id'], $wishlistGemIds);
    ?>
    <div class="card">
        <div class="card-image">
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($gem['title']) ?>" />
            <div class="card-overlay">
                <button class="wishlist-btn <?= $inWishlist ? 'active' : '' ?>" data-gem-id="<?= $gem['id'] ?>" aria-label="Add to wishlist" title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                    <i class="fa fa-heart"></i>
                </button>
                <a href="gem_detail.php?id=<?= $gem['id'] ?>" aria-label="View details" title="View details"><i class="fa fa-eye"></i></a>
            </div>
        </div>
        <div class="card-content">
            <h3><?= htmlspecialchars($gem['title']) ?></h3>
            <p class="card-meta"><?= htmlspecialchars($gem['carat']) ?> Carat &bull; <?= htmlspecialchars($gem['color']) ?> &bull; <?= $hasCert ? '<span class="certified">Certified</span>' : '<span class="uncertified">Uncertified</span>' ?></p>
            <p class="price">Rs <?= number_format($gem['price'], 2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?></p>
            <p class="seller">Seller: <?= htmlspecialchars($gem['seller_name']) ?></p>
        </div>
    </div>
    <?php
}
?>
