<?php
include __DIR__ . '/../../config/db.php'; 
session_start();

// Only allow admin
if($_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

// Handle AJAX approve/reject
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])){
    $id = (int)$_POST['id'];
    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE gems SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);

    if($stmt->execute()){
        echo "✔ Gem has been " . strtoupper($status) . " successfully!";
    } else {
        echo "❌ Error updating status.";
    }
    $stmt->close();
    exit; // stop further execution for AJAX
}

// Fetch pending gems with seller info
$pending_gems = $conn->query("
    SELECT g.*, u.full_name AS seller_name 
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.status='pending'
    ORDER BY g.id DESC
");
?>

<h2 style="font-size:18px; font-weight:bold; margin-bottom:12px;">Pending Gem Listings</h2>

<table style="width:100%; border-collapse:collapse; font-size:14px;">
<thead>
<tr style="background-color:#f3f3f3;">
    <th style="border:1px solid #ccc; padding:8px;">Title</th>
    <th style="border:1px solid #ccc; padding:8px;">Type</th>
    <th style="border:1px solid #ccc; padding:8px;">Carat</th>
    <th style="border:1px solid #ccc; padding:8px;">Color</th>
    <th style="border:1px solid #ccc; padding:8px;">Clarity</th>
    <th style="border:1px solid #ccc; padding:8px;">Origin</th>
    <th style="border:1px solid #ccc; padding:8px;">Certificate</th>
    <th style="border:1px solid #ccc; padding:8px;">Images</th>
    <th style="border:1px solid #ccc; padding:8px;">Price</th>
    <th style="border:1px solid #ccc; padding:8px;">Seller</th>
    <th style="border:1px solid #ccc; padding:8px;">Actions</th>
</tr>
</thead>
<tbody>
<?php while($g = $pending_gems->fetch_assoc()): ?>
<tr id="gemRow_<?php echo $g['id']; ?>" style="border-bottom:1px solid #ccc;">
    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['title']); ?></td>
    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['type']); ?></td>
    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['carat']); ?></td>
    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['color']); ?></td>
    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['clarity']); ?></td>
    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['origin']); ?></td>

    <!-- Certificate -->
    <td style="border:1px solid #ccc; padding:6px; text-align:center;">
        <?php if(!empty($g['certificate']) && file_exists('../' . $g['certificate'])): ?>
            <a href="<?php echo '../' . htmlspecialchars($g['certificate']); ?>" target="_blank">View PDF</a>
        <?php else: ?>
            N/A
        <?php endif; ?>
    </td>

    <!-- Gem Images -->
    <td style="border:1px solid #ccc; padding:6px; text-align:center;">
        <?php
        $stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id=?");
        $stmtImg->bind_param("i", $g['id']);
        $stmtImg->execute();
        $images = $stmtImg->get_result();
        while($img = $images->fetch_assoc()):
            $imgUrl = '../' . $img['image_path'];
            if(file_exists($imgUrl)):
        ?>
            <img src="<?php echo htmlspecialchars($imgUrl); ?>" 
                 style="width:40px; height:40px; object-fit:cover; border:1px solid #ccc; margin:1px; border-radius:4px;">
        <?php 
            endif;
        endwhile; 
        $stmtImg->close();
        ?>
    </td>

    <td style="border:1px solid #ccc; padding:6px;">
        Rs <?php echo number_format($g['price'],2); ?><br>
        <?php if($g['is_negotiable']): ?><span style="color:green; font-size:12px;">(Negotiable)</span><?php endif; ?>
    </td>

    <td style="border:1px solid #ccc; padding:6px;"><?php echo htmlspecialchars($g['seller_name']); ?></td>

    <td style="border:1px solid #ccc; padding:6px; text-align:center;">
        <button onclick="updateGemStatus(<?php echo $g['id']; ?>,'approved', this)" 
                style="color:green; text-decoration:underline; margin-right:8px; background:none; border:none; cursor:pointer;">
            Approve
        </button>
        <button onclick="updateGemStatus(<?php echo $g['id']; ?>,'rejected', this)" 
                style="color:red; text-decoration:underline; background:none; border:none; cursor:pointer;">
            Reject
        </button>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<div id="msg" style="margin-top:10px; font-weight:bold;"></div>

<script>
function updateGemStatus(id, status, btn){
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true); // same page
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;

            // Replace buttons with status text
            btn.parentElement.innerHTML = '<span style="color:' + (status==='approved'?'green':'red') + '; font-weight:bold;">' + status.toUpperCase() + '</span>';
        }
    };
    xhr.send("id=" + id + "&status=" + status);
}
</script>
