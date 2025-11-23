<?php
require __DIR__ . '/../includes/db.php';
$id = intval($_GET['id']);
$blog = $conn->query("SELECT b.*, u.full_name FROM blogs b JOIN users u ON b.user_id=u.id WHERE b.id=$id AND b.status='approved'")->fetch_assoc();
if(!$blog) die("Blog not found");

$images = $conn->query("SELECT * FROM blog_images WHERE blog_id=$id");
$videos = $conn->query("SELECT * FROM blog_videos WHERE blog_id=$id");
?>

<h1><?= htmlspecialchars($blog['title']) ?></h1>
<p>By <?= htmlspecialchars($blog['full_name']) ?> | <?= $blog['created_at'] ?></p>
<p><?= nl2br(htmlspecialchars($blog['content'])) ?></p>

<h3>Images</h3>
<?php while($img = $images->fetch_assoc()): ?>
<img src="<?= $img['image_path'] ?>" width="200">
<?php endwhile; ?>

<h3>Videos</h3>
<?php while($vid = $videos->fetch_assoc()): ?>
<video width="400" controls><source src="<?= $vid['video_path'] ?>"></video>
<?php endwhile; ?>
