<?php
include './admin/confi.php';
session_start();

// Initialize the cart if it's not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Function to calculate the cart total
function calculateCartTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $drink_price = $item['drink_price'];
        $quantity = $item['quantity'];
        $vat = ($drink_price * $quantity) * 0.10; // VAT is 10%
        $item_total = ($drink_price * $quantity) + $vat;
        $total += $item_total;
    }
    return $total;
}

// Function to get discount for coupon
function getDiscountForCoupon($coupon_code, $total) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM bar_coupon WHERE coupon_code = ? AND status = '1' AND expired_on >= CURDATE()");
    $stmt->bind_param('s', $coupon_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $discount = 0;

    if ($result->num_rows > 0) {
        $coupon = $result->fetch_assoc();
        $coupon_type = $coupon['coupon_type'];
        $coupon_value = $coupon['coupon_value'];
        $cart_min_value = $coupon['cart_min_value'];

        if ($total >= $cart_min_value) {
            if ($coupon_type == 'P') {
                $discount = ($total * $coupon_value) / 100;
            } else {
                $discount = $coupon_value;
            }
        }
    }

    $stmt->close();
    return $discount;
}

// Calculate total and discount
$total = calculateCartTotal($_SESSION['cart']);
$discount = isset($_SESSION['coupon_code']) ? getDiscountForCoupon($_SESSION['coupon_code'], $total) : 0;
$_SESSION['discount'] = $discount;
$_SESSION['final_price'] = $total - $discount;

// Handle quantity updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_index'])) {
    $item_index = $_POST['item_index'];
    $action = $_POST['action']; // Action can be 'increment' or 'decrement'

    // Update item quantity in the session cart array
    if (isset($_SESSION['cart'][$item_index])) {
        if ($action === 'increment') {
            $_SESSION['cart'][$item_index]['quantity']++;
        } elseif ($action === 'decrement') {
            if ($_SESSION['cart'][$item_index]['quantity'] > 1) {
                $_SESSION['cart'][$item_index]['quantity']--;
            } else {
                // Remove item if quantity becomes zero
                unset($_SESSION['cart'][$item_index]);
            }
        }
    }

    // Recalculate the total and discount
    $total = calculateCartTotal($_SESSION['cart']);
    if (isset($_SESSION['coupon_code'])) {
        $discount = getDiscountForCoupon($_SESSION['coupon_code'], $total);
        $_SESSION['discount'] = $discount;
        $_SESSION['final_price'] = $total - $discount;
    }

    // Redirect back to the cart page
    header("Location: barcart.php");
    exit;
}

// Remove coupon code
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['discount']);
    unset($_SESSION['coupon_code']);
    unset($_SESSION['final_price']);
    header("Location: barcart.php");
    exit;
}

// Check if a coupon is applied
$coupon_applied = isset($_SESSION['discount']) && isset($_SESSION['coupon_code']);
$coupon_code = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .fixed-header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background-color: #343a40;
            color: #fff;
        }

        .fixed-header .navbar-brand {
            color: #ffc107;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .fixed-header .navbar {
            padding: 0;
        }

        .fixed-header .text-info {
            text-align: right;
            font-size: 0.9rem;
        }

        .fixed-header .form-control {
            border-radius: 0;
        }

        .fixed-header .btn-outline-success {
            border-radius: 0;
        }

        h2 {
            color: #007bff;
        }

        table th, table td {
            vertical-align: middle;
        }

        .table thead th {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .invalid-feedback {
            display: block;
        }

        .text-end {
            text-align: right;
        }

        .error {
            color: red;
        }

        .container.coupon {
            display: flex;
            justify-content: center;
            text-align: center;
        }

        .container.coupon a,
        .container.coupon form {
            margin: 5px;
        }

        .success {
            color: green; /* or any other color you prefer for success */
        }
        /* decrement increment css + -*/
        #inc_dec{
            padding-right: 8px;
            border-right-width: 0px;
            padding-left: 8px;
            padding-bottom: o;
            padding-top: o;
            border-left-width: 0px;
            border-bottom-width: 0px;
            border-top-width: 0px;
            padding-bottom: 0px;
            padding-top: 0px;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size:10px ;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .text-end {
                text-align: left;
            }

            .quantity {
                display: flex;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Bar Cart</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Menu Item</th>
                        <th scope="col">Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">VAT (10%)</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $key => $item):
                        $drink_name = $item['drink_name'];
                        $drink_price = $item['drink_price'];
                        $quantity = $item['quantity'];
                        $vat = ($drink_price * $quantity) * 0.10; // VAT is 10%
                        $item_total = ($drink_price * $quantity) + $vat;
                        $grand_total += $item_total;
                    ?>
                    <tr>
                        <th scope="row"><?php echo $key + 1; ?></th>
                        <td><?php echo $drink_name; ?></td>
                        <td><?php echo $drink_price; ?></td>
                        <td>
                            <form method="post" action="barcart.php" style="display:inline;">
                                <input type="hidden" name="item_index" value="<?php echo $key; ?>">
                                <input type="hidden" name="action" value="decrement">
                                <button id="inc_dec" type="submit" class="btn btn-secondary btn-sm">-</button>
                            </form>
                            <?php echo $quantity; ?>
                            <form method="post" action="barcart.php" style="display:inline;">
                                <input type="hidden" name="item_index" value="<?php echo $key; ?>">
                                <input type="hidden" name="action" value="increment">
                                <button id="inc_dec" type="submit" class="btn btn-secondary btn-sm">+</button>
                            </form>
                        </td>
                        <td><?php echo number_format($vat, 2); ?></td>
                        <td><?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="text-start"><b>Grand Total</b></td>
                        <td><b><?php echo number_format($grand_total, 2); ?></b></td>
                    </tr>
                    <tr class="success">
                        <td colspan="5" class="text-start"><b>Discount
                            <?php if ($coupon_code): ?>
                                <small class="success">(<?php echo htmlspecialchars($coupon_code); ?>)</small>
                            <?php endif; ?>
                            </b>
                        </td>
                        <td><b><?php echo number_format($discount, 2); ?></b></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-start"><b>To Pay</b></td>
                        <td><b><?php echo number_format($grand_total - $discount, 2); ?></b></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Apply Coupon Section -->
        <div class="container coupon">
            <a href="barcoupon.php" class="btn btn-primary btn-sm">Apply Coupon</a>
            <?php if ($coupon_applied): ?>
                <form method="post" action="barcart.php" style="display:inline;">
                    <button type="submit" name="remove_coupon" class="btn btn-danger btn-sm">Remove Coupon</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="barmenu.php" class="btn btn-primary btn-sm">Add More Items</a>
        </div>

         <!-- Order Form -->
         <h2 class="mt-5">Order Form</h2>
        <form id="orderForm" method="POST" action="barprocess_order.php" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="mobile" class="form-label">Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile" required>
            </div>
            <div class="mb-3">
                <label for="room" class="form-label">Room Number</label>
                <input type="text" class="form-control" id="room" name="room" required>
            </div>
            <div class="mb-3">
                <label for="instruction" class="form-label">Instruction</label>
                <input type="text" class="form-control" id="instruction" name="instruction">
            </div>
            <div class="text-center m-5">
                <input type="submit" name="submit" class="btn btn-primary" value="Place Order   ">
            </div>
        </form>
    </div>
</body>
</html>
