<?php
session_start();
include 'admin_header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include __DIR__ . '/../../config/db.php';

// Handle deactivate requests via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'] === 'deactivate' ? 'deactivated' : '';

    if($action){
        $stmt = $conn->prepare("UPDATE seller_kyc SET status='deactivated' WHERE id=?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            echo "✔ Seller has been DEACTIVATED successfully!";
        } else {
            echo "❌ Error updating status.";
        }
        $stmt->close();
        exit;
    }
}

// Fetch approved seller KYC
$stmt = $conn->prepare("
    SELECT k.id, u.full_name, u.email, k.ngja_license, k.id_proof
    FROM seller_kyc k
    JOIN users u ON k.user_id = u.id
    WHERE k.status='approved'
    ORDER BY k.id DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Approved Sellers - Admin Dashboard</title>
<link rel="stylesheet" href="../css/approved-sellers.css">
</head>
<body>

<div class="form-box">
    <h1 class="title">Approved Sellers</h1>
    <div id="msg" class="status-msg"></div>

    <table class="gem-table">
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
                        <img src="../<?= htmlspecialchars($row['ngja_license']) ?>" class="doc-img clickable-img" data-full="../<?= htmlspecialchars($row['ngja_license']) ?>">
                    <?php else: ?>N/A<?php endif; ?>
                </td>

                <td data-label="ID Proof">
                    <?php if(file_exists("../" . $row['id_proof'])): ?>
                        <img src="../<?= htmlspecialchars($row['id_proof']) ?>" class="doc-img clickable-img" data-full="../<?= htmlspecialchars($row['id_proof']) ?>">
                    <?php else: ?>N/A<?php endif; ?>
                </td>

                <td data-label="Actions">
                    <button class="reject-btn" onclick="updateSellerStatus(<?= $row['id'] ?>,'deactivate', this)">Deactivate</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Image Modal -->
<div id="imgModal" class="img-modal">
  <span class="img-close">&times;</span>
  <img class="img-modal-content" id="modalImage" alt="Expanded Image">
</div>

<script>
function updateSellerStatus(id, action, btn){
    if(!confirm(`Are you sure you want to ${action} this seller?`)) return;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function(){
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;
            btn.parentElement.innerHTML = '<span class="status-rejected">DEACTIVATED</span>';
        }
    };
    xhr.send("id=" + id + "&action=" + action);
}

// Image modal logic
document.querySelectorAll('.clickable-img').forEach(img => {
    img.addEventListener('click', () => {
        const modal = document.getElementById('imgModal');
        const modalImg = document.getElementById('modalImage');
        modal.style.display = "block";
        modalImg.src = img.getAttribute('data-full');
    });
});

document.querySelector('.img-close').addEventListener('click', () => {
    document.getElementById('imgModal').style.display = "none";
});

document.getElementById('imgModal').addEventListener('click', (e) => {
    if(e.target === e.currentTarget){
        e.currentTarget.style.display = "none";
    }
});
</script>

</body>
</html>

<?php $stmt->close(); ?>
