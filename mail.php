<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

$mail = new PHPMailer(true); 

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = '684edf001@smtp-brevo.com'; 
    $mail->Password = 'GV07d5QNCBJhIEnb'; 
    $mail->SMTPSecure = 'PHPMailer::ENCRYPTION_STARTTLS';
    $mail->Port = 587;

    $mail->setFrom('deploymenttasksheduler@admin.com', 'Deployment Task Scheduler');
    $mail->addAddress($email, 'User'); 

    $mail->isHTML(true); 
    $mail->Subject = "Schedule Change Notification";
    $mail->Body = '<!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                                color: #333333;
                            }
                            .container {
                                max-width: 600px;
                                margin: 0 auto;
                                padding: 20px;
                                border: 1px solid #dddddd;
                                border-radius: 5px;
                            }
                            h2 {
                                color: #0066cc;
                            }
                            p {
                                margin: 15px 0;
                            }
                            .details {
                                background-color: #f9f9f9;
                                padding: 15px;
                                border-radius: 5px;
                                border: 1px solid #eeeeee;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h2>Important: Deployment Schedule Change Notice</h2>
                            <p>Dear User,</p>
                            <p>I am writing to inform you about a change in the deployment schedule. After reviewing our current timelines and considering the impact on ongoing operations, we have decided to adjust the deployment schedule as follows:</p>
                            <div class="details">
                                <p><strong>Previous Deployment Date:</strong>'. $oldDate.'</p>
                                <p><strong>New Deployment Date:</strong> '.$newDate.'</p>
                                <p><strong>Reason for Change:</strong> '.$reason.'</p>
                            </div>
                            <pThis adjustment will allow us to ensure that the deployment is carried out smoothly without any disruptions to our critical services. Please review the new schedule and adjust your plans accordingly.</p>
                            <p>If you have any questions or need further clarification, feel free to reach out.</p>
                            <p>Thank you for your understanding and continued support.</p>
                            <p>Best regards,</p>
                            <p><strong>Admin</strong></p>
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