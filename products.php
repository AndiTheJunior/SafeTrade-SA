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

$sql = "SELECT products.*, users.fullname AS seller_name
        FROM products
        INNER JOIN users
            ON products.user_id = users.id
        WHERE products.status = 'active'";

$params = [];

if($category != '')
{
    $sql .= " AND products.category = ?";
    $params[] = $category;
}

if($search != '')
{
    $sql .= " AND (
        products.title LIKE ?
        OR products.description LIKE ?
        OR products.category LIKE ?
        OR products.location LIKE ?
    )";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY products.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt;

?>

<div class="marketplace-header">

    <h2>
        SafeTrade Marketplace
    </h2>

    <p>
        Browse products from verified SafeTrade sellers.
    </p>

</div>

<form method="GET" class="marketplace-search">

    <input
        type="text"
        name="search"
        value="<?= htmlspecialchars($search); ?>"
        placeholder="Search products..."
    >

    <select name="category">

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

    <a
        href="products.php"
        class="clear-link">
        Clear
    </a>

</form>

<div class="products">

<?php

if($products->rowCount() == 0)
{
?>

    <div class="empty-marketplace">

        <h3>
            No products found
        </h3>

        <p>
            Try changing your search or category filter.
        </p>

    </div>

<?php
}

while($product = $products->fetch())
{
?>

    <div class="card">

        <?php if(!empty($product['image'])): ?>

            <img
                src="uploads/products/<?= htmlspecialchars($product['image']); ?>"
                alt="<?= htmlspecialchars($product['title']); ?>"
            >

        <?php endif; ?>

        <h3>
            <?= htmlspecialchars($product['title']); ?>
        </h3>

        <p>
            R<?= number_format((float)$product['price'], 2); ?>
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
            Seller:
            <?= htmlspecialchars($product['seller_name']); ?>
        </p>

        <p>
            Status:
            <?= htmlspecialchars(ucfirst($product['status'])); ?>
        </p>

        <a
            href="product-details.php?id=<?= (int)$product['id']; ?>">
            View Details
        </a>

    </div>

<?php
}
?>

</div>

<?php include 'includes/footer.php'; ?>