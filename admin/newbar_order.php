<?php
session_start();
include 'confi.php';
include 'function.php';

if (!isset($_SESSION['Login'])) {
    header("Location: login.php");
}

// Fetch search query and date range if they exist
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the SQL query with optional search and date range filters
$sql = "SELECT o.id AS order_id, o.name, o.mobile, o.room, o.instruction, o.created_at,
        oi.drink_name, oi.drink_price, oi.quantity, oi.item_total, oi.vat,
        oi.discount_amount, o.coupon_code
        FROM barnew_orders o
        JOIN barnew_order_items oi ON o.id = oi.order_id
        WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (o.id LIKE '%$search_query%' OR o.name LIKE '%$search_query%' OR o.mobile LIKE '%$search_query%' OR o.room LIKE '%$search_query%' OR oi.dish_name LIKE '%$search_query%')";
}

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
}

$sql .= " ORDER BY o.created_at DESC, o.id ASC";

$result = $conn->query($sql);

$barnew_orders = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];
        if (!isset($barnew_orders[$order_id])) {
            $date = new DateTime($row['created_at']);
            $formatted_date = $date->format('d-m-Y H:i:s');

            $barnew_orders[$order_id] = [
                'name' => $row['name'],
                'mobile' => $row['mobile'],
                'room' => $row['room'],
                'instruction' => $row['instruction'],
                'created_at' => $formatted_date,
                'items' => [],
                'total_price' => 0,
                'discount_amount' => $row['discount_amount'],
                'coupon_code' => $row['coupon_code']
            ];
        }
        $item_total_price = $row['item_total'];
        $barnew_orders[$order_id]['total_price'] += $item_total_price;
        $vat = ($item_total_price * 10) / 100;
        $barnew_orders[$order_id]['items'][] = [
            'drink_name' => $row['drink_name'],
            'drink_price' => $row['drink_price'],
            'quantity' => $row['quantity'],
            'item_total' => $item_total_price,
            'vat' => $row['vat'],
        ];
    }
}
            // Count the number of new orders
        $sql_count = "SELECT COUNT(DISTINCT order_id) AS num_barnew_orders FROM barnew_order_items";
        $result_count = $conn->query($sql_count);
        $num_barnew_orders = ($result_count->num_rows > 0) ? $result_count->fetch_assoc()['num_barnew_orders'] : 0;


     ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>New Order (<?php echo $num_barnew_orders; ?>)</title>
    <link rel="stylesheet" href="admin.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        * {
            margin: 0px;
            padding: 0px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }

        form{
            width: 500px;
        }
        .success {
            color: green; /* or any other color you prefer for success */
        }
    </style>
</head>
<body onload="startRefresh()">
 <div class="sidebar"  class="active">
            <?php include('adminhead.php');?>
    </div>

