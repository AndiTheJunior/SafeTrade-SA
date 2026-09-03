<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('seller');

$message = "";

/*
 * Accept an order.
 */
if(isset($_POST['accept_order']))
{
    $order_id = $_POST['order_id'];

    if(is_numeric($order_id))
    {
        $stmt = $pdo->prepare(
            "UPDATE orders
             SET status = 'accepted'
             WHERE id = ?
             AND seller_id = ?
             AND status = 'pending'"
        );

        $stmt->execute([
            $order_id,
            $_SESSION['user_id']
        ]);

        if($stmt->rowCount() === 1)
        {
            $message = "Order accepted successfully.";
        }
        else
        {
            $message = "The order could not be accepted.";
        }
    }
}

/*
 * Cancel an order and return the product
 * to the marketplace.
 */
if(isset($_POST['cancel_order']))
{
    $order_id = $_POST['order_id'];

    if(is_numeric($order_id))
    {
        try
        {
            $pdo->beginTransaction();

            /*
             * Find the order belonging to the logged-in seller.
             */
            $orderStmt = $pdo->prepare(
                "SELECT product_id
                 FROM orders
                 WHERE id = ?
                 AND seller_id = ?
                 AND status IN ('pending', 'accepted')
                 FOR UPDATE"
            );

            $orderStmt->execute([
                $order_id,
                $_SESSION['user_id']
            ]);

            $order = $orderStmt->fetch();

            if(!$order)
            {
                throw new Exception("The order could not be cancelled.");
            }

            /*
             * Cancel the order.
             */
            $cancelStmt = $pdo->prepare(
                "UPDATE orders
                 SET status = 'cancelled'
                 WHERE id = ?
                 AND seller_id = ?
                 AND status IN ('pending', 'accepted')"
            );

            $cancelStmt->execute([
                $order_id,
                $_SESSION['user_id']
            ]);

            if($cancelStmt->rowCount() !== 1)
            {
                throw new Exception("The order could not be cancelled.");
            }

            /*
             * Return the product to the marketplace.
             */
            $productStmt = $pdo->prepare(
                "UPDATE products
                 SET status = 'active'
                 WHERE id = ?
                 AND status = 'sold'"
            );

            $productStmt->execute([
                $order['product_id']
            ]);

            if($productStmt->rowCount() !== 1)
            {
                throw new Exception("The product could not be returned to the marketplace.");
            }

            $pdo->commit();

            $message = "Order cancelled successfully. The product is available again.";
        }
        catch(Exception $e)
        {
            if($pdo->inTransaction())
            {
                $pdo->rollBack();
            }

            $message = $e->getMessage();
        }
    }
}

/*
 * Complete an order.
 */
if(isset($_POST['complete_order']))
{
    $order_id = $_POST['order_id'];

    if(is_numeric($order_id))
    {
        $stmt = $pdo->prepare(
            "UPDATE orders
             SET status = 'completed'
             WHERE id = ?
             AND seller_id = ?
             AND status = 'accepted'"
        );

        $stmt->execute([
            $order_id,
            $_SESSION['user_id']
        ]);

        if($stmt->rowCount() === 1)
        {
            $message = "Order marked as completed.";
        }
        else
        {
            $message = "The order could not be completed.";
        }
    }
}

/*
 * Get all orders for the logged-in seller.
 */
$stmt = $pdo->prepare(
    "SELECT orders.*,
            products.title AS product_title,
            products.image AS product_image,
            users.fullname AS buyer_name,
            users.email AS buyer_email,
            users.phone AS buyer_phone
     FROM orders
     INNER JOIN products
        ON orders.product_id = products.id
     INNER JOIN users
        ON orders.buyer_id = users.id
     WHERE orders.seller_id = ?
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
Seller Orders
</h2>

<?php if($message): ?>

<p>
<?= htmlspecialchars($message); ?>
</p>

<?php endif; ?>

<?php

if($orders->rowCount() == 0)
{
?>

<p>
You have not received any orders yet.
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
Buyer:
<?= htmlspecialchars($order['buyer_name']); ?>
</p>

<p>
Buyer Email:
<?= htmlspecialchars($order['buyer_email']); ?>
</p>

<p>
Buyer Phone:
<?= htmlspecialchars($order['buyer_phone'] ?? 'Not provided'); ?>
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

<?php if($order['status'] === 'pending'): ?>

<form method="POST">

<input
type="hidden"
name="order_id"
value="<?= (int)$order['id']; ?>">

<button
type="submit"
name="accept_order">
Accept Order
</button>

<button
type="submit"
name="cancel_order"
onclick="return confirm('Are you sure you want to cancel this order?');">
Cancel Order
</button>

</form>

<?php elseif($order['status'] === 'accepted'): ?>

<form method="POST">

<input
type="hidden"
name="order_id"
value="<?= (int)$order['id']; ?>">

<button
type="submit"
name="complete_order"
onclick="return confirm('Mark this order as completed?');">
Complete Order
</button>

<button
type="submit"
name="cancel_order"
onclick="return confirm('Are you sure you want to cancel this order?');">
Cancel Order
</button>

</form>

<?php elseif($order['status'] === 'completed'): ?>

<p>
This order has been completed.
</p>

<?php elseif($order['status'] === 'cancelled'): ?>

<p>
This order has been cancelled.
</p>

<?php endif; ?>

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