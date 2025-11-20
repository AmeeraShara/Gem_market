<?php
session_start();
include __DIR__ . '/../config/db.php';

// Fetch last 4 approved gems with seller info
$sqlLatest = "
    SELECT g.*, u.name AS seller_name
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

// Fetch first image for each gem in batch
$imagesByGem = [];
if (!empty($gemIds)) {
    $ids = implode(',', array_map('intval', $gemIds));
    $imgRes = $conn->query("SELECT gem_id, image_path FROM gem_images WHERE gem_id IN ($ids) GROUP BY gem_id");
    while ($img = $imgRes->fetch_assoc()) {
        $imagesByGem[$img['gem_id']] = $img['image_path'];
    }
}

// Fetch wishlist items for logged in user
$wishlistGemIds = [];
if (isset($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
    $resWishlist = $conn->query("SELECT gem_id FROM wishlist WHERE user_id = $userId");
    while ($row = $resWishlist->fetch_assoc()) {
        $wishlistGemIds[] = $row['gem_id'];
    }
}
?>
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

<!-- Header -->
<header>
  <div class="container header-container">
    <a href="#" class="logo">Gem</a>
    <nav class="header-nav">
      <a href="#">Home</a>
      <a href="#">Blog</a>
      <a href="#">Browse Gems</a>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php">Login</a>
      <?php else: ?>
        <a href="logout.php">Logout</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- Hero Section -->
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

<!-- Latest Uploads Section -->
<section class="latest-section">
  <div class="container">
    <h2>Latest Uploads</h2>
    <div class="latest-grid">
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

<!-- Footer -->
<footer>
  <div class="container footer-grid">
    <div>
      <h3>GemMarketplace</h3>
      <p>Connecting buyers and sellers of premium gemstones globally.</p>
    </div>
    <div>
      <h3>Links</h3>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">Browse Gems</a></li>
        <li><a href="#">Blog</a></li>
      </ul>
    </div>
    <div>
      <h3>Contact</h3>
      <p>Email: support@gemmarketplace.com</p>
      <p>Phone: +1 234 567 890</p>
    </div>
  </div>
  <div class="footer-copy">&copy; 2025 GemMarketplace. All rights reserved.</div>
</footer>

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
        // Toggle class to change color
        button.classList.toggle('active');
        // Update tooltip/title
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

</script>

</body>
</html>
