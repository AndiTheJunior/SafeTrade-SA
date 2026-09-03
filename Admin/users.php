<?php

include '../includes/auth.php';
include '../includes/role-auth.php';
include '../config/database.php';

requireRole('admin');

$stmt = $pdo->prepare(
    "SELECT id,
            fullname,
            email,
            phone,
            role,
            verification_status,
            created_at
     FROM users
     ORDER BY id DESC"
);

$stmt->execute();

$users = $stmt;

include '../includes/header.php';

?>

<div class="admin-table-page">

    <div class="page-header">

        <div>

            <h1>
                User Management
            </h1>

            <p>
                View registered SafeTrade users and their account status.
            </p>

        </div>

        <a href="index.php" class="secondary-btn">
            Back to Admin Dashboard
        </a>

    </div>


    <?php if($users->rowCount() == 0): ?>

        <div class="empty-state">

            <h3>
                No Users
            </h3>

            <p>
                There are currently no registered users.
            </p>

        </div>

    <?php else: ?>

        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Verification</th>
                        <th>Registered</th>

                    </tr>

                </thead>

                <tbody>

                    <?php while($user = $users->fetch()): ?>

                        <tr>

                            <td>
                                #<?= (int)$user['id']; ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($user['fullname']); ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['email']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $user['phone'] ?? 'Not provided'
                                ); ?>
                            </td>

                            <td>

                                <span class="user-role-badge role-<?= htmlspecialchars($user['role']); ?>">

                                    <?= htmlspecialchars(
                                        ucfirst($user['role'])
                                    ); ?>

                                </span>

                            </td>

                            <td>

                                <span class="verification-status verification-status-<?= htmlspecialchars($user['verification_status']); ?>">

                                    <?= htmlspecialchars(
                                        ucfirst($user['verification_status'])
                                    ); ?>

                                </span>

                            </td>

                            <td>
                                <?= htmlspecialchars($user['created_at']); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>