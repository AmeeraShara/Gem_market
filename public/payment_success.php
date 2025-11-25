<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) die("Unauthorized");
if (!isset($_GET['order_id'])) die("Order ID missing");

$orderId = intval($_GET['order_id']);

// Fetch order + gem info
$stmt = $conn->prepare("
    SELECT o.*, g.title AS gem_title, g.carat, g.color, g.clarity, g.price
    FROM orders o
    JOIN gems g ON o.gem_id = g.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) die("Order not found");

// Include header
include __DIR__ . '/header.php';
?>

<h1>Payment Successful ✅</h1>
<h2>Order Details</h2>
<table>
    <tr><th>Order ID</th><td><?= $order['id'] ?></td></tr>
    <tr><th>Gem Name</th><td><?= htmlspecialchars($order['gem_title']) ?></td></tr>
    <tr><th>Carat</th><td><?= htmlspecialchars($order['carat']) ?> ct</td></tr>
    <tr><th>Color</th><td><?= htmlspecialchars($order['color']) ?></td></tr>
    <tr><th>Clarity</th><td><?= htmlspecialchars($order['clarity']) ?></td></tr>
    <tr><th>Price</th><td>Rs <?= number_format($order['price'], 2) ?></td></tr>
    <tr><th>Payment Method</th><td><?= htmlspecialchars($order['payment_method']) ?></td></tr>
    <tr><th>Status</th><td><?= htmlspecialchars($order['status']) ?></td></tr>
</table>
