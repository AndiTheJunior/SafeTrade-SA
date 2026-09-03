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

<div class="admin-orders-page">

    <div class="page-header">

        <div>

            <h1>
                Order Monitoring
            </h1>

            <p>
                View and monitor all buyer and seller orders on SafeTrade.
            </p>

        </div>

        <a href="index.php" class="secondary-btn">
            Back to Admin Dashboard
        </a>

    </div>


    <?php if($orders->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Orders
            </h3>

            <p>
                There are currently no orders in the system.
            </p>

        </div>

    <?php else: ?>

        <div class="admin-orders-grid">

            <?php while($order = $orders->fetch()): ?>

                <div class="admin-order-card">

                    <?php if(!empty($order['product_image'])): ?>

                        <div class="admin-order-image">

                            <img
                                src="../uploads/products/<?= htmlspecialchars($order['product_image']); ?>"
                                alt="<?= htmlspecialchars($order['product_title']); ?>"
                            >

                        </div>

                    <?php endif; ?>


                    <div class="admin-order-content">

                        <div class="admin-order-header">

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


                        <div class="admin-order-parties">

                            <div class="admin-party-box">

                                <span class="admin-party-label">
                                    Buyer
                                </span>

                                <strong>
                                    <?= htmlspecialchars($order['buyer_name']); ?>
                                </strong>

                                <p>
                                    <?= htmlspecialchars($order['buyer_email']); ?>
                                </p>

                            </div>


                            <div class="admin-party-box">

                                <span class="admin-party-label">
                                    Seller
                                </span>

                                <strong>
                                    <?= htmlspecialchars($order['seller_name']); ?>
                                </strong>

                                <p>
                                    <?= htmlspecialchars($order['seller_email']); ?>
                                </p>

                            </div>

                        </div>


                        <div class="admin-order-meta">

                            <div>

                                <span>
                                    Amount
                                </span>

                                <strong class="admin-order-amount">
                                    R<?= number_format((float)$order['amount'], 2); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Order Date
                                </span>

                                <strong>
                                    <?= htmlspecialchars($order['created_at']); ?>
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>


    <div class="page-bottom-actions">

        <a href="index.php" class="secondary-btn">
            Admin Dashboard
        </a>

        <a href="payments.php" class="secondary-btn">
            Payment Monitoring
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>