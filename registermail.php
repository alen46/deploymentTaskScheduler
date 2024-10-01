<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

$mail = new PHPMailer(true); 

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alen.maxwellgs@gmail.com'; 
    $mail->Password = 'zfyppejbyqoonfzo'; 
    $mail->Port = 587;

    $mail->setFrom('deploymenttasksheduler@admin.com', 'Deployment Task Scheduler');
    $mail->addAddress($email, 'User'); 

    $mail->isHTML(true); 
    $mail->Subject = "Welcome to Deployment Task Scheduler";
    $mail->Body = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Welcome to Our Service</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                color: #333;
                                line-height: 1.6;
                                margin: 0;
                                padding: 0;
                                background-color: #f4f4f4;
                            }
                            .container {
                                width: 80%;
                                margin: 0 auto;
                                padding: 20px;
                                background: #fff;
                                border-radius: 8px;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                            }
                            h1 {
                                color: #333;
                            }
                            p {
                                margin: 0 0 10px;
                            }
                            .button {
                                display: inline-block;
                                padding: 10px 20px;
                                font-size: 16px;
                                color: #fff;
                                background-color: #007BFF;
                                text-decoration: none;
                                border-radius: 5px;
                            }
                            .footer {
                                font-size: 14px;
                                color: #666;
                                margin-top: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h1>Welcome to Our Service!</h1>
                            <p>Hello '.$name.',</p>
                            <p>Thank you for registering with us. We are excited to have you on board!</p>
                            <p>Here are your account details:</p>
                            <p><strong>Email:</strong> '.$email.'</p>
                            <p><strong>Password:</strong> '.$pass.'</p>
                            <p>Please use the credentials above to log in to your account.</p>
                            <p>For security reasons, we strongly recommend that you change your password immediately after logging in.
                            <p>If you have any questions or need assistance, feel free to contact our support team.</p>
                            <p>Best regards,</p>
                            <p>Admin</p>
                            <div class="footer">
                                <p>&copy; 2024 Deployment Task Scheduler. All rights reserved.</p>
                                <p>If you did not register for this account, please ignore this email.</p>
                            </div>
                        </div>
                    </body>
                    </html>
';
    $mail->AltBody = 'This is the plain text version of the email content';
    $mail->send();
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>