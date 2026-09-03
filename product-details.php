<?php

include 'includes/auth.php';
include 'config/database.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: products.php");
    exit();
}

$id = $_GET['id'];

/*
 * Get the active product and seller information.
 */
$stmt = $pdo->prepare(
    "SELECT products.*,
            users.fullname AS seller_name,
            users.verification_status AS seller_verification_status
     FROM products
     INNER JOIN users
        ON products.user_id = users.id
     WHERE products.id = ?
     AND products.status = 'active'"
);

$stmt->execute([
    $id
]);

$product = $stmt->fetch();

if(!$product)
{
    header("Location: products.php");
    exit();
}

/*
 * Prevent the seller from messaging themselves.
 */
$isSeller = ($_SESSION['user_id'] == $product['user_id']);

$error = "";
$success = "";

/*
 * Process a buyer order.
 */
if(isset($_POST['place_order']))
{
    if($_SESSION['role'] !== 'buyer')
    {
        $error = "Only buyers can place orders.";
    }
    elseif($isSeller)
    {
        $error = "You cannot order your own product.";
    }
    else
    {
        try
        {
            $pdo->beginTransaction();

            /*
             * Lock the product row while checking its availability.
             */
            $orderProductStmt = $pdo->prepare(
                "SELECT id, user_id, price, status
                 FROM products
                 WHERE id = ?
                 FOR UPDATE"
            );

            $orderProductStmt->execute([
                $product['id']
            ]);

            $orderProduct = $orderProductStmt->fetch();

            if(!$orderProduct)
            {
                throw new Exception("Product not found.");
            }

            if($orderProduct['status'] !== 'active')
            {
                throw new Exception("This product is no longer available.");
            }

            /*
             * Make sure there is no existing active order.
             */
            $existingOrderStmt = $pdo->prepare(
                "SELECT id
                 FROM orders
                 WHERE product_id = ?
                 AND status IN ('pending', 'accepted')
                 LIMIT 1"
            );

            $existingOrderStmt->execute([
                $product['id']
            ]);

            if($existingOrderStmt->fetch())
            {
                throw new Exception("This product already has an active order.");
            }

            /*
             * Create the order using the current product price.
             */
            $orderStmt = $pdo->prepare(
                "INSERT INTO orders
                (buyer_id, seller_id, product_id, amount, status)
                VALUES (?, ?, ?, ?, 'pending')"
            );

            $orderStmt->execute([
                $_SESSION['user_id'],
                $orderProduct['user_id'],
                $orderProduct['id'],
                $orderProduct['price']
            ]);

            /*
             * Mark the product as sold so another buyer
             * cannot place an order for it.
             */
            $updateProductStmt = $pdo->prepare(
                "UPDATE products
                 SET status = 'sold'
                 WHERE id = ?
                 AND status = 'active'"
            );

            $updateProductStmt->execute([
                $product['id']
            ]);

            if($updateProductStmt->rowCount() !== 1)
            {
                throw new Exception("The product is no longer available.");
            }

            $pdo->commit();

            header("Location: buyer/orders.php");
            exit();
        }
        catch(Exception $e)
        {
            if($pdo->inTransaction())
            {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

/*
 * Process the message form.
 */
if(isset($_POST['send_message']))
{
    $message = trim($_POST['message']);

    if($message == '')
    {
        $error = "Please enter a message.";
    }
    elseif($isSeller)
    {
        $error = "You cannot message yourself.";
    }
    else
    {
        $stmt = $pdo->prepare(
            "INSERT INTO messages
            (sender_id, receiver_id, product_id, message)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $product['user_id'],
            $product['id'],
            $message
        ]);

        $success = "Message sent successfully.";
    }
}

include 'includes/header.php';

?>

<div class="form-container">

<?php if(!empty($product['image'])): ?>

<img
src="uploads/products/<?= htmlspecialchars($product['image']); ?>"
style="width:100%;"
>

<?php endif; ?>

<h2>
<?= htmlspecialchars($product['title']); ?>
</h2>

<p>
<?= htmlspecialchars($product['description']); ?>
</p>

<p>
Price:
R<?= htmlspecialchars($product['price']); ?>
</p>

<p>
Category:
<?= htmlspecialchars($product['category']); ?>
</p>

<p>
Location:
<?= htmlspecialchars($product['location']); ?>
</p>

<p>
Seller:
<?= htmlspecialchars($product['seller_name']); ?>

<?php if($product['seller_verification_status'] === 'verified'): ?>

✓ Verified

<?php else: ?>

— Unverified

<?php endif; ?>

</p>

<?php

$reviewStmt = $pdo->prepare(
    "SELECT
        AVG(rating) AS average_rating,
        COUNT(*) AS review_count
     FROM reviews
     WHERE seller_id = ?"
);

$reviewStmt->execute([
    $product['user_id']
]);

$reviewSummary = $reviewStmt->fetch();

?>

<p>

Rating:

<?php

if($reviewSummary['review_count'] > 0)
{
    $averageRating = round($reviewSummary['average_rating']);

for($i = 1; $i <= 5; $i++)
{
    if($i <= $averageRating)
    {
        echo "⭐";
    }
    else
    {
        echo "☆";
    }
}

echo " ";
echo number_format($reviewSummary['average_rating'], 1);
echo "/5 ";

echo "(" . $reviewSummary['review_count'];

if($reviewSummary['review_count'] == 1)
{
    echo " review)";
}
else
{
    echo " reviews)";
}
}
else
{
    echo "No reviews yet";
}

?>

</p>

<hr>

<h3>
Reviews
</h3>

<?php

$reviewsStmt = $pdo->prepare(
    "SELECT reviews.*,
            users.fullname AS reviewer_name
     FROM reviews
     INNER JOIN users
        ON reviews.reviewer_id = users.id
     WHERE reviews.product_id = ?
     ORDER BY reviews.created_at DESC"
);

$reviewsStmt->execute([
    $product['id']
]);

$reviews = $reviewsStmt;

if($reviews->rowCount() == 0)
{
?>

<p>
No reviews yet.
</p>

<?php
}

while($review = $reviews->fetch())
{
?>

<div class="card">

<p>
<strong>
<?= htmlspecialchars($review['reviewer_name']); ?>
</strong>
</p>

<p>

<?php

for($i = 1; $i <= 5; $i++)
{
    if($i <= $review['rating'])
    {
        echo "⭐";
    }
    else
    {
        echo "☆";
    }
}

?>

</p>
<p>
<?= htmlspecialchars($review['review']); ?>
</p>

<p>
Reviewed:
<?= htmlspecialchars($review['created_at']); ?>
</p>

</div>

<br>

<?php
}

?>

<p>
Status:
<?= htmlspecialchars($product['status']); ?>
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

<?php if(!$isSeller): ?>

<?php if($_SESSION['role'] === 'buyer'): ?>

<hr>

<h3>
Buy This Product
</h3>

<p>
By placing an order, this product will be reserved for you.
</p>

<form method="POST">

<button
type="submit"
name="place_order"
onclick="return confirm('Are you sure you want to place this order?');">
Place Order
</button>

</form>

<?php endif; ?>

<h3>
Contact Seller
</h3>

<form method="POST">

<textarea
name="message"
placeholder="Write a message to the seller..."
rows="5"
style="width:100%;padding:10px;"
required></textarea>

<br><br>

<button
type="submit"
name="send_message">
Send Message
</button>

</form>

<?php else: ?>

<p>
This is your product.
</p>

<?php endif; ?>

<br>

<a href="products.php">
Back to Marketplace
</a>

</div>

<?php include 'includes/footer.php'; ?>