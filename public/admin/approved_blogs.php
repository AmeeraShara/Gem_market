<?php
session_start();
include __DIR__ . '/../../config/db.php'; 
include 'admin_header.php';

// Only admin access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

// Handle deactivate via AJAX
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])){
    $id = (int)$_POST['id'];
    $action = $_POST['action'] === 'deactivate' ? 'deactivated' : '';

    if($action){
        $stmt = $conn->prepare("UPDATE blogs SET status='deactivated' WHERE id=?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            echo "✔ Blog has been DEACTIVATED successfully!";
        } else {
            echo "❌ Error updating status.";
        }
        $stmt->close();
        exit;
    }
}

// Pagination settings
$limit = 5; // blogs per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total approved blogs
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM blogs WHERE status='approved'");
$totalRow = $totalResult->fetch_assoc();
$totalBlogs = $totalRow['total'];
$totalPages = ceil($totalBlogs / $limit);

// Fetch approved blogs with LIMIT & OFFSET
$stmt = $conn->prepare("
    SELECT b.id, b.title, b.content, u.full_name,
           (SELECT image_path FROM blog_images WHERE blog_id=b.id LIMIT 1) AS featured_image
    FROM blogs b
    JOIN users u ON b.user_id=u.id
    WHERE b.status='approved'
    ORDER BY b.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Approved Blogs - Admin Dashboard</title>
<link rel="stylesheet" href="../css/approved-blogs.css">
</head>
<body>

<div class="form-box">
    <h1 class="title">Approved Blogs</h1>
    <div id="msg" class="status-msg"></div>

    <table class="gem-table">
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
                        <img src="<?= '../' . htmlspecialchars($row['featured_image']); ?>" class="doc-img clickable-img" data-full="../<?= htmlspecialchars($row['featured_image']); ?>" alt="Featured Image">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td data-label="Preview"><?= nl2br(htmlspecialchars(substr($row['content'],0,100))); ?>...</td>
                <td data-label="Actions">
                    <button class="reject-btn" onclick="updateBlogStatus(<?= $row['id']; ?>,'deactivate', this)">Deactivate</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<!-- Pagination -->
<div class="pagination">
    <?php if($totalPages > 1): ?>
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<!-- Image Modal -->
<div id="imgModal" class="img-modal">
  <span class="img-close">&times;</span>
  <img class="img-modal-content" id="modalImage" alt="Expanded Image">
</div>

<script>
function updateBlogStatus(id, action, btn){
    if(!confirm(`Are you sure you want to ${action} this blog?`)) return;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function(){
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;
            btn.parentElement.innerHTML = '<span class="status-rejected">DEACTIVATED</span>';
        }
    };
    xhr.send("id=" + id + "&action=" + action);
}

// Image modal logic
document.querySelectorAll('.clickable-img').forEach(img => {
    img.addEventListener('click', () => {
        const modal = document.getElementById('imgModal');
        const modalImg = document.getElementById('modalImage');
        modal.style.display = "block";
        modalImg.src = img.getAttribute('data-full');
    });
});

document.querySelector('.img-close').addEventListener('click', () => {
    document.getElementById('imgModal').style.display = "none";
});

document.getElementById('imgModal').addEventListener('click', (e) => {
    if(e.target === e.currentTarget){
        e.currentTarget.style.display = "none";
    }
});
</script>

</body>
</html>

<?php $stmt->close(); ?>
