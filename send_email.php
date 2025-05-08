<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendTicketConfirmationEmail($to, $ticketId, $subject) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ststicket2525@gmail.com';
        $mail->Password = 'qyel ldbr mfxe ctww'; // qyel ldbr mfxe ctww
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('ststicket2525@gmail.com', 'Support System');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Ticket Created - #$ticketId";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #5e7eb6; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8fafc; }
                .ticket-id { font-weight: bold; color: #5e7eb6; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Ticket Created Successfully</h2>
                </div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>Thank you for creating a new ticket with us. Your ticket has been successfully created and will be reviewed by our team.</p>
                    <p>Your Ticket ID: <span class='ticket-id'>#$ticketId</span></p>
                    <p>Subject: $subject</p>
                    <p>You can view your ticket status at any time by visiting our support portal.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $message;
        $mail->AltBody = "Your ticket #$ticketId has been created successfully. Subject: $subject";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}
?> 