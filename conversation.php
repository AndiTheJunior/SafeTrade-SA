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

$product_id = $_GET['product_id'];
$buyer_id = $_GET['user_id'];

$error = "";
$success = "";

/*
 * Send seller reply.
 */
if(isset($_POST['send_reply']))
{
    $reply = trim($_POST['reply']);

    if($reply == '')
    {
        $error = "Please enter a reply.";
    }
    else
    {
        /*
         * Verify that the buyer actually has
         * a conversation with this seller
         * for this product.
         */
        $stmt = $pdo->prepare(
            "SELECT id
             FROM messages
             WHERE product_id = ?
             AND sender_id = ?
             AND receiver_id = ?
             LIMIT 1"
        );

        $stmt->execute([
            $product_id,
            $buyer_id,
            $_SESSION['user_id']
        ]);

        $existingMessage = $stmt->fetch();

        if(!$existingMessage)
        {
            $error = "Conversation not found.";
        }
        else
        {
            /*
             * Save the seller's reply.
             */
            $stmt = $pdo->prepare(
                "INSERT INTO messages
                (sender_id, receiver_id, product_id, message)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $_SESSION['user_id'],
                $buyer_id,
                $product_id,
                $reply
            ]);

            $success = "Reply sent successfully.";
        }
    }
}

/*
 * Get the conversation.
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
     WHERE messages.product_id = ?
     AND (
         (messages.sender_id = ? AND messages.receiver_id = ?)
         OR
         (messages.sender_id = ? AND messages.receiver_id = ?)
     )
     ORDER BY messages.created_at ASC"
);

$stmt->execute([
    $product_id,
    $buyer_id,
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $buyer_id
]);

$messages = $stmt;

include 'includes/header.php';

?>

<div class="form-container">

<h2>
Conversation
</h2>

<?php if($error): ?>

<p>
<?= htmlspecialchars($error); ?>
</p>

<?php endif; ?>

<?php if($success): ?>

<p>
<?= htmlspecialchars($success); ?>
</p>

<?php endif; ?>

<?php

$firstMessage = $messages->fetch();

if(!$firstMessage)
{
?>

<p>
Conversation not found.
</p>

<a href="messages.php">
Back to Messages
</a>

<?php
}
else
{
?>

<h3>
<?= htmlspecialchars($firstMessage['product_title']); ?>
</h3>

<p>
Conversation with:
<?= htmlspecialchars(
    $firstMessage['sender_id'] == $_SESSION['user_id']
    ? $firstMessage['receiver_name']
    : $firstMessage['sender_name']
); ?>
</p>

<hr>

<?php

do
{
?>

<div class="card">

<p>
<strong>
<?= htmlspecialchars($firstMessage['sender_name']); ?>:
</strong>
</p>

<p>
<?= htmlspecialchars($firstMessage['message']); ?>
</p>

<p>
<?= htmlspecialchars($firstMessage['created_at']); ?>
</p>

</div>

<br>

<?php

}
while($firstMessage = $messages->fetch());

?>

<hr>

<h3>
Reply
</h3>

<form method="POST">

<textarea
name="reply"
placeholder="Write your reply..."
rows="5"
style="width:100%;padding:10px;"
required></textarea>

<br><br>

<button
type="submit"
name="send_reply">
Send Reply
</button>

</form>

<?php
}
?>

<br>

<a href="messages.php">
Back to Messages
</a>

</div>

<?php include 'includes/footer.php'; ?>