<?php

include '../config/database.php';

$users =
$pdo->query(
"SELECT * FROM users ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>
<head>
<title>Users</title>
</head>

<body>

<h1>Registered Users</h1>

<table border="1">

<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
</tr>

<?php
while($user = $users->fetch())
{
?>

<tr>

<td>
<?php echo $user['id']; ?>
</td>

<td>
<?php echo $user['fullname']; ?>
</td>

<td>
<?php echo $user['email']; ?>
</td>

<td>
<?php echo $user['phone']; ?>
</td>

</tr>

<?php
}
?>

</table>

</body>
</html>