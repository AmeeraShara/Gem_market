<?php
session_start();
require __DIR__ . '/../config/db.php';

/* PAGINATION */
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

/* FILTER OPTIONS FUNCTION */
function getOptions($conn, $column) {
    $res = $conn->query("SELECT DISTINCT $column FROM gems WHERE $column != '' AND $column IS NOT NULL");
    $vals = [];
    while ($row = $res->fetch_assoc()) {
        $vals[] = $row[$column];
    }
    return $vals;
}

/* FETCH FILTERS */
$types  = getOptions($conn, 'type');
$colors = getOptions($conn, 'color');
$carats = getOptions($conn, 'carat');
sort($carats);
$certificates = ['yes','no'];

/* BUILD WHERE CLAUSE */
$where = ["g.status='approved'"];

if (!empty($_GET['type'])) {
    $type = $conn->real_escape_string($_GET['type']);
    $where[] = "g.type='$type'";
}

if (!empty($_GET['carat'])) {
    $carat = $conn->real_escape_string($_GET['carat']);
    $where[] = "g.carat='$carat'";
}

if (!empty($_GET['color'])) {
    $color = $conn->real_escape_string($_GET['color']);
    $where[] = "g.color='$color'";
}

if (!empty($_GET['cert'])) {
    $where[] = ($_GET['cert'] === 'yes') ? "g.certificate != ''" : "g.certificate = ''";
}

if (!empty($_GET['location'])) {
    $location = $conn->real_escape_string($_GET['location']);
    $where[] = "g.location LIKE '%$location%'";
}

$filterSQL = implode(" AND ", $where);

/* COUNT FOR PAGINATION */
$countRes = $conn->query("SELECT COUNT(*) AS total FROM gems g WHERE $filterSQL");
$totalGems = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalGems / $limit);

/* FETCH GEM DATA */
$query = "
    SELECT g.*, u.full_name AS seller_name 
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE $filterSQL
    ORDER BY g.created_at DESC
    LIMIT $limit OFFSET $offset
";

$res = $conn->query($query);

$gems = [];
$gemIds = [];
while ($g = $res->fetch_assoc()) {
    $gems[] = $g;
    $gemIds[] = $g['id'];
}

/* FETCH IMAGES */
$imagesByGem = [];
if (!empty($gemIds)) {
    $ids = implode(',', array_map('intval', $gemIds));
    $imgRes = $conn->query("SELECT gem_id, image_path FROM gem_images WHERE gem_id IN ($ids) GROUP BY gem_id");
    while ($img = $imgRes->fetch_assoc()) {
        $imagesByGem[$img['gem_id']] = $img['image_path'];
    }
}

/* USER WISHLIST */
$wishlistGemIds = [];
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $wlRes = $conn->query("SELECT gem_id FROM wishlist WHERE user_id=$uid");
    while ($row = $wlRes->fetch_assoc()) {
        $wishlistGemIds[] = $row['gem_id'];
    }
}

include __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Browse Gems</title>

<!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/browse.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<section class="latest-section">
    <div class="container">
        <h2 class="section-title">Browse Gems</h2>

        <!-- FILTER BAR -->
        <form id="filterForm" method="GET">
            <div class="filter-container" style="flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                <select name="type">
                    <option value="">All Types</option>
                    <?php foreach($types as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>" <?= (($_GET['type'] ?? '') == $t) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="carat">
                    <option value="">Any Carat</option>
                    <?php foreach($carats as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= (($_GET['carat'] ?? '') == $c) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c) ?> Carat
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="color">
                    <option value="">Any Color</option>
                    <?php foreach($colors as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= (($_GET['color'] ?? '') == $c) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="cert">
                    <option value="">Certification</option>
                    <option value="yes" <?= (($_GET['cert'] ?? '') == 'yes') ? 'selected' : '' ?>>Certified</option>
                    <option value="no" <?= (($_GET['cert'] ?? '') == 'no') ? 'selected' : '' ?>>Uncertified</option>
                </select>

                <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
            </div>
        </form>

        <!-- GEM GRID -->
        <div class="latest-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:20px;">
            <?php foreach ($gems as $gem):
                $imgPath = $imagesByGem[$gem['id']] ?? "https://via.placeholder.com/400x500?text=No+Image";
                $hasCert = !empty($gem['certificate']);
                $inWishlist = in_array($gem['id'], $wishlistGemIds);
            ?>
            <div class="card">
                <div class="card-image">
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($gem['title']) ?>" />
                    <div class="card-overlay">
                        <button
                            class="wishlist-btn <?= $inWishlist ? 'active' : '' ?>"
                            data-gem-id="<?= $gem['id'] ?>"
                            title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                            <i class="fa fa-heart"></i>
                        </button>
                        <a href="gem_detail.php?id=<?= $gem['id'] ?>" title="View details">
                            <i class="fa fa-eye"></i>
                        </a>
                    </div>
                </div>

                <div class="card-content">
                    <h3><?= htmlspecialchars($gem['title']) ?></h3>
                    <p class="card-meta">
                        <?= htmlspecialchars($gem['carat']) ?> Carat • <?= htmlspecialchars($gem['color']) ?> •
                        <?= $hasCert ? '<span class="certified">Certified</span>' : '<span class="uncertified">Uncertified</span>' ?>
                    </p>
                    <p class="price">Rs <?= number_format($gem['price'], 2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?></p>
                    <p class="seller">Seller: <?= htmlspecialchars($gem['seller_name']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <div class="pagination" style="margin-top:20px; display:flex; justify-content:center; flex-wrap:wrap; gap:5px;">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="<?= $i == $page ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>

<script>
// Wishlist toggle
document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const gemId = btn.dataset.gemId;
        const loggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
        if (!loggedIn) { alert('Please log in'); return; }

        const url = btn.classList.contains('active') ? 'wishlist-remove.php' : 'wishlist-add.php';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'gem_id=' + gemId
        })
        .then(r => r.json())
        .then(d => { if(d.status==='success') btn.classList.toggle('active'); else alert(d.message); })
        .catch(()=>alert('Error updating wishlist'));
    });
});

// Auto-submit filters
const filterForm = document.getElementById('filterForm');
filterForm.querySelectorAll('select,input[name="location"]').forEach(el => {
    el.addEventListener('change', ()=>filterForm.submit());
});

// Debounce location input
let typingTimer;
const locInput = filterForm.querySelector('input[name="location"]');
locInput.addEventListener('keyup', ()=>{
    clearTimeout(typingTimer);
    typingTimer = setTimeout(()=>filterForm.submit(), 500);
});
</script>

</body>
</html>
