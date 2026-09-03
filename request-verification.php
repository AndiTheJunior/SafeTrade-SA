<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT fullname, email, verification_status
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
$messageType = "";

if(isset($_POST['request_verification']))
{
    if($user['verification_status'] === 'unverified')
    {
        $update = $pdo->prepare(
            "UPDATE users
             SET verification_status = 'pending'
             WHERE id = ?
             AND verification_status = 'unverified'"
        );

        $update->execute([
            $user_id
        ]);

        if($update->rowCount() === 1)
        {
            $message = "Your verification request has been submitted successfully.";
            $messageType = "success";

            $user['verification_status'] = 'pending';
        }
        else
        {
            $message = "Your verification request could not be submitted.";
            $messageType = "error";
        }
    }
}

include 'includes/header.php';

?>

<div class="account-verification-page">

    <div class="page-header">

        <div>

            <h1>
                Account Verification
            </h1>

            <p>
                Manage your SafeTrade seller verification status.
            </p>

        </div>

        <a href="dashboard.php" class="secondary-btn">
            Back to Dashboard
        </a>

    </div>


    <?php if($message): ?>

        <div class="status-message <?= htmlspecialchars($messageType); ?>">

            <?= htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="verification-profile">

        <div class="verification-profile-header">

            <div>

                <h2>
                    <?= htmlspecialchars($user['fullname']); ?>
                </h2>

                <p>
                    <?= htmlspecialchars($user['email']); ?>
                </p>

            </div>


            <span class="verification-status
                verification-status-<?= htmlspecialchars($user['verification_status']); ?>">

                <?= htmlspecialchars(ucfirst($user['verification_status'])); ?>

            </span>

        </div>


        <div class="verification-content">

            <?php if($user['verification_status'] === 'unverified'): ?>

                <h3>
                    Become a Verified Seller
                </h3>

                <p>
                    Request account verification to show buyers that your
                    SafeTrade seller account has been reviewed.
                </p>

                <p>
                    Once submitted, an administrator will review your request.
                </p>

                <form method="POST">

                    <button
                        type="submit"
                        name="request_verification"
                        class="verification-request-btn">

                        Request Verification

                    </button>

                </form>


            <?php elseif($user['verification_status'] === 'pending'): ?>

                <div class="verification-info-box pending">

                    <h3>
                        Verification Pending
                    </h3>

                    <p>
                        Your request has been submitted and is currently
                        waiting for administrator approval.
                    </p>

                    <p>
                        You do not need to submit another request.
                    </p>

                </div>


            <?php elseif($user['verification_status'] === 'verified'): ?>

                <div class="verification-info-box verified">

                    <h3>
                        Verified Seller
                    </h3>

                    <p>
                        Your SafeTrade seller account has been verified.
                    </p>

                    <p>
                        Your verified status can be shown to buyers on
                        your marketplace listings.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>