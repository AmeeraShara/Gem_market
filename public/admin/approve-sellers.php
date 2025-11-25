<?php
session_start();
include 'admin_header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include __DIR__ . '/../../config/db.php';

// Handle approve/reject requests via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE seller_kyc SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "✔ Seller has been " . strtoupper($status) . " successfully!";
    } else {
        echo "❌ Error updating status.";
    }
    $stmt->close();
    exit;
}

// Fetch pending seller KYC requests
$stmt = $conn->prepare("
    SELECT k.id, u.full_name, u.email, k.ngja_license, k.id_proof
    FROM seller_kyc k
    JOIN users u ON k.user_id = u.id
    WHERE k.status='pending'
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Approve Sellers - Admin Dashboard</title>
<link rel="stylesheet" href="../css/admin-seller.css" />
<style>
/* Table and button styles */
.status-approved { color: #10B981; font-weight: bold; }
.status-rejected { color: #EF4444; font-weight: bold; }
.doc-img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
.approve-btn, .reject-btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin: 2px; }
.approve-btn { background-color: #10B981; color: #fff; }
.reject-btn { background-color: #EF4444; color: #fff; }
.approve-btn:hover { background-color: #0f9d6f; }
.reject-btn:hover { background-color: #d63a3a; }
.status-msg { margin-top: 15px; font-weight: bold; text-align: center; color: #444; }
</style>
</head>
<body>

<div class="form-box">
    <h1 class="title">Approve Sellers</h1>

    <table class="blog-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>NGJA License</th>
                <th>ID Proof</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr id="sellerRow_<?= $row['id'] ?>">
                <td data-label="Name"><?= htmlspecialchars($row['full_name']) ?></td>
                <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                <td data-label="NGJA License">
                    <?php if(file_exists("../" . $row['ngja_license'])): ?>
                        <img src="../<?= htmlspecialchars($row['ngja_license']) ?>" class="doc-img">
                    <?php else: ?>Not uploaded<?php endif; ?>
                </td>
                <td data-label="ID Proof">
                    <?php if(file_exists("../" . $row['id_proof'])): ?>
                        <img src="../<?= htmlspecialchars($row['id_proof']) ?>" class="doc-img">
                    <?php else: ?>Not uploaded<?php endif; ?>
                </td>
                <td data-label="Actions">
                    <button class="approve-btn" onclick="updateSellerStatus(<?= $row['id'] ?>,'approved', this)">Approve</button>
                    <button class="reject-btn" onclick="updateSellerStatus(<?= $row['id'] ?>,'rejected', this)">Reject</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<script>
function updateSellerStatus(id, status, btn){
    if(!confirm(`Are you sure you want to mark this seller as ${status}?`)) return;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true); // Send request to same page
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;

            btn.parentElement.innerHTML =
                '<span class="' + (status==='approved'?'status-approved':'status-rejected') + '">' +
                status.toUpperCase() + '</span>';
        }
    };

    xhr.send("id=" + id + "&status=" + status);
}
</script>

</body>
</html>

<?php $stmt->close(); ?>
