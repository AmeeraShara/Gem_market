<?php
include __DIR__ . '/../config/db.php';
session_start();

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

// Collect gem IDs for batch image query
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gem Marketplace</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-family: 'Inter', sans-serif; margin:0; padding:0; background-color:#f9fafb; }
header { background-color:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.1); position:sticky; top:0; z-index:20; }
header a { text-decoration:none; color:#111; font-weight:600; }
header nav a { margin-left:24px; }
.btn-primary { background-color:#2563EB; padding:8px 20px; border-radius:9999px; font-weight:600; color:white; text-align:center; display:inline-block; transition:0.3s; }
.btn-primary:hover { background-color:#1D4ED8; }
.card { background-color:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.1); cursor:pointer; transition:all 0.3s; }
.card img { width:100%; height:320px; object-fit:cover; transition:transform 0.3s; }
.card img:hover { transform:scale(1.05); }
.card-overlay { position:absolute; bottom:12px; left:50%; transform:translateX(-50%); display:flex; gap:8px; opacity:0; transition:opacity 0.3s; background-color:rgba(255,255,255,0.3); backdrop-filter:blur(4px); border-radius:50%; padding:4px; }
.card-overlay button, .card-overlay a { padding:6px; border-radius:50%; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.card:hover .card-overlay { opacity:1; }
@media screen and (max-width:1024px) {
  .hero-grid { display:block; }
  .hero-main, .hero-side { width:100%; margin-bottom:16px; }
}
@media screen and (max-width:768px) {
  header nav { display:flex; flex-direction:column; align-items:flex-start; }
  header nav a { margin:6px 0 0 0; }
  .latest-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:16px; }
  .card img { height:240px; }
}
</style>
</head>
<body>

<!-- Header -->
<header>
  <div style="max-width:1200px; margin:0 auto; padding:16px; display:flex; justify-content:space-between; align-items:center;">
    <a href="#" style="font-size:24px; font-weight:800;">Gem</a>
    <nav style="display:flex; align-items:center; font-size:16px; color:#555;">
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
<section style="max-width:1200px; margin:32px auto; padding:0 16px;">
  <div class="hero-grid" style="display:grid; grid-template-columns:2fr 1fr; gap:16px;">
    <div class="hero-main" style="position:relative; border-radius:12px; overflow:hidden;">
      <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80" alt="Gem hero" style="width:100%; height:420px; object-fit:cover;">
      <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
      <div style="position:absolute; bottom:24px; left:24px; color:white; max-width:400px;">
        <h1 style="font-size:32px; font-weight:800; margin-bottom:8px;">Yours Truly</h1>
        <p style="font-size:18px; font-weight:400; margin-bottom:12px;">The Amora Collection</p>
        <a href="#" class="btn-primary">Shop Now</a>
      </div>
    </div>
    <div class="hero-side" style="position:relative; border-radius:12px; overflow:hidden;">
      <img src="https://images.unsplash.com/photo-1501386767819-c4c6f75d9c77?auto=format&fit=crop&w=600&q=80" alt="Gem Closeup" style="width:100%; height:420px; object-fit:cover;">
      <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent);"></div>
      <div style="position:absolute; bottom:16px; left:16px; color:white; font-weight:600; font-size:16px; max-width:240px;">
        The Perfect Gift Awaits
        <p style="font-size:12px; font-weight:400; margin-top:4px;">Find the perfect gift that embodies love, elegance, and sentimentality.</p>
        <a href="#" class="btn-primary" style="margin-top:8px;">Shop Now</a>
      </div>
    </div>
  </div>
</section>

<!-- Latest Uploads Section -->
<section style="padding:48px 0; background-color:#f9fafb;">
  <div style="max-width:1200px; margin:0 auto; padding:0 16px;">
    <h2 style="font-size:32px; font-weight:700; text-align:center; margin-bottom:24px;">Latest Uploads</h2>

    <div class="latest-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:24px; justify-content:center;">
      <?php foreach ($latestGems as $gem): 
            $imgPath = isset($imagesByGem[$gem['id']]) 
                       ? $imagesByGem[$gem['id']]  
                       : "https://via.placeholder.com/400x500?text=No+Image";
            $hasCert = !empty($gem['certificate']);
      ?>
      <div class="card">
        <div style="position:relative; overflow:hidden;">
          <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($gem['title']); ?>">
          <div class="card-overlay">
            <button style="color:#e11d48; background-color:rgba(255,255,255,0.6);"><i class="fa fa-heart"></i></button>
            <a href="gem_detail.php?id=<?php echo $gem['id']; ?>" style="color:#2563eb; background-color:rgba(255,255,255,0.6);"><i class="fa fa-eye"></i></a>
          </div>
        </div>
        <div style="padding:12px; display:flex; flex-direction:column; justify-content:space-between; height:160px;">
          <div>
            <h3 style="font-size:16px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($gem['title']); ?></h3>
            <p style="font-size:12px; color:#555; margin-top:4px;">
              <?php echo htmlspecialchars($gem['carat']); ?> Carat &bull; <?php echo htmlspecialchars($gem['color']); ?> &bull;
              <?php echo $hasCert ? '<span style="color:#16a34a; font-weight:700;">Certified</span>' 
                                   : '<span style="color:#dc2626; font-weight:700;">Uncertified</span>'; ?>
            </p>
          </div>
          <div style="margin-top:8px;">
            <p style="font-size:14px; font-weight:700; color:#1d4ed8;">
              Rs <?php echo number_format($gem['price'],2); ?> <?php echo $gem['is_negotiable'] ? '(Negotiable)' : ''; ?>
            </p>
            <p style="font-size:12px; color:#777; margin-top:4px;">Seller: <?php echo htmlspecialchars($gem['seller_name']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Footer -->
<footer style="background-color:#111; color:#ccc; margin-top:40px; padding:48px 16px;">
  <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:24px;">
    <div>
      <h3 style="font-weight:700; font-size:18px; margin-bottom:8px; color:white;">GemMarketplace</h3>
      <p>Connecting buyers and sellers of premium gemstones globally.</p>
    </div>
    <div>
      <h3 style="font-weight:700; font-size:18px; margin-bottom:8px; color:white;">Links</h3>
      <ul style="padding:0; margin:0; list-style:none;">
        <li><a href="#" style="color:#ccc; text-decoration:none;">Home</a></li>
        <li><a href="#" style="color:#ccc; text-decoration:none;">Browse Gems</a></li>
        <li><a href="#" style="color:#ccc; text-decoration:none;">Blog</a></li>
      </ul>
    </div>
    <div>
      <h3 style="font-weight:700; font-size:18px; margin-bottom:8px; color:white;">Contact</h3>
      <p>Email: support@gemmarketplace.com</p>
      <p>Phone: +1 234 567 890</p>
    </div>
  </div>
  <div style="text-align:center; margin-top:24px; color:#777; font-size:12px;">&copy; 2025 GemMarketplace. All rights reserved.</div>
</footer>

</body>
</html>
