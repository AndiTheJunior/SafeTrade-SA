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

/*
 * First verify that this product belongs
 * to the currently logged-in seller.
 */
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

/*
 * Delete the product.
 */
$stmt = $pdo->prepare(
    "DELETE FROM products
     WHERE id = ? AND user_id = ?"
);

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

/*
 * Delete the associated image if it exists.
 */
if(!empty($product['image']))
{
    $imagePath = "uploads/products/" . $product['image'];

    if(file_exists($imagePath))
    {
        unlink($imagePath);
    }
}

header("Location: my-products.php");
exit();

?>