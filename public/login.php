<?php
include __DIR__ . '/../config/db.php';
session_start();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']   = $user['role'];
            $_SESSION['name']   = $user['name'];

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php"); exit;
            } elseif ($user['role'] === 'seller') {
                header("Location: seller-dashboard.php"); exit;
            } else {
                header("Location: index.php"); exit;
            }
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - GemMarketplace</title>
<link rel="stylesheet" href="css/login.css">
</head>

<body>

<div class="container">

    <!-- LEFT PANEL (IMAGE) -->
    <div class="left-panel">

    </div>

    <!-- RIGHT PANEL (FORM) -->
    <div class="right-panel">
        <div class="form-box">

            <h1>Login</h1>

            <?php if(isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>

            <p class="register-text">
                Don't have an account?
                <a href="register.php">Register</a>
            </p>

        </div>
    </div>

</div>

</body>
</html>

