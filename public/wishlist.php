<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch wishlist items
$sql = "
    SELECT 
        w.id AS wishlist_id, 
        g.*, 
        u.full_name AS seller_name, 
        (SELECT image_path FROM gem_images WHERE gem_id = g.id LIMIT 1) AS image_path
    FROM wishlist w
    JOIN gems g ON w.gem_id = g.id
    JOIN users u ON g.seller_id = u.id
    WHERE w.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Wishlist</title>
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/wishlist.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Ensure footer is below content */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.main-content {
    flex: 1;
}
</style>
</head>
<body>

<!-- HEADER -->
<?php include __DIR__ . '/header.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="container">
        <h2>Your Wishlist</h2>

        <div class="latest-grid">
            <?php if ($res->num_rows == 0): ?>
                <p>No items in wishlist.</p>
            <?php endif; ?>

            <?php while ($gem = $res->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-image">
                        <img src="<?= $gem['image_path'] ?? 'https://via.placeholder.com/400x500?text=No+Image' ?>">

                        <div class="card-overlay">
                            <button class="wishlist-remove" data-id="<?= $gem['id'] ?>">
                                <i class="fa fa-heart-broken"></i>
                            </button>

                            <a href="gem_detail.php?id=<?= $gem['id'] ?>">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-content">
                        <h3><?= htmlspecialchars($gem['title']) ?></h3>
                        <p><?= $gem['carat'] ?> Carat • <?= $gem['color'] ?></p>
                        <p class="price">Rs <?= number_format($gem['price'], 2) ?></p>
                        <p class="seller">Seller: <?= $gem['seller_name'] ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- FOOTER -->
<?php include __DIR__ . '/footer.php'; ?>

<!-- JAVASCRIPT -->
<script>
document.querySelectorAll('.wishlist-remove').forEach(btn => {
    btn.addEventListener('click', () => {
        let id = btn.getAttribute('data-id');

        fetch('wishlist-remove.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'gem_id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                btn.closest('.card').remove();
            } else {
                alert(data.message);
            }
        });
    });
});
</script>

</body>
</html>
