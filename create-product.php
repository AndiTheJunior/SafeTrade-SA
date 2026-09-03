<?php

include 'includes/auth.php';
include 'includes/role-auth.php';
include 'config/database.php';

requireRole('seller');

$error = "";

$title = "";
$description = "";
$category = "";
$price = "";
$location = "";

$allowedCategories = [
    'Electronics',
    'Fashion',
    'Vehicles',
    'Property',
    'Services'
];

if(isset($_POST['create']))
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


    /*
     * Validate image upload.
     */
    if(
        $error === '' &&
        (
            !isset($_FILES['image']) ||
            $_FILES['image']['error'] !== UPLOAD_ERR_OK
        )
    )
    {
        $error = "Please upload a valid product image.";
    }


    if($error === '' && $_FILES['image']['size'] > 5 * 1024 * 1024)
    {
        $error = "Product image must not exceed 5 MB.";
    }


    $imageFileName = null;

    if($error === '')
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
                /*
                 * Generate a new random filename instead of
                 * trusting the original uploaded filename.
                 */
                $extension = $allowedImageTypes[$mimeType];

                $imageFileName =
                    bin2hex(random_bytes(16)) .
                    "." .
                    $extension;
            }
        }
    }


    /*
     * Save the product only after validation succeeds.
     */
    if($error === '')
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

        $destination =
            $uploadDirectory .
            DIRECTORY_SEPARATOR .
            $imageFileName;

        if(
            !move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $destination
            )
        )
        {
            $error =
                "The product image could not be uploaded.";
        }
        else
        {
            try
            {
                $stmt = $pdo->prepare(
                    "INSERT INTO products
                    (
                        user_id,
                        title,
                        description,
                        category,
                        price,
                        image,
                        location
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $_SESSION['user_id'],
                    $title,
                    $description,
                    $category,
                    (float)$price,
                    $imageFileName,
                    $location
                ]);

                $_SESSION['product_message'] =
                    "Product created successfully.";

                $_SESSION['product_message_type'] =
                    "success";

                header("Location: my-products.php");
                exit();
            }
            catch(Exception $e)
            {
                /*
                 * Remove the uploaded image if the database
                 * operation fails.
                 */
                if(file_exists($destination))
                {
                    unlink($destination);
                }

                $error =
                    "The product could not be created.";
            }
        }
    }
}

include 'includes/header.php';

?>

<div class="form-container">

    <h2>
        Create Product
    </h2>

    <p>
        Add a new product to the SafeTrade marketplace.
    </p>


    <?php if($error): ?>

        <div class="status-message error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data"
        onsubmit="return validateProductForm()">


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
            Product Image
        </label>

        <input
            type="file"
            name="image"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            required>


        <p>
            Accepted images: JPG, PNG or WEBP. Maximum size: 5 MB.
        </p>


        <button
            type="submit"
            name="create">

            Create Product

        </button>

    </form>


    <br>

    <a href="my-products.php">
        Back to My Products
    </a>

</div>

<?php include 'includes/footer.php'; ?>