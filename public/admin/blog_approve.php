<?php
session_start();
include __DIR__ . '/../../config/db.php'; 
include 'admin_header.php';

// Only admin access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

// Handle AJAX approve/reject
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])){
    $id = (int)$_POST['id'];
    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE blogs SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    if($stmt->execute()){
        echo "✔ Blog has been " . strtoupper($status) . " successfully!";
    } else {
        echo "❌ Error updating status.";
    }
    $stmt->close();
    exit;
}

// Fetch pending blogs
$stmt = $conn->prepare("
    SELECT b.id, b.title, b.content, u.full_name,
           (SELECT image_path FROM blog_images WHERE blog_id=b.id LIMIT 1) AS featured_image
    FROM blogs b
    JOIN users u ON b.user_id=u.id
    WHERE b.status='pending'
    ORDER BY b.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pending Blogs - Admin Dashboard</title>
<link rel="stylesheet" href="../css/admin-blogs.css">
</head>
<body>

<div class="form-box">

    <h1 class="title">Pending Blogs</h1>

    <table class="blog-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Featured Image</th>
                <th>Preview</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr id="blogRow_<?= $row['id']; ?>">
                <td data-label="Title"><?= htmlspecialchars($row['title']); ?></td>
                <td data-label="Author"><?= htmlspecialchars($row['full_name']); ?></td>
                <td data-label="Featured Image" style="text-align:center;">
                    <?php if($row['featured_image'] && file_exists('../' . $row['featured_image'])): ?>
                        <img src="<?= '../' . htmlspecialchars($row['featured_image']); ?>" class="doc-img" alt="Featured Image">
                    <?php else: ?>
                        No Image
                    <?php endif; ?>
                </td>
                <td data-label="Preview"><?= nl2br(htmlspecialchars(substr($row['content'],0,100))); ?>...</td>
                <td data-label="Actions">
                    <button class="approve-btn" onclick="updateBlogStatus(<?= $row['id']; ?>,'approved', this)">Approve</button>
                    <button class="reject-btn" onclick="updateBlogStatus(<?= $row['id']; ?>,'rejected', this)">Reject</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div id="msg" class="status-msg"></div>
</div>

<script>
function updateBlogStatus(id, status, btn){
    if (!confirm(`Are you sure you want to mark this blog as ${status}?`)) return;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;
            btn.parentElement.innerHTML =
                "<span class='" + (status==='approved'?'status-approved':'status-rejected') + "'>" 
                + status.toUpperCase() + "</span>";
        }
    };
    xhr.send("id=" + id + "&status=" + status);
}
</script>

</body>
</html>

<?php $stmt->close(); ?>
