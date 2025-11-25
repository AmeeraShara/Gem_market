<?php
session_start();
include __DIR__ . '/../../config/db.php'; 
include 'admin_header.php';

// Only admin access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
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
    exit;
}

// Fetch pending gems with seller info
$stmt = $conn->prepare("
    SELECT g.*, u.full_name AS seller_name 
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.status='pending'
    ORDER BY g.id DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pending Gems - Admin Dashboard</title>
<link rel="stylesheet" href="../css/admin-gems.css" />
<style>
/* Modal styles */
.img-modal {
  display: none;
  position: fixed;
  z-index: 9999;
  padding-top: 60px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.8);
}

.img-modal-content {
  margin: auto;
  display: block;
  max-width: 90%;
  max-height: 80vh;
  border-radius: 10px;
  box-shadow: 0 0 10px #fff;
}

.img-close {
  position: absolute;
  top: 20px;
  right: 35px;
  color: #fff;
  font-size: 40px;
  font-weight: bold;
  cursor: pointer;
  user-select: none;
}

.img-close:hover {
  color: #bbb;
}
</style>
</head>
<body>

<div class="form-box">

    <h1 class="title">Pending Gem Listings</h1>

    <table class="gem-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Carat</th>
                <th>Color</th>
                <th>Clarity</th>
                <th>Origin</th>
                <th>Certificate</th>
                <th>Images</th>
                <th>Price</th>
                <th>Seller</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr id="gemRow_<?= $row['id'] ?>">
                <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                <td data-label="Type"><?= htmlspecialchars($row['type']) ?></td>
                <td data-label="Carat"><?= htmlspecialchars($row['carat']) ?></td>
                <td data-label="Color"><?= htmlspecialchars($row['color']) ?></td>
                <td data-label="Clarity"><?= htmlspecialchars($row['clarity']) ?></td>
                <td data-label="Origin"><?= htmlspecialchars($row['origin']) ?></td>

                <!-- Certificate -->
                <td data-label="Certificate">
                    <?php if(!empty($row['certificate']) && file_exists('../' . $row['certificate'])): ?>
                        <a href="../<?= htmlspecialchars($row['certificate']) ?>" target="_blank">View PDF</a>
                    <?php else: ?>N/A<?php endif; ?>
                </td>

                <!-- Gem Images -->
                <td data-label="Images">
                    <?php
                    $stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id=?");
                    $stmtImg->bind_param("i", $row['id']);
                    $stmtImg->execute();
                    $images = $stmtImg->get_result();
                    while($img = $images->fetch_assoc()):
                        $imgUrl = '../' . $img['image_path'];
                        if(file_exists($imgUrl)):
                    ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>" class="doc-img clickable-img" alt="Gem Image" data-full="<?= htmlspecialchars($imgUrl) ?>">
                    <?php
                        endif;
                    endwhile;
                    $stmtImg->close();
                    ?>
                </td>

                <td data-label="Price">
                    Rs <?= number_format($row['price'],2) ?>
                    <?php if($row['is_negotiable']): ?>
                        <span style="color:green; font-size:12px;">(Negotiable)</span>
                    <?php endif; ?>
                </td>

                <td data-label="Seller"><?= htmlspecialchars($row['seller_name']) ?></td>

                <td data-label="Actions">
                    <button class="approve-btn" onclick="updateGemStatus(<?= $row['id'] ?>,'approved', this)">Approve</button>
                    <button class="reject-btn" onclick="updateGemStatus(<?= $row['id'] ?>,'rejected', this)">Reject</button>
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
function updateGemStatus(id, status, btn){
    if(!confirm(`Are you sure you want to mark this gem as ${status}?`)) return;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function(){
        if(xhr.readyState === 4 && xhr.status === 200){
            document.getElementById('msg').innerHTML = xhr.responseText;
            btn.parentElement.innerHTML = '<span class="' + (status==='approved'?'status-approved':'status-rejected') + '">' + status.toUpperCase() + '</span>';
        }
    };
    xhr.send("id=" + id + "&status=" + status);
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

// Close modal on clicking outside the image
document.getElementById('imgModal').addEventListener('click', (e) => {
    if(e.target === e.currentTarget){
        e.currentTarget.style.display = "none";
    }
});
</script>

</body>
</html>

<?php $stmt->close(); ?>
