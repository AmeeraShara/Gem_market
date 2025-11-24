<?php
session_start();
include "../config/db.php";

// Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Validate blog ID
if (!isset($_POST['blog_id']) || !is_numeric($_POST['blog_id'])) {
    die("Invalid blog ID.");
}
$blog_id = intval($_POST['blog_id']);

// Fetch blog to ensure ownership
$stmt = $conn->prepare("SELECT * FROM blogs WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $blog_id, $user_id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$blog) {
    die("Blog not found or permission denied.");
}

// Update blog info
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE blogs SET title=?, content=?, status=? WHERE id=? AND user_id=?");
$stmt->bind_param("sssii", $title, $content, $status, $blog_id, $user_id);
$stmt->execute();
$stmt->close();

// Remove selected images
if (!empty($_POST['remove_images'])) {
    foreach ($_POST['remove_images'] as $img_id) {
        $stmtDel = $conn->prepare("SELECT image_path FROM blog_images WHERE id=? AND blog_id=?");
        $stmtDel->bind_param("ii", $img_id, $blog_id);
        $stmtDel->execute();
        $res = $stmtDel->get_result();
        if ($res->num_rows) {
            $row = $res->fetch_assoc();
            if (file_exists($row['image_path'])) unlink($row['image_path']);
            $stmtDel2 = $conn->prepare("DELETE FROM blog_images WHERE id=?");
            $stmtDel2->bind_param("i", $img_id);
            $stmtDel2->execute();
            $stmtDel2->close();
        }
        $stmtDel->close();
    }
}

// Upload new images
$blog_img_dir = "../public/uploads/blog_images/";
if (!is_dir($blog_img_dir)) mkdir($blog_img_dir, 0777, true);

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
        $filename = time() . "_" . basename($_FILES['images']['name'][$i]);
        $targetFile = $blog_img_dir . $filename;
        if (move_uploaded_file($tmpName, $targetFile)) {
            $stmtImg = $conn->prepare("INSERT INTO blog_images (blog_id, image_path) VALUES (?, ?)");
            $stmtImg->bind_param("is", $blog_id, $targetFile);
            $stmtImg->execute();
            $stmtImg->close();
        }
    }
}

$msg = "Blog updated successfully!";
header("Location: blog_edit.php?id=" . $blog_id . "&msg=" . urlencode($msg));
exit;
