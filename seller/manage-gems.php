<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

$seller_id = $_SESSION['user_id'];

// Fetch gems
$stmt = $conn->prepare("
    SELECT id, title, type, carat, color, clarity, origin, certificate, description,
           price, is_negotiable, status
    FROM gems
    WHERE seller_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$gems = $stmt->get_result();
?>

<?php include "../seller/seller_header.php"; ?>
<link rel="stylesheet" href="../public/css/seller-gem-list.css">

<h2>Your Gem Listings</h2>

<table>
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

            <td><?= htmlspecialchars($g['title']) ?></td>
            <td><?= htmlspecialchars($g['type']) ?></td>
            <td><?= htmlspecialchars($g['carat']) ?></td>
            <td>Rs <?= number_format($g['price'], 2) ?></td>

            <td>
                <?php
                if ($g['status'] === 'approved') echo '<span class="status-approved">Approved</span>';
                elseif ($g['status'] === 'pending') echo '<span class="status-pending">Pending</span>';
                elseif ($g['status'] === 'rejected') echo '<span class="status-rejected">Rejected</span>';
                else echo '<span class="status-rejected">Deactivated</span>';
                ?>
            </td>

            <td>
                <?php if ($g['status'] === 'deactivated'): ?>

                    <span class="status-rejected">Deactivated</span>

                <?php else: ?>

                    <!-- KEEP SAME VIEW BUTTON -->
                    <button class="view-btn"
                        onclick="window.location.href='../seller/view-gem.php?id=<?= $g['id'] ?>'"
                        style="padding:6px 12px; background:#F9A8D4; color:#DB2777; border:none; border-radius:4px; cursor:pointer;">
                        View
                    </button>

                    <button class="action-btn edit"
                        onclick="window.location.href='../seller/edit-gem.php?id=<?= $g['id'] ?>'">
                        Edit
                    </button>

                    <button class="action-btn delete" data-id="<?= $g['id'] ?>">Delete</button>

                <?php endif; ?>
            </td>

        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<script>
// AJAX Delete (Deactivate)
document.querySelectorAll('.action-btn.delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;

        if (!confirm("Are you sure you want to deactivate this gem?")) return;

        fetch(`../seller/delete-gem.php?id=${id}`)
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload(); // refresh page
        });
    });
});
</script>
