<?php
/**
 * This script retrieves deployment details scheduled for tomorrow and sends a reminder email to the portal owner.
 */

require 'conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try{
    $date = new DateTime();
    $date->modify('+1 day'); 
    $formattedDate = $date->format('Y-m-d');
    $stmt = $conn->prepare('SELECT users.email, users.username, deployment.deployment_date, portal.portalname, portal.purl from deployment inner JOIN portal on portal.pid = deployment.portal_id INNER JOIN users on users.userid = portal.portal_owner where deployment_date = :date');
    $stmt->bindParam(':date', $formattedDate, PDO::PARAM_STR);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res){
        $email = $res['email'];
        $name = $res['username'];
        $ddate = $res['deployment_date'];
        $portl = $res['portalname'];
        $purl = $res['purl'];
        

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
            $mail->Subject = "Deployment Scheduled for Tomorrow";
            $mail->Body = '<!DOCTYPE html>
                            <html lang="en">
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>Deployment Reminder</title>
                                <style>
                                    body {
                                        font-family: Georgia, serif;
                                        line-height: 1.6;
                                        color: #ffffff;
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
                            <body style="font-family: Arial, sans-serif; line-height: 1.6;">
                                <table width="100%" cellpadding="10" cellspacing="0" border="0">
                                    <tr>
                                        <td>
                                            <h2 style="color: #333;">Reminder: Deployment Scheduled for Tomorrow</h2>
                                            <p>Dear '.$name.',</p>
                                            <p>This is a friendly reminder that there is a scheduled deployment for the following portal tomorrow:</p>
                                            <div class="details">
                                            <p>
                                                <strong>Portal Name:</strong> '.$portl.'<br>
                                                <strong>Portal URL:</strong> '.$purl.'<br>
                                                <strong>Deployment Date:</strong> '.$ddate.'
                                            </p>
                                            </div>
                                            <p>Please ensure all necessary preparations are completed by then.</p>
                                            <p>If you have any questions or need assistance, feel free to reach out.</p>
                                            <p>Thank you for your attention.</p>
                                            <br>
                                            <p>Best regards,<br>
                                            Admin<br>
                                            Deployment Task Scheduler</p>
                                        </td>
                                    </tr>
                                </table>
                            </body>
                            </html>
        ';
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
} catch (PDOException $e) {
    echo json_encode(array("response" => "Database error: " . $e->getMessage()));
} catch (Exception $e) {
    echo json_encode(array("response" => "General error: " . $e->getMessage()));
} finally {
    $conn = null;
}
?>