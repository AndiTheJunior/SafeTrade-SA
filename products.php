<?php

include 'config/database.php';
include 'includes/header.php';

$category = '';
$search = '';

if(isset($_GET['category']))
{
    $category = trim($_GET['category']);
}

if(isset($_GET['search']))
{
    $search = trim($_GET['search']);
}

/*
 * Build the marketplace query dynamically
 * while still using prepared statements.
 */

$sql = "SELECT * FROM products WHERE 1=1";

$params = [];

if($category != '')
{
    $sql .= " AND category = ?";
    $params[] = $category;
}

if($search != '')
{
    $sql .= " AND (
        title LIKE ?
        OR description LIKE ?
        OR category LIKE ?
        OR location LIKE ?
    )";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$products = $stmt;

?>

<h2 style="padding:20px;">
Marketplace
</h2>

<form method="GET" style="padding:20px;">

<input
type="text"
name="search"
value="<?= htmlspecialchars($search); ?>"
placeholder="Search products..."
style="width:100%;padding:10px;margin-bottom:10px;"
>

<select
name="category"
style="padding:10px;margin-bottom:10px;"
>

<option value="">
All Categories
</option>

<option value="Electronics"
<?= $category === 'Electronics' ? 'selected' : ''; ?>>
Electronics
</option>

<option value="Fashion"
<?= $category === 'Fashion' ? 'selected' : ''; ?>>
Fashion
</option>

<option value="Vehicles"
<?= $category === 'Vehicles' ? 'selected' : ''; ?>>
Vehicles
</option>

<option value="Property"
<?= $category === 'Property' ? 'selected' : ''; ?>>
Property
</option>

<option value="Services"
<?= $category === 'Services' ? 'selected' : ''; ?>>
Services
</option>

</select>

<button type="submit">
Search / Filter
</button>

<a href="products.php">
Clear
</a>

</form>

<div class="products">

<?php

if($products->rowCount() == 0)
{
?>

<p style="padding:20px;">
No products found.
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
>

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
<?= htmlspecialchars($product['location']); ?>
</p>

<a href="product-details.php?id=<?= (int)$product['id']; ?>">
View Details
</a>

</div>

<?php
}
?>

</div>

<?php include 'includes/footer.php'; ?>