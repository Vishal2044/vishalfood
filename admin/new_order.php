<?php
session_start();
include 'confi.php';
include 'function.php';

// Handle the delete order request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    if (deleteOrderFromDatabase($order_id)) {
        echo "success";
    } else {
        echo "Error deleting order.";
    }
    exit; // Exit to prevent further execution of the script
}

// Redirect if user is not logged in
if (!isset($_SESSION['Login'])) {
    header("Location: login.php");
    exit;
}

// Fetch search query and date range if they exist
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the SQL query with optional search and date range filters
$sql = "SELECT o.id AS order_id, o.name, o.mobile, o.room, o.instruction, o.created_at,
        oi.dish_name, oi.dish_price, oi.quantity, oi.item_total, oi.cgst, oi.sgst,
        oi.discount_amount, o.coupon_code
        FROM new_orders o
        JOIN new_order_items oi ON o.id = oi.order_id
        WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (o.id LIKE '%$search_query%' OR o.name LIKE '%$search_query%' OR o.mobile LIKE '%$search_query%' OR o.room LIKE '%$search_query%' OR oi.dish_name LIKE '%$search_query%')";
}

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
}

$sql .= " ORDER BY o.created_at DESC, o.id ASC";

$result = $conn->query($sql);

$new_orders = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];
        if (!isset($new_orders[$order_id])) {
            $date = new DateTime($row['created_at']);
            $formatted_date = $date->format('d-m-Y H:i:s');

            $new_orders[$order_id] = [
                'name' => $row['name'],
                'mobile' => $row['mobile'],
                'room' => $row['room'],
                'instruction' => $row['instruction'],
                'created_at' => $formatted_date,
                'items' => [],
                'total_price' => 0,
                'discount_amount' => $row['discount_amount'],
                'coupon_code' => $row['coupon_code'],
            ];
        }
        $item_total_price = $row['item_total'];
        $new_orders[$order_id]['total_price'] += $item_total_price;
        $cgst = ($item_total_price * 2.5) / 100;
        $sgst = ($item_total_price * 2.5) / 100;
        $new_orders[$order_id]['items'][] = [
            'dish_name' => $row['dish_name'],
            'dish_price' => $row['dish_price'],
            'quantity' => $row['quantity'],
            'item_total' => $item_total_price,
            'cgst' => $row['cgst'],
            'sgst' => $row['sgst'],
        ];
    }
}

// Count the number of new orders
$sql_count = "SELECT COUNT(DISTINCT order_id) AS num_new_orders FROM new_order_items";
$result_count = $conn->query($sql_count);
$num_new_orders = ($result_count->num_rows > 0) ? $result_count->fetch_assoc()['num_new_orders'] : 0;