<section class="home-section">
    <nav>
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard">New Bar Order</span>
        </div>
        
        <!-- Search Form -->
        <form method="GET" action="" >
            <div class="search-box">
                <input type="text" name="search" class="form-control" placeholder="Search by Order ID, Name, Mobile, or Room" autocomplete="off" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="bx bx-search" type="submit"></button>
            </div>       
        </form>
            <!-- HOTEL NAME hotel_name.php -->
            <?php include('hotel_name.php') ?>

    </nav>

    <div class="home-content">
        <div class="overview-boxes">
            <div class="">
                <div class="right-side">
                    <a href="newbar_order.php" class="btn btn-primary no-print position-relative">
                        <i class='bx bx-wine'></i> New Bar Order
                        <?php if ($num_new_bar_orders > 0): ?>
                            <span class="badge bg-danger custom-badge position-absolute top-0 start-100 translate-middle">
                                <?php echo $num_new_bar_orders; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                        &nbsp; &nbsp;
                    <a href="new_order.php" class="btn btn-outline-danger no-print position-relative">
                        <i class='bx bx-restaurant'></i> New Food Order
                            <?php if ($num_new_food_orders > 0): ?>
                                <span class="badge bg-danger custom-badge position-absolute top- start-100 translate-middle">
                                    <?php echo $num_new_food_orders; ?>
                                </span>
                            <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col text-center text-info">
                    <h3>Bar Order</h3>
                </div>
            </div>
        </div>

        <div class="sales-boxes mt-3">
            <div class="recent-sales box">
                <div class="">
                    <div class="container" style="width: 1140px;">
                        <?php if (!empty($barnew_orders)): ?>
                            <?php foreach ($barnew_orders as $order_id => $order): ?>
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col">
                                                <strong>Order ID:</strong> <span class="order-id"><?php echo $order_id; ?></span> &nbsp;
                                                <strong>Room Number:</strong> <?php echo $order['room']; ?> &nbsp;
                                                <strong>Contact Number:</strong> <?php echo $order['mobile']; ?> &nbsp;  &nbsp;  &nbsp;
                                                <button id="confirmButton" class="btn btn-primary no-print" onclick="confirmOrder(this)">Confirm Order</button> &nbsp;  &nbsp;


                                               <!-- Existing code -->
                                               <button class="btn btn-secondary no-print" onclick="window.open('barkot_print.php?order_id=<?php echo $order_id; ?>', '_blank')">Print KOT </button>  &nbsp;  &nbsp;
                                                <button class="btn btn-success no-print" onclick="window.open('barnew_order_invoice.php?order_id=<?php echo $order_id; ?>', '_blank')">Print Order</button>

                                                <!-- Existing code -->

                                                <br>
                                                <strong>Customer Name:</strong> <?php echo $order['name']; ?> &nbsp;
                                                <strong>Instructions:</strong> <?php echo $order['instruction']; ?>  &nbsp;  &nbsp;
                                                <strong>Time:</strong> <?php echo $order['created_at']; ?> 

                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Dish Name</th>
                                                    <th scope="col">Quantity</th>
                                                    <th scope="col">Price</th>
                                                    <th scope="col">VAT</th>
                                                    <th scope="col">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <tr>
                                                        <td><?php echo $item['drink_name']; ?></td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td>₹<?php echo number_format($item['drink_price'], 2); ?></td>
                                                        <td>₹<?php echo number_format($item['vat'], 2); ?></td>
                                                        <td>₹<?php echo number_format($item['item_total'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-start"><strong>Grand Total:</strong></td>
                                                    <td colspan="2"><strong>₹<?php echo number_format($order['total_price'], 2); ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-start">
                                                        <strong class="success">Discount</strong>
                                                        <small class="success">(<?php echo htmlspecialchars($order['coupon_code']); ?>)</small>
                                                    </td>
                                                    <td colspan="2">
                                                        <strong class="success">₹<?php echo number_format($order['discount_amount'], 2); ?></strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-start"><strong>To Pay:</strong></td>
                                                    <td colspan="2"><strong>₹<?php echo number_format(($order['total_price'] - $order['discount_amount']), 2); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No orders found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// function printOrder(button) {
//     var orderCard = button.closest('.card');
//     if (orderCard) {
//         var printWindow = window.open('', '', 'height=600,width=800');
//         printWindow.document.write('<html><head><title>Order Details</title>');
//         printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
//         printWindow.document.write('</head><body>');
//         printWindow.document.write(orderCard.innerHTML);
//         printWindow.document.write('</body></html>');
//         printWindow.document.close();
//         printWindow.print();
//     }
// }

document.addEventListener('DOMContentLoaded', (event) => {
    const buttons = document.querySelectorAll('#confirmButton');
    buttons.forEach(button => {
        const orderId = button.closest('.card').querySelector('.order-id').textContent.trim();
        const savedState = localStorage.getItem('order_' + orderId);
        const savedTime = localStorage.getItem('timer_' + orderId) || 0;
        if (savedState === 'preparing') {
            startTimer(button, orderId, savedTime);
        }
    });
});

function confirmOrder(button) {
    const orderCard = button.closest('.card');
    const orderId = orderCard.querySelector('.order-id').textContent.trim();
    
    if (button.textContent.startsWith('Confirm Order')) {
        startTimer(button, orderId, 0); // Start from 0 seconds
    } else if (button.textContent.startsWith('Preparing')) {
        // Stop the timer and show alert
        clearInterval(button.timer);
        const timeElapsed = parseInt(button.timerValue);
        
        const userChoice = confirm(`Timer paused at ${timeElapsed} seconds. Choose OK to delete the order or Cancel to continue.`);
        if (userChoice) {
            // User clicked OK, delete the order
            deleteOrder(orderCard, orderId);
        } else {
            // User clicked Cancel, resume the timer
            startTimer(button, orderId, timeElapsed);
        }
    }
}

function startTimer(button, orderId, startTime) {
    button.timerValue = startTime;
    button.textContent = `Preparing (${startTime}s)`;
    button.className = 'btn btn-warning no-print';
    localStorage.setItem('order_' + orderId, 'preparing');

    button.timer = setInterval(() => {
        button.timerValue++;
        button.textContent = `Preparing (${button.timerValue}s)`;
        localStorage.setItem('timer_' + orderId, button.timerValue);
    }, 1000);
}

function deleteOrder(orderCard, orderId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "bardelete_order.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            if (xhr.responseText === "success") {
                if (orderCard) {
                    orderCard.remove(); // Remove the order card from the DOM
                    localStorage.removeItem('order_' + orderId);
                    localStorage.removeItem('timer_' + orderId); // Clear the timer from localStorage
                }
            } else {
                alert("Order deletion: " + xhr.responseText);
            }
        }
    };
    xhr.send("order_id=" + orderId);
}


    
let sidebar = document.querySelector(".sidebar");
let sidebarBtn = document.querySelector(".sidebarBtn");
sidebarBtn.onclick = function() {
    sidebar.classList.toggle("active");
    if (sidebar.classList.contains("active")) {
        sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
    } else {
        sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    }
}

    function startRefresh() {
            setTimeout("window.location.reload(true);", 5000); // Reload page every 5 seconds
        }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
