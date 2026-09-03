<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('admin');

$message = "";
$messageType = "";


/*
 * Approve verification request
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

        if($stmt->rowCount() === 1)
        {
            $message = "Seller verification approved successfully.";
            $messageType = "success";
        }
        else
        {
            $message = "The verification request could not be approved.";
            $messageType = "error";
        }
    }
}


/*
 * Reject verification request
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

        if($stmt->rowCount() === 1)
        {
            $message = "Verification request rejected.";
            $messageType = "success";
        }
        else
        {
            $message = "The verification request could not be rejected.";
            $messageType = "error";
        }
    }
}


/*
 * Get pending verification requests
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

<div class="verification-page">

    <div class="page-header">

        <div>

            <h1>
                Seller Verification
            </h1>

            <p>
                Review pending SafeTrade seller verification requests.
            </p>

        </div>

        <a href="index.php" class="secondary-btn">
            Back to Admin Dashboard
        </a>

    </div>


    <?php if($message): ?>

        <div class="status-message <?= htmlspecialchars($messageType); ?>">

            <?= htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if($requests->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Pending Requests
            </h3>

            <p>
                There are currently no seller verification requests waiting for review.
            </p>

        </div>

    <?php else: ?>

        <div class="verification-grid">

            <?php while($user = $requests->fetch()): ?>

                <div class="verification-card">

                    <div class="verification-card-header">

                        <h3>
                            <?= htmlspecialchars($user['fullname']); ?>
                        </h3>

                        <span class="pending-badge">
                            Pending
                        </span>

                    </div>


                    <div class="verification-details">

                        <p>
                            <strong>Email:</strong>
                            <?= htmlspecialchars($user['email']); ?>
                        </p>

                        <p>
                            <strong>Phone:</strong>
                            <?= htmlspecialchars($user['phone'] ?? 'Not provided'); ?>
                        </p>

                        <p>
                            <strong>Role:</strong>
                            <?= htmlspecialchars(ucfirst($user['role'])); ?>
                        </p>

                        <p>
                            <strong>Registered:</strong>
                            <?= htmlspecialchars($user['created_at']); ?>
                        </p>

                    </div>


                    <form method="POST" class="verification-actions">

                        <input
                            type="hidden"
                            name="user_id"
                            value="<?= (int)$user['id']; ?>"
                        >

                        <button
                            type="submit"
                            name="approve"
                            class="approve-btn">
                            Approve
                        </button>

                        <button
                            type="submit"
                            name="reject"
                            class="reject-btn">
                            Reject
                        </button>

                    </form>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>