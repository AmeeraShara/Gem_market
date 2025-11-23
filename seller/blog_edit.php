<?php
session_start();
require __DIR__ . '/../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: ../login.php"); exit;
}

$id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

$blog = $conn->query("SELECT * FROM blogs WHERE id=$id AND user_id=$user_id AND status='pending'")->fetch_assoc();
if(!$blog) die("Cannot edit this blog");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $conn->query("UPDATE blogs SET title='$title',content='$content' WHERE id=$id");
    header("Location: blog_list.php"); exit;
}
?>
<form method="POST">
<input type="text" name="title" value="<?= htmlspecialchars($blog['title']) ?>" required><br>
<textarea name="content" required><?= htmlspecialchars($blog['content']) ?></textarea><br>
<button type="submit">Update</button>
</form>
