<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../public/login.php");
    exit;
}

include "../config/db.php";

$seller_id = $_SESSION['user_id'];

// Fetch gems including deactivated ones
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
<link rel="stylesheet" href="../public/css/seller-gem-list.css">


<!-- NAVIGATION -->
<nav style="display:flex; justify-content:space-between; align-items:center; background:#F9A8D4; padding:10px 20px; border-radius:8px; margin-bottom:20px;">
    <div style="font-weight:bold; font-size:18px; color:#DB2777;">Seller Dashboard</div>
    <div>
        <a href="gem-listings.php" style="margin-right:15px; text-decoration:none; color:#DB2777; font-weight:bold;">Gem Listings</a>
        <a href="../seller/add-gem.php" style="margin-right:15px; text-decoration:none; color:#DB2777; font-weight:bold;">Add New Gem</a>
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
            // Fetch gem images (use full path from DB)
            $stmtImg = $conn->prepare("SELECT image_path FROM gem_images WHERE gem_id = ?");
            $stmtImg->bind_param("i", $g['id']);
            $stmtImg->execute();
            $images = $stmtImg->get_result();
            $imgArray = [];
            while ($img = $images->fetch_assoc()) {
                $imgArray[] = $img['image_path'];
            }
            $stmtImg->close();

            // Fetch gem videos
            $stmtVdo = $conn->prepare("SELECT video_path FROM gem_videos WHERE gem_id = ?");
            $stmtVdo->bind_param("i", $g['id']);
            $stmtVdo->execute();
            $videos = $stmtVdo->get_result();
            $vdoArray = [];
            while ($v = $videos->fetch_assoc()) {
                $vdoArray[] = $v['video_path'];
            }
            $stmtVdo->close();

            // Certificate path
            $certPath = !empty($g['certificate']) ? $g['certificate'] : '';
            ?>
            <tr style="border-bottom:1px solid #ccc; <?= $g['status'] === 'deactivated' ? 'opacity:0.5;' : '' ?>">
                <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($g['title']) ?></td>
                <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($g['type']) ?></td>
                <td style="border:1px solid #ccc; padding:6px;"><?= htmlspecialchars($g['carat']) ?></td>
                <td style="border:1px solid #ccc; padding:6px;">Rs <?= number_format($g['price'], 2) ?></td>
                <td style="border:1px solid #ccc; padding:6px;">
                    <?php
                    if ($g['status'] === 'approved') echo '<span style="color:green; font-weight:bold;">Approved</span>';
                    elseif ($g['status'] === 'pending') echo '<span style="color:orange; font-weight:bold;">Pending</span>';
                    elseif ($g['status'] === 'rejected') echo '<span style="color:red; font-weight:bold;">Rejected</span>';
                    else echo '<span style="color:red; font-weight:bold;">Deactivated</span>';
                    ?>
                </td>
                <td style="border:1px solid #ccc; padding:6px; text-align:center;">
                    <?php if ($g['status'] === 'deactivated'): ?>
                        <span style="color:red; font-weight:bold;">Deactivated</span>
                    <?php else: ?>
                        <button class="view-btn"
                            data-title="<?= htmlspecialchars($g['title']) ?>"
                            data-type="<?= htmlspecialchars($g['type']) ?>"
                            data-carat="<?= htmlspecialchars($g['carat']) ?>"
                            data-color="<?= htmlspecialchars($g['color']) ?>"
                            data-clarity="<?= htmlspecialchars($g['clarity']) ?>"
                            data-origin="<?= htmlspecialchars($g['origin']) ?>"
                            data-price="<?= number_format($g['price'], 2) ?>"
                            data-negotiable="<?= $g['is_negotiable'] ?>"
                            data-description="<?= htmlspecialchars($g['description']) ?>"
                            data-certificate="<?= htmlspecialchars($certPath) ?>"
                            data-images='<?= json_encode($imgArray) ?>'
                            data-videos='<?= json_encode($vdoArray) ?>'>View</button>

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

<div id="msg" style="margin-top:10px; font-weight:bold;"></div>

<!-- Modal for gem details -->
<div id="gemModal">
    <div class="modal-content">
        <span id="modalClose">&times;</span>

        <!-- Column 1 -->
        <div class="modal-column">
            <label>Title</label>
            <input type="text" id="modalTitle" readonly>

            <label>Type</label>
            <input type="text" id="modalType" readonly>

            <label>Price</label>
            <input type="text" id="modalPrice" readonly>

            <label>Certificate</label>
            <div id="modalCertificate"></div>

                        <label>Images</label>
            <div class="modal-images" id="modalImages"></div>

            <label>Videos</label>
            <div class="modal-videos" id="modalVideos"></div>
        </div>

        <!-- Column 2 -->
        <div class="modal-column">
            <label>Carat</label>
            <input type="text" id="modalCarat" readonly>

            <label>Color</label>
            <input type="text" id="modalColor" readonly>

            <label>Clarity</label>
            <input type="text" id="modalClarity" readonly>

            <label>Origin</label>
            <input type="text" id="modalOrigin" readonly>

            <label>Negotiable</label>
            <input type="text" id="modalNegotiable" readonly>

            <label>Description</label>
            <textarea id="modalDescription" rows="4" readonly></textarea>


        </div>
    </div>
