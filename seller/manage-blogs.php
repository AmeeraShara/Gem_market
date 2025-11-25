<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, title, status FROM blogs 
    WHERE user_id = ? ORDER BY id DESC
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$blogs = $stmt->get_result();

include "../seller/seller_header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Blogs - Seller Dashboard</title>
<link rel="stylesheet" href="../public/css/seller-blogs.css">
</head>
<body>

<div class="form-box">

    <h1 class="title">Your Blog Posts</h1>

    <table class="blog-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($b = $blogs->fetch_assoc()): ?>
            <tr class="<?= $b['status'] === 'deactivated' ? 'deactivated' : '' ?>">
                
                <td data-label="Title"><?= htmlspecialchars($b['title']) ?></td>

                <td data-label="Status">
                    <?php if ($b['status'] === 'approved'): ?>
                        <span class="status-approved">Approved</span>
                    <?php elseif ($b['status'] === 'pending'): ?>
                        <span class="status-pending">Pending</span>
                    <?php elseif ($b['status'] === 'rejected'): ?>
                        <span class="status-rejected">Rejected</span>
                    <?php else: ?>
                        <span class="status-rejected">Deactivated</span>
                    <?php endif; ?>
                </td>

                <td data-label="Actions">
                    <?php if ($b['status'] !== 'deactivated'): ?>
                        <button class="view-btn" onclick="window.location.href='blog_view.php?id=<?= $b['id'] ?>'">View</button>

                        <button class="action-btn edit" onclick="window.location.href='blog_edit.php?id=<?= $b['id'] ?>'">Edit</button>

                        <button class="action-btn delete" data-id="<?= $b['id'] ?>">Delete</button>
                    <?php else: ?>
                        <span class="status-rejected">Deactivated</span>
                    <?php endif; ?>
                </td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.action-btn.delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (!confirm("Are you sure you want to deactivate this blog?")) return;

        fetch(`blog_delete.php?id=${id}`)
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload();
        });
    });
});
</script>

</body>
</html>
