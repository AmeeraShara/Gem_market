<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
  header("Location: ../public/login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $user_id = intval($_SESSION['user_id']);

    $conn->query("INSERT INTO blogs (user_id,title,content) VALUES ($user_id,'$title','$content')");
    $blog_id = $conn->insert_id;

    // Upload Images
    if(!empty($_FILES['images']['name'][0])){
        foreach($_FILES['images']['name'] as $i => $name){
            $tmp_name = $_FILES['images']['tmp_name'][$i];
            $path = '../uploads/blogs/images/' . time().'_'.$name;
            move_uploaded_file($tmp_name,$path);
            $conn->query("INSERT INTO blog_images (blog_id,image_path) VALUES ($blog_id,'$path')");
        }
    }

    // Upload Videos
    if(!empty($_FILES['videos']['name'][0])){
        foreach($_FILES['videos']['name'] as $i => $name){
            $tmp_name = $_FILES['videos']['tmp_name'][$i];
            $path = '../uploads/blogs/videos/' . time().'_'.$name;
            move_uploaded_file($tmp_name,$path);
            $conn->query("INSERT INTO blog_videos (blog_id,video_path) VALUES ($blog_id,'$path')");
        }
    }

    header("Location: blog_list.php?msg=added");
    exit;
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required><br>
    <textarea name="content" placeholder="Content" required></textarea><br>
    <label>Images:</label><input type="file" name="images[]" multiple accept="image/*"><br>
    <label>Videos:</label><input type="file" name="videos[]" multiple accept="video/*"><br>
    <button type="submit">Submit Blog</button>
</form>
