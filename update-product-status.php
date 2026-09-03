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

try
{
    $pdo->beginTransaction();

    /*
     * Lock the product while checking its status.
     */
    $stmt = $pdo->prepare(
        "SELECT id, status
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
     * Active -> Sold
     *
     * Do not allow a manual status change while an
     * active order already exists.
     */
    if($product['status'] === 'active')
    {
        $orderStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM orders
             WHERE product_id = ?
             AND status IN ('pending', 'accepted')"
        );

        $orderStmt->execute([
            $productId
        ]);

        if((int)$orderStmt->fetchColumn() > 0)
        {
            throw new Exception(
                "This product has an active order and its status cannot be changed manually."
            );
        }

        $newStatus = 'sold';
    }


    /*
     * Sold -> Active
     *
     * A product that has a pending, accepted or
     * completed order must not be relisted.
     */
    elseif($product['status'] === 'sold')
    {
        $orderStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM orders
             WHERE product_id = ?
             AND status IN ('pending', 'accepted', 'completed')"
        );

        $orderStmt->execute([
            $productId
        ]);

        if((int)$orderStmt->fetchColumn() > 0)
        {
            throw new Exception(
                "This product cannot be reactivated because it belongs to an existing order."
            );
        }

        $newStatus = 'active';
    }
    else
    {
        throw new Exception(
            "Invalid product status."
        );
    }


    $updateStmt = $pdo->prepare(
        "UPDATE products
         SET status = ?
         WHERE id = ?
         AND user_id = ?"
    );

    $updateStmt->execute([
        $newStatus,
        $productId,
        $_SESSION['user_id']
    ]);

    $pdo->commit();

    $_SESSION['product_message'] =
        "Product status updated successfully.";

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