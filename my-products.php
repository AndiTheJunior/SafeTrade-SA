<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

$stmt = $pdo->prepare(
    "SELECT *
     FROM products
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->execute([
    $_SESSION['user_id']
]);

$products = $stmt;

include 'includes/header.php';

?>

<div class="form-container">

<h2>
My Products
</h2>

<a href="create-product.php">
Add New Product
</a>

<br><br>

<?php

if($products->rowCount() == 0)
{
?>

<p>
You have not listed any products yet.
</p>

<?php
}

while($product = $products->fetch())
{
?>

<div class="card">

<?php if(!empty($product['image'])): ?>

<img
src="uploads/products/<?= htmlspecialchars($product['image']); ?>"
style="width:100%;">

<?php endif; ?>

<h3>
<?= htmlspecialchars($product['title']); ?>
</h3>

<p>
R<?= htmlspecialchars($product['price']); ?>
</p>

<p>
Category:
<?= htmlspecialchars($product['category']); ?>
</p>

<p>
Location:
<?= htmlspecialchars($product['location']); ?>
</p>

<p>
Status:
<?= htmlspecialchars($product['status']); ?>
</p>

<a href="product-details.php?id=<?= $product['id']; ?>">
View Details
</a>

<br><br>

<a href="edit-product.php?id=<?= $product['id']; ?>">
Edit Product
</a>

</div>

<br>

<?php
}

?>

</div>

<?php include 'includes/footer.php'; ?>