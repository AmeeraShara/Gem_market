<?php 
include __DIR__ . '/../config/db.php';

if (isset($_POST['register'])) {

    // Sanitize inputs
    $full_name = mysqli_real_escape_string($conn, $_POST['name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $phone     = !empty($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : null;
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role      = $_POST['role'];
    $language  = $_POST['language'] ?? 'en';
    $is_verified = 0;

    // Profile image handling
    $profile_image = NULL;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $profile_dir = "uploads/profile_images";
        if (!is_dir($profile_dir)) mkdir($profile_dir, 0777, true);

        $profile_image = $profile_dir . "/" . time() . "_" . basename($_FILES['profile_image']['name']);
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_image)) {
            die("❌ Failed to upload profile image");
        }
    }

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, phone, password, role, language_preference, is_verified, profile_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssssis", $full_name, $email, $phone, $password, $role, $language, $is_verified, $profile_image);

    if (!$stmt->execute()) {
        die("❌ User Insert Error: " . $stmt->error);
    }

    $user_id = $stmt->insert_id;
    $stmt->close();

    // Seller KYC
    if ($role === "seller") {

        $certificate_dir = "uploads/user_reg";
        $id_dir = "uploads/id";

        if (!is_dir($certificate_dir)) mkdir($certificate_dir, 0777, true);
        if (!is_dir($id_dir)) mkdir($id_dir, 0777, true);

        $license = $certificate_dir . "/" . $user_id . "_license_" . basename($_FILES['license']['name']);
        $id_proof = $id_dir . "/" . $user_id . "_id_" . basename($_FILES['id_proof']['name']);

        move_uploaded_file($_FILES['license']['tmp_name'], $license);
        move_uploaded_file($_FILES['id_proof']['tmp_name'], $id_proof);

        $kyc_stmt = $conn->prepare("INSERT INTO seller_kyc (user_id, ngja_license, id_proof) VALUES (?, ?, ?)");
        $kyc_stmt->bind_param("iss", $user_id, $license, $id_proof);
        $kyc_stmt->execute();
        $kyc_stmt->close();
    }

    echo "<p class='success-popup'>✔ Registration successful! </p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - GemMarketplace</title>
<link rel="stylesheet" href="css/register.css">

</head>

<body>
<div class="container">

    <div class="left-panel"></div>

    <div class="right-panel">
        <div class="form-box">
            <h1>Register</h1>

            <form action="" method="POST" enctype="multipart/form-data">

                <!-- Row 1 -->
                <div class="row">
                    <div class="col">
                        <input type="text" name="name" placeholder="Full Name" required>
                    </div>
                    <div class="col">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row">
                    <div class="col">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="col">
                        <input type="text" name="phone" placeholder="Phone (optional)">
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="row">
                    <div class="col">
                        <label>Language Preference</label>
                        <select name="language">
                            <option value="en">English</option>
                            <option value="si">Sinhala</option>
                            <option value="ta">Tamil</option>
                        </select>
                    </div>

                    <div class="col">
                        <label>Role</label>
                        <select name="role" 
                            onchange="document.getElementById('seller-kyc').style.display = this.value === 'seller' ? 'block' : 'none';">
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <!-- Profile Image Full Row -->
                <div class="row">
                    <div class="col-full">
                        <label>Profile Image (optional)</label>
                        <input type="file" name="profile_image">
                    </div>
                </div>

                <!-- Seller KYC Block -->
                <div id="seller-kyc" style="display:none; margin-top:10px;">

                    <div class="row">
                        <div class="col">
                            <label>NGJA License</label>
                            <input type="file" name="license">
                        </div>

                        <div class="col">
                            <label>ID Proof</label>
                            <input type="file" name="id_proof">
                        </div>
                    </div>

                </div>

                <button type="submit" name="register">Register</button>

            </form>

            <p class="register-text">
                Already have an account? <a href="login.php">Login</a>
            </p>

        </div>
    </div>

</div>
</body>
</html>
