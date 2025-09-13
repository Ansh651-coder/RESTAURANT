<?php
session_start();
include "DataBase.php";
require('razorpay-php/Razorpay.php');  // manually downloaded SDK

use Razorpay\Api\Api;

// Fetch reservation
if (!isset($_GET['reservation_id'])) {
    die("Reservation not found.");
}

$reservationId = intval($_GET['reservation_id']);
$res = $con->query("SELECT * FROM reservations WHERE id=$reservationId")->fetch_assoc();
if (!$res) {
    die("Invalid reservation.");
}

// 50% advance amount (example: assume ₹2000 per reservation, so advance is 1000)
$totalAmount = 2000; // later you can calculate dynamically
$advanceAmount = $totalAmount / 2;

$keyId = "rzp_test_RGzL2916LDmPiD";      // your Test Key ID
$keySecret = "IluLGWZGirtnqBWK6zHMN0I6"; // your Test Key Secret

$api = new Api($keyId, $keySecret);

// Create Razorpay Order
$order = $api->order->create([
    'receipt' => "order_rcptid_$reservationId",
    'amount' => $advanceAmount * 100, // amount in paise
    'currency' => 'INR',
    'payment_capture' => 1 // auto capture
]);

// Save order in DB
$con->query("INSERT INTO payments (reservation_id, amount, status, razorpay_order_id) 
             VALUES ($reservationId, $advanceAmount, 'pending', '" . $order['id'] . "')");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pay for Reservation</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body>
    <h2>Pay 50% Advance for Reservation #<?= $reservationId ?></h2>
    <button id="payBtn">Pay Now</button>

    <script>
        var options = {
            "key": "<?= $keyId ?>",
            "amount": "<?= $advanceAmount * 100 ?>",
            "currency": "INR",
            "name": "Wok N Bowl",
            "description": "Table Reservation Advance",
            "order_id": "<?= $order['id'] ?>",
            "handler": function (response) {
                // Redirect to verify page
                window.location.href = "verify_payment.php?payment_id=" + response.razorpay_payment_id
                    + "&order_id=" + response.razorpay_order_id
                    + "&reservation_id=<?= $reservationId ?>";
            },
            "prefill": {
                "name": "<?= $res['full_name'] ?>",
                "email": "<?= $res['email'] ?>",
                "contact": "<?= $res['phone'] ?>"
            },
            "theme": {
                "color": "#e63946"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('payBtn').onclick = function (e) {
            rzp1.open();
            e.preventDefault();
        }
    </script>
</body>

</html>