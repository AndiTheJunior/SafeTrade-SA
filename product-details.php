<?php

if(session_status() === PHP_SESSION_NONE)
{
    session_start();
}

include 'config/database.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: products.php");
    exit();
}

$productId = (int)$_GET['id'];


/*
 * Get product and seller information.
 */
$stmt = $pdo->prepare(
    "SELECT products.*,
            users.fullname AS seller_name,
            users.verification_status AS seller_verification_status
     FROM products
     INNER JOIN users
        ON products.user_id = users.id
     WHERE products.id = ?"
);

$stmt->execute([
    $productId
]);

$product = $stmt->fetch();

if(!$product)
{
    header("Location: products.php");
    exit();
}


$isLoggedIn =
    isset($_SESSION['user_id']);

$isSeller =
    $isLoggedIn &&
    $_SESSION['user_id'] == $product['user_id'];

$isAdmin =
    $isLoggedIn &&
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'admin';


/*
 * Sold products are hidden from normal marketplace users.
 * The owner and admin may still view them.
 */
if(
    $product['status'] !== 'active' &&
    !$isSeller &&
    !$isAdmin
)
{
    header("Location: products.php");
    exit();
}


$error = "";
$success = "";


/*
 * Place an order.
 */
if(isset($_POST['place_order']))
{
    if(!$isLoggedIn)
    {
        $error = "Please log in before placing an order.";
    }
    elseif(
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'buyer'
    )
    {
        $error = "Only buyers can place orders.";
    }
    elseif($isSeller)
    {
        $error = "You cannot order your own product.";
    }
    elseif($product['status'] !== 'active')
    {
        $error = "This product is no longer available.";
    }
    else
    {
        try
        {
            $pdo->beginTransaction();

            /*
             * Lock product while confirming availability.
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

            $orderProduct =
                $orderProductStmt->fetch();

            if(!$orderProduct)
            {
                throw new Exception(
                    "Product not found."
                );
            }

            if($orderProduct['status'] !== 'active')
            {
                throw new Exception(
                    "This product is no longer available."
                );
            }


            /*
             * Prevent another active order
             * for the same product.
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
                throw new Exception(
                    "This product already has an active order."
                );
            }


            /*
             * Create order using current price.
             */
            $orderStmt = $pdo->prepare(
                "INSERT INTO orders
                (
                    buyer_id,
                    seller_id,
                    product_id,
                    amount,
                    status
                )
                VALUES (?, ?, ?, ?, 'pending')"
            );

            $orderStmt->execute([
                $_SESSION['user_id'],
                $orderProduct['user_id'],
                $orderProduct['id'],
                $orderProduct['price']
            ]);


            /*
             * Reserve product.
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
                throw new Exception(
                    "The product is no longer available."
                );
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
 * Send message to seller.
 */
if(isset($_POST['send_message']))
{
    $message =
        trim($_POST['message'] ?? '');

    if(!$isLoggedIn)
    {
        $error =
            "Please log in before contacting the seller.";
    }
    elseif(
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'buyer'
    )
    {
        $error =
            "Only buyers can contact sellers from a product listing.";
    }
    elseif($isSeller)
    {
        $error =
            "You cannot message yourself.";
    }
    elseif($message === '')
    {
        $error =
            "Please enter a message.";
    }
    else
    {
        $messageStmt = $pdo->prepare(
            "INSERT INTO messages
            (
                sender_id,
                receiver_id,
                product_id,
                message
            )
            VALUES (?, ?, ?, ?)"
        );

        $messageStmt->execute([
            $_SESSION['user_id'],
            $product['user_id'],
            $product['id'],
            $message
        ]);

        $success =
            "Message sent successfully.";
    }
}


/*
 * Seller rating summary.
 */
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

$reviewSummary =
    $reviewStmt->fetch();


/*
 * Reviews for this product.
 */
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


include 'includes/header.php';

?>

<div class="product-details-page">

    <div class="product-details-top">

        <a href="products.php" class="secondary-btn">
            Back to Marketplace
        </a>

        <?php if($isSeller): ?>

            <a
                href="edit-product.php?id=<?= (int)$product['id']; ?>"
                class="secondary-btn">

                Edit Product

            </a>

        <?php endif; ?>

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


    <div class="product-detail-layout">

        <div class="product-detail-image">

            <?php if(!empty($product['image'])): ?>

                <img
                    src="uploads/products/<?= htmlspecialchars($product['image']); ?>"
                    alt="<?= htmlspecialchars($product['title']); ?>"
                >

            <?php else: ?>

                <div class="product-no-image">
                    No product image
                </div>

            <?php endif; ?>

        </div>


        <div class="product-detail-content">

            <div class="product-detail-heading">

                <div>

                    <span class="product-category-label">
                        <?= htmlspecialchars($product['category']); ?>
                    </span>

                    <h1>
                        <?= htmlspecialchars($product['title']); ?>
                    </h1>

                </div>


                <span class="product-status product-status-<?= htmlspecialchars($product['status']); ?>">

                    <?= htmlspecialchars(
                        ucfirst($product['status'])
                    ); ?>

                </span>

            </div>


            <div class="product-detail-price">

                R<?= number_format(
                    (float)$product['price'],
                    2
                ); ?>

            </div>


            <p class="product-description">
                <?= nl2br(
                    htmlspecialchars(
                        $product['description']
                    )
                ); ?>
            </p>


            <div class="product-info-grid">

                <div>

                    <span>
                        Location
                    </span>

                    <strong>
                        <?= htmlspecialchars($product['location']); ?>
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


            <div class="seller-trust-row">

                <?php if(
                    $product['seller_verification_status']
                    === 'verified'
                ): ?>

                    <span class="seller-verified-badge">
                        &#10003; Verified Seller
                    </span>

                <?php else: ?>

                    <span class="seller-unverified-badge">
                        Unverified Seller
                    </span>

                <?php endif; ?>


                <span class="seller-rating">

                    <?php

                    if($reviewSummary['review_count'] > 0)
                    {
                        $averageRating =
                            round(
                                $reviewSummary['average_rating']
                            );

                        for($i = 1; $i <= 5; $i++)
                        {
                            if($i <= $averageRating)
                            {
                                echo "&#9733;";
                            }
                            else
                            {
                                echo "&#9734;";
                            }
                        }

                        echo " " .
                            number_format(
                                (float)$reviewSummary['average_rating'],
                                1
                            ) .
                            "/5";

                        echo " (" .
                            (int)$reviewSummary['review_count'];

                        echo
                            $reviewSummary['review_count'] == 1
                            ? " review)"
                            : " reviews)";
                    }
                    else
                    {
                        echo "No reviews yet";
                    }

                    ?>

                </span>

            </div>


            <?php if($isSeller): ?>

                <div class="product-owner-notice">

                    <strong>
                        This is your product listing.
                    </strong>

                    <p>
                        Manage it from your seller product dashboard.
                    </p>

                </div>


            <?php elseif(
                $isLoggedIn &&
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'buyer' &&
                $product['status'] === 'active'
            ): ?>

                <div class="product-buyer-actions">

                    <div class="product-order-box">

                        <h3>
                            Buy This Product
                        </h3>

                        <p>
                            Placing an order reserves this product
                            while the seller reviews your request.
                        </p>

                        <form method="POST">

                            <button
                                type="submit"
                                name="place_order"
                                class="product-order-btn"
                                onclick="return confirm('Are you sure you want to place this order?');">

                                Place Order

                            </button>

                        </form>

                    </div>


                    <div class="product-contact-box">

                        <h3>
                            Contact Seller
                        </h3>

                        <form method="POST">

                            <textarea
                                name="message"
                                rows="5"
                                placeholder="Write a message to the seller..."
                                required></textarea>

                            <button
                                type="submit"
                                name="send_message">

                                Send Message

                            </button>

                        </form>

                    </div>

                </div>


            <?php elseif(!$isLoggedIn): ?>

                <div class="product-login-notice">

                    <h3>
                        Interested in this product?
                    </h3>

                    <p>
                        Log in as a buyer to contact the seller
                        or place an order.
                    </p>

                    <a href="login.php" class="home-primary-btn">
                        Login
                    </a>

                    <a href="register.php" class="home-secondary-btn">
                        Create Account
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- REVIEWS -->

    <section class="product-reviews-section">

        <div class="product-reviews-heading">

            <h2>
                Reviews
            </h2>

            <p>
                Feedback from SafeTrade buyers.
            </p>

        </div>


        <?php if($reviews->rowCount() == 0): ?>

            <div class="empty-state">

                <h3>
                    No Reviews Yet
                </h3>

                <p>
                    This product has not received any reviews yet.
                </p>

            </div>

        <?php else: ?>

            <div class="product-review-grid">

                <?php while($review = $reviews->fetch()): ?>

                    <div class="product-review-card">

                        <div class="review-card-heading">

                            <strong>
                                <?= htmlspecialchars(
                                    $review['reviewer_name']
                                ); ?>
                            </strong>

                            <span class="review-stars">

                                <?php

                                for($i = 1; $i <= 5; $i++)
                                {
                                    echo
                                        $i <= $review['rating']
                                        ? "&#9733;"
                                        : "&#9734;";
                                }

                                ?>

                            </span>

                        </div>


                        <p>
                            <?= nl2br(
                                htmlspecialchars(
                                    $review['review']
                                )
                            ); ?>
                        </p>


                        <small>
                            Reviewed:
                            <?= htmlspecialchars(
                                $review['created_at']
                            ); ?>
                        </small>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php endif; ?>

    </section>

</div>

<?php include 'includes/footer.php'; ?>