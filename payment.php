<?php
session_start();
include "DataBase.php";
require('razorpay-php/Razorpay.php');  // Razorpay SDK
include "payment_config.php";

use Razorpay\Api\Api;

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

if (!isset($_GET['reservation_id'])) {
    die("⚠️ Reservation ID missing!");
}

$reservationId = intval($_GET['reservation_id']);
$userId = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'advance';

// Fetch reservation
$stmt = $con->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $reservationId, $userId);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    die("⚠️ Reservation not found.");
}

// Pricing
$pricePerSeat = 500;
$totalAmount = $reservation['party_size'] * $pricePerSeat;

if ($type == "advance") {
    $amountToPay = $totalAmount * 0.5;
    $label = "Advance Payment (50%)";
    $paymentType = "advance";
} else {
    $amountToPay = $totalAmount - ($reservation['advance_paid'] ? $totalAmount * 0.5 : 0);
    $label = "Remaining Payment (Final)";
    $paymentType = "final";
}

// Razorpay API
$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

// Create Razorpay Order
$order = $api->order->create([
    'receipt' => "order_rcptid_$reservationId",
    'amount' => $amountToPay * 100,
    'currency' => 'INR',
    'payment_capture' => 1
]);

// Save order in DB
$stmt = $con->prepare("INSERT INTO payments (reservation_id, user_id, amount, status, payment_type, razorpay_order_id) 
                       VALUES (?, ?, ?, 'pending', ?, ?)");
$razorpayOrderId = $order['id'];
$stmt->bind_param("iisss", $reservationId, $userId, $amountToPay, $paymentType, $razorpayOrderId);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $label ?> - Wok N Bowl</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 40px;
        }

        .card {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #e63946;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #e63946;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #c1121f;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>💳 <?= $label ?></h2>
        <p><b>Reservation ID:</b> <?= $reservation['id'] ?></p>
        <p><b>Name:</b> <?= htmlspecialchars($reservation['full_name']) ?></p>
        <p><b>Party Size:</b> <?= $reservation['party_size'] ?></p>
        <p><b>Total Amount:</b> ₹<?= $totalAmount ?></p>
        <p><b>To Pay Now:</b> ₹<?= $amountToPay ?></p>

        <button id="payBtn" class="btn">Pay Now</button>
    </div>

    <script>
        var options = {
            "key": "<?= RAZORPAY_KEY_ID ?>",
            "amount": "<?= $amountToPay * 100 ?>",
            "currency": "INR",
            "name": "Wok N Bowl",
            "description": "<?= $label ?>",
            "order_id": "<?= $order['id'] ?>",
            "handler": function (response) {
                window.location.href = "verify_payment.php?payment_id=" + response.razorpay_payment_id
                    + "&order_id=" + response.razorpay_order_id
                    + "&reservation_id=<?= $reservationId ?>"
                    + "&type=<?= $paymentType ?>";
            },
            "prefill": {
                "name": "<?= htmlspecialchars($reservation['full_name']) ?>",
                "email": "<?= htmlspecialchars($reservation['email']) ?>",
                "contact": "<?= htmlspecialchars($reservation['phone']) ?>"
            },
            "theme": { "color": "#e63946" }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('payBtn').onclick = function (e) {
            rzp1.open();
            e.preventDefault();
        }
    </script>
</body>

</html>