<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, title, type, carat, color, clarity, origin, certificate, 
           price, is_negotiable, status
    FROM gems
    WHERE seller_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$gems = $stmt->get_result();
?>

<!-- NAVIGATION -->
<nav style="display:flex; justify-content:space-between; align-items:center; background:#F9A8D4; padding:10px 20px; border-radius:8px; margin-bottom:20px;">
    <div style="font-weight:bold; font-size:18px; color:#DB2777;">Seller Dashboard</div>
    <div>
        <a href="gem-listings.php" style="margin-right:15px; text-decoration:none; color:#DB2777; font-weight:bold;">Gem Listings</a>
        <a href="add-gem.php" style="margin-right:15px; text-decoration:none; color:#DB2777; font-weight:bold;">Add New Gem</a>
        <a href="../public/logout.php" style="text-decoration:none; color:red; font-weight:bold;">Logout</a>
    </div>
</nav>

<h2 style="font-size:18px; font-weight:bold; margin-bottom:12px;">Your Gem Listings</h2>

<table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
        <tr style="background-color:#f3f3f3;">
            <th style="border:1px solid #ccc; padding:8px;">Title</th>
            <th style="border:1px solid #ccc; padding:8px;">Type</th>
            <th style="border:1px solid #ccc; padding:8px;">Carat</th>
            <th style="border:1px solid #ccc; padding:8px;">Price</th>
            <th style="border:1px solid #ccc; padding:8px;">Status</th>
            <th style="border:1px solid #ccc; padding:8px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($g = $gems->fetch_assoc()): ?>
            <?php
            // Fetch gem images
            $stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id = ?");
            $stmtImg->bind_param("i", $g['id']);
            $stmtImg->execute();
            $images = $stmtImg->get_result();
            $imgArray = [];
            while ($img = $images->fetch_assoc()) {
                $imgArray[] = "uploads/gems/" . basename($img['image_path']);
            }

            $certPath = !empty($g['certificate']) ? "uploads/certificates/" . basename($g['certificate']) : '';
            ?>
            <tr style="border-bottom:1px solid #ccc;">
                <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($g['title']) ?></td>
                <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($g['type']) ?></td>
                <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($g['carat']) ?></td>
                <td style="border:1px solid #ccc; padding:6px;">Rs <?= number_format($g['price'], 2) ?></td>
                <td style="border:1px solid #ccc; padding:6px;">
                    <?php
                    if ($g['status'] === 'approved') echo '<span style="color:green; font-weight:bold;">Approved</span>';
                    elseif ($g['status'] === 'pending') echo '<span style="color:orange; font-weight:bold;">Pending</span>';
                    else echo '<span style="color:red; font-weight:bold;">Rejected</span>';
                    ?>
                </td>
                <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                    <button class="view-btn"
                        data-title="<?= htmlspecialchars($g['title']) ?>"
                        data-type="<?= htmlspecialchars($g['type']) ?>"
                        data-carat="<?= htmlspecialchars($g['carat']) ?>"
                        data-color="<?= htmlspecialchars($g['color']) ?>"
                        data-clarity="<?= htmlspecialchars($g['clarity']) ?>"
                        data-origin="<?= htmlspecialchars($g['origin']) ?>"
                        data-price="<?= number_format($g['price'], 2) ?>"
                        data-negotiable="<?= $g['is_negotiable'] ?>"
                        data-certificate="<?= $certPath ?>"
                        data-images='<?= json_encode($imgArray) ?>'>View</button>
                    <a href="../seller/edit-gem.php?id=<?= $g['id'] ?>" style="color:blue; text-decoration:underline; margin-left:5px;">Edit</a>
                    <a href="../seller/delete-gem.php?id=<?= $g['id'] ?>"
                        onclick="return confirm('Are you sure you want to delete this gem?');"
                        style="color:red; text-decoration:underline; margin-left:5px;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div id="msg" style="margin-top:10px; font-weight:bold;"></div>