// Function to delete order from the database
function deleteOrderFromDatabase($order_id) {
    global $conn; // Access the global connection variable
    $stmt = $conn->prepare("DELETE FROM new_orders WHERE id = ?");
    $stmt->bind_param("s", $order_id); // Assuming order_id is a string
    $result = $stmt->execute();

    $stmt->close();
    return $result; // Return true or false based on success
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>New Order (<?php echo $num_new_orders; ?>)</title>
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
            <?php include 'adminhead.php';?>
    </div>

<section class="home-section">
    <nav>
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard">New Food Order</span>
        </div>

        <!-- Search Form -->
        <form method="GET" action="" >
            <div class="search-box">
                <input type="text" name="search" class="form-control" placeholder="Search by Order ID, Name, Mobile, or Room" autocomplete="off" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="bx bx-search" type="submit"></button>
            </div>
        </form>
            <!-- HOTEL NAME hotel_name.php -->
            <?php include 'hotel_name.php'?>

    </nav>

    <div class="home-content">
    <div class="overview-boxes">
    <div class="">
        <div class="right-side">
            <a href="new_order.php" class="btn btn-primary no-print position-relative">
                <i class='bx bx-restaurant'></i> New Food Order
                <?php if ($num_new_food_orders > 0): ?>
                    <span class="badge bg-danger custom-badge position-absolute top- start-100 translate-middle">
                        <?php echo $num_new_food_orders; ?>
                    </span>
                <?php endif;?>
            </a>
            &nbsp; &nbsp;
            <a href="newbar_order.php" class="btn btn-outline-danger no-print position-relative">
                <i class='bx bx-wine'></i>New Bar Order
                <?php if ($num_new_bar_orders > 0): ?>
                    <span class="badge bg-danger custom-badge position-absolute top-0 start-100 translate-middle">
                        <?php echo $num_new_bar_orders; ?>
                    </span>
                <?php endif;?>
            </a>
        </div>
    </div>
</div>

        <div class="container">
            <div class="row">
                <div class="col text-center text-info">
                    <h3>Food Order</h3>
                </div>
            </div>
        </div>
        <div class="sales-boxes mt-3">
            <div class="recent-sales box">
                <div class="">
                    <div class="container" style="width: 1140px;">
                        <?php if (!empty($new_orders)): ?>
                            <?php foreach ($new_orders as $order_id => $order): ?>
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col">
                                                <strong>Order ID:</strong> <span class="order-id"><?php echo $order_id; ?></span> &nbsp;
                                                <strong>Room Number:</strong> <?php echo $order['room']; ?> &nbsp;
                                                <strong>Contact Number:</strong> <?php echo $order['mobile']; ?> &nbsp;  &nbsp;  &nbsp;
                                                <button id="confirmButton" class="btn btn-primary no-print" onclick="confirmOrder(this)">Confirm Order</button> &nbsp;  &nbsp;


                                               <!-- Existing code -->
                                               <button class="btn btn-secondary no-print" onclick="window.open('kot_print.php?order_id=<?php echo $order_id; ?>', '_blank')">Print KOT </button>  &nbsp;  &nbsp;
                                                <button class="btn btn-success no-print" onclick="window.open('new_order_invoice.php?order_id=<?php echo $order_id; ?>', '_blank')">Print Order</button>

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
                                                    <th scope="col">CGST</th>
                                                    <th scope="col">SGST</th>
                                                    <th scope="col">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <tr>
                                                        <td><?php echo $item['dish_name']; ?></td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td>₹<?php echo number_format($item['dish_price'], 2); ?></td>
                                                        <td>₹<?php echo number_format($item['cgst'], 2); ?></td>
                                                        <td>₹<?php echo number_format($item['sgst'], 2); ?></td>
                                                        <td>₹<?php echo number_format($item['item_total'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach;?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-start"><strong>Grand Total</strong></td>
                                                    <td><strong>₹<?php echo number_format($order['total_price'], 2); ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-start">
                                                        <strong class="success">Discount</strong>
                                                        <small class="success">(<?php echo htmlspecialchars($order['coupon_code']); ?>)</small>
                                                    </td>
                                                    <td>
                                                        <strong class="success">₹<?php echo number_format($order['discount_amount'], 2); ?></strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-start"><strong>To Pay</strong></td>
                                                    <td><strong>₹<?php echo number_format(($order['total_price'] - $order['discount_amount']), 2); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach;?>
                        <?php else: ?>
                            <div class="alert alert-info">No orders found.</div>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>

    // PLAY sound
    
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

    // Save timer states before leaving the page
    window.addEventListener('beforeunload', () => {
        buttons.forEach(button => {
            if (button.timer) {
                clearInterval(button.timer); // Stop the timer
                localStorage.setItem('timer_' + button.orderId, button.timerValue); // Save current timer value
            }
        });
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

        const userChoice = confirm(`Timer paused at ${formatTime(timeElapsed)}. Choose OK to delete the order or Cancel to continue.`);
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
    button.orderId = orderId; // Save order ID for future reference
    button.textContent = `Preparing (${formatTime(startTime)})`;
    button.className = 'btn btn-warning no-print';
    localStorage.setItem('order_' + orderId, 'preparing');

    button.timer = setInterval(() => {
        button.timerValue++;
        button.textContent = `Preparing (${formatTime(button.timerValue)})`;
        localStorage.setItem('timer_' + orderId, button.timerValue);
    }, 1000);
}

function deleteOrder(orderCard, orderId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "delete_order.php", true);
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

// Function to format time as mm:ss
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
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