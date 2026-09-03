<?php

include 'includes/auth.php';

if($_SESSION['role'] === 'admin')
{
    header("Location: Admin/index.php");
    exit();
}

include 'includes/header.php';

?>

<div class="dashboard-container">

    <div class="dashboard-header">

        <h1>
            Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?>
        </h1>

        <p>
            Welcome to your SafeTrade SA dashboard.
        </p>

    </div>


    <?php if($_SESSION['role'] === 'seller'): ?>

        <div class="dashboard-grid">

            <a href="create-product.php" class="dashboard-card">

                <h3>Add Product</h3>

                <p>
                    List a new product on the SafeTrade marketplace.
                </p>

            </a>


            <a href="my-products.php" class="dashboard-card">

                <h3>My Products</h3>

                <p>
                    Manage your products and listings.
                </p>

            </a>


            <a href="messages.php" class="dashboard-card">

                <h3>Messages</h3>

                <p>
                    Communicate with potential buyers.
                </p>

            </a>


            <a href="seller/orders.php" class="dashboard-card">

                <h3>Orders</h3>

                <p>
                    View and manage orders for your products.
                </p>

            </a>


            <a href="request-verification.php" class="dashboard-card">

                <h3>Account Verification</h3>

                <p>
                    Request verification and check your verification status.
                </p>

            </a>


            <a href="products.php" class="dashboard-card">

                <h3>Marketplace</h3>

                <p>
                    Browse products available on SafeTrade.
                </p>

            </a>

        </div>


    <?php elseif($_SESSION['role'] === 'buyer'): ?>

        <div class="dashboard-grid">

            <a href="buyer-messages.php" class="dashboard-card">

                <h3>Messages</h3>

                <p>
                    Communicate with SafeTrade sellers.
                </p>

            </a>


            <a href="buyer/orders.php" class="dashboard-card">

                <h3>My Orders</h3>

                <p>
                    View your orders and payment status.
                </p>

            </a>


            <a href="products.php" class="dashboard-card">

                <h3>Marketplace</h3>

                <p>
                    Browse products available for purchase.
                </p>

            </a>

        </div>

    <?php endif; ?>


    <div class="dashboard-actions">

        <a href="logout.php" class="btn dashboard-logout">
            Logout
        </a>

    </div>

</div>

<?php include 'includes/footer.php'; ?>