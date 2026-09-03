<?php

include 'includes/auth.php';
include 'config/database.php';

if(
    !isset($_GET['product_id']) ||
    !isset($_GET['seller_id']) ||
    !is_numeric($_GET['product_id']) ||
    !is_numeric($_GET['seller_id'])
)
{
    header("Location: buyer-messages.php");
    exit();
}

$product_id = $_GET['product_id'];
$seller_id = $_GET['seller_id'];
$buyer_id = $_SESSION['user_id'];

$error = "";
$success = "";

/*
 * Verify that the buyer has actually
 * communicated with this seller about
 * this product.
 */
$stmt = $pdo->prepare(
    "SELECT id
     FROM messages
     WHERE product_id = ?
     AND sender_id = ?
     AND receiver_id = ?
     LIMIT 1"
);

$stmt->execute([
    $product_id,
    $buyer_id,
    $seller_id
]);

$conversation = $stmt->fetch();

if(!$conversation)
{
    header("Location: buyer-messages.php");
    exit();
}

/*
 * Get product and seller information.
 */
$stmt = $pdo->prepare(
    "SELECT products.title AS product_title,
            users.fullname AS seller_name
     FROM products
     INNER JOIN users
        ON products.user_id = users.id
     WHERE products.id = ?
     AND products.user_id = ?"
);

$stmt->execute([
    $product_id,
    $seller_id
]);

$product = $stmt->fetch();

if(!$product)
{
    header("Location: buyer-messages.php");
    exit();
}

/*
 * Check whether the buyer has already
 * reviewed this seller/product.
 */
$stmt = $pdo->prepare(
    "SELECT id
     FROM reviews
     WHERE reviewer_id = ?
     AND seller_id = ?
     AND product_id = ?
     LIMIT 1"
);

$stmt->execute([
    $buyer_id,
    $seller_id,
    $product_id
]);

$existingReview = $stmt->fetch();

/*
 * Process review submission.
 */
if(isset($_POST['submit_review']))
{
    $rating = $_POST['rating'] ?? '';
    $review = trim($_POST['review'] ?? '');

    if(!is_numeric($rating) || $rating < 1 || $rating > 5)
    {
        $error = "Please select a rating between 1 and 5.";
    }
    elseif($review == '')
    {
        $error = "Please write a review.";
    }
    elseif($existingReview)
    {
        $error = "You have already reviewed this product.";
    }
    else
    {
        $stmt = $pdo->prepare(
            "INSERT INTO reviews
            (reviewer_id, seller_id, product_id, rating, review)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $buyer_id,
            $seller_id,
            $product_id,
            $rating,
            $review
        ]);

        $success = "Your review was submitted successfully.";

        $existingReview = true;
    }
}

include 'includes/header.php';

?>

<div class="form-container">

<h2>
Leave a Review
</h2>

<p>
Product:
<?= htmlspecialchars($product['product_title']); ?>
</p>

<p>
Seller:
<?= htmlspecialchars($product['seller_name']); ?>
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

<?php if(!$existingReview): ?>

<form method="POST">

<label>
Rating:
</label>

<br>

<select name="rating" required>

<option value="">
Select Rating
</option>

<option value="5">
5 - Excellent
</option>

<option value="4">
4 - Good
</option>

<option value="3">
3 - Average
</option>

<option value="2">
2 - Poor
</option>

<option value="1">
1 - Very Poor
</option>

</select>

<br><br>

<label>
Review:
</label>

<br>

<textarea
name="review"
rows="5"
placeholder="Write your review..."
style="width:100%;padding:10px;"
required></textarea>

<br><br>

<button
type="submit"
name="submit_review">
Submit Review
</button>

</form>

<?php else: ?>

<p>
You have already reviewed this product.
</p>

<?php endif; ?>

<br>

<a href="buyer-messages.php">
Back to Messages
</a>

</div>

<?php include 'includes/footer.php'; ?>