<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, title, type, carat, price, status
    FROM gems
    WHERE seller_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$gems = $stmt->get_result();

include "../seller/seller_header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Gems - Dashboard</title>
<link rel="stylesheet" href="../public/css/seller-gems.css">
</head>
<body>

<div class="form-box">
    <button 
        class="close-btn"
        onclick="window.location.href='../public/seller-dashboard.php'"
        title="Close"
    >&times;</button>

    <h1 class="title">Your Gem Listings</h1>

    <table class="gem-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Carat</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($g = $gems->fetch_assoc()): ?>
            <tr class="<?= $g['status'] === 'deactivated' ? 'deactivated' : '' ?>">
                <td data-label="Title"><?= htmlspecialchars($g['title']) ?></td>
                <td data-label="Type"><?= htmlspecialchars($g['type']) ?></td>
                <td data-label="Carat"><?= htmlspecialchars($g['carat']) ?></td>
                <td data-label="Price">Rs <?= number_format($g['price'], 2) ?></td>
                <td data-label="Status">
                    <?php if ($g['status'] === 'approved'): ?>
                        <span class="status-approved">Approved</span>
                    <?php elseif ($g['status'] === 'pending'): ?>
                        <span class="status-pending">Pending</span>
                    <?php elseif ($g['status'] === 'rejected'): ?>
                        <span class="status-rejected">Rejected</span>
                    <?php else: ?>
                        <span class="status-rejected">Deactivated</span>
                    <?php endif; ?>
                </td>
                <td data-label="Actions">
                    <?php if ($g['status'] !== 'deactivated'): ?>
                        <button class="view-btn" onclick="window.location.href='../seller/view-gem.php?id=<?= $g['id'] ?>'">View</button>
                        <button class="action-btn edit" onclick="window.location.href='../seller/edit-gem.php?id=<?= $g['id'] ?>'">Edit</button>
                        <button class="action-btn delete" data-id="<?= $g['id'] ?>">Delete</button>
                    <?php else: ?>
                        <span class="status-rejected">Deactivated</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.action-btn.delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (!confirm("Are you sure you want to deactivate this gem?")) return;

        fetch(`../seller/delete-gem.php?id=${id}`)
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload();
        });
    });
});
</script>

</body>
</html>
