<?php

include 'includes/auth.php';
include 'config/database.php';

if(isset($_POST['create']))
{

    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $location = $_POST['location'];

    $image = $_FILES['image']['name'];

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        "uploads/products/" . $image
    );

    $stmt = $pdo->prepare(
        "INSERT INTO products
        (user_id, title, description, category, price, image, location)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([

        $_SESSION['user_id'],
        $title,
        $description,
        $category,
        $price,
        $image,
        $location

    ]);

    header("Location: products.php");
    exit();

}

include 'includes/header.php';

?>

<div class="form-container">

    <h2>Create Product</h2>

    <form
       
        method="POST"
        enctype="multipart/form-data"
        onsubmit="return validateProductForm()">

        <input
            type="text"
            name="title"
            placeholder="Product Name"
            required>

        <textarea
            name="description"
            placeholder="Description"
            required></textarea>

        <select
            name="category"
            required>

            <option value="">
                Choose Category
            </option>

            <option value="Electronics">
                Electronics
            </option>

            <option value="Fashion">
                Fashion
            </option>

            <option value="Vehicles">
                Vehicles
            </option>

            <option value="Property">
                Property
            </option>

            <option value="Services">
                Services
            </option>

        </select>

        <input
            type="number"
            step="0.01"
            name="price"
            placeholder="Price"
            required>

        <input
            type="text"
            name="location"
            placeholder="Location"
            required>

        <input
            type="file"
            name="image"
            required>

        <button
            type="submit"
            name="create">

            Create Product

        </button>

    </form>

</div>

<?php include 'includes/footer.php'; ?>