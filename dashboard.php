<?php

include 'includes/auth.php';
include 'includes/header.php';

?>

<div class="form-container">

<h2>

Welcome

<?= $_SESSION['fullname']; ?>

</h2>

<p>

You are successfully logged in.

</p>

<?php if($_SESSION['role'] === 'seller'): ?>

<a href="create-product.php">
Add Product
</a>

<br><br>

<?php endif; ?>

<br><br>

<a href="products.php">
View Marketplace
</a>

<br><br>

<a href="logout.php">
Logout
</a>

</div>

<?php include 'includes/footer.php'; ?>