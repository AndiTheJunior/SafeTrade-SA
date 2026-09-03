<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

/*
 * Get messages sent to the logged-in seller.
 */
$stmt = $pdo->prepare(
    "SELECT messages.*,
            users.fullname AS sender_name,
            products.title AS product_title
     FROM messages

     INNER JOIN users
        ON messages.sender_id = users.id

     INNER JOIN products
        ON messages.product_id = products.id

     WHERE messages.receiver_id = ?

     ORDER BY messages.created_at DESC"
);

$stmt->execute([
    $_SESSION['user_id']
]);

$messages = $stmt;

include 'includes/header.php';

?>

<div class="messages-page">

    <div class="page-header">

        <div>

            <h1>
                Seller Messages
            </h1>

            <p>
                View conversations from buyers interested in your products.
            </p>

        </div>

        <a href="dashboard.php" class="secondary-btn">
            Back to Dashboard
        </a>

    </div>


    <?php if($messages->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Messages
            </h3>

            <p>
                You have not received any buyer messages yet.
            </p>

        </div>

    <?php else: ?>

        <div class="message-list">

            <?php while($message = $messages->fetch()): ?>

                <div class="message-preview-card">

                    <div class="message-preview-top">

                        <div>

                            <span class="message-product-label">
                                Product
                            </span>

                            <h3>
                                <?= htmlspecialchars($message['product_title']); ?>
                            </h3>

                        </div>

                        <span class="message-date">
                            <?= htmlspecialchars($message['created_at']); ?>
                        </span>

                    </div>


                    <div class="message-sender">

                        <strong>
                            From:
                        </strong>

                        <?= htmlspecialchars($message['sender_name']); ?>

                    </div>


                    <p class="message-preview-text">
                        <?= htmlspecialchars($message['message']); ?>
                    </p>


                    <a
                        href="conversation.php?product_id=<?= (int)$message['product_id']; ?>&user_id=<?= (int)$message['sender_id']; ?>"
                        class="message-open-btn">

                        View Conversation

                    </a>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>