</div>


<!-- Media Modal -->
<div id="mediaModal" onclick="this.style.display='none'">
    <video id="modalVideo" controls style="display:none;"></video>
    <img id="modalImg">
</div>


<script>
    const gemModal = document.getElementById('gemModal');
    const modalClose = document.getElementById('modalClose');

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const gemModal = document.getElementById('gemModal');
            gemModal.style.display = 'flex';

            // Fill inputs
            document.getElementById('modalTitle').value = btn.dataset.title;
            document.getElementById('modalType').value = btn.dataset.type;
            document.getElementById('modalCarat').value = btn.dataset.carat;
            document.getElementById('modalColor').value = btn.dataset.color;
            document.getElementById('modalClarity').value = btn.dataset.clarity;
            document.getElementById('modalOrigin').value = btn.dataset.origin;
            document.getElementById('modalPrice').value = btn.dataset.price;
            document.getElementById('modalNegotiable').value = btn.dataset.negotiable == 1 ? 'Yes' : 'No';
            document.getElementById('modalDescription').value = btn.dataset.description || 'N/A';

            // Certificate
            const certSpan = document.getElementById('modalCertificate');
            if (btn.dataset.certificate) {
                const ext = btn.dataset.certificate.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    certSpan.innerHTML = `<img src="${btn.dataset.certificate}" style="width:80px;height:80px;object-fit:cover;cursor:pointer;" onclick="openMediaModal('${btn.dataset.certificate}','image')">`;
                } else {
                    certSpan.innerHTML = `<a href="${btn.dataset.certificate}" target="_blank">View PDF</a>`;
                }
            } else certSpan.innerText = 'N/A';

            // Images
            const imagesDiv = document.getElementById('modalImages');
            imagesDiv.innerHTML = '';
            JSON.parse(btn.dataset.images).forEach(src => {
                const imgEl = document.createElement('img');
                imgEl.src = src;
                imgEl.onclick = () => openMediaModal(src, 'image');
                imagesDiv.appendChild(imgEl);
            });

            // Videos
            const videosDiv = document.getElementById('modalVideos');
            videosDiv.innerHTML = '';
            JSON.parse(btn.dataset.videos).forEach(src => {
                const videoEl = document.createElement('video');
                videoEl.src = src;
                videoEl.controls = true;
                videoEl.onclick = () => openMediaModal(src, 'video');
                videosDiv.appendChild(videoEl);
            });
        });
    });

    function openMediaModal(src, type) {
        const mediaModal = document.getElementById('mediaModal');
        const imgEl = document.getElementById('modalImg');
        const videoEl = document.getElementById('modalVideo');

        if (type === 'image') {
            imgEl.src = src;
            imgEl.style.display = 'block';
            videoEl.style.display = 'none';
        } else {
            videoEl.src = src;
            videoEl.style.display = 'block';
            imgEl.style.display = 'none';
        }
        mediaModal.style.display = 'flex';
    }

    document.getElementById('modalClose').onclick = () => document.getElementById('gemModal').style.display = 'none';


    modalClose.onclick = () => gemModal.style.display = 'none';
    window.onclick = (e) => {
        if (e.target == gemModal) gemModal.style.display = 'none';
    };

    function openMediaModal(src, type) {
        const mediaModal = document.getElementById('mediaModal');
        const imgEl = document.getElementById('modalImg');
        const videoEl = document.getElementById('modalVideo');

        if (type === 'image') {
            imgEl.src = src;
            imgEl.style.display = 'block';
            videoEl.style.display = 'none';
        } else {
            videoEl.src = src;
            videoEl.style.display = 'block';
            imgEl.style.display = 'none';
        }
        mediaModal.style.display = 'flex';
    }

    // AJAX soft delete
    document.querySelectorAll('.action-btn.delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const gemId = btn.dataset.id;
            if (!confirm('Are you sure you want to deactivate this gem?')) return;

            fetch(`../seller/delete-gem.php?id=${gemId}`)
                .then(res => res.text())
                .then(msg => {
                    alert(msg);

                    const row = btn.closest('tr');

                    // Disable all buttons/links
                    row.querySelectorAll('button, a').forEach(el => el.disabled = true);

                    // Replace Actions column
                    const actionCell = row.querySelector('td:last-child');
                    actionCell.innerHTML = '<span style="color:red; font-weight:bold;">Deactivated</span>';

                    // Gray out row
                    row.style.opacity = '0.5';
                })
                .catch(err => alert('AJAX error: ' + err));
        });
    });
</script>