<?php

session_start();

include 'config/database.php';

$error = "";

if(isset($_POST['login']))
{

$email = $_POST['email'];
$password = $_POST['password'];

$stmt =
$pdo->prepare(
"SELECT * FROM users
WHERE email=?"
);

$stmt->execute([$email]);

$user =
$stmt->fetch();

if(
$user &&
password_verify(
$password,
$user['password']
)
)
{

$_SESSION['user_id'] =
$user['id'];

$_SESSION['fullname'] =
$user['fullname'];

header(
"Location: dashboard.php"
);

exit();

}
else
{

$error =
"Invalid email or password";

}

}

?>

<?php include 'includes/header.php'; ?>

<div class="form-container">

<h2>Login</h2>

<?php if($error): ?>

<p>
<?= $error ?>
</p>

<?php endif; ?>

<form method="POST">

<input
type="email"
name="email"
placeholder="Email"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button
type="submit"
name="login">

Login

</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>