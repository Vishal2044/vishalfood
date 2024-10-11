<?php
include 'confi.php';
require '../vendor/autoload.php'; // Ensure you include this line
session_start();
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['username'])) {
    header("Location: request_otp.php");
    exit();
}

if (isset($_POST['submit'])) {
    $username = $_SESSION['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Update the password in the database
        $sql = "UPDATE admin_ragister SET password='$new_password' WHERE username='$username'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            // Send email to user
            $email_query = "SELECT email FROM admin_ragister WHERE username='$username'";
            $email_result = mysqli_query($conn, $email_query);
            $user = mysqli_fetch_assoc($email_result);
            $email = $user['email'];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'clickndbooking@gmail.com';
                $mail->Password = 'psno sfsu oveu xyag'; // Use App Password here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('clickndbooking@gmail.com', 'RoomServed');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Changed Successfully';
                $mail->Body = "Dear $username,<br><br>Your password has been changed successfully.<br><br>Best Regards,<br>Your Website Team";

                $mail->send();
                echo "<script>alert('Password changed successfully and email sent.')</script>";
                header("Location: login.php");
                exit();
            } catch (Exception $e) {
                echo "<script>alert('Password changed but email could not be sent. Error: {$mail->ErrorInfo}')</script>";
            }
        } else {
            echo "<script>alert('Error changing password. Please try again.')</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match.')</script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password</title>
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
    <h1 class="text-center">Change Password</h1>
    <form action="" method="POST">
        <input type="password" name="new_password" placeholder="Enter new password" required>
        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
        <input name="submit" class="btn-primary" type="submit" value="Change Password">
    </form>
</div>

</body>
</html>
