<?php
session_start();
include 'confi.php';
include 'function.php';

// Check if the user is logged in
if (!isset($_SESSION['Login'])) {
    header("Location: login.php");
}

$msg = "";
$category_id = "";
$drink = "";
$drink_detail = "";
$drink_price = "";
$type = "";
$id = "";

// Check if an ID is passed, meaning we are updating an existing record
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $id = $_GET['id'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM barmenu WHERE id='$id'"));
    $category_id = $row['category_id'];
    $drink = $row['drink'];
    $drink_detail = $row['drink_detail'];
    $drink_price = $row['drink_price'];
}

// Handle form submission
if (isset($_POST['submit'])) {
    $category_id = $_POST['category_id'];
    $drink = $_POST['drink'];
    $drink_detail = $_POST['drink_detail'];
    $drink_price = $_POST['drink_price'];

    // Check if the drink already exists in the table, excluding the current record if updating
if ($id == '') {
    $sql = "SELECT * FROM barmenu WHERE drink='$drink'";
} else {
    $sql = "SELECT * FROM barmenu WHERE drink='$drink' AND id!='$id'";
}

$result = mysqli_query($conn, $sql);

// Check if the query failed and log the error
if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

// Proceed if the query is successful
if (mysqli_num_rows($result) > 0) {
    $msg = "drink already added";
} else {
    // Insert or update the record
    if ($id == '') {
        mysqli_query($conn, "INSERT INTO barmenu (category_id, drink, drink_detail, status, drink_price) VALUES ('$category_id', '$drink', '$drink_detail', 1, '$drink_price')");
    } else {
        mysqli_query($conn, "UPDATE barmenu SET category_id='$category_id', drink='$drink', drink_detail='$drink_detail', drink_price='$drink_price' WHERE id='$id'");
    }

    // Redirect to the menu page after successful insert/update
    redirect('barmenu.php');
}
}

// Fetch the active categories from the barcategory table
$result_category = mysqli_query($conn, "SELECT * FROM barcategory WHERE status='1' ORDER BY category ASC");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Bar Menu Form</title>
    <link rel="stylesheet" href="admin.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .category_form {
            margin-left: 500px;
        }

        .form-control {
            width: 100%;
        }

        * {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <?php include 'adminhead.php';?>
</div>

<section class="home-section">
    <nav>
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard">Add Menu Items</span>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class='bx bx-search'></i>
        </div>
        <?php include 'hotel_name.php'?>
    </nav>

    <div class="home-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <p>
                        <a href="barmenu.php">Menu / </a>
                        <span href="#">Add New Menu</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="overview-boxes">
            <div class="category_form">
                <div class="right-side">
                    <h4 class="text-info">Add New Items</h4>
                </div>
            </div>
        </div>
        <div class="container center-side">
            <div class="row">
                <div class="col">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <form class="forms-sample" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="exampleInputName1">Category</label>
                                        <select class="form-control" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php
while ($row_category = mysqli_fetch_assoc($result_category)) {
    if ($row_category['id'] == $category_id) {
        echo "<option value='" . $row_category['id'] . "' selected>" . $row_category['category'] . "</option>";
    } else {
        echo "<option value='" . $row_category['id'] . "'>" . $row_category['category'] . "</option>";
    }
}
?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputName1">Menu Items</label>
                                        <input type="text" class="form-control" placeholder="Items Name" name="drink" required value="<?php echo $drink ?>">
                                        <div class="error mt8"><?php echo $msg ?></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail3" required>Item Detail</label>
                                        <textarea name="drink_detail" class="form-control" placeholder="drink Detail"><?php echo $drink_detail ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail3" required>Item Price</label>
                                        <input type="number" class="form-control" placeholder="Price" name="drink_price" required value="<?php echo $drink_price ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-4 text-center" style="margin-left: 500px;" name="submit">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    sidebarBtn.onclick = function () {
        sidebar.classList.toggle("active");
        if (sidebar.classList.contains("active")) {
            sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else {
            sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
