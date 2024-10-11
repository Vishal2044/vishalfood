<?php
include('./admin/confi.php'); // Include your database connection file
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $room = $conn->real_escape_string($_POST['room']);
    $instruction = isset($_POST['instruction']) ? $conn->real_escape_string($_POST['instruction']) : '';
    $coupon_code = isset($_SESSION['coupon_code']) ? $conn->real_escape_string($_SESSION['coupon_code']) : '';
    $discount_amount = isset($_SESSION['discount']) ? $_SESSION['discount'] : 0;

    // Calculate total amount
    $total_amount = 0;
    $cart = $_SESSION['cart'] ?? [];
    foreach ($cart as $item) {
        $drink_price = $item['drink_price'];
        $quantity = $item['quantity'];
        $vat = ($drink_price * $quantity) * 0.10; // Assuming VAT is 10%
        $item_total = ($drink_price * $quantity) + $vat;
        $total_amount += $item_total;
    }

    // Calculate grand total after discount
    $grand_total = $total_amount - $discount_amount;

    // Insert order details into the barnew_orders table
    $stmt = $conn->prepare("INSERT INTO barnew_orders (name, mobile, room, instruction, coupon_code, discount_amount, grand_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssid", $name, $mobile, $room, $instruction, $coupon_code, $discount_amount, $grand_total);

    if ($stmt->execute()) {
        // Get the last inserted order ID for barnew_orders
        $order_id = $stmt->insert_id;

        // Insert order details into the barsave_orders table
        $save_order_stmt = $conn->prepare("INSERT INTO barsave_orders (name, mobile, room, instruction, coupon_code, discount_amount, grand_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $save_order_stmt->bind_param("sssssid", $name, $mobile, $room, $instruction, $coupon_code, $discount_amount, $grand_total);

        if ($save_order_stmt->execute()) {
            $save_order_id = $save_order_stmt->insert_id; // Get the last inserted order ID for barsave_orders

            // Insert cart items into the order_items table
            foreach ($cart as $item) {
                $drink_name = $conn->real_escape_string($item['drink_name']);
                $drink_price = $item['drink_price'];
                $quantity = $item['quantity'];
                $vat = ($drink_price * $quantity) * 0.10; // Assuming VAT is 10%
                $item_total = ($drink_price * $quantity) + $vat;

                // Insert order items for barnew_orders
                $item_stmt = $conn->prepare("INSERT INTO barnew_order_items (order_id, drink_name, drink_price, quantity, vat, discount_amount, item_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $item_stmt->bind_param("isdiidd", $order_id, $drink_name, $drink_price, $quantity, $vat, $discount_amount, $item_total);
                $item_stmt->execute();

                // Insert order items for barsave_orders
                $save_item_stmt = $conn->prepare("INSERT INTO barsave_order_items (order_id, drink_name, drink_price, quantity, vat, discount_amount, item_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $save_item_stmt->bind_param("isdiidd", $save_order_id, $drink_name, $drink_price, $quantity, $vat, $discount_amount, $item_total);
                $save_item_stmt->execute();
            }

            // Clear the cart
            unset($_SESSION['cart']);
            unset($_SESSION['discount']);
            unset($_SESSION['coupon_code']);
            echo "<div class='alert alert-success' role='alert'>Order placed successfully!</div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error inserting save order: " . $save_order_stmt->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error inserting new order: " . $stmt->error . "</div>";
    }

    // Close all prepared statements
    $stmt->close();
    $save_order_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .alert {
            margin: 20px auto;
            color: green;
            border: 1px solid black;
            width: 80%;
            max-width: 600px;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        h2 {
            color: #343a40;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h2>Order Confirmation</h2>
        <a href="barmenu.php" class="btn btn-primary">Back to Cart</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
