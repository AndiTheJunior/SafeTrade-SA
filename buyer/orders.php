<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('buyer');

/*
 * Get all orders belonging to the logged-in buyer.
 */
$stmt = $pdo->prepare(
    "SELECT orders.*,
            products.title AS product_title,
            products.image AS product_image,
            users.fullname AS seller_name
     FROM orders
     INNER JOIN products
        ON orders.product_id = products.id
     INNER JOIN users
        ON orders.seller_id = users.id
     WHERE orders.buyer_id = ?
     ORDER BY orders.created_at DESC"
);

$stmt->execute([
    $_SESSION['user_id']
]);

$orders = $stmt;

include '../includes/header.php';

?>

<div class="form-container">

<h2>
My Orders
</h2>

<?php

if($orders->rowCount() == 0)
{
?>

<p>
You have not placed any orders yet.
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
Seller:
<?= htmlspecialchars($order['seller_name']); ?>
</p>

<p>
Amount:
R<?= number_format($order['amount'], 2); ?>
</p>

<p>
Status:
<strong>
<?= htmlspecialchars(ucfirst($order['status'])); ?>
</strong>
</p>

<p>
Order Date:
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

<br><br>

<a href="../products.php">
Back to Marketplace
</a>

</div>

<?php include '../includes/footer.php'; ?>