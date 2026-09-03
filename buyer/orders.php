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

<div class="orders-page">

    <div class="page-header">

        <div>

            <h1>
                My Orders
            </h1>

            <p>
                Track your SafeTrade purchases and payment progress.
            </p>

        </div>

        <a href="../dashboard.php" class="secondary-btn">
            Back to Dashboard
        </a>

    </div>


    <?php if($orders->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Orders Yet
            </h3>

            <p>
                You have not placed any orders yet.
            </p>

            <a href="../products.php" class="btn">
                Browse Marketplace
            </a>

        </div>

    <?php else: ?>

        <div class="orders-grid">

            <?php while($order = $orders->fetch()): ?>

                <div class="order-card">

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
                                <strong>Seller:</strong>
                                <?= htmlspecialchars($order['seller_name']); ?>
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

                            <?php if($order['status'] === 'accepted'): ?>

                                <p>
                                    The seller has accepted your order.
                                    You can now proceed to payment.
                                </p>

                            <?php elseif($order['status'] === 'pending'): ?>

                                <p>
                                    Waiting for the seller to accept this order before payment.
                                </p>

                            <?php elseif($order['status'] === 'completed'): ?>

                                <p>
                                    This order has been completed successfully.
                                </p>

                            <?php elseif($order['status'] === 'cancelled'): ?>

                                <p>
                                    This order has been cancelled.
                                </p>

                            <?php endif; ?>

                        </div>


                        <?php if($order['status'] === 'accepted'): ?>

                            <a
                                href="checkout.php?order_id=<?= (int)$order['id']; ?>"
                                class="payment-btn">

                                Proceed to Payment

                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>


    <div class="page-bottom-actions">

        <a href="../products.php" class="secondary-btn">
            Browse Marketplace
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>