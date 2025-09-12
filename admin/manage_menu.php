<?php
include "../DataBase.php"; // DB connection

// --- ADD CATEGORY ---
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $slug = strtolower(str_replace(" ", "-", $name));
    $description = $_POST['description'];
    $sql = "INSERT INTO menu_categories (name, slug, description) VALUES ('$name', '$slug', '$description')";
    mysqli_query($con, $sql);
    header("Location: manage_menu.php");
    exit;
}

// --- UPDATE CATEGORY ---
if (isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $slug = strtolower(str_replace(" ", "-", $name));
    $description = $_POST['description'];
    $sql = "UPDATE menu_categories SET name='$name', slug='$slug', description='$description' WHERE id=$id";
    mysqli_query($con, $sql);
    header("Location: manage_menu.php");
    exit;
}

// --- DELETE CATEGORY ---
if (isset($_GET['delete_category'])) {
    $id = $_GET['delete_category'];
    mysqli_query($con, "DELETE FROM menu_categories WHERE id=$id");
    header("Location: manage_menu.php");
    exit;
}

// --- ADD ITEM ---
if (isset($_POST['add_item'])) {
    $category_id = $_POST['category_id'];
    $name = $_POST['item_name'];
    $course = $_POST['course'];
    $price = $_POST['price'];
    $description = $_POST['item_description'];

    // Image upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/menu/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/menu/" . $fileName;
        } else {
            die("Upload failed. Check folder permissions.");
        }
    }

    $sql = "INSERT INTO menu_items (category_id, name, course, price, description, image) 
            VALUES ('$category_id','$name','$course','$price','$description','$imagePath')";
    mysqli_query($con, $sql) or die("Insert error: " . mysqli_error($con));
    header("Location: manage_menu.php");
    exit;
}

// --- UPDATE ITEM ---
if (isset($_POST['update_item'])) {
    $id = $_POST['id'];
    $category_id = $_POST['category_id'];
    $name = $_POST['item_name'];
    $course = $_POST['course'];
    $price = $_POST['price'];
    $description = $_POST['item_description'];

    $imageUpdate = "";
    if (!empty($_FILES['image']['name'])) {
        $oldImageQuery = mysqli_query($con, "SELECT image FROM menu_items WHERE id=$id");
        $oldImageData = mysqli_fetch_assoc($oldImageQuery);
        $oldImagePath = "../" . $oldImageData['image'];

        $targetDir = "../uploads/menu/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            if (!empty($oldImageData['image']) && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            $imageUpdate = ", image='uploads/menu/$fileName'";
        } else {
            die("Update failed. Check folder permissions.");
        }
    }

    $sql = "UPDATE menu_items 
            SET category_id='$category_id', name='$name', course='$course', 
                price='$price', description='$description' $imageUpdate
            WHERE id=$id";
    mysqli_query($con, $sql) or die("Update error: " . mysqli_error($con));
    header("Location: manage_menu.php");
    exit;
}

// --- DELETE ITEM ---
if (isset($_GET['delete_item'])) {
    $id = $_GET['delete_item'];
    mysqli_query($con, "DELETE FROM menu_items WHERE id=$id");
    header("Location: manage_menu.php");
    exit;
}

// --- SEARCH ---
$search = "";
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

$categories = mysqli_query($con, "SELECT * FROM menu_categories WHERE name LIKE '%$search%' ORDER BY id ASC");

