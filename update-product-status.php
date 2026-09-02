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
 * Verify that the product belongs to
 * the currently logged-in seller.
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
 * Change the product status.
 */
$newStatus = ($product['status'] === 'active')
    ? 'sold'
    : 'active';

$stmt = $pdo->prepare(
    "UPDATE products
     SET status = ?
     WHERE id = ? AND user_id = ?"
);

$stmt->execute([
    $newStatus,
    $id,
    $_SESSION['user_id']
]);

header("Location: my-products.php");
exit();

?>