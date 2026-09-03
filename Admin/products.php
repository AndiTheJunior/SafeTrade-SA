<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('admin');

$stmt = $pdo->prepare(
    "SELECT products.*,
            users.fullname AS seller_name,
            users.email AS seller_email
     FROM products

     INNER JOIN users
        ON products.user_id = users.id

     ORDER BY products.id DESC"
);

$stmt->execute();

$products = $stmt;

include '../includes/header.php';

?>

<div class="admin-table-page">

    <div class="page-header">

        <div>

            <h1>
                Product Management
            </h1>

            <p>
                View and monitor products listed on SafeTrade.
            </p>

        </div>

        <a href="index.php" class="secondary-btn">
            Back to Admin Dashboard
        </a>

    </div>


    <?php if($products->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Products
            </h3>

            <p>
                There are currently no products in the system.
            </p>

        </div>

    <?php else: ?>

        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Product</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created</th>

                    </tr>

                </thead>

                <tbody>

                    <?php while($product = $products->fetch()): ?>

                        <tr>

                            <td>
                                #<?= (int)$product['id']; ?>
                            </td>

                            <td>

                                <div class="admin-product-cell">

                                    <?php if(!empty($product['image'])): ?>

                                        <img
                                            src="../uploads/products/<?= htmlspecialchars($product['image']); ?>"
                                            alt="<?= htmlspecialchars($product['title']); ?>"
                                        >

                                    <?php endif; ?>

                                    <strong>
                                        <?= htmlspecialchars($product['title']); ?>
                                    </strong>

                                </div>

                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($product['seller_name']); ?>
                                </strong>

                                <small>
                                    <?= htmlspecialchars($product['seller_email']); ?>
                                </small>

                            </td>

                            <td>
                                <?= htmlspecialchars($product['category']); ?>
                            </td>

                            <td>
                                <strong class="table-price">
                                    R<?= number_format(
                                        (float)$product['price'],
                                        2
                                    ); ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($product['location']); ?>
                            </td>

                            <td>

                                <span class="product-status product-status-<?= htmlspecialchars($product['status']); ?>">

                                    <?= htmlspecialchars(
                                        ucfirst($product['status'])
                                    ); ?>

                                </span>

                            </td>

                            <td>
                                <?= htmlspecialchars($product['created_at']); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>