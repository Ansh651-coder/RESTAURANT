<?php
include "DataBase.php";
require('razorpay-php/Razorpay.php');

use Razorpay\Api\Api;

$keyId = "rzp_test_RGnV1AM3laAkfA";
$keySecret = "WtvX2AMs3hrOJdf7BlUlhtY";
$api = new Api($keyId, $keySecret);

$paymentId = $_GET['payment_id'];
$orderId = $_GET['order_id'];
$resId = intval($_GET['reservation_id']);

// Fetch payment details from Razorpay
$payment = $api->payment->fetch($paymentId);

if ($payment->status == "captured") {
    $con->query("UPDATE payments SET status='paid', razorpay_payment_id='$paymentId' WHERE razorpay_order_id='$orderId'");
    echo "<h2>✅ Payment Successful! Your table is reserved.</h2>";
    echo "<a href='reservation.php'>Go back</a>";
} else {
    $con->query("UPDATE payments SET status='failed' WHERE razorpay_order_id='$orderId'");
    echo "<h2>❌ Payment Failed. Please try again.</h2>";
}
