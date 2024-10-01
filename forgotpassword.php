<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 
require 'conn.php';

$email = $_POST['email'];
$pass = '';
$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
for ($i = 0; $i < 8; $i++) {
    $n = rand(0, strlen($alphabet)-1);
    $pass .= $alphabet[$n];
}
$password = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare('update password = :pass from users where emal= :email');
$stmt->bindParam(':email', $email);
$stmt->bindParam(':pass', $pass);
$mail = new PHPMailer(true); 

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alen.maxwellgs@gmail.com'; 
    $mail->Password = 'zfyppejbyqoonfzo'; 
    $mail->SMTPSecure = 'PHPMailer::ENCRYPTION_STARTTLS';
    $mail->Port = 587;

    $mail->setFrom('deploymenttasksheduler@admin.com', 'Deployment Task Scheduler');
    $mail->addAddress($email, 'User'); 

    $mail->isHTML(true); 
    $mail->Subject = "Your New Password - Please Reset It";
    $mail->Body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="padding: 20px;">
                <table width="600px" cellpadding="0" cellspacing="0" border="0" align="center" style="border: 1px solid #e0e0e0; padding: 20px; background-color: #ffffff;">
                    <tr>
                        <td style="text-align: center; padding-bottom: 20px;">
                            <h2 style="margin: 0; color: #0073e6;">Password Reset</h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>Dear <strong></strong>,</p>
                            <p>We received a request to reset your password. For your security, we have generated a new temporary password for your account.</p>
                            <p><strong>Your New Temporary Password:</strong></p>
                            <p style="font-size: 18px; font-weight: bold; color: #0073e6;">'.$pass.'</p>
                            <p>Please use this temporary password to log in to your account. Once logged in, we strongly recommend that you change this password immediately to something more secure and memorable.</p>
                            <p><strong>To change your password:</strong></p>
                            <ol>
                                <li>Log in to your account using the temporary password provided above.</li>
                                <li>Select "Change Password" and follow the instructions.</li>
                            </ol>
                            <p>If you did not request this password reset or if you have any concerns, please contact our support team immediately.</p>
                            <p>Thank you for your prompt attention to this matter.</p>
                            <p>Best regards,</p>
                            <p><strong>Admin</strong><br>
                               <br>
                               Deployment TAsk Scheduler<br>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

';
    $mail->AltBody = 'This is the plain text version of the email content';
    $mail->send();
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>