$items = mysqli_query($con, "SELECT mi.*, mc.name as category_name 
                             FROM menu_items mi 
                             JOIN menu_categories mc ON mi.category_id = mc.id 
                             WHERE mi.name LIKE '%$search%' OR mc.name LIKE '%$search%' 
                             ORDER BY mi.id ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Menu - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #333;
            overflow-y: auto;
        }

        main {
            margin-top: 70px;
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
            display: block;
        }

        .main {
            margin-left: 240px;
            padding: 20px;
            min-height: 100vh;
            overflow-x: auto;
        }

        .header {
            background: #fff;
            padding: 15px 20px;
            border-left: 6px solid #c0392b;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            color: #c0392b;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <?php include __DIR__ . "/admin_sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main">
        <div class="header">
            <h1>Manage Menu</h1>
        </div>

        <!-- Search -->
        <form method="POST" class="d-flex mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search category or item..."
                value="<?php echo $search; ?>">
            <button class="btn btn-danger ms-2">Search</button>
        </form>

        <!-- Add Category -->
        <div class="card p-3 mb-4">
            <h3>Add Category</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-4"><input type="text" name="name" class="form-control"
                            placeholder="Category Name" required></div>
                    <div class="col-md-6"><input type="text" name="description" class="form-control"
                            placeholder="Description"></div>
                    <div class="col-md-2"><button type="submit" name="add_category"
                            class="btn btn-danger w-100">Add</button></div>
                </div>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="card p-3 mb-4">
            <h3>Categories</h3>
            <div class="table-container">
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><?php echo $cat['name']; ?></td>
                            <td><?php echo $cat['description']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editCategory<?php echo $cat['id']; ?>">Edit</button>
                                <a href="?delete_category=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this category?')">Delete</a>
                            </td>
                        </tr>

                        <!-- Edit Category Modal -->
                        <div class="modal fade" id="editCategory<?php echo $cat['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Category</h5>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                            <input type="text" name="name" class="form-control mb-2"
                                                value="<?php echo $cat['name']; ?>" required>
                                            <input type="text" name="description" class="form-control"
                                                value="<?php echo $cat['description']; ?>">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update_category"
                                                class="btn btn-success">Update</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </table>
            </div>
        </div>

        <!-- Add Item -->
        <div class="card p-3 mb-4">
            <h3>Add Item</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            $catList = mysqli_query($con, "SELECT * FROM menu_categories");
                            while ($c = mysqli_fetch_assoc($catList)) {
                                echo "<option value='{$c['id']}'>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3"><input type="text" name="item_name" class="form-control"
                            placeholder="Item Name" required></div>
                    <div class="col-md-2">
                        <select name="course" class="form-control" required>
                            <option>Starter</option>
                            <option>Main Course</option>
                            <option>Dessert</option>
                        </select>
                    </div>
                    <div class="col-md-2"><input type="number" step="0.01" name="price" class="form-control"
                            placeholder="Price" required></div>
                    <div class="col-md-2"><input type="file" name="image" class="form-control" required></div>
                </div>
                <textarea name="item_description" class="form-control" placeholder="Item Description"></textarea>
                <button type="submit" name="add_item" class="btn btn-danger mt-2">Add</button>
            </form>
        </div>

        <!-- Items Table -->
        <div class="card p-3 mb-4">
            <h3>Menu Items</h3>
            <div class="table-container">
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($item = mysqli_fetch_assoc($items)) { ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo $item['category_name']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['course']; ?></td>
                            <td>₹<?php echo $item['price']; ?></td>
                            <td><?php echo $item['description']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editItem<?php echo $item['id']; ?>">Edit</button>
                                <a href="?delete_item=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this item?')">Delete</a>
                            </td>
                        </tr>

                        <!-- Edit Item Modal -->
                        <div class="modal fade" id="editItem<?php echo $item['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Item</h5>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

                                            <!-- Category -->
                                            <select name="category_id" class="form-control mb-2" required>
                                                <option value="">Select Category</option>
                                                <?php
                                                $catList = mysqli_query($con, "SELECT * FROM menu_categories");
                                                while ($c = mysqli_fetch_assoc($catList)) {
                                                    $selected = ($c['id'] == $item['category_id']) ? "selected" : "";
                                                    echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
                                                }
                                                ?>
                                            </select>

                                            <!-- Name -->
                                            <input type="text" name="item_name" class="form-control mb-2"
                                                value="<?php echo $item['name']; ?>" required>

                                            <!-- Course -->
                                            <select name="course" class="form-control mb-2" required>
                                                <option value="Starter" <?php if ($item['course'] == "Starter")
                                                    echo "selected"; ?>>Starter</option>
                                                <option value="Main Course" <?php if ($item['course'] == "Main Course")
                                                    echo "selected"; ?>>Main Course</option>
                                                <option value="Dessert" <?php if ($item['course'] == "Dessert")
                                                    echo "selected"; ?>>Dessert</option>
                                            </select>

                                            <!-- Price -->
                                            <input type="number" step="0.01" name="price" class="form-control mb-2"
                                                value="<?php echo $item['price']; ?>" required>

                                            <!-- Description -->
                                            <textarea name="item_description"
                                                class="form-control mb-2"><?php echo $item['description']; ?></textarea>

                                            <!-- Current image -->
                                            <?php if ($item['image']) { ?>
                                                <p>Current Image:</p>
                                                <img src="<?php echo $item['image']; ?>" alt=""
                                                    style="width:80px; height:auto;">
                                            <?php } ?>

                                            <!-- Upload new -->
                                            <input type="file" name="image" class="form-control mt-2">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update_item" class="btn btn-success">Update</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>