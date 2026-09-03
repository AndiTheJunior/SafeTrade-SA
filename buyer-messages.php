<?php

include 'includes/auth.php';
include 'config/database.php';

/*
 * Get conversations involving the currently
 * logged-in buyer.
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

<div class="form-container">

<h2>
My Messages
</h2>

<?php

if($messages->rowCount() == 0)
{
?>

<p>
You have no messages yet.
</p>

<?php
}

while($message = $messages->fetch())
{
?>

<div class="card">

<h3>
<?= htmlspecialchars($message['product_title']); ?>
</h3>

<p>
From:
<?= htmlspecialchars($message['sender_name']); ?>
</p>

<p>
To:
<?= htmlspecialchars($message['receiver_name']); ?>
</p>

<p>
<?= htmlspecialchars($message['message']); ?>
</p>

<p>
Sent:
<?= htmlspecialchars($message['created_at']); ?>
</p>

<a href="buyer-conversation.php?product_id=<?= (int)$message['product_id']; ?>&user_id=<?= (int)(
    $message['sender_id'] == $_SESSION['user_id']
    ? $message['receiver_id']
    : $message['sender_id']
); ?>">
View Conversation
</a>

</div>

<br>

<?php
}

?>

<a href="dashboard.php">
Back to Dashboard
</a>

</div>

<?php include 'includes/footer.php'; ?>