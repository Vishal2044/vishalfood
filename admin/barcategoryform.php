<?php
session_start();
include('confi.php');
include('function.php');

// Check if the user is logged in
if (!isset($_SESSION['Login'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
$category = "";
$order_number = "";
$id = "";

// Fetch existing category data if the ID is provided
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM barcategory WHERE id='$id'"));
    $category = htmlspecialchars($row['category']);
    $order_number = htmlspecialchars($row['order_number']);
}

// Handle form submission
if (isset($_POST['submit'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $order_number = mysqli_real_escape_string($conn, $_POST['order_number']);
    $added_on = date('Y-m-d H:i:s');
    
    // Check if the category already exists
    if ($id == '') {
        $sql = "SELECT * FROM barcategory WHERE category='$category'";
    } else {
        $sql = "SELECT * FROM barcategory WHERE category='$category' AND id != '$id'";
    }

    if (mysqli_num_rows(mysqli_query($conn, $sql)) > 0) {
        $msg = "Category already added";
    } else {
        if ($id == '') {
            // Insert new category
            $query = "INSERT INTO barcategory(`category`, `order_number`, `status`, `added_on`) 
                      VALUES('$category', '$order_number', 1, '$added_on')";
        } else {
            // Update existing category
            $query = "UPDATE barcategory SET category='$category', order_number='$order_number' WHERE id='$id'";
        }

        if (mysqli_query($conn, $query)) {
            header("Location: barcategory.php");
            exit();
        } else {
            $msg = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Form</title>
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
            text-transform: capitalize;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <?php include('adminhead.php'); ?>
</div>

<section class="home-section">
    <nav>
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard">Add New Category</span>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class='bx bx-search'></i>
        </div>
        <?php include('hotel_name.php') ?>
    </nav>

    <div class="home-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <p>
                        <a href="category.php">Bar Category / </a> 
                        <span>Add New Category</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="overview-boxes">
            <div class="category_form">
                <div class="right-side">
                    <h4 class="text-info">Add New Category</h4>
                </div>
            </div>
        </div>
        <div class="container center-side">
            <div class="row">
                <div class="col">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <form class="forms-sample" method="post">
                                    <div class="form-group">
                                        <label for="exampleInputName1">Category</label>
                                        <input type="text" class="form-control" placeholder="ex: wine, vodka" name="category" required value="<?php echo $category ?>">
                                        <div class="error mt8 text-danger"><?php echo $msg ?></div>
                                    </div>
                                    <div class="form-group pt-3">
                                        <label for="exampleInputEmail3">Order Number</label>
                                        <input type="text" class="form-control" placeholder="Order Number" name="order_number" value="<?php echo $order_number ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2 mt-4" name="submit" style="margin-left: 500px;">Submit</button>
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
    sidebarBtn.onclick = function() {
        sidebar.classList.toggle("active");
        if(sidebar.classList.contains("active")){
            sidebarBtn.classList.replace("bx-menu" ,"bx-menu-alt-right");
        } else {
            sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
