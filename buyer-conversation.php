<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('buyer');

if(
    !isset($_GET['product_id']) ||
    !isset($_GET['user_id']) ||
    !is_numeric($_GET['product_id']) ||
    !is_numeric($_GET['user_id'])
)
{
    header("Location: buyer-messages.php");
    exit();
}

$productId = (int)$_GET['product_id'];
$sellerId = (int)$_GET['user_id'];
$buyerId = (int)$_SESSION['user_id'];

$error = "";
$success = "";


/*
 * Verify seller and product.
 */
$productStmt = $pdo->prepare(
    "SELECT products.id,
            products.title AS product_title,
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
 * Verify conversation exists.
 */
$conversationStmt = $pdo->prepare(
    "SELECT id
     FROM messages

     WHERE product_id = ?

     AND (
        (sender_id = ? AND receiver_id = ?)
        OR
        (sender_id = ? AND receiver_id = ?)
     )

     LIMIT 1"
);

$conversationStmt->execute([
    $productId,
    $buyerId,
    $sellerId,
    $sellerId,
    $buyerId
]);

if(!$conversationStmt->fetch())
{
    header("Location: buyer-messages.php");
    exit();
}


/*
 * Buyer reply.
 */
if(isset($_POST['send_reply']))
{
    $reply = trim($_POST['reply'] ?? '');

    if($reply === '')
    {
        $error = "Please enter a reply.";
    }
    elseif(strlen($reply) > 5000)
    {
        $error = "Your message is too long.";
    }
    else
    {
        $replyStmt = $pdo->prepare(
            "INSERT INTO messages
            (
                sender_id,
                receiver_id,
                product_id,
                message
            )
            VALUES (?, ?, ?, ?)"
        );

        $replyStmt->execute([
            $buyerId,
            $sellerId,
            $productId,
            $reply
        ]);

        $success = "Reply sent successfully.";
    }
}


/*
 * Load conversation.
 */
$messagesStmt = $pdo->prepare(
    "SELECT messages.*,
            sender.fullname AS sender_name,
            receiver.fullname AS receiver_name

     FROM messages

     INNER JOIN users AS sender
        ON messages.sender_id = sender.id

     INNER JOIN users AS receiver
        ON messages.receiver_id = receiver.id

     WHERE messages.product_id = ?

     AND (
        (messages.sender_id = ? AND messages.receiver_id = ?)
        OR
        (messages.sender_id = ? AND messages.receiver_id = ?)
     )

     ORDER BY messages.created_at ASC"
);

$messagesStmt->execute([
    $productId,
    $buyerId,
    $sellerId,
    $sellerId,
    $buyerId
]);

$messages = $messagesStmt;


/*
 * Review only after completed order.
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


include 'includes/header.php';

?>

<div class="conversation-page">

    <div class="page-header">

        <div>

            <h1>
                Conversation
            </h1>

            <p>
                <strong>Product:</strong>
                <?= htmlspecialchars($product['product_title']); ?>
            </p>

            <p>
                <strong>Seller:</strong>
                <?= htmlspecialchars($product['seller_name']); ?>
            </p>

        </div>

        <a href="buyer-messages.php" class="secondary-btn">
            Back to Messages
        </a>

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


    <div class="conversation-box">

        <?php while($message = $messages->fetch()): ?>

            <div class="message-row
                <?= $message['sender_id'] == $buyerId
                    ? 'message-own'
                    : 'message-other'; ?>">

                <div class="message-bubble">

                    <strong>
                        <?= htmlspecialchars($message['sender_name']); ?>
                    </strong>

                    <p>
                        <?= nl2br(
                            htmlspecialchars($message['message'])
                        ); ?>
                    </p>

                    <small>
                        <?= htmlspecialchars($message['created_at']); ?>
                    </small>

                </div>

            </div>

        <?php endwhile; ?>

    </div>


    <div class="conversation-reply">

        <h2>
            Reply
        </h2>

        <form method="POST">

            <textarea
                name="reply"
                rows="5"
                maxlength="5000"
                placeholder="Write your reply..."
                required></textarea>

            <button
                type="submit"
                name="send_reply">

                Send Reply

            </button>

        </form>

    </div>


    <div class="conversation-review-action">

        <?php if($completedOrder): ?>

            <a
                href="review-seller.php?product_id=<?= $productId; ?>&seller_id=<?= $sellerId; ?>"
                class="home-secondary-btn">

                Leave a Review

            </a>

        <?php else: ?>

            <p>
                You can review this seller after your order
                for this product has been completed.
            </p>

        <?php endif; ?>

    </div>

</div>

<?php include 'includes/footer.php'; ?>