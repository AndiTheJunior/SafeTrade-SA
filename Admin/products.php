<?php

include '../config/database.php';

$products =
$pdo->query(
"SELECT * FROM products ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>
<head>
<title>Products</title>
</head>

<body>

<h1>All Products</h1>

<table border="1">

<tr>
<th>ID</th>
<th>Title</th>
<th>Category</th>
<th>Price</th>
<th>Location</th>
</tr>

<?php
while($product = $products->fetch())
{
?>

<tr>

<td>
<?php echo $product['id']; ?>
</td>

<td>
<?php echo $product['title']; ?>
</td>

<td>
<?php echo $product['category']; ?>
</td>

<td>
R<?php echo $product['price']; ?>
</td>

<td>
<?php echo $product['location']; ?>
</td>

</tr>

<?php
}
?>

</table>

</body>
</html>