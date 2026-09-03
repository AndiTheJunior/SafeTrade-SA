<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('buyer');

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

$productId = (int)$_GET['product_id'];
$sellerId = (int)$_GET['seller_id'];
$buyerId = (int)$_SESSION['user_id'];

$error = "";
$success = "";


/*
 * Verify that this product belongs to the supplied seller.
 */
$productStmt = $pdo->prepare(
    "SELECT products.title AS product_title,
            users.fullname AS seller_name

     FROM products

     INNER JOIN users
        ON products.user_id = users.id

     WHERE products.id = ?
     AND products.user_id = ?
     AND users.role = 'seller'"
);

$productStmt->execute([
    $productId,
    $sellerId
]);

$product = $productStmt->fetch();

if(!$product)
{
    header("Location: buyer-messages.php");
    exit();
}


/*
 * A buyer may review only after completing an order
 * for this exact product and seller.
 */
$orderStmt = $pdo->prepare(
    "SELECT id
     FROM orders

     WHERE buyer_id = ?
     AND seller_id = ?
     AND product_id = ?
     AND status = 'completed'

     LIMIT 1"
);

$orderStmt->execute([
    $buyerId,
    $sellerId,
    $productId
]);

$completedOrder = $orderStmt->fetch();

if(!$completedOrder)
{
    header("Location: buyer-messages.php");
    exit();
}


/*
 * Check for an existing review.
 */
$reviewStmt = $pdo->prepare(
    "SELECT id
     FROM reviews

     WHERE reviewer_id = ?
     AND seller_id = ?
     AND product_id = ?

     LIMIT 1"
);

$reviewStmt->execute([
    $buyerId,
    $sellerId,
    $productId
]);

$existingReview = $reviewStmt->fetch();


/*
 * Process review.
 */
if(isset($_POST['submit_review']))
{
    $rating = $_POST['rating'] ?? '';
    $review = trim($_POST['review'] ?? '');

    if(
        !is_numeric($rating) ||
        (int)$rating < 1 ||
        (int)$rating > 5
    )
    {
        $error =
            "Please select a rating between 1 and 5.";
    }
    elseif($review === '')
    {
        $error =
            "Please write a review.";
    }
    elseif(strlen($review) > 5000)
    {
        $error =
            "Your review is too long.";
    }
    elseif($existingReview)
    {
        $error =
            "You have already reviewed this product.";
    }
    else
    {
        try
        {
            $insertStmt = $pdo->prepare(
                "INSERT INTO reviews
                (
                    reviewer_id,
                    seller_id,
                    product_id,
                    rating,
                    review
                )
                VALUES (?, ?, ?, ?, ?)"
            );

            $insertStmt->execute([
                $buyerId,
                $sellerId,
                $productId,
                (int)$rating,
                $review
            ]);

            $success =
                "Your review was submitted successfully.";

            $existingReview = true;
        }
        catch(PDOException $e)
        {
            $error =
                "The review could not be submitted.";
        }
    }
}


include 'includes/header.php';

?>

<div class="review-page">

    <div class="page-header">

        <div>

            <h1>
                Leave a Review
            </h1>

            <p>
                Share your experience with this SafeTrade seller.
            </p>

        </div>

        <a href="buyer-messages.php" class="secondary-btn">
            Back to Messages
        </a>

    </div>


    <div class="review-form-card">

        <div class="review-product-summary">

            <div>

                <span>
                    Product
                </span>

                <strong>
                    <?= htmlspecialchars($product['product_title']); ?>
                </strong>

            </div>


            <div>

                <span>
                    Seller
                </span>

                <strong>
                    <?= htmlspecialchars($product['seller_name']); ?>
                </strong>

            </div>

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


        <?php if(!$existingReview): ?>

            <form method="POST" class="review-form">

                <div class="form-group">

                    <label for="rating">
                        Rating
                    </label>

                    <select
                        id="rating"
                        name="rating"
                        required>

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

                </div>


                <div class="form-group">

                    <label for="review">
                        Review
                    </label>

                    <textarea
                        id="review"
                        name="review"
                        rows="6"
                        maxlength="5000"
                        placeholder="Describe your experience with the seller..."
                        required></textarea>

                </div>


                <button
                    type="submit"
                    name="submit_review"
                    class="review-submit-btn">

                    Submit Review

                </button>

            </form>

        <?php else: ?>

            <div class="review-complete">

                <h3>
                    Review Submitted
                </h3>

                <p>
                    You have already reviewed this product and seller.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php include 'includes/footer.php'; ?>