<?php
session_start();
include __DIR__ . '/../../config/db.php';
include 'admin_header.php';

// Only admin access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}

// Handle AJAX deactivation
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])){
    $id = (int)$_POST['id'];
    $action = $_POST['action'] === 'deactivate' ? 'deactivated' : '';

    if($action){
        $stmt = $conn->prepare("UPDATE gems SET status='deactivated' WHERE id=?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            echo "✔ Gem has been DEACTIVATED successfully!";
        } else {
            echo "❌ Error updating status.";
        }
        $stmt->close();
        exit;
    }
}

// Pagination settings
$limit = 10; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total approved gems
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM gems WHERE status='approved'");
$totalRow = $totalResult->fetch_assoc();
$totalGems = $totalRow['total'];
$totalPages = ceil($totalGems / $limit);

// Fetch approved gems with seller info for current page
$stmt = $conn->prepare("
    SELECT g.*, u.full_name AS seller_name
    FROM gems g
    JOIN users u ON g.seller_id = u.id
    WHERE g.status='approved'
    ORDER BY g.id DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Approved Gems - Admin Dashboard</title>
<link rel="stylesheet" href="../css/approved-gems.css">
</head>
<body>

<div class="form-box">
    <h1 class="title">Approved Gem Listings</h1>
    <div id="msg" class="status-msg"></div>

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
                    <button class="reject-btn" onclick="updateGemStatus(<?= $row['id'] ?>,'deactivate', this)">Deactivate</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>


</div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if($totalPages > 1): ?>
            <?php for($i=1; $i<=$totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i==$page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>

<!-- Image Modal -->
<div id="imgModal" class="img-modal">
  <span class="img-close">&times;</span>
  <img class="img-modal-content" id="modalImage" alt="Expanded Image">
</div>

<script>
function updateGemStatus(id, action, btn){
    if(!confirm(`Are you sure you want to ${action} this gem?`)) return;

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
