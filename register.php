<?php

if(session_status() === PHP_SESSION_NONE)
{
    session_start();
}

include 'config/database.php';

if(isset($_SESSION['user_id']))
{
    header("Location: dashboard.php");
    exit();
}

$error = "";

$fullname = "";
$email = "";
$phone = "";
$role = "buyer";

$allowedRoles = [
    'buyer',
    'seller'
];

if(isset($_POST['register']))
{
    $fullname = trim($_POST['fullname'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? 'buyer');

    /*
     * Server-side validation.
     */
    if($fullname === '')
    {
        $error = "Please enter your full name.";
    }
    elseif(strlen($fullname) > 100)
    {
        $error = "Full name must not exceed 100 characters.";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $error = "Please enter a valid email address.";
    }
    elseif(strlen($email) > 100)
    {
        $error = "Email address must not exceed 100 characters.";
    }
    elseif($phone === '')
    {
        $error = "Please enter your phone number.";
    }
    elseif(strlen($phone) > 20)
    {
        $error = "Phone number must not exceed 20 characters.";
    }
    elseif(strlen($password) < 8)
    {
        $error = "Password must contain at least 8 characters.";
    }
    elseif(!in_array($role, $allowedRoles, true))
    {
        $error = "Please select a valid account type.";
    }


    /*
     * Check whether the email is already registered.
     */
    if($error === '')
    {
        $checkStmt = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $checkStmt->execute([
            $email
        ]);

        if($checkStmt->fetch())
        {
            $error =
                "An account with this email address already exists.";
        }
    }


    /*
     * Create account.
     */
    if($error === '')
    {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        try
        {
            $stmt = $pdo->prepare(
                "INSERT INTO users
                (
                    fullname,
                    email,
                    phone,
                    password,
                    role
                )
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $fullname,
                $email,
                $phone,
                $passwordHash,
                $role
            ]);

            header(
                "Location: login.php?registered=1"
            );

            exit();
        }
        catch(PDOException $e)
        {
            $error =
                "Your account could not be created. Please try again.";
        }
    }
}

include 'includes/header.php';

?>

<div class="form-container">

    <h2>
        Create Account
    </h2>

    <p>
        Join SafeTrade SA as a buyer or seller.
    </p>


    <?php if($error): ?>

        <div class="status-message error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label for="fullname">
            Full Name
        </label>

        <input
            type="text"
            id="fullname"
            name="fullname"
            value="<?= htmlspecialchars($fullname); ?>"
            placeholder="Full Name"
            maxlength="100"
            required
        >


        <label for="email">
            Email Address
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email); ?>"
            placeholder="Email"
            maxlength="100"
            required
        >


        <label for="phone">
            Phone Number
        </label>

        <input
            type="text"
            id="phone"
            name="phone"
            value="<?= htmlspecialchars($phone); ?>"
            placeholder="Phone"
            maxlength="20"
            required
        >


        <label for="role">
            Account Type
        </label>

        <select
            id="role"
            name="role"
            required>

            <option
                value="buyer"
                <?= $role === 'buyer' ? 'selected' : ''; ?>>

                Buyer

            </option>

            <option
                value="seller"
                <?= $role === 'seller' ? 'selected' : ''; ?>>

                Seller

            </option>

        </select>


        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Minimum 8 characters"
            minlength="8"
            required
        >


        <button
            type="submit"
            name="register">

            Create Account

        </button>

    </form>


    <p>
        Already have an account?

        <a href="login.php">
            Login
        </a>
    </p>

</div>

<?php include 'includes/footer.php'; ?>