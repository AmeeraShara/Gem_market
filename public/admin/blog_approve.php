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

<h2 style="font-size:18px; font-weight:bold; margin-bottom:12px;">Pending Blogs</h2>

<table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
        <tr style="background-color:#f3f3f3;">
            <th style="border:1px solid #ccc; padding:8px;">Title</th>
            <th style="border:1px solid #ccc; padding:8px;">Author</th>
            <th style="border:1px solid #ccc; padding:8px;">Featured Image</th>
            <th style="border:1px solid #ccc; padding:8px;">Preview</th>
            <th style="border:1px solid #ccc; padding:8px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr id="blogRow_<?php echo $row['id']; ?>">
            <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($row['title']) ?></td>
            <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($row['full_name']) ?></td>
            <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                <?php if($row['featured_image'] && file_exists('../' . $row['featured_image'])): ?>
                    <img src="<?= '../' . htmlspecialchars($row['featured_image']) ?>" 
                         alt="Featured Image" style="width:50px; height:50px; object-fit:cover; border-radius:4px; border:1px solid #ccc;">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>
            <td style="border:1px solid #ccc; padding:6px;"><?= nl2br(htmlspecialchars(substr($row['content'],0,100))) ?>...</td>
            <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                <button onclick="updateBlogStatus(<?= $row['id']; ?>,'approved', this)" 
                        style="color:green; text-decoration:underline; margin-right:8px; background:none; border:none; cursor:pointer;">
                    Approve
                </button>
                <button onclick="updateBlogStatus(<?= $row['id']; ?>,'rejected', this)" 
                        style="color:red; text-decoration:underline; background:none; border:none; cursor:pointer;">
                    Reject
                </button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<div id="msg" style="margin-top:10px; font-weight:bold;"></div>

<script>
function updateBlogStatus(id, status, btn){
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true); // same page
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;

            // Replace buttons with status text
            btn.parentElement.innerHTML = '<span style="color:' + (status==='approved'?'green':'red') + '; font-weight:bold;">' + status.toUpperCase() + ' </span>';
        }
    };
    xhr.send("id=" + id + "&status=" + status);
}
</script>

<?php $stmt->close(); ?>
