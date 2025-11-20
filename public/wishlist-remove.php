<?php
session_start();
include __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please login to remove from wishlist']);
    exit;
}

$userId = intval($_SESSION['user_id']);
$gemId = intval($_POST['gem_id'] ?? 0);

if ($gemId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid gem ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND gem_id = ?");
$stmt->bind_param("ii", $userId, $gemId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Removed from wishlist']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
$stmt->close();
$conn->close();
?>
