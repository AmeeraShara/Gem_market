<?php
include __DIR__ . '/../../config/db.php'; // adjust path if needed
session_start();
include 'admin_header.php';
// Only allow admin access
if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

// Handle AJAX approve/reject
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])){
    $id = (int)$_POST['id'];
    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE seller_kyc SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);

    if($stmt->execute()){
        echo "✔ Seller has been " . strtoupper($status) . " successfully!";
    } else {
        echo "❌ Error updating status.";
    }
    $stmt->close();
    exit; // Important: stop further execution for AJAX
}

// Fetch pending sellers
$stmt = $conn->prepare("
    SELECT k.id, u.full_name, u.email, k.ngja_license, k.id_proof
    FROM seller_kyc k
    JOIN users u ON k.user_id = u.id
    WHERE k.status='pending'
");
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 style="font-size:18px; font-weight:bold; margin-bottom:12px;">Pending Sellers</h2>

<table id="sellersTable" style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
        <tr style="background-color:#f3f3f3;">
            <th style="border:1px solid #ccc; padding:8px;">Name</th>
            <th style="border:1px solid #ccc; padding:8px;">Email</th>
            <th style="border:1px solid #ccc; padding:8px;">NGJA License</th>
            <th style="border:1px solid #ccc; padding:8px;">ID Proof</th>
            <th style="border:1px solid #ccc; padding:8px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr id="sellerRow_<?php echo $row['id']; ?>">
            <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($row['email']); ?></td>

            <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                <?php if(file_exists('../' . $row['ngja_license'])): ?>
                    <a href="<?php echo '../' . htmlspecialchars($row['ngja_license']); ?>" target="_blank">
                        <img src="<?php echo '../' . htmlspecialchars($row['ngja_license']); ?>" 
                             alt="NGJA License" 
                             style="width:50px; height:50px; object-fit:cover; border:1px solid #ccc; border-radius:4px;">
                    </a>
                <?php else: ?>
                    Not uploaded
                <?php endif; ?>
            </td>

            <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                <?php if(file_exists('../' . $row['id_proof'])): ?>
                    <a href="<?php echo '../' . htmlspecialchars($row['id_proof']); ?>" target="_blank">
                        <img src="<?php echo '../' . htmlspecialchars($row['id_proof']); ?>" 
                             alt="ID Proof" 
                             style="width:50px; height:50px; object-fit:cover; border:1px solid #ccc; border-radius:4px;">
                    </a>
                <?php else: ?>
                    Not uploaded
                <?php endif; ?>
            </td>

            <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                <button onclick="updateSellerStatus(<?php echo $row['id']; ?>,'approved', this)" 
                        style="color:green; text-decoration:underline; margin-right:8px; background:none; border:none; cursor:pointer;">
                    Approve
                </button>
                <button onclick="updateSellerStatus(<?php echo $row['id']; ?>,'rejected', this)" 
                        style="color:red; text-decoration:underline; background:none; border:none; cursor:pointer;">
                    Reject
                </button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<div id="msg" style="margin-top:10px; font-weight:bold;"></div>

<script>
function updateSellerStatus(id, status, btn){
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true); // same page
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;
            
            // Replace buttons with status text
            btn.parentElement.innerHTML = '<span style="color:' + (status==='approved'?'green':'red') + '; font-weight:bold;">' + status.toUpperCase() + ' </span>';
        }
    };
    xhr.send("id=" + id + "&status=" + status);
}
</script>

<?php $stmt->close(); ?>
