<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: my-products.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare(
    "SELECT *
     FROM products
     WHERE id = ? AND user_id = ?"
);

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

$product = $stmt->fetch();

if(!$product)
{
    header("Location: my-products.php");
    exit();
}

if(isset($_POST['update']))
{
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $location = $_POST['location'];

    $stmt = $pdo->prepare(
        "UPDATE products
         SET title = ?,
             description = ?,
             category = ?,
             price = ?,
             location = ?
         WHERE id = ? AND user_id = ?"
    );

    $stmt->execute([
        $title,
        $description,
        $category,
        $price,
        $location,
        $id,
        $_SESSION['user_id']
    ]);

    header("Location: my-products.php");
    exit();
}

include 'includes/header.php';

?>

<div class="form-container">

<h2>
Edit Product
</h2>

<form method="POST">

<input
    type="text"
    name="title"
    value="<?= htmlspecialchars($product['title']); ?>"
    placeholder="Product Name"
    required>

<textarea
    name="description"
    placeholder="Description"
    required><?= htmlspecialchars($product['description']); ?></textarea>

<select
    name="category"
    required>

    <option value="">Choose Category</option>

    <option value="Electronics"
        <?= $product['category'] === 'Electronics' ? 'selected' : ''; ?>>
        Electronics
    </option>

    <option value="Fashion"
        <?= $product['category'] === 'Fashion' ? 'selected' : ''; ?>>
        Fashion
    </option>

    <option value="Vehicles"
        <?= $product['category'] === 'Vehicles' ? 'selected' : ''; ?>>
        Vehicles
    </option>

    <option value="Property"
        <?= $product['category'] === 'Property' ? 'selected' : ''; ?>>
        Property
    </option>

    <option value="Services"
        <?= $product['category'] === 'Services' ? 'selected' : ''; ?>>
        Services
    </option>

</select>

<input
    type="number"
    step="0.01"
    name="price"
    value="<?= htmlspecialchars($product['price']); ?>"
    placeholder="Price"
    required>

<input
    type="text"
    name="location"
    value="<?= htmlspecialchars($product['location']); ?>"
    placeholder="Location"
    required>

<button
    type="submit"
    name="update">

    Update Product

</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>