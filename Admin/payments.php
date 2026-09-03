<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';
include '../controllers/PaymentController.php';

requireRole('admin');

$paymentController = new PaymentController($pdo);

$payments = $paymentController->getAllPayments();

include '../includes/header.php';

?>

<div class="form-container">

<h2>
Admin - All Payments
</h2>

<p>
View and monitor all payments made through SafeTrade.
</p>

<hr>

<?php

if($payments->rowCount() == 0)
{
?>

<p>
There are no payments in the system yet.
</p>

<?php
}

while($payment = $payments->fetch())
{
?>

<div class="card">

<h3>
<?= htmlspecialchars($payment['product_title']); ?>
</h3>

<p>
<strong>Payment ID:</strong>
<?= (int)$payment['id']; ?>
</p>

<p>
<strong>Order ID:</strong>
<?= (int)$payment['order_id']; ?>
</p>

<p>
<strong>Buyer:</strong>
<?= htmlspecialchars($payment['buyer_name']); ?>
</p>

<p>
<strong>Seller:</strong>
<?= htmlspecialchars($payment['seller_name']); ?>
</p>

<p>
<strong>Amount:</strong>
R<?= number_format($payment['amount'], 2); ?>
</p>

<p>
<strong>Payment Method:</strong>
<?= htmlspecialchars($payment['payment_method']); ?>
</p>

<p>
<strong>Transaction Reference:</strong>
<?= htmlspecialchars($payment['transaction_reference'] ?? 'Not provided'); ?>
</p>

<p>
<strong>Payment Status:</strong>
<?= htmlspecialchars(ucfirst($payment['status'])); ?>
</p>

<p>
<strong>Payment Date:</strong>
<?= htmlspecialchars($payment['created_at']); ?>
</p>

</div>

<br>

<?php
}

?>

<a href="../dashboard.php">
Back to Dashboard
</a>

<br><br>

<a href="orders.php">
View All Orders
</a>

</div>

<?php include '../includes/footer.php'; ?>