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

<div class="admin-payments-page">

    <div class="page-header">

        <div>

            <h1>
                Payment Monitoring
            </h1>

            <p>
                View and monitor all SafeTrade payment records.
            </p>

        </div>

        <a href="index.php" class="secondary-btn">
            Back to Admin Dashboard
        </a>

    </div>


    <?php if($payments->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Payments
            </h3>

            <p>
                There are currently no payment records in the system.
            </p>

        </div>

    <?php else: ?>

        <div class="admin-payments-grid">

            <?php while($payment = $payments->fetch()): ?>

                <div class="admin-payment-card">

                    <div class="admin-payment-header">

                        <div>

                            <span class="payment-record-number">
                                Payment #<?= (int)$payment['id']; ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars($payment['product_title']); ?>
                            </h3>

                            <span class="payment-order-number">
                                Order #<?= (int)$payment['order_id']; ?>
                            </span>

                        </div>


                        <span class="payment-status-badge payment-status-<?= htmlspecialchars($payment['status']); ?>">

                            <?= htmlspecialchars(ucfirst($payment['status'])); ?>

                        </span>

                    </div>


                    <div class="admin-payment-parties">

                        <div class="admin-party-box">

                            <span class="admin-party-label">
                                Buyer
                            </span>

                            <strong>
                                <?= htmlspecialchars($payment['buyer_name']); ?>
                            </strong>

                        </div>


                        <div class="admin-party-box">

                            <span class="admin-party-label">
                                Seller
                            </span>

                            <strong>
                                <?= htmlspecialchars($payment['seller_name']); ?>
                            </strong>

                        </div>

                    </div>


                    <div class="admin-payment-details">

                        <div class="payment-detail-item">

                            <span>
                                Amount
                            </span>

                            <strong class="admin-payment-amount">
                                R<?= number_format((float)$payment['amount'], 2); ?>
                            </strong>

                        </div>


                        <div class="payment-detail-item">

                            <span>
                                Payment Method
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $payment['payment_method']
                                        )
                                    )
                                ); ?>
                            </strong>

                        </div>


                        <div class="payment-detail-item">

                            <span>
                                Transaction Reference
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $payment['transaction_reference']
                                    ?? 'Not provided'
                                ); ?>
                            </strong>

                        </div>


                        <div class="payment-detail-item">

                            <span>
                                Payment Date
                            </span>

                            <strong>
                                <?= htmlspecialchars($payment['created_at']); ?>
                            </strong>

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

        <a href="orders.php" class="secondary-btn">
            Order Monitoring
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>