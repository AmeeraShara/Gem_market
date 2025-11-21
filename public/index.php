<?php
session_start();
include __DIR__ . '/../config/db.php';

$sqlLatest = "
    SELECT g.*, u.full_name AS seller_name
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.status='approved'
    ORDER BY g.created_at DESC
    LIMIT 4
";
$resLatest = mysqli_query($conn, $sqlLatest);

$gemIds = [];
$latestGems = [];
while ($gem = mysqli_fetch_assoc($resLatest)) {
    $latestGems[] = $gem;
    $gemIds[] = $gem['id'];
}

$imagesByGem = [];
if (!empty($gemIds)) {
    $ids = implode(',', array_map('intval', $gemIds));
    $imgRes = $conn->query("SELECT gem_id, image_path FROM gem_images WHERE gem_id IN ($ids) GROUP BY gem_id");
    while ($img = $imgRes->fetch_assoc()) {
        $imagesByGem[$img['gem_id']] = $img['image_path'];
    }
}

$wishlistGemIds = [];
if (isset($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
    $resWishlist = $conn->query("SELECT gem_id FROM wishlist WHERE user_id = $userId");
    while ($row = $resWishlist->fetch_assoc()) {
        $wishlistGemIds[] = $row['gem_id'];
    }
}

// Fetch filter values from DB
$typeRes = $conn->query("SELECT DISTINCT type FROM gems WHERE type IS NOT NULL AND type != ''");
$types = [];
while ($row = $typeRes->fetch_assoc()) $types[] = $row['type'];

$colorRes = $conn->query("SELECT DISTINCT color FROM gems WHERE color IS NOT NULL AND color != ''");
$colors = [];
while ($row = $colorRes->fetch_assoc()) $colors[] = $row['color'];

$certRes = $conn->query("SELECT DISTINCT certificate FROM gems");
$certificates = [];
while ($row = $certRes->fetch_assoc()) {
    $certificates[] = !empty($row['certificate']) ? 'yes' : 'no';
}
$certificates = array_unique($certificates);

$caratRes = $conn->query("SELECT DISTINCT carat FROM gems WHERE carat IS NOT NULL");
$carats = [];
while ($row = $caratRes->fetch_assoc()) $carats[] = $row['carat'];
sort($carats);
?>

<?php include __DIR__ . '/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gem Marketplace</title>
<link rel="stylesheet" href="css/index.css" />
<link rel="stylesheet" href="css/wishlist.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<header>

</header>

<section class="hero-section">
  <div class="hero-grid">
    <div class="hero-main">
      <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80" alt="Gem hero" />
      <div class="hero-overlay-main"></div>
      <div class="hero-text-main">
        <h1>Yours Truly</h1>
        <p>The Amora Collection</p>
        <a href="#" class="btn-primary">Shop Now</a>
      </div>
    </div>
    <div class="hero-side">
      <img src="https://images.unsplash.com/photo-1501386767819-c4c6f75d9c77?auto=format&fit=crop&w=600&q=80" alt="Gem Closeup" />
      <div class="hero-overlay-side"></div>
      <div class="hero-text-side">
        <p>The Perfect Gift Awaits</p>
        <small>Find the perfect gift that embodies love, elegance, and sentimentality.</small>
        <a href="#" class="btn-primary">Shop Now</a>
      </div>
    </div>
  </div>
</section>

<section class="latest-section">
  <div class="container">
    <h2>Latest Uploads</h2>

    <!-- Filters -->
    <div class="filter-container" style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
      <select id="filterType">
        <option value="">All Types</option>
        <?php foreach($types as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>

      <select id="filterCarat">
        <option value="">Any Carat</option>
        <?php foreach($carats as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?> Carat</option>
        <?php endforeach; ?>
      </select>

      <select id="filterColor">
        <option value="">Any Color</option>
        <?php foreach($colors as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>

      <select id="filterCert">
        <option value="">All Certifications</option>
        <?php foreach($certificates as $cert): ?>
          <option value="<?= $cert ?>"><?= $cert === 'yes' ? 'Certified' : 'Uncertified' ?></option>
        <?php endforeach; ?>
      </select>

      <input type="text" id="filterLocation" placeholder="Location" />
    </div>

    <!-- Gems grid -->
    <div class="latest-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
      <?php foreach ($latestGems as $gem):
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
              aria-label="Add to wishlist"
              title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>"
            >
              <i class="fa fa-heart"></i>
            </button>
            <a href="gem_detail.php?id=<?= $gem['id'] ?>" aria-label="View details" title="View details">
              <i class="fa fa-eye"></i>
            </a>
          </div>
        </div>
        <div class="card-content">
          <h3><?= htmlspecialchars($gem['title']) ?></h3>
          <p class="card-meta">
            <?= htmlspecialchars($gem['carat']) ?> Carat &bull; <?= htmlspecialchars($gem['color']) ?> &bull;
            <?= $hasCert ? '<span class="certified">Certified</span>' : '<span class="uncertified">Uncertified</span>' ?>
          </p>
          <p class="price">Rs <?= number_format($gem['price'], 2) ?> <?= $gem['is_negotiable'] ? '(Negotiable)' : '' ?></p>
          <p class="seller">Seller: <?= htmlspecialchars($gem['seller_name']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<?php include __DIR__ . '/footer.php'; ?>


<script>
document.querySelectorAll('.wishlist-btn').forEach(button => {
  button.addEventListener('click', () => {
    const gemId = button.getAttribute('data-gem-id');
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
        button.setAttribute(
          'title',
          button.classList.contains('active') ? 'Remove from wishlist' : 'Add to wishlist'
        );
      } else {
        alert(data.message);
      }
    })
    .catch(() => alert('Error updating wishlist'));
  });
});
function fetchFilteredGems() {
    const type = document.getElementById('filterType').value;
    const carat = document.getElementById('filterCarat').value;
    const color = document.getElementById('filterColor').value;
    const cert = document.getElementById('filterCert').value;
    const location = document.getElementById('filterLocation').value;

    const params = new URLSearchParams({ type, carat, color, cert, location });

    fetch('fetch_gems.php?' + params.toString())
        .then(res => res.text())
        .then(html => {
            document.querySelector('.latest-grid').innerHTML = html;
        });
}

// Attach event listeners
document.querySelectorAll('#filterType, #filterCarat, #filterColor, #filterCert, #filterLocation')
    .forEach(el => el.addEventListener('change', fetchFilteredGems));
</script>

</body>
</html>
