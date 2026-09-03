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
$email = "";

$registrationSuccess =
    isset($_GET['registered']) &&
    $_GET['registered'] === '1';


if(isset($_POST['login']))
{
    $email =
        strtolower(
            trim($_POST['email'] ?? '')
        );

    $password =
        $_POST['password'] ?? '';


    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $error =
            "Please enter a valid email address.";
    }
    elseif($password === '')
    {
        $error =
            "Please enter your password.";
    }
    else
    {
        $stmt = $pdo->prepare(
            "SELECT
                id,
                fullname,
                email,
                password,
                role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->execute([
            $email
        ]);

        $user = $stmt->fetch();


        if(
            $user &&
            password_verify(
                $password,
                $user['password']
            )
        )
        {
            /*
             * Prevent session fixation.
             */
            session_regenerate_id(true);

            $_SESSION['user_id'] =
                $user['id'];

            $_SESSION['fullname'] =
                $user['fullname'];

            $_SESSION['role'] =
                $user['role'];

            header(
                "Location: dashboard.php"
            );

            exit();
        }
        else
        {
            $error =
                "Invalid email or password.";
        }
    }
}


include 'includes/header.php';

?>

<div class="form-container">

    <h2>
        Login
    </h2>

    <p>
        Sign in to your SafeTrade SA account.
    </p>


    <?php if($registrationSuccess): ?>

        <div class="status-message success">

            Your account was created successfully.
            You can now log in.

        </div>

    <?php endif; ?>


    <?php if($error): ?>

        <div class="status-message error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label for="email">
            Email Address
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email); ?>"
            placeholder="Email"
            required
        >


        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Password"
            required
        >


        <button
            type="submit"
            name="login">

            Login

        </button>

    </form>


    <p>
        Don't have an account?

        <a href="register.php">
            Create Account
        </a>
    </p>

</div>

<?php include 'includes/footer.php'; ?>