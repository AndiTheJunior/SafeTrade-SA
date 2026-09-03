<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('buyer');

/*
 * Get all messages involving the logged-in buyer.
 */
$stmt = $pdo->prepare(
    "SELECT messages.*,
            sender.fullname AS sender_name,
            receiver.fullname AS receiver_name,
            products.title AS product_title

     FROM messages

     INNER JOIN users AS sender
        ON messages.sender_id = sender.id

     INNER JOIN users AS receiver
        ON messages.receiver_id = receiver.id

     INNER JOIN products
        ON messages.product_id = products.id

     WHERE messages.sender_id = ?
        OR messages.receiver_id = ?

     ORDER BY messages.created_at DESC"
);

$stmt->execute([
    $_SESSION['user_id'],
    $_SESSION['user_id']
]);

$messages = $stmt;

include 'includes/header.php';

?>

<div class="messages-page">

    <div class="page-header">

        <div>

            <h1>
                My Messages
            </h1>

            <p>
                View your conversations with SafeTrade sellers.
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
                You have not started any seller conversations yet.
            </p>

            <a href="products.php" class="btn">
                Browse Marketplace
            </a>

        </div>

    <?php else: ?>

        <div class="message-list">

            <?php while($message = $messages->fetch()): ?>

                <?php

                $otherUserId =
                    $message['sender_id'] == $_SESSION['user_id']
                    ? $message['receiver_id']
                    : $message['sender_id'];

                $otherUserName =
                    $message['sender_id'] == $_SESSION['user_id']
                    ? $message['receiver_name']
                    : $message['sender_name'];

                ?>

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
                            Conversation with:
                        </strong>

                        <?= htmlspecialchars($otherUserName); ?>

                    </div>


                    <p class="message-preview-text">
                        <?= htmlspecialchars($message['message']); ?>
                    </p>


                    <a
                        href="buyer-conversation.php?product_id=<?= (int)$message['product_id']; ?>&user_id=<?= (int)$otherUserId; ?>"
                        class="message-open-btn">

                        View Conversation

                    </a>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>