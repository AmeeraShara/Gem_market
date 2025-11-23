<?php
require __DIR__ . '/../includes/db.php';

// Fetch all approved blogs
$blogsRes = $conn->query("
    SELECT b.id, b.title, b.content, b.created_at, u.full_name,
           (SELECT image_path FROM blog_images WHERE blog_id=b.id LIMIT 1) AS featured_image
    FROM blogs b
    JOIN users u ON b.user_id=u.id
    WHERE b.status='approved'
    ORDER BY b.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blogs - Gem Marketplace</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

<!-- Header -->
<header class="bg-white shadow">
    <div class="container mx-auto px-4 py-4">
        <h1 class="text-2xl font-bold">Gem Marketplace Blogs</h1>
    </div>
</header>

<!-- Blogs Listing -->
<main class="container mx-auto px-4 py-6">
    <?php if($blogsRes->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($blog = $blogsRes->fetch_assoc()): ?>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <?php if($blog['featured_image']): ?>
                        <img src="<?= htmlspecialchars($blog['featured_image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x200?text=No+Image" alt="No Image" class="w-full h-48 object-cover">
                    <?php endif; ?>
                    <div class="p-4">
                        <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($blog['title']) ?></h2>
                        <p class="text-gray-600 text-sm mb-2">By <?= htmlspecialchars($blog['full_name']) ?> | <?= date('d M Y', strtotime($blog['created_at'])) ?></p>
                        <p class="text-gray-700 mb-3"><?= nl2br(htmlspecialchars(substr($blog['content'],0,150))) ?>...</p>
                        <a href="blog_view.php?id=<?= $blog['id'] ?>" class="text-blue-600 hover:underline font-semibold">Read More</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600">No blogs available at the moment.</p>
    <?php endif; ?>
</main>

</body>
</html>
