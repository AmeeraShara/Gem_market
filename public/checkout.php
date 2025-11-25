<?php
session_start();
require __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check Gem ID
if (!isset($_POST['gem_id']) || empty($_POST['gem_id'])) {
    die("❌ Gem ID not received");
}

$gemId = intval($_POST['gem_id']);

// Fetch gem safely
$sql = "SELECT * FROM gems WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $gemId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Gem not found or not approved.");
}

$gem = $result->fetch_assoc();
include __DIR__ . '/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= htmlspecialchars($gem['title']) ?></title>
    <link rel="stylesheet" href="../public/css/checkout.css">
</head>
<body>

<div class="checkout-container">
    <h1 class="title">Checkout</h1>

    <div class="grid">

        <!-- GEM DETAILS -->
        <div class="box">
            <h2>Gem Details</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($gem['title']) ?></p>
            <p><strong>Carat:</strong> <?= htmlspecialchars($gem['carat']) ?> ct</p>
            <p><strong>Color:</strong> <?= htmlspecialchars($gem['color']) ?></p>
            <p><strong>Clarity:</strong> <?= htmlspecialchars($gem['clarity']) ?></p>
            <p><strong>Price:</strong> <b>Rs <?= number_format($gem['price'], 2) ?></b></p>
        </div>

        <!-- PAYMENT FORM -->
        <div class="box">
            <h2>Payment</h2>

            <form action="process_payment.php" method="POST">
                <input type="hidden" name="gem_id" value="<?= $gem['id'] ?>">

                <label>Select Payment Method</label>
                <select name="payment_method" id="payment_method" onchange="togglePayment()" required>
                    <option value="">-- Choose Payment Method --</option>
                    <option value="card">Credit / Debit Card</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="cod">Cash on Delivery</option>
                </select>

                <!-- CARD PAYMENT -->
                <div class="payment-section" id="cardSection">
                    <label>Card Holder Name</label>
                    <input type="text" name="card_name" placeholder="John Doe">

                    <label>Card Number</label>
                    <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">

                    <label>Expiry Date</label>
                    <input type="month" name="expiry">

                    <label>CVV</label>
                    <input type="password" name="cvv" maxlength="4">
                </div>

                <!-- BANK TRANSFER -->
                <div class="payment-section" id="bankSection">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" placeholder="Commercial Bank">

                    <label>Transaction ID</label>
                    <input type="text" name="transaction_id">
                </div>

                <!-- CASH ON DELIVERY -->
                <div class="payment-section" id="codSection">
                    <p>You will pay when the gem arrives at your shipping address.</p>
                </div>

                <button type="submit" class="submit-btn">Confirm Order</button>
            </form>
        </div>

    </div>
</div>

<script>
function togglePayment() {
    let method = document.getElementById("payment_method").value;

    document.getElementById("cardSection").style.display = "none";
    document.getElementById("bankSection").style.display = "none";
    document.getElementById("codSection").style.display = "none";

    if(method === "card") document.getElementById("cardSection").style.display = "block";
    if(method === "bank") document.getElementById("bankSection").style.display = "block";
    if(method === "cod") document.getElementById("codSection").style.display = "block";
}
</script>

</body>
</html>
