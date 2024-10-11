<?php

// this page hendl cart diffrtnt page cart emty
    session_start();

    // Empty the cart
    unset($_SESSION['cart']);

    // Redirect to the index page
    header("Location: index.php");
    exit();
?>