<!-- Modal for gem details -->
<div id="gemModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); overflow:auto; justify-content:center; align-items:center; z-index:50; padding:20px;">
    <div style="background:#fff; border-radius:8px; max-width:700px; width:100%; padding:20px; position:relative;">
        <span id="modalClose" style="position:absolute; top:10px; right:15px; font-size:22px; cursor:pointer;">&times;</span>
        <h2 id="modalTitle"></h2>
        <p><strong>Type:</strong> <span id="modalType"></span></p>
        <p><strong>Carat:</strong> <span id="modalCarat"></span></p>
        <p><strong>Color:</strong> <span id="modalColor"></span></p>
        <p><strong>Clarity:</strong> <span id="modalClarity"></span></p>
        <p><strong>Origin:</strong> <span id="modalOrigin"></span></p>
        <p><strong>Price:</strong> Rs <span id="modalPrice"></span> <span id="modalNegotiable" style="color:green;"></span></p>
        <p><strong>Certificate:</strong> <span id="modalCertificate"></span></p>
        <p><strong>Images:</strong></p>
        <div id="modalImages" style="display:flex; flex-wrap:wrap; gap:5px;"></div>
    </div>
</div>

<!-- Modal for viewing larger images -->
<div id="imgModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); justify-content:center; align-items:center; z-index:60;" onclick="this.style.display='none'">
    <img id="modalImg" src="" style="max-width:90%; max-height:90%; border:5px solid #fff; border-radius:10px;">
</div>

<script>
    const gemModal = document.getElementById('gemModal');
    const modalClose = document.getElementById('modalClose');

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            gemModal.style.display = 'flex';
            document.getElementById('modalTitle').innerText = btn.dataset.title;
            document.getElementById('modalType').innerText = btn.dataset.type;
            document.getElementById('modalCarat').innerText = btn.dataset.carat;
            document.getElementById('modalColor').innerText = btn.dataset.color;
            document.getElementById('modalClarity').innerText = btn.dataset.clarity;
            document.getElementById('modalOrigin').innerText = btn.dataset.origin;
            document.getElementById('modalPrice').innerText = btn.dataset.price;
            document.getElementById('modalNegotiable').innerText = btn.dataset.negotiable == 1 ? '(Negotiable)' : '';

            // Certificate
            const certSpan = document.getElementById('modalCertificate');
            if (btn.dataset.certificate) {
                const ext = btn.dataset.certificate.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    certSpan.innerHTML = `<img src="${btn.dataset.certificate}" style="width:80px; height:80px; object-fit:cover; cursor:pointer;" onclick="openImgModal('${btn.dataset.certificate}')">`;
                } else {
                    certSpan.innerHTML = `<a href="${btn.dataset.certificate}" target="_blank">View PDF</a>`;
                }
            } else {
                certSpan.innerText = 'N/A';
            }

            // Images
            const imagesDiv = document.getElementById('modalImages');
            imagesDiv.innerHTML = '';
            const imgs = JSON.parse(btn.dataset.images);
            imgs.forEach(src => {
                const imgEl = document.createElement('img');
                imgEl.src = src;
                imgEl.style.width = '80px';
                imgEl.style.height = '80px';
                imgEl.style.objectFit = 'cover';
                imgEl.style.cursor = 'pointer';
                imgEl.style.border = '1px solid #ccc';
                imgEl.style.borderRadius = '4px';
                imgEl.style.margin = '2px';
                imgEl.onclick = () => openImgModal(src);
                imagesDiv.appendChild(imgEl);
            });
        });
    });

    modalClose.onclick = () => gemModal.style.display = 'none';
    window.onclick = (e) => {
        if (e.target == gemModal) gemModal.style.display = 'none';
    };

    // Function to open larger image modal
    function openImgModal(src) {
        const imgModal = document.getElementById('imgModal');
        document.getElementById('modalImg').src = src;
        imgModal.style.display = 'flex';
    }
</script>