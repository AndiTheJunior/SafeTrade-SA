<?php

include 'config/database.php';
include 'includes/header.php';

$category = '';

if(isset($_GET['category']))
{
    $category = $_GET['category'];
}

if($category != '')
{
    $stmt = $pdo->prepare(
        "SELECT * FROM products
         WHERE category = ?
         ORDER BY id DESC"
    );

    $stmt->execute([$category]);

    $products = $stmt;
}
else
{
    $products = $pdo->query(
        "SELECT * FROM products
         ORDER BY id DESC"
    );
}

?>

<form method="GET" style="padding:20px;">

<select name="category">

<option value="">
All Categories
</option>

<option value="Electronics">
Electronics
</option>

<option value="Fashion">
Fashion
</option>

<option value="Vehicles">
Vehicles
</option>

<option value="Property">
Property
</option>

<option value="Services">
Services
</option>

</select>

<button type="submit">
Filter
</button>

</form>

<h2 style="padding:20px;">
Marketplace
</h2>

<input
type="text"
id="searchInput"
onkeyup="searchProducts()"
placeholder="Search products..."
style="width:100%;padding:10px;margin-bottom:20px;">

<div class="products">

<?php
while(
$product =
$products->fetch()
)
{
?>

<div class="card">

<img
src="uploads/products/<?=
$product['image']
?>">

<h3>
<?= $product['title']; ?>
</h3>

<p>
R<?= $product['price']; ?>
</p>

<p>
Category:
<?= $product['category']; ?>
</p>

<p>
<?= $product['location']; ?>
</p>

<a href=
"product-details.php?id=
<?= $product['id']; ?>">

View Details

</a>

</div>

<?php } ?>

</div>

<?php include 'includes/footer.php'; ?>