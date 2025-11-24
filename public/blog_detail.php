<?php
session_start();
include __DIR__ . '/../config/db.php';

// Get blog ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$blog_id = intval($_GET['id']);

// Fetch blog details
$sql = "
    SELECT b.*, u.full_name AS author_name
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

<!-- Browse page CSS -->
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/browse.css">

<!-- Blog-specific CSS -->
<link rel="stylesheet" href="css/blog.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<section class="blog-detail-section">
    <div class="container">
        <h1 class="blog-title"><?= htmlspecialchars($blog['title']) ?></h1>
        <p class="author">By <?= htmlspecialchars($blog['author_name']) ?> | <?= date('F j, Y', strtotime($blog['created_at'])) ?></p>

        <?php if (!empty($images)): ?>
            <div class="blog-images">
                <?php foreach ($images as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="blog-content">
            <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </div>

        <a href="index.php" class="btn-primary">Back to Home</a>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
