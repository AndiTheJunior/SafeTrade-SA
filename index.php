<?php

include 'includes/header.php';

?>

<main>

    <!-- =========================
         HERO SECTION
    ========================== -->

    <section class="home-hero">

        <div class="home-hero-content">

            <span class="home-eyebrow">
                South African C2C Marketplace
            </span>

            <h1>
                Buy and sell with confidence on SafeTrade SA
            </h1>

            <p>
                Discover products from local sellers, communicate directly,
                place orders and manage transactions through one simple
                marketplace.
            </p>

            <div class="home-hero-actions">

                <a href="products.php" class="home-primary-btn">
                    Browse Marketplace
                </a>

                <?php if(isset($_SESSION['user_id'])): ?>

                    <a href="dashboard.php" class="home-secondary-btn">
                        Go to Dashboard
                    </a>

                <?php else: ?>

                    <a href="register.php" class="home-secondary-btn">
                        Create Account
                    </a>

                    <a href="login.php" class="home-text-link">
                        Login
                    </a>

                <?php endif; ?>

            </div>

        </div>


        <div class="home-hero-panel">

            <div class="hero-panel-card">

                <span class="hero-panel-label">
                    SafeTrade SA
                </span>

                <h2>
                    A marketplace designed for safer local trading.
                </h2>

                <div class="hero-feature">

                    <strong>
                        Verified Sellers
                    </strong>

                    <span>
                        Seller accounts can request administrator verification.
                    </span>

                </div>


                <div class="hero-feature">

                    <strong>
                        Direct Messaging
                    </strong>

                    <span>
                        Buyers and sellers can communicate about listed products.
                    </span>

                </div>


                <div class="hero-feature">

                    <strong>
                        Order Tracking
                    </strong>

                    <span>
                        Follow orders from placement through completion.
                    </span>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================
         HOW IT WORKS
    ========================== -->

    <section class="home-section">

        <div class="home-section-heading">

            <span>
                Simple Process
            </span>

            <h2>
                How SafeTrade Works
            </h2>

            <p>
                SafeTrade brings buyers and sellers together through a
                straightforward marketplace workflow.
            </p>

        </div>


        <div class="home-steps">

            <div class="home-step-card">

                <span class="home-step-number">
                    01
                </span>

                <h3>
                    Discover
                </h3>

                <p>
                    Browse marketplace listings and search by product,
                    category or location.
                </p>

            </div>


            <div class="home-step-card">

                <span class="home-step-number">
                    02
                </span>

                <h3>
                    Connect
                </h3>

                <p>
                    View product information and communicate directly with
                    sellers.
                </p>

            </div>


            <div class="home-step-card">

                <span class="home-step-number">
                    03
                </span>

                <h3>
                    Order
                </h3>

                <p>
                    Place an order and wait for the seller to review and
                    accept it.
                </p>

            </div>


            <div class="home-step-card">

                <span class="home-step-number">
                    04
                </span>

                <h3>
                    Complete
                </h3>

                <p>
                    Record payment and track the transaction until the order
                    is completed.
                </p>

            </div>

        </div>

    </section>


    <!-- =========================
         BUYERS AND SELLERS
    ========================== -->

    <section class="home-audience-section">

        <div class="home-audience-card buyer">

            <span class="home-card-label">
                For Buyers
            </span>

            <h2>
                Find what you need
            </h2>

            <p>
                Search listings, contact sellers, place orders and keep track
                of your purchases from your SafeTrade dashboard.
            </p>

            <ul>
                <li>Search and filter marketplace products</li>
                <li>Message sellers directly</li>
                <li>Track your orders</li>
                <li>Record demonstration payments</li>
            </ul>

            <a href="products.php">
                Explore Marketplace
            </a>

        </div>


        <div class="home-audience-card seller">

            <span class="home-card-label">
                For Sellers
            </span>

            <h2>
                Turn products into opportunities
            </h2>

            <p>
                Create listings, communicate with buyers and manage your
                incoming orders from one seller dashboard.
            </p>

            <ul>
                <li>Create and manage product listings</li>
                <li>Receive buyer messages</li>
                <li>Accept, complete or cancel orders</li>
                <li>Request seller verification</li>
            </ul>

            <?php if(isset($_SESSION['user_id'])): ?>

                <a href="dashboard.php">
                    Open Dashboard
                </a>

            <?php else: ?>

                <a href="register.php">
                    Create an Account
                </a>

            <?php endif; ?>

        </div>

    </section>


    <!-- =========================
         TRUST SECTION
    ========================== -->

    <section class="home-trust-section">

        <div class="home-trust-content">

            <span>
                Built Around Trust
            </span>

            <h2>
                More visibility throughout the transaction
            </h2>

            <p>
                SafeTrade combines seller verification, messaging, order
                tracking, reviews and administrative monitoring to create
                a clearer marketplace experience.
            </p>

        </div>


        <div class="home-trust-grid">

            <div>
                <strong>
                    Seller Verification
                </strong>

                <p>
                    Sellers can request verification that is reviewed by
                    a SafeTrade administrator.
                </p>
            </div>


            <div>
                <strong>
                    Order Management
                </strong>

                <p>
                    Buyers and sellers can track the status of marketplace
                    orders.
                </p>
            </div>


            <div>
                <strong>
                    Admin Monitoring
                </strong>

                <p>
                    Administrators can monitor users, products, orders,
                    payments and verification requests.
                </p>
            </div>

        </div>

    </section>


    <!-- =========================
         FINAL CTA
    ========================== -->

    <section class="home-cta">

        <div>

            <h2>
                Ready to explore SafeTrade?
            </h2>

            <p>
                Browse the marketplace and discover products available
                from local sellers.
            </p>

        </div>


        <a href="products.php" class="home-cta-btn">
            View Marketplace
        </a>

    </section>

</main>

<?php include 'includes/footer.php'; ?>