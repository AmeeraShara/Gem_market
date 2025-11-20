<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$gem_id = isset($_POST['gem_id']) ? (int)$_POST['gem_id'] : 0;

if ($gem_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid gem ID']);
    exit;
}

// Check if already in wishlist
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id=? AND gem_id=?");
$stmt->bind_param("ii", $user_id, $gem_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Already in wishlist']);
    exit;
}
$stmt->close();

// Insert into wishlist
$stmt = $conn->prepare("INSERT INTO wishlist (user_id, gem_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $gem_id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Added to wishlist']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
