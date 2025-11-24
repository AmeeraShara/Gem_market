<?php
session_start();
include "../config/db.php";

// Restrict to sellers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: ../public/login.php");
    exit;
}

$msg = "";

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
            $path = '../public/uploads/blogs/images/' . time().'_'.$name;
            move_uploaded_file($tmp_name,$path);
            $conn->query("INSERT INTO blog_images (blog_id,image_path) VALUES ($blog_id,'$path')");
        }
    }

    // Upload Videos
    if(!empty($_FILES['videos']['name'][0])){
        foreach($_FILES['videos']['name'] as $i => $name){
            $tmp_name = $_FILES['videos']['tmp_name'][$i];
            $path = '../public/uploads/blogs/videos/' . time().'_'.$name;
            move_uploaded_file($tmp_name,$path);
            $conn->query("INSERT INTO blog_videos (blog_id,video_path) VALUES ($blog_id,'$path')");
        }
    }

    $msg = "Blog submitted! Await admin approval.";
}

include "../seller/seller_header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog - Seller Dashboard</title>
    <link rel="stylesheet" href="../public/css/add-blog.css">
</head>
<body>

<div class="form-box">
    <button 
        style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; font-size: 24px; cursor: pointer; color: #555;"
        onclick="window.location.href='../public/seller-dashboard.php'" title="Close"
    >&times;</button>

    <h1 class="title">Add New Blog</h1>

    <?php if($msg): ?>
        <div class="alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form action="#" method="POST" enctype="multipart/form-data">

        <div class="form-columns">
            <div class="form-column">
                <div class="form-row">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                                <div class="form-row">
                    <label>Content</label>
                    <textarea name="content" rows="5" required></textarea>
                </div>

                <div class="form-row">
                    <label>Videos</label>
                    <input type="file" name="videos[]" multiple accept="video/*">
                </div>

                <div class="form-row">
                    <label>Images</label>
                    <input type="file" name="images[]" multiple accept="image/*">
                </div>
            </div>

            <div class="form-column">

            </div>
        </div>

        <br>
        <div class="button-row">
            <button type="button" class="back-btn" onclick="window.location.href='../public/seller-dashboard.php'">Back</button>
            <button type="submit" class="submit-btn">Submit Blog</button>
        </div>
    </form>
</div>

</body>
</html>
