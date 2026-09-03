<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('admin');

$message = "";

/*
 * Approve a verification request.
 */
if(isset($_POST['approve']))
{
    $user_id = $_POST['user_id'];

    if(is_numeric($user_id))
    {
        $stmt = $pdo->prepare(
            "UPDATE users
             SET verification_status = 'verified'
             WHERE id = ?
             AND verification_status = 'pending'"
        );

        $stmt->execute([
            $user_id
        ]);

        $message = "User verification approved.";
    }
}

/*
 * Reject a verification request.
 */
if(isset($_POST['reject']))
{
    $user_id = $_POST['user_id'];

    if(is_numeric($user_id))
    {
        $stmt = $pdo->prepare(
            "UPDATE users
             SET verification_status = 'unverified'
             WHERE id = ?
             AND verification_status = 'pending'"
        );

        $stmt->execute([
            $user_id
        ]);

        $message = "Verification request rejected.";
    }
}

/*
 * Get all pending verification requests.
 */
$stmt = $pdo->prepare(
    "SELECT id, fullname, email, phone, role, created_at
     FROM users
     WHERE verification_status = 'pending'
     ORDER BY id DESC"
);

$stmt->execute();

$requests = $stmt;

include '../includes/header.php';

?>

<div class="form-container">

<h2>
Verification Requests
</h2>

<?php if($message): ?>

<p>
<?= htmlspecialchars($message); ?>
</p>

<?php endif; ?>

<?php

if($requests->rowCount() == 0)
{
?>

<p>
There are no pending verification requests.
</p>

<?php
}

while($user = $requests->fetch())
{
?>

<div class="card">

<h3>
<?= htmlspecialchars($user['fullname']); ?>
</h3>

<p>
Email:
<?= htmlspecialchars($user['email']); ?>
</p>

<p>
Phone:
<?= htmlspecialchars($user['phone'] ?? 'Not provided'); ?>
</p>

<p>
Role:
<?= htmlspecialchars($user['role']); ?>
</p>

<p>
Registered:
<?= htmlspecialchars($user['created_at']); ?>
</p>

<form method="POST">

<input
type="hidden"
name="user_id"
value="<?= (int)$user['id']; ?>">

<button
type="submit"
name="approve">

Approve

</button>

<button
type="submit"
name="reject">

Reject

</button>

</form>

</div>

<br>

<?php
}

?>

<a href="dashboard.php">
Back to Admin Dashboard
</a>

</div>

<?php include '../includes/footer.php'; ?>