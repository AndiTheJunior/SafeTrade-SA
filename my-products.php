<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

$stmt = $pdo->prepare(
    "SELECT *
     FROM products
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->execute([
    $_SESSION['user_id']
]);

$products = $stmt;

include 'includes/header.php';

?>

<div class="my-products-page">

    <div class="page-header">

        <div>

            <h1>
                My Products
            </h1>

            <p>
                Manage your SafeTrade marketplace listings.
            </p>

        </div>

        <a href="create-product.php" class="home-primary-btn">
            Add New Product
        </a>

    </div>


    <?php if(isset($_SESSION['product_message'])): ?>

        <div class="status-message <?= htmlspecialchars(
            $_SESSION['product_message_type'] ?? 'success'
        ); ?>">

            <?= htmlspecialchars(
                $_SESSION['product_message']
            ); ?>

        </div>

        <?php

        unset($_SESSION['product_message']);
        unset($_SESSION['product_message_type']);

        ?>

    <?php endif; ?>


    <?php if($products->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Products Yet
            </h3>

            <p>
                You have not listed any products yet.
            </p>

            <a href="create-product.php" class="btn">
                Create Your First Product
            </a>

        </div>

    <?php else: ?>

        <div class="seller-products-grid">

            <?php while($product = $products->fetch()): ?>

                <div class="seller-product-card">

                    <div class="seller-product-image">

                        <?php if(!empty($product['image'])): ?>

                            <img
                                src="uploads/products/<?= htmlspecialchars($product['image']); ?>"
                                alt="<?= htmlspecialchars($product['title']); ?>"
                            >

                        <?php else: ?>

                            <div class="product-no-image">
                                No image
                            </div>

                        <?php endif; ?>


                        <span class="product-status product-status-<?= htmlspecialchars($product['status']); ?>">

                            <?= htmlspecialchars(
                                ucfirst($product['status'])
                            ); ?>

                        </span>

                    </div>


                    <div class="seller-product-content">

                        <span class="product-category-label">
                            <?= htmlspecialchars($product['category']); ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($product['title']); ?>
                        </h3>

                        <div class="seller-product-price">

                            R<?= number_format(
                                (float)$product['price'],
                                2
                            ); ?>

                        </div>


                        <p>
                            <strong>Location:</strong>
                            <?= htmlspecialchars($product['location']); ?>
                        </p>


                        <div class="seller-product-links">

                            <a
                                href="product-details.php?id=<?= (int)$product['id']; ?>"
                                class="seller-product-view">

                                View Details

                            </a>

                            <a
                                href="edit-product.php?id=<?= (int)$product['id']; ?>"
                                class="seller-product-edit">

                                Edit

                            </a>

                        </div>


                        <div class="seller-product-actions">

                            <form
                                method="POST"
                                action="update-product-status.php">

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$product['id']; ?>">

                                <?php if($product['status'] === 'active'): ?>

                                    <button
                                        type="submit"
                                        class="product-status-btn"
                                        onclick="return confirm('Mark this product as sold?');">

                                        Mark as Sold

                                    </button>

                                <?php else: ?>

                                    <button
                                        type="submit"
                                        class="product-status-btn"
                                        onclick="return confirm('Mark this product as active again?');">

                                        Mark as Active

                                    </button>

                                <?php endif; ?>

                            </form>


                            <form
                                method="POST"
                                action="delete-product.php">

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$product['id']; ?>">

                                <button
                                    type="submit"
                                    class="product-delete-btn"
                                    onclick="return confirm('Are you sure you want to permanently delete this product?');">

                                    Delete

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>


    <div class="page-bottom-actions">

        <a href="dashboard.php" class="secondary-btn">
            Back to Dashboard
        </a>

    </div>

</div>

<?php include 'includes/footer.php'; ?>