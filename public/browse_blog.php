<?php
session_start();
require __DIR__ . '/../config/db.php';

/* PAGINATION */
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

/* COUNT TOTAL BLOGS */
$countRes = $conn->query("SELECT COUNT(*) AS total FROM blogs WHERE status='approved'");
$totalBlogs = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalBlogs / $limit);

/* FETCH BLOGS */
$res = $conn->query("
    SELECT b.*, u.full_name AS author_name
    FROM blogs b
    JOIN users u ON b.user_id = u.id
    WHERE b.status='approved'
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset
");

$blogs = [];
$blogIds = [];
while ($b = $res->fetch_assoc()) {
    $blogs[] = $b;
    $blogIds[] = $b['id'];
}

/* FETCH BLOG IMAGES */
$imagesByBlog = [];
if (!empty($blogIds)) {
    $ids = implode(',', array_map('intval', $blogIds));
    $imgRes = $conn->query("SELECT blog_id, image_path FROM blog_images WHERE blog_id IN ($ids) GROUP BY blog_id");
    while ($img = $imgRes->fetch_assoc()) {
        $imagesByBlog[$img['blog_id']] = $img['image_path'];
    }
}

include __DIR__ . '/header.php';
?>
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/browse.css">
<link rel="stylesheet" href="css/blog.css">

<section class="latest-section">
    <div class="container">
        <h2 class="section-title">All Blogs</h2>

        <div class="latest-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px;">
            <?php foreach ($blogs as $blog): 
                $imgPath = $imagesByBlog[$blog['id']] ?? "https://via.placeholder.com/400x250?text=No+Image";
            ?>
            <div class="card">
                <div class="card-image">
                    <a href="blog.php?id=<?= $blog['id'] ?>">
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                    </a>
                </div>
                <div class="card-content">
                    <h3><a href="blog.php?id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a></h3>
                    <p class="author">By <?= htmlspecialchars($blog['author_name']) ?> | <?= date('F j, Y', strtotime($blog['created_at'])) ?></p>
                    <p class="excerpt"><?= htmlspecialchars(substr($blog['content'], 0, 120)) ?>...</p>
                    <a href="blog_detail.php?id=<?= $blog['id'] ?>" class="btn-primary">Read More</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <div class="pagination" style="margin-top:20px; display:flex; justify-content:center; flex-wrap:wrap; gap:5px;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i=1; $i <= $totalPages; $i++): ?>
                <a class="<?= $i==$page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
