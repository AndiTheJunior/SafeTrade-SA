<?php

include 'includes/auth.php';
include 'config/database.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT fullname, verification_status
     FROM users
     WHERE id = ?"
);

$stmt->execute([
    $user_id
]);

$user = $stmt->fetch();

if(!$user)
{
    header("Location: dashboard.php");
    exit();
}

$message = "";

if(isset($_POST['request_verification']))
{
    if($user['verification_status'] === 'unverified')
    {
        $update = $pdo->prepare(
            "UPDATE users
             SET verification_status = 'pending'
             WHERE id = ?"
        );

        $update->execute([
            $user_id
        ]);

        $message = "Your verification request has been submitted.";

        $user['verification_status'] = 'pending';
    }
}

include 'includes/header.php';

?>

<div class="form-container">

<h2>
Account Verification
</h2>

<p>
Welcome,
<?= htmlspecialchars($user['fullname']); ?>
</p>

<p>

Verification status:

<strong>

<?= htmlspecialchars($user['verification_status']); ?>

</strong>

</p>

<?php if($message): ?>

<p>
<?= htmlspecialchars($message); ?>
</p>

<?php endif; ?>

<?php if($user['verification_status'] === 'unverified'): ?>

<form method="POST">

<button
type="submit"
name="request_verification">

Request Verification

</button>

</form>

<?php elseif($user['verification_status'] === 'pending'): ?>

<p>
Your verification request is waiting for admin approval.
</p>

<?php elseif($user['verification_status'] === 'verified'): ?>

<p>
Your account has been verified.
</p>

<?php endif; ?>

<br>

<a href="dashboard.php">
Back to Dashboard
</a>

</div>

<?php include 'includes/footer.php'; ?>