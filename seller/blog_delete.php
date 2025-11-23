<?php
session_start();
require __DIR__ . '/../includes/db.php';
$id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);
$conn->query("DELETE FROM blogs WHERE id=$id AND user_id=$user_id AND status='pending'");
header("Location: blog_list.php");
exit;
?>
