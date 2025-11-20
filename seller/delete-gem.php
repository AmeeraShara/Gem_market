<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    exit('Unauthorized');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid gem ID');
}

$gem_id = intval($_GET['id']);
$seller_id = $_SESSION['user_id'];

// Soft delete: mark as deactivated
$stmt = $conn->prepare("UPDATE gems SET status='deactivated' WHERE id=? AND seller_id=?");
$stmt->bind_param("ii", $gem_id, $seller_id);
$stmt->execute();
$stmt->close();

echo "Gem deactivated successfully";
