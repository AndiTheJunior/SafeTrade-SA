<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('seller');

$message = "";
$messageType = "";


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
            $messageType = "success";
        }
        else
        {
            $message = "The order could not be accepted.";
            $messageType = "error";
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
                throw new Exception(
                    "The order could not be cancelled."
                );
            }

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
                throw new Exception(
                    "The order could not be cancelled."
                );
            }

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
                throw new Exception(
                    "The product could not be returned to the marketplace."
                );
            }

            $pdo->commit();

            $message =
                "Order cancelled successfully. " .
                "The product is available again.";

            $messageType = "success";
        }
        catch(Exception $e)
        {
            if($pdo->inTransaction())
            {
                $pdo->rollBack();
            }

            $message = $e->getMessage();
            $messageType = "error";
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
            $messageType = "success";
        }
        else
        {
            $message = "The order could not be completed.";
            $messageType = "error";
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

<div class="orders-page">

    <div class="page-header">

        <div>

            <h1>
                Seller Orders
            </h1>

            <p>
                Review and manage orders placed for your products.
            </p>

        </div>

        <a href="../dashboard.php" class="secondary-btn">
            Back to Dashboard
        </a>

    </div>


    <?php if($message): ?>

        <div class="status-message <?= htmlspecialchars($messageType); ?>">
            <?= htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if($orders->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Orders Yet
            </h3>

            <p>
                You have not received any orders yet.
            </p>

            <a href="../products.php" class="btn">
                View Marketplace
            </a>

        </div>

    <?php else: ?>

        <div class="orders-grid">

            <?php while($order = $orders->fetch()): ?>

                <div class="order-card
                    <?= empty($order['product_image']) ? 'order-card-no-image' : ''; ?>">

                    <?php if(!empty($order['product_image'])): ?>

                        <div class="order-image">

                            <img
                                src="../uploads/products/<?= htmlspecialchars($order['product_image']); ?>"
                                alt="<?= htmlspecialchars($order['product_title']); ?>"
                            >

                        </div>

                    <?php endif; ?>


                    <div class="order-content">

                        <div class="order-card-header">

                            <div>

                                <span class="order-number">
                                    Order #<?= (int)$order['id']; ?>
                                </span>

                                <h3>
                                    <?= htmlspecialchars($order['product_title']); ?>
                                </h3>

                            </div>


                            <span class="order-status order-status-<?= htmlspecialchars($order['status']); ?>">

                                <?= htmlspecialchars(ucfirst($order['status'])); ?>

                            </span>

                        </div>


                        <div class="order-details">

                            <p>
                                <strong>Buyer:</strong>
                                <?= htmlspecialchars($order['buyer_name']); ?>
                            </p>

                            <p>
                                <strong>Email:</strong>
                                <?= htmlspecialchars($order['buyer_email']); ?>
                            </p>

                            <p>
                                <strong>Phone:</strong>
                                <?= htmlspecialchars(
                                    $order['buyer_phone'] ?? 'Not provided'
                                ); ?>
                            </p>

                            <p>
                                <strong>Amount:</strong>
                                R<?= number_format((float)$order['amount'], 2); ?>
                            </p>

                            <p>
                                <strong>Order Date:</strong>
                                <?= htmlspecialchars($order['created_at']); ?>
                            </p>

                        </div>


                        <div class="order-message">

                            <?php if($order['status'] === 'pending'): ?>

                                <p>
                                    This order is waiting for your response.
                                    Accept it to allow the buyer to proceed
                                    to payment.
                                </p>

                            <?php elseif($order['status'] === 'accepted'): ?>

                                <p>
                                    You have accepted this order.
                                    Complete it when the transaction has
                                    been successfully concluded.
                                </p>

                            <?php elseif($order['status'] === 'completed'): ?>

                                <p>
                                    This order has been completed.
                                </p>

                            <?php elseif($order['status'] === 'cancelled'): ?>

                                <p>
                                    This order has been cancelled and the
                                    product has been returned to the marketplace.
                                </p>

                            <?php endif; ?>

                        </div>


                        <?php if($order['status'] === 'pending'): ?>

                            <form
                                method="POST"
                                class="seller-order-actions">

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= (int)$order['id']; ?>"
                                >

                                <button
                                    type="submit"
                                    name="accept_order"
                                    class="accept-order-btn">

                                    Accept Order

                                </button>

                                <button
                                    type="submit"
                                    name="cancel_order"
                                    class="cancel-order-btn"
                                    onclick="return confirm('Are you sure you want to cancel this order?');">

                                    Cancel Order

                                </button>

                            </form>


                        <?php elseif($order['status'] === 'accepted'): ?>

                            <form
                                method="POST"
                                class="seller-order-actions">

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= (int)$order['id']; ?>"
                                >

                                <button
                                    type="submit"
                                    name="complete_order"
                                    class="complete-order-btn"
                                    onclick="return confirm('Mark this order as completed?');">

                                    Complete Order

                                </button>

                                <button
                                    type="submit"
                                    name="cancel_order"
                                    class="cancel-order-btn"
                                    onclick="return confirm('Are you sure you want to cancel this order?');">

                                    Cancel Order

                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>


    <div class="page-bottom-actions">

        <a href="../products.php" class="secondary-btn">
            View Marketplace
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>