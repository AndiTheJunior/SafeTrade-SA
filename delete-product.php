<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

if(
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['product_id']) ||
    !is_numeric($_POST['product_id'])
)
{
    header("Location: my-products.php");
    exit();
}

$productId = (int)$_POST['product_id'];
$imageToDelete = null;

try
{
    $pdo->beginTransaction();

    /*
     * Verify ownership and lock the product.
     */
    $stmt = $pdo->prepare(
        "SELECT id, image
         FROM products
         WHERE id = ?
         AND user_id = ?
         FOR UPDATE"
    );

    $stmt->execute([
        $productId,
        $_SESSION['user_id']
    ]);

    $product = $stmt->fetch();

    if(!$product)
    {
        throw new Exception(
            "Product not found or you do not own this product."
        );
    }


    /*
     * Protect marketplace history.
     *
     * Because orders, messages and reviews reference
     * products, do not physically delete listings that
     * already have activity attached to them.
     */
    $referenceStmt = $pdo->prepare(
        "SELECT
            (SELECT COUNT(*)
             FROM orders
             WHERE product_id = ?)

            +

            (SELECT COUNT(*)
             FROM messages
             WHERE product_id = ?)

            +

            (SELECT COUNT(*)
             FROM reviews
             WHERE product_id = ?)

            AS reference_count"
    );

    $referenceStmt->execute([
        $productId,
        $productId,
        $productId
    ]);

    $referenceCount =
        (int)$referenceStmt->fetchColumn();

    if($referenceCount > 0)
    {
        throw new Exception(
            "This product cannot be deleted because transaction or communication history exists. Mark it as sold instead."
        );
    }


    $deleteStmt = $pdo->prepare(
        "DELETE FROM products
         WHERE id = ?
         AND user_id = ?"
    );

    $deleteStmt->execute([
        $productId,
        $_SESSION['user_id']
    ]);

    if($deleteStmt->rowCount() !== 1)
    {
        throw new Exception(
            "The product could not be deleted."
        );
    }

    $imageToDelete = $product['image'];

    $pdo->commit();


    /*
     * Remove the associated image only after the
     * database deletion succeeds.
     */
    if(!empty($imageToDelete))
    {
        $imagePath =
            "uploads/products/" .
            basename($imageToDelete);

        if(file_exists($imagePath))
        {
            unlink($imagePath);
        }
    }

    $_SESSION['product_message'] =
        "Product deleted successfully.";

    $_SESSION['product_message_type'] =
        "success";
}
catch(Exception $e)
{
    if($pdo->inTransaction())
    {
        $pdo->rollBack();
    }

    $_SESSION['product_message'] =
        $e->getMessage();

    $_SESSION['product_message_type'] =
        "error";
}

header("Location: my-products.php");
exit();