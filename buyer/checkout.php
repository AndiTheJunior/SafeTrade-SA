<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';
include '../controllers/PaymentController.php';

requireRole('buyer');

$error = "";
$success = "";

/*
 * Make sure an order ID was provided.
 */
if(!isset($_GET['order_id']) || !is_numeric($_GET['order_id']))
{
    header("Location: orders.php");
    exit();
}

$orderId = $_GET['order_id'];

/*
 * Get the buyer's order.
 *
 * The buyer_id condition prevents a buyer
 * from accessing another buyer's order.
 */
$orderStmt = $pdo->prepare(
    "SELECT orders.*,
            products.title AS product_title,
            products.image AS product_image,
            users.fullname AS seller_name
     FROM orders

     INNER JOIN products
        ON orders.product_id = products.id

     INNER JOIN users
        ON orders.seller_id = users.id

     WHERE orders.id = ?
     AND orders.buyer_id = ?"
);

$orderStmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $orderStmt->fetch();

if(!$order)
{
    header("Location: orders.php");
    exit();
}


/*
 * Check whether a payment already exists.
 */
$paymentController = new PaymentController($pdo);

$existingPayment = $paymentController->getPaymentByOrder(
    $order['id']
);


/*
 * Process the payment form.
 */
if(isset($_POST['make_payment']))
{
    if($order['status'] !== 'accepted')
    {
        $error =
            "Payment can only be made after the seller accepts the order.";
    }
    elseif($existingPayment && $existingPayment['status'] === 'paid')
    {
        $error =
            "This order has already been paid.";
    }
    else
    {
        $paymentMethod =
            trim($_POST['payment_method']);

        $transactionReference =
            trim($_POST['transaction_reference']);

        if($paymentMethod === '')
        {
            $error =
                "Please select a payment method.";
        }
        elseif($transactionReference === '')
        {
            $error =
                "Please enter a payment reference.";
        }
        else
        {
            if($existingPayment)
            {
                $updated =
                    $paymentController->updatePaymentStatus(
                        $existingPayment['id'],
                        'paid'
                    );

                if($updated)
                {
                    $success =
                        "Payment recorded successfully.";
                }
                else
                {
                    $error =
                        "The payment could not be updated.";
                }
            }
            else
            {
                $created =
                    $paymentController->createPayment(
                        $order['id'],
                        $_SESSION['user_id'],
                        $order['amount'],
                        $paymentMethod,
                        $transactionReference
                    );

                if($created)
                {
                    /*
                     * Payments are initially created as pending.
                     * For this demo workflow they are then marked paid.
                     */
                    $existingPayment =
                        $paymentController->getPaymentByOrder(
                            $order['id']
                        );

                    if($existingPayment)
                    {
                        $paymentController->updatePaymentStatus(
                            $existingPayment['id'],
                            'paid'
                        );

                        $success =
                            "Payment recorded successfully.";
                    }
                    else
                    {
                        $error =
                            "Payment was created but could not be retrieved.";
                    }
                }
                else
                {
                    $error =
                        "The payment could not be created.";
                }
            }

            /*
             * Refresh payment information.
             */
            $existingPayment =
                $paymentController->getPaymentByOrder(
                    $order['id']
                );
        }
    }
}

include '../includes/header.php';

?>

<div class="checkout-page">

    <div class="page-header">

        <div>

            <h1>
                Checkout
            </h1>

            <p>
                Review your order and complete the SafeTrade demo payment.
            </p>

        </div>

        <a href="orders.php" class="secondary-btn">
            Back to My Orders
        </a>

    </div>


    <?php if($error): ?>

        <div class="status-message error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if($success): ?>

        <div class="status-message success">
            <?= htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <div class="checkout-layout">

        <!-- ORDER SUMMARY -->

        <div class="checkout-summary">

            <h2>
                Order Summary
            </h2>


            <?php if(!empty($order['product_image'])): ?>

                <div class="checkout-product-image">

                    <img
                        src="../uploads/products/<?= htmlspecialchars($order['product_image']); ?>"
                        alt="<?= htmlspecialchars($order['product_title']); ?>"
                    >

                </div>

            <?php endif; ?>


            <div class="checkout-product-info">

                <span class="order-number">
                    Order #<?= (int)$order['id']; ?>
                </span>

                <h3>
                    <?= htmlspecialchars($order['product_title']); ?>
                </h3>


                <div class="checkout-summary-row">

                    <span>
                        Seller
                    </span>

                    <strong>
                        <?= htmlspecialchars($order['seller_name']); ?>
                    </strong>

                </div>


                <div class="checkout-summary-row">

                    <span>
                        Order Status
                    </span>

                    <span class="order-status order-status-<?= htmlspecialchars($order['status']); ?>">
                        <?= htmlspecialchars(ucfirst($order['status'])); ?>
                    </span>

                </div>


                <div class="checkout-summary-row checkout-total">

                    <span>
                        Amount Due
                    </span>

                    <strong>
                        R<?= number_format((float)$order['amount'], 2); ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- PAYMENT SECTION -->

        <div class="payment-panel">

            <?php if(
                $existingPayment &&
                $existingPayment['status'] === 'paid'
            ): ?>

                <div class="payment-complete">

                    <div class="payment-complete-heading">

                        <span class="payment-status-badge paid">
                            Paid
                        </span>

                        <h2>
                            Payment Complete
                        </h2>

                    </div>

                    <p>
                        Your payment has been recorded successfully.
                    </p>


                    <div class="payment-details">

                        <div class="payment-detail-row">

                            <span>
                                Payment Method
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $existingPayment['payment_method']
                                        )
                                    )
                                ); ?>
                            </strong>

                        </div>


                        <div class="payment-detail-row">

                            <span>
                                Transaction Reference
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $existingPayment['transaction_reference']
                                ); ?>
                            </strong>

                        </div>


                        <div class="payment-detail-row">

                            <span>
                                Payment Date
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $existingPayment['created_at']
                                ); ?>
                            </strong>

                        </div>


                        <div class="payment-detail-row">

                            <span>
                                Amount Paid
                            </span>

                            <strong>
                                R<?= number_format(
                                    (float)$existingPayment['amount'],
                                    2
                                ); ?>
                            </strong>

                        </div>

                    </div>

                </div>


            <?php elseif($order['status'] !== 'accepted'): ?>

                <div class="payment-unavailable">

                    <h2>
                        Payment Not Available Yet
                    </h2>

                    <p>
                        Payment becomes available after the seller accepts
                        your order.
                    </p>

                    <a href="orders.php" class="secondary-btn">
                        Return to My Orders
                    </a>

                </div>


            <?php else: ?>

                <h2>
                    Payment Details
                </h2>

                <div class="demo-payment-notice">

                    <strong>
                        Demo Payment System
                    </strong>

                    <p>
                        This checkout records a demonstration payment only.
                        No real money will be transferred.
                    </p>

                </div>


                <form method="POST" class="checkout-form">

                    <div class="form-group">

                        <label for="payment_method">
                            Payment Method
                        </label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            required>

                            <option value="">
                                Select payment method
                            </option>

                            <option value="bank_transfer">
                                Bank Transfer
                            </option>

                            <option value="cash">
                                Cash
                            </option>

                            <option value="manual">
                                Manual Payment
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="transaction_reference">
                            Transaction / Payment Reference
                        </label>

                        <input
                            type="text"
                            id="transaction_reference"
                            name="transaction_reference"
                            placeholder="Example: SAFE-ORDER-001"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="make_payment"
                        class="checkout-payment-btn"
                        onclick="return confirm('Confirm that you want to record this payment?');">

                        Record Payment

                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>


    <div class="page-bottom-actions">

        <a href="orders.php" class="secondary-btn">
            My Orders
        </a>

        <a href="../dashboard.php" class="secondary-btn">
            Dashboard
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>