<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) die("Unauthorized");

$userId = $_SESSION['user_id'];
$gemId = intval($_POST['gem_id']);

// Fetch gem
$stmt = $conn->prepare("SELECT * FROM gems WHERE id = ?");
$stmt->bind_param("i", $gemId);
$stmt->execute();
$gem = $stmt->get_result()->fetch_assoc();

if (!$gem) die("Gem not found");

// Insert order
$stmt = $conn->prepare("INSERT INTO orders (user_id, gem_id, payment_method, status) VALUES (?, ?, ?, ?)");
$status = "paid";
$paymentMethod = $_POST['payment_method'] ?? 'unknown';
$stmt->bind_param("iiss", $userId, $gemId, $paymentMethod, $status);
$stmt->execute();

// Get last inserted order ID
$orderId = $conn->insert_id;

// Redirect to success page
header("Location: payment_success.php?order_id=" . $orderId);
exit;
