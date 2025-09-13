<?php
session_start();
include "DataBase.php";
require('razorpay-php/Razorpay.php');
include "payment_config.php";

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

if (!isset($_GET['payment_id'], $_GET['order_id'], $_GET['reservation_id'], $_GET['type'])) {
    die("⚠️ Invalid payment request.");
}

$paymentId = $_GET['payment_id'];
$orderId = $_GET['order_id'];
$reservationId = intval($_GET['reservation_id']);
$paymentType = $_GET['type'];

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

try {
    // Fetch payment from Razorpay
    $payment = $api->payment->fetch($paymentId);

    // Verify signature
    $attributes = [
        'razorpay_order_id' => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $_GET['razorpay_signature'] ?? ''
    ];
    $api->utility->verifyPaymentSignature($attributes);

    // Update payment record in DB
    $stmt = $con->prepare("UPDATE payments SET status='success', razorpay_payment_id=? 
                           WHERE razorpay_order_id=? AND reservation_id=?");
    $stmt->bind_param("ssi", $paymentId, $orderId, $reservationId);
    $stmt->execute();

    // Update reservation payment flags
    if ($paymentType === "advance") {
        $stmt = $con->prepare("UPDATE reservations SET advance_paid=1 WHERE id=?");
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();
        // Mark table as occupied automatically
        $stmt2 = $con->prepare("SELECT table_id FROM reservations WHERE id=?");
        $stmt2->bind_param("i", $reservationId);
        $stmt2->execute();
        $stmt2->bind_result($tableId);
        $stmt2->fetch();
        $stmt2->close();

        $stmt3 = $con->prepare("UPDATE restaurant_tables SET status='occupied' WHERE id=?");
        $stmt3->bind_param("i", $tableId);
        $stmt3->execute();
    }
    if ($paymentType === "final") {
        $stmt = $con->prepare("UPDATE reservations SET fully_paid=1, status='confirmed' WHERE id=?");
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();

        // Mark table as occupied automatically
        $stmt2 = $con->prepare("SELECT table_id FROM reservations WHERE id=?");
        $stmt2->bind_param("i", $reservationId);
        $stmt2->execute();
        $stmt2->bind_result($tableId);
        $stmt2->fetch();
        $stmt2->close();

        $stmt3 = $con->prepare("UPDATE restaurant_tables SET status='occupied' WHERE id=?");
        $stmt3->bind_param("i", $tableId);
        $stmt3->execute();
    }

    echo "<h2 style='color:green;'>✅ Payment Successful!</h2>";
    echo "<p>Reservation ID: $reservationId</p>";
    echo "<p>Payment ID: $paymentId</p>";
    echo "<a href='reservation.php'>Go back to My Reservations</a>";

} catch (SignatureVerificationError $e) {
    // Payment failed
    $stmt = $con->prepare("UPDATE payments SET status='failed' 
                           WHERE razorpay_order_id=? AND reservation_id=?");
    $stmt->bind_param("si", $orderId, $reservationId);
    $stmt->execute();

    echo "<h2 style='color:red;'>⚠️ Payment Verification Failed!</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href='payment.php?reservation_id=$reservationId&type=$paymentType'>Try Again</a>";
}
?>