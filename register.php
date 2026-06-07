<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/database.php';

if(isset($_POST['register']))
{

$fullname = $_POST['fullname'];
$email = $_POST['email'];
$phone = $_POST['phone'];

$password = password_hash(
$_POST['password'],
PASSWORD_DEFAULT
);

$sql =
"INSERT INTO users
(fullname,email,phone,password)
VALUES (?,?,?,?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
$fullname,
$email,
$phone,
$password
]);

header("Location: login.php");
exit();

}

?>
<h2>Register</h2>

<form method="POST" onsubmit="return validateRegisterForm()">

<input type="text"
name="fullname"
placeholder="Full Name"
required>

<input type="email"
name="email"
placeholder="Email"
required>

<input type="text"
name="phone"
placeholder="Phone"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<button
type="submit"
name="register">

Register

</button>

</form>

</div>

<?php include 'includes/header.php'; ?>