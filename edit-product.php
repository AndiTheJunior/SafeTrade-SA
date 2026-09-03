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

$productId = (int)$_GET['id'];

$allowedCategories = [
    'Electronics',
    'Fashion',
    'Vehicles',
    'Property',
    'Services'
];


/*
 * Get the product and verify ownership.
 */
$stmt = $pdo->prepare(
    "SELECT *
     FROM products
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
    $productId,
    $_SESSION['user_id']
]);

$product = $stmt->fetch();

if(!$product)
{
    header("Location: my-products.php");
    exit();
}


$error = "";

$title = $product['title'];
$description = $product['description'];
$category = $product['category'];
$price = $product['price'];
$location = $product['location'];


/*
 * Process product update.
 */
if(isset($_POST['update']))
{
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');


    /*
     * Validate product information.
     */
    if($title === '')
    {
        $error = "Please enter a product name.";
    }
    elseif(strlen($title) > 255)
    {
        $error = "Product name must not exceed 255 characters.";
    }
    elseif($description === '')
    {
        $error = "Please enter a product description.";
    }
    elseif(!in_array($category, $allowedCategories, true))
    {
        $error = "Please select a valid product category.";
    }
    elseif(!is_numeric($price) || (float)$price <= 0)
    {
        $error = "Please enter a valid price greater than zero.";
    }
    elseif($location === '')
    {
        $error = "Please enter the product location.";
    }
    elseif(strlen($location) > 100)
    {
        $error = "Location must not exceed 100 characters.";
    }


    $newImageFileName = null;
    $newImageDestination = null;


    /*
     * A new image is optional when editing.
     */
    $hasNewImage =
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;


    if($error === '' && $hasNewImage)
    {
        if($_FILES['image']['error'] !== UPLOAD_ERR_OK)
        {
            $error = "The new product image could not be uploaded.";
        }
        elseif($_FILES['image']['size'] > 5 * 1024 * 1024)
        {
            $error = "Product image must not exceed 5 MB.";
        }
        else
        {
            $tmpName = $_FILES['image']['tmp_name'];

            /*
             * Confirm that the uploaded file is actually an image.
             */
            $imageInfo = @getimagesize($tmpName);

            if($imageInfo === false)
            {
                $error = "The uploaded file is not a valid image.";
            }
            else
            {
                $allowedImageTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $mimeType = $imageInfo['mime'];

                if(!isset($allowedImageTypes[$mimeType]))
                {
                    $error =
                        "Only JPG, PNG and WEBP images are allowed.";
                }
                else
                {
                    $extension =
                        $allowedImageTypes[$mimeType];

                    $newImageFileName =
                        bin2hex(random_bytes(16)) .
                        "." .
                        $extension;
                }
            }
        }
    }


    /*
     * Save the replacement image first, if one was supplied.
     */
    if($error === '' && $newImageFileName !== null)
    {
        $uploadDirectory =
            __DIR__ . '/uploads/products';

        if(!is_dir($uploadDirectory))
        {
            mkdir(
                $uploadDirectory,
                0755,
                true
            );
        }

        $newImageDestination =
            $uploadDirectory .
            DIRECTORY_SEPARATOR .
            $newImageFileName;

        if(
            !move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $newImageDestination
            )
        )
        {
            $error =
                "The new product image could not be saved.";
        }
    }


    /*
     * Update the database after validation succeeds.
     */
    if($error === '')
    {
        try
        {
            $imageToStore =
                $newImageFileName !== null
                ? $newImageFileName
                : $product['image'];

            $updateStmt = $pdo->prepare(
                "UPDATE products
                 SET title = ?,
                     description = ?,
                     category = ?,
                     price = ?,
                     location = ?,
                     image = ?
                 WHERE id = ?
                 AND user_id = ?"
            );

            $updateStmt->execute([
                $title,
                $description,
                $category,
                (float)$price,
                $location,
                $imageToStore,
                $productId,
                $_SESSION['user_id']
            ]);


            /*
             * If a replacement image was successfully stored,
             * remove the previous image.
             */
            if(
                $newImageFileName !== null &&
                !empty($product['image'])
            )
            {
                $oldImagePath =
                    __DIR__ .
                    '/uploads/products/' .
                    basename($product['image']);

                if(file_exists($oldImagePath))
                {
                    unlink($oldImagePath);
                }
            }


            $_SESSION['product_message'] =
                "Product updated successfully.";

            $_SESSION['product_message_type'] =
                "success";

            header("Location: my-products.php");
            exit();
        }
        catch(Exception $e)
        {
            /*
             * If the database update fails,
             * remove the newly uploaded image.
             */
            if(
                $newImageDestination !== null &&
                file_exists($newImageDestination)
            )
            {
                unlink($newImageDestination);
            }

            $error =
                "The product could not be updated.";
        }
    }
}


include 'includes/header.php';

?>

<div class="form-container">

    <h2>
        Edit Product
    </h2>

    <p>
        Update your SafeTrade product listing.
    </p>


    <?php if($error): ?>

        <div class="status-message error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if(!empty($product['image'])): ?>

        <p>
            Current Image
        </p>

        <img
            src="uploads/products/<?= htmlspecialchars($product['image']); ?>"
            alt="<?= htmlspecialchars($product['title']); ?>"
            style="width:100%;max-height:250px;object-fit:cover;border-radius:8px;margin-bottom:20px;"
        >

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data">

        <label>
            Product Name
        </label>

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars($title); ?>"
            placeholder="Product Name"
            maxlength="255"
            required>


        <label>
            Description
        </label>

        <textarea
            name="description"
            placeholder="Description"
            required><?= htmlspecialchars($description); ?></textarea>


        <label>
            Category
        </label>

        <select
            name="category"
            required>

            <option value="">
                Choose Category
            </option>

            <?php foreach($allowedCategories as $allowedCategory): ?>

                <option
                    value="<?= htmlspecialchars($allowedCategory); ?>"
                    <?= $category === $allowedCategory ? 'selected' : ''; ?>>

                    <?= htmlspecialchars($allowedCategory); ?>

                </option>

            <?php endforeach; ?>

        </select>


        <label>
            Price
        </label>

        <input
            type="number"
            step="0.01"
            min="0.01"
            name="price"
            value="<?= htmlspecialchars($price); ?>"
            placeholder="Price"
            required>


        <label>
            Location
        </label>

        <input
            type="text"
            name="location"
            value="<?= htmlspecialchars($location); ?>"
            placeholder="Location"
            maxlength="100"
            required>


        <label>
            Replace Product Image
        </label>

        <input
            type="file"
            name="image"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        >

        <p>
            Leave this blank to keep the existing image.
            JPG, PNG and WEBP only. Maximum size: 5 MB.
        </p>


        <button
            type="submit"
            name="update">

            Update Product

        </button>

    </form>


    <br>

    <a href="my-products.php">
        Back to My Products
    </a>

</div>

<?php include 'includes/footer.php'; ?>