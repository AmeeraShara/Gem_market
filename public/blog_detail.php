<?php
session_start();
include __DIR__ . '/../config/db.php';

// Get blog ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$blog_id = intval($_GET['id']);

// Fetch blog details including associated gem
$sql = "
    SELECT b.*, u.full_name AS author_name, b.gem_id
    FROM blogs b
    JOIN users u ON b.user_id = u.id
    WHERE b.id = $blog_id AND b.status = 'approved'
    LIMIT 1
";
$res = $conn->query($sql);
$blog = $res->fetch_assoc();

if (!$blog) {
    echo "Blog not found or not approved.";
    exit;
}

// Fetch blog images
$images = [];
$imgRes = $conn->query("SELECT image_path FROM blog_images WHERE blog_id = $blog_id");
while ($row = $imgRes->fetch_assoc()) {
    $images[] = $row['image_path'];
}

// Fetch associated gem details if available
$gem = null;
if (!empty($blog['gem_id'])) {
    $gem_id = intval($blog['gem_id']);
    $gemRes = $conn->query("SELECT * FROM gems WHERE id=$gem_id AND status='approved' LIMIT 1");
    $gem = $gemRes->fetch_assoc();
}

include __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($blog['title']) ?></title>

<!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/blog.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<section class="blog-detail-section py-5">
    <div class="container">
        <h1 class="blog-title"><?= htmlspecialchars($blog['title']) ?></h1>
        <p class="author mb-4">By <?= htmlspecialchars($blog['author_name']) ?> | <?= date('F j, Y', strtotime($blog['created_at'])) ?></p>

        <?php if (!empty($images)): ?>
            <div class="blog-images mb-4">
                <?php foreach ($images as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="img-fluid mb-2">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="blog-content mb-4">
            <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </div>

        <?php if ($gem): ?>
            <div class="associated-gem mb-4 p-3 border rounded bg-light">
                <h4>Associated Gem: <a href="gem_detail.php?id=<?= $gem['id'] ?>"><?= htmlspecialchars($gem['title']) ?></a></h4>
                <p>
                    Type: <?= htmlspecialchars($gem['type']) ?> | 
                    Carat: <?= htmlspecialchars($gem['carat']) ?> | 
                    Color: <?= htmlspecialchars($gem['color']) ?> | 
                    Price: Rs <?= number_format($gem['price'], 2) ?>
                </p>
                <a href="gem_detail.php?id=<?= $gem['id'] ?>" class="btn btn-primary">View Gem Details</a>
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary">Back to Home</a>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
