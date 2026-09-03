<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

if(
    !isset($_GET['product_id']) ||
    !isset($_GET['user_id']) ||
    !is_numeric($_GET['product_id']) ||
    !is_numeric($_GET['user_id'])
)
{
    header("Location: messages.php");
    exit();
}

$productId = (int)$_GET['product_id'];
$buyerId = (int)$_GET['user_id'];
$sellerId = (int)$_SESSION['user_id'];

$error = "";
$success = "";


/*
 * Verify that this product belongs to the
 * currently logged-in seller.
 */
$productStmt = $pdo->prepare(
    "SELECT products.id,
            products.title AS product_title,
            users.fullname AS buyer_name

     FROM products

     INNER JOIN messages
        ON messages.product_id = products.id

     INNER JOIN users
        ON users.id = ?

     WHERE products.id = ?
     AND products.user_id = ?
     AND users.role = 'buyer'

     AND (
        (messages.sender_id = ? AND messages.receiver_id = ?)
        OR
        (messages.sender_id = ? AND messages.receiver_id = ?)
     )

     LIMIT 1"
);

$productStmt->execute([
    $buyerId,
    $productId,
    $sellerId,
    $buyerId,
    $sellerId,
    $sellerId,
    $buyerId
]);

$product = $productStmt->fetch();

if(!$product)
{
    header("Location: messages.php");
    exit();
}


/*
 * Process seller reply.
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
            $sellerId,
            $buyerId,
            $productId,
            $reply
        ]);

        $success = "Reply sent successfully.";
    }
}


/*
 * Get complete conversation.
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
                <strong>Buyer:</strong>
                <?= htmlspecialchars($product['buyer_name']); ?>
            </p>

        </div>

        <a href="messages.php" class="secondary-btn">
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
                <?= $message['sender_id'] == $sellerId
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

</div>

<?php include 'includes/footer.php'; ?>