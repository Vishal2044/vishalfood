<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';
include 'confi.php';

session_start();

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // Fetch user email from the database
    $email_query = "SELECT username FROM admin_ragister WHERE email='$email'";
    $email_result = mysqli_query($conn, $email_query);
    $user = mysqli_fetch_assoc($email_result);

    if ($user) {
        $username = $user['username'];
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['username'] = $username;

        // Send OTP to user email
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
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body = "Dear $username,<br><br>Your OTP for password reset is: <strong>$otp</strong>.<br><br>Best Regards,<br>Your Website Team";

            $mail->send();
            echo "<script>alert('OTP has been sent to your email.')</script>";
            header("Location: verify_otp.php");
            exit();
        } catch (Exception $e) {
            echo "<script>alert('Error sending OTP. Please try again. Error: {$mail->ErrorInfo}')</script>";
        }
    } else {
        echo "<script>alert('Email not found.')</script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request OTP</title>
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
    <h1 class="text-center">Request OTP</h1>
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input name="submit" class="btn-primary" type="submit" value="Send OTP">
    </form>
</div>

</body>
</html>
