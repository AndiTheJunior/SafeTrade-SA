<?php

include 'includes/auth.php';
include 'config/database.php';

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

$product_id = $_GET['product_id'];
$other_user_id = $_GET['user_id'];
$current_user_id = $_SESSION['user_id'];

/*
 * Get the conversation between the logged-in buyer
 * and the other user for this product.
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
    $current_user_id,
    $other_user_id,
    $other_user_id,
    $current_user_id
]);

$messages = $stmt;

include 'includes/header.php';

?>

<div class="form-container">

<h2>
Conversation
</h2>

<?php

$firstMessage = $messages->fetch();

if(!$firstMessage)
{
?>

<p>
Conversation not found.
</p>

<a href="buyer-messages.php">
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
    $firstMessage['sender_id'] == $current_user_id
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

<?php
}
?>

<br>

<a href="buyer-messages.php">
Back to Messages
</a>

</div>

<?php include 'includes/footer.php'; ?>