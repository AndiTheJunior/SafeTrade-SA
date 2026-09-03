<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('admin');


$userCount =
$pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();


$productCount =
$pdo->query(
    "SELECT COUNT(*) FROM products"
)->fetchColumn();


$orderCount =
$pdo->query(
    "SELECT COUNT(*) FROM orders"
)->fetchColumn();


$paymentCount =
$pdo->query(
    "SELECT COUNT(*) FROM payments"
)->fetchColumn();


$pendingVerificationCount =
$pdo->query(
    "SELECT COUNT(*)
     FROM users
     WHERE verification_status = 'pending'"
)->fetchColumn();


include '../includes/header.php';

?>

<div class="admin-dashboard">

    <div class="admin-header">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION['fullname']); ?>.
        </p>

        <p>
            Manage SafeTrade users, products, verification,
            orders and payments from one place.
        </p>

    </div>


    <div class="admin-stats">

        <div class="admin-stat-card">

            <h3>
                <?= (int)$userCount; ?>
            </h3>

            <p>
                Total Users
            </p>

        </div>


        <div class="admin-stat-card">

            <h3>
                <?= (int)$productCount; ?>
            </h3>

            <p>
                Total Products
            </p>

        </div>


        <div class="admin-stat-card">

            <h3>
                <?= (int)$orderCount; ?>
            </h3>

            <p>
                Total Orders
            </p>

        </div>


        <div class="admin-stat-card">

            <h3>
                <?= (int)$paymentCount; ?>
            </h3>

            <p>
                Total Payments
            </p>

        </div>


        <div class="admin-stat-card">

            <h3>
                <?= (int)$pendingVerificationCount; ?>
            </h3>

            <p>
                Pending Verification
            </p>

        </div>

    </div>


    <div class="admin-section">

        <h2>
            Administration
        </h2>


        <div class="admin-grid">


            <a href="verification.php" class="admin-card">

                <h3>
                    Seller Verification
                </h3>

                <p>
                    Review and approve or reject seller
                    verification requests.
                </p>

                <?php if($pendingVerificationCount > 0): ?>

                    <span class="admin-badge">
                        <?= (int)$pendingVerificationCount; ?>
                        Pending
                    </span>

                <?php endif; ?>

            </a>


            <a href="orders.php" class="admin-card">

                <h3>
                    Order Monitoring
                </h3>

                <p>
                    Monitor all orders placed on the
                    SafeTrade marketplace.
                </p>

            </a>


            <a href="payments.php" class="admin-card">

                <h3>
                    Payment Monitoring
                </h3>

                <p>
                    View and monitor payment records
                    across the platform.
                </p>

            </a>


            <a href="users.php" class="admin-card">

                <h3>
                    User Management
                </h3>

                <p>
                    View registered SafeTrade users.
                </p>

            </a>


            <a href="products.php" class="admin-card">

                <h3>
                    Product Management
                </h3>

                <p>
                    View products listed on the marketplace.
                </p>

            </a>


            <a href="../products.php" class="admin-card">

                <h3>
                    Marketplace
                </h3>

                <p>
                    View the SafeTrade marketplace as
                    it appears to users.
                </p>

            </a>


        </div>

    </div>


    <div class="admin-actions">

        <a href="../logout.php" class="btn dashboard-logout">
            Logout
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>