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
 * The buyer_id condition is important because
 * a buyer must not be able to access another
 * buyer's order by changing the URL.
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
        $error = "Payment can only be made after the seller accepts the order.";
    }
    elseif($existingPayment && $existingPayment['status'] === 'paid')
    {
        $error = "This order has already been paid.";
    }
    else
    {
        $paymentMethod = trim($_POST['payment_method']);
        $transactionReference = trim($_POST['transaction_reference']);

        if($paymentMethod === '')
        {
            $error = "Please select a payment method.";
        }
        elseif($transactionReference === '')
        {
            $error = "Please enter a payment reference.";
        }
        else
        {
            if($existingPayment)
            {
                $updated = $paymentController->updatePaymentStatus(
                    $existingPayment['id'],
                    'paid'
                );

                if($updated)
                {
                    $success = "Payment recorded successfully.";
                }
                else
                {
                    $error = "The payment could not be updated.";
                }
            }
            else
            {
                $created = $paymentController->createPayment(
                    $order['id'],
                    $_SESSION['user_id'],
                    $order['amount'],
                    $paymentMethod,
                    $transactionReference
                );

                if($created)
                {
                    /*
                     * The first payment record is created as
                     * pending. Mark it as paid for this demo
                     * payment workflow.
                     */
                    $existingPayment = $paymentController->getPaymentByOrder(
                        $order['id']
                    );

                    if($existingPayment)
                    {
                        $paymentController->updatePaymentStatus(
                            $existingPayment['id'],
                            'paid'
                        );

                        $success = "Payment recorded successfully.";
                    }
                    else
                    {
                        $error = "Payment was created but could not be retrieved.";
                    }
                }
                else
                {
                    $error = "The payment could not be created.";
                }
            }

            /*
             * Refresh the payment information.
             */
            $existingPayment = $paymentController->getPaymentByOrder(
                $order['id']
            );
        }
    }
}

include '../includes/header.php';

?>

<div class="form-container">

<h2>
Checkout
</h2>

<p>
Review your order and record your payment.
</p>

<hr>

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
<strong>
R<?= number_format($order['amount'], 2); ?>
</strong>
</p>

<p>
Order Status:
<strong>
<?= htmlspecialchars(ucfirst($order['status'])); ?>
</strong>
</p>

<hr>

<?php if($error): ?>

<p>
<?= htmlspecialchars($error); ?>
</p>

<?php endif; ?>

<?php if($success): ?>

<p>
<?= htmlspecialchars($success); ?>
</p>

<?php endif; ?>

<?php if($existingPayment && $existingPayment['status'] === 'paid'): ?>

<h3>
Payment Complete
</h3>

<p>
Payment Status:
<strong>
Paid
</strong>
</p>

<p>
Payment Method:
<?= htmlspecialchars($existingPayment['payment_method']); ?>
</p>

<p>
Transaction Reference:
<?= htmlspecialchars($existingPayment['transaction_reference']); ?>
</p>

<p>
Payment Date:
<?= htmlspecialchars($existingPayment['created_at']); ?>
</p>

<?php elseif($order['status'] !== 'accepted'): ?>

<p>
Payment is available after the seller accepts your order.
</p>

<?php else: ?>

<h3>
Make Payment
</h3>

<p>
This is currently a demonstration payment system.
No real money will be transferred.
</p>

<form method="POST">

<label>
Payment Method
</label>

<br>

<select name="payment_method" required>

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

<br><br>

<label>
Transaction / Payment Reference
</label>

<br>

<input
type="text"
name="transaction_reference"
placeholder="Enter payment reference"
required
>

<br><br>

<button
type="submit"
name="make_payment"
onclick="return confirm('Confirm that you want to record this payment?');">
Record Payment
</button>

</form>

<?php endif; ?>

<br>

<a href="orders.php">
Back to My Orders
</a>

<br><br>

<a href="../dashboard.php">
Back to Dashboard
</a>

</div>

<?php include '../includes/footer.php'; ?>