<?php
session_start();
include 'confi.php';
include 'function.php';

// Handle Restaurant Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restaurant-status'])) {
    $status = $_POST['restaurant-status'];
    $query = "UPDATE restaurant_time SET status = ? WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Restaurant status updated successfully";
    } else {
        $_SESSION['message'] = "Failed to update restaurant status";
    }
    $stmt->close();
}

// Handle Bar Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bar-status'])) {
    $bar_status = $_POST['bar-status'];
    $bar_update_query = "UPDATE bar_time SET status = ? WHERE id = 1";
    $bar_stmt = $conn->prepare($bar_update_query);
    $bar_stmt->bind_param("s", $bar_status);
    if ($bar_stmt->execute()) {
        $_SESSION['message'] = "Bar status updated successfully";
    } else {
        $_SESSION['message'] = "Failed to update bar status";
    }
    $bar_stmt->close();
}

// Fetch Restaurant Status
$status_query = "SELECT status, open_time, close_time FROM restaurant_time WHERE id=1";
$status_result = $conn->query($status_query);
$status_row = $status_result->fetch_assoc();
$current_status = $status_row['status'];
$open_time = $status_row['open_time'];
$close_time = $status_row['close_time'];

// Fetch Bar Status
$bar_status_query = "SELECT status, open_time, close_time FROM bar_time WHERE id=1";
$bar_status_result = $conn->query($bar_status_query);
$bar_row = $bar_status_result->fetch_assoc();
$bar_status = $bar_row['status'];
$bar_open_time = $bar_row['open_time'];
$bar_close_time = $bar_row['close_time'];

// SQL query to fetch admin user data
$sql = "SELECT id, username, contac_number, email, addres, password, join_date FROM admin_ragister";
$result = $conn->query($sql);

// Check current time for automatic status update
date_default_timezone_set('Asia/Kolkata'); // Set your timezone here
$current_time = date('H:i:s');

// Convert restaurant times to timestamps for comparison
$open_time_ts = strtotime($open_time);
$close_time_ts = strtotime($close_time);
$current_time_ts = strtotime($current_time);

// Update the current restaurant status based on time
if ($current_time_ts >= $open_time_ts && $current_time_ts < $close_time_ts) {
    $current_status = 'open';
} else {
    $current_status = 'close';
}

// Update restaurant status in the database if it changed
$query = "UPDATE restaurant_time SET status = ? WHERE id = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_status);
$stmt->execute();
$stmt->close();

// Convert bar times to timestamps for comparison
$bar_open_time_ts = strtotime($bar_open_time);
$bar_close_time_ts = strtotime($bar_close_time);

// Update the current bar status based on time
if ($current_time_ts >= $bar_open_time_ts && $current_time_ts < $bar_close_time_ts) {
    $bar_status = 'open';
} else {
    $bar_status = 'close';
}

// Update bar status in the database if it changed
$bar_query = "UPDATE bar_time SET status = ? WHERE id = 1";
$bar_stmt = $conn->prepare($bar_query);
$bar_stmt->bind_param("s", $bar_status);
$bar_stmt->execute();
$bar_stmt->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="admin.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10"> <!-- Auto reload every 10 seconds -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .category_form {
            margin-left: 500px;
        }

        .form-control {
            width: 100%;
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
            <span class="dashboard">Admin Profile</span>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class='bx bx-search'></i>
        </div>
        <!-- HOTEL NAME hotel_name.php -->
        <?php include 'hotel_name.php'?>
    </nav>

    <div class="home-content">
        <div class="overview-boxes">
            <div class="category_form">
                <div class="right-side">
                    <h4 class="text-info">Admin Profile</h4>
                </div>
            </div>
        </div>
        <div class="container center-side text-center">
            <div class="row">
                <div class="col">
                    <div class="container mt-3">
                        <table class="table table-striped table-bordered">
                            <thead class="table-info">
                                <tr>
                                    <th>Username</th>
                                    <th>Contact Number</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Join Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["contac_number"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["addres"]) . "</td>";

        // Convert and format the join_date
        $joinDate = new DateTime($row["join_date"]);
        echo "<td>" . $joinDate->format('d-m-Y') . "</td>";
        echo "<td>";
        echo '<a href="update_admin.php?id=' . $row["id"] . '" class="btn btn-primary">Modify</a>'; // Modify button
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No results found</td></tr>";
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="container pt-3">
            <div class="row">
                <div class="col">
                    <h5><b>Contact Us</b></h5>
                    <p>Contact Number : +91 9904802044 / +91 9904802044 &nbsp; &nbsp;&nbsp; email: infotech@gmail.com</p>
                </div>
            </div>
        </div>

        <div class="container mt-5">
        <div class="row">
            <div class="col">
                <h5 class="mb-4 text-success" style="margin-right: 920px; white-space: nowrap;">Restaurant Time:
                    <?php
echo htmlspecialchars($open_time) . " TO " . htmlspecialchars($close_time);
?>
                    <a class="btn btn-primary" href="restaurant_time.php">Change Time</a>
                </h5>
            </div>
            <div class="col">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="restaurant-status">Select Restaurant Status:</label>
                        <select class="form-control" id="restaurant-status" name="restaurant-status">
                            <option value="open" class="text-success" <?php echo $current_status == 'open' ? 'selected' : ''; ?>>Restaurant Open</option>
                            <option value="close" class="text-danger" <?php echo $current_status == 'close' ? 'selected' : ''; ?>>Restaurant Close</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Update Status</button>
                </form>
            </div>

            <div class="col">
                <h5 class="mb-4 text-success" style="margin-right: 920px; white-space: nowrap;">Bar Time:
                    <?php
echo htmlspecialchars($bar_open_time) . " TO " . htmlspecialchars($bar_close_time);
?>
                    <a class="btn btn-primary" href="bar_time.php">Change Time</a>
                </h5>
            </div>
            <div class="col">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="bar-status">Select Bar Status:</label>
                        <select class="form-control" id="bar-status" name="bar-status">
                            <option value="open" class="text-success" <?php echo $bar_status == 'open' ? 'selected' : ''; ?>>Bar Open</option>
                            <option value="close" class="text-danger" <?php echo $bar_status == 'close' ? 'selected' : ''; ?>>Bar Close</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Update Status</button>
                </form>
            </div>
        </div>
    </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>