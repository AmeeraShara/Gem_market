<?php 
include __DIR__ . '/../config/db.php';

if (isset($_POST['register'])) {

    $name  = $_POST['name'];
    $email = $_POST['email'];
    $pwd   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$name', '$email', '$pwd', '$role')";

    if (!mysqli_query($conn, $sql)) {
        die("❌ User Insert Error: " . mysqli_error($conn));
    }

    $user_id = mysqli_insert_id($conn);

    if ($role === "seller") {

        $certificate_dir = "uploads/user_reg";
        $id_dir = "uploads/id";

        if (!is_dir($certificate_dir)) mkdir($certificate_dir, 0777, true);
        if (!is_dir($id_dir)) mkdir($id_dir, 0777, true);

        $license = $certificate_dir . "/" . $user_id . "_license_" . basename($_FILES['license']['name']);
        $id_proof = $id_dir . "/" . $user_id . "_id_" . basename($_FILES['id_proof']['name']);
        
        if (!move_uploaded_file($_FILES['license']['tmp_name'], $license)) {
            die("❌ Failed to upload NGJA license");
        }

        if (!move_uploaded_file($_FILES['id_proof']['tmp_name'], $id_proof)) {
            die("❌ Failed to upload ID proof");
        }

        $kyc_sql = "INSERT INTO seller_kyc (user_id, ngja_license, id_proof)
                    VALUES ('$user_id', '$license', '$id_proof')";

        if (!mysqli_query($conn, $kyc_sql)) {
            die("❌ KYC Insert Error: " . mysqli_error($conn));
        }
    }

echo "<p class='success-popup'>✔ Registration successful! Await admin approval if you are a seller.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - GemMarketplace</title>

<!-- Import your pink CSS -->
<link rel="stylesheet" href="css/register.css">

</head>

<body>

<div class="container">

    <!-- LEFT IMAGE PANEL -->
    <div class="left-panel">
    
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="right-panel">
        <div class="form-box">

            <h1>Register</h1>

            <form action="" method="POST" enctype="multipart/form-data">

                <input type="text" name="name" placeholder="Full Name" required>

                <input type="email" name="email" placeholder="Email" required>

                <input type="password" name="password" placeholder="Password" required>

                <select name="role" id="role" 
                        onchange="document.getElementById('seller-kyc').style.display = this.value === 'seller' ? 'block' : 'none';">
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                    <option value="admin">Admin</option>
                </select>

                <!-- Seller KYC -->
                <div id="seller-kyc" style="display:none; margin-top:10px;">
                    <label>NGJA License</label>
                    <input type="file" name="license">

                    <label>ID Proof</label>
                    <input type="file" name="id_proof">
                </div>

                <button type="submit" name="register">Register</button>

            </form>

            <p class="register-text">
                Already have an account?
                <a href="login.php">Login</a>
            </p>

        </div>
    </div>

</div>

</body>
</html>
