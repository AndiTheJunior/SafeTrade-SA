<?php

include '../config/database.php';

$userCount =
$pdo->query(
"SELECT COUNT(*) FROM users"
)->fetchColumn();

$productCount =
$pdo->query(
"SELECT COUNT(*) FROM products"
)->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
</head>

<body>

<h1>Admin Dashboard</h1>

<p>
Total Users:
<?php echo $userCount; ?>
</p>

<p>
Total Products:
<?php echo $productCount; ?>
</p>

<p>
<a href="users.php">
View Users
</a>
</p>

<p>
<a href="products.php">
View Products
</a>
</p>

</body>
</html>