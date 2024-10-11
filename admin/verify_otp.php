<?php
session_start();

if (isset($_POST['verify'])) {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        // OTP is correct, proceed to change password
        header("Location: change_password.php");
        exit();
    } else {
        echo "<script>alert('Invalid OTP. Please try again.')</script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type='submit'] {
            cursor: pointer;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="center">
    <h1 class="text-center">Verify OTP</h1>
    <form action="" method="POST">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <input name="verify" class="btn-primary" type="submit" value="Verify OTP">
    </form>
</div>
</body>
</html>
