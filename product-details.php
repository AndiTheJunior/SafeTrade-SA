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
    "SELECT products.*, users.fullname AS seller_name
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
</p>

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