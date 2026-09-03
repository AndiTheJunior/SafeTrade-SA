<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('admin');

/*
 * Get all orders in the system.
 */
$stmt = $pdo->prepare(
    "SELECT orders.*,
            products.title AS product_title,
            products.image AS product_image,

            buyers.fullname AS buyer_name,
            buyers.email AS buyer_email,

            sellers.fullname AS seller_name,
            sellers.email AS seller_email

     FROM orders

     INNER JOIN products
        ON orders.product_id = products.id

     INNER JOIN users AS buyers
        ON orders.buyer_id = buyers.id

     INNER JOIN users AS sellers
        ON orders.seller_id = sellers.id

     ORDER BY orders.created_at DESC"
);

$stmt->execute();

$orders = $stmt;

include '../includes/header.php';

?>

<div class="form-container">

<h2>
Admin - All Orders
</h2>

<p>
View and monitor all buyer and seller orders.
</p>

<hr>

<?php

if($orders->rowCount() == 0)
{
?>

<p>
There are no orders in the system yet.
</p>

<?php
}

while($order = $orders->fetch())
{
?>

<div class="card">

<h3>
<?= htmlspecialchars($order['product_title']); ?>
</h3>

<?php if(!empty($order['product_image'])): ?>

<img
src="../uploads/products/<?= htmlspecialchars($order['product_image']); ?>"
style="width:200px;"
>

<?php endif; ?>

<p>
<strong>Order ID:</strong>
<?= (int)$order['id']; ?>
</p>

<p>
<strong>Buyer:</strong>
<?= htmlspecialchars($order['buyer_name']); ?>
</p>

<p>
<strong>Buyer Email:</strong>
<?= htmlspecialchars($order['buyer_email']); ?>
</p>

<p>
<strong>Seller:</strong>
<?= htmlspecialchars($order['seller_name']); ?>
</p>

<p>
<strong>Seller Email:</strong>
<?= htmlspecialchars($order['seller_email']); ?>
</p>

<p>
<strong>Amount:</strong>
R<?= number_format($order['amount'], 2); ?>
</p>

<p>
<strong>Status:</strong>

<?= htmlspecialchars(ucfirst($order['status'])); ?>

</p>

<p>
<strong>Order Date:</strong>
<?= htmlspecialchars($order['created_at']); ?>
</p>

</div>

<br>

<?php
}

?>

<a href="../dashboard.php">
Back to Dashboard
</a>

</div>

<?php include '../includes/footer.php'; ?>