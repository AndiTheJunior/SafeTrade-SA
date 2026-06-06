<?php

include 'config/database.php';

$id = $_GET['id'];

$stmt =
$pdo->prepare(
"SELECT * FROM products
WHERE id=?"
);

$stmt->execute([$id]);

$product =
$stmt->fetch();

include 'includes/header.php';

?>

<div class="form-container">

<img
src="uploads/products/<?= $product['image']; ?>"
style="width:100%;">

<h2>
<?= $product['title']; ?>
</h2>

<p>
<?= $product['description']; ?>
</p>

<p>
R<?= $product['price']; ?>
</p>

<p>
<?= $product['location']; ?>
</p>

</div>

<?php include 'includes/footer.php'; ?>