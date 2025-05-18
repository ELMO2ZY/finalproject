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

function sendPasswordChangedEmail($to) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ststicket2525@gmail.com';
        $mail->Password = 'qyel ldbr mfxe ctww';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('ststicket2525@gmail.com', 'Support System');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Your password has been changed';
        $mail->Body = '<h2>Password Changed</h2><p>Your password was successfully changed. If you did not perform this action, please contact support immediately.</p>';
        $mail->AltBody = 'Your password was successfully changed. If you did not perform this action, please contact support immediately.';
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Password change email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendResetEmail($to, $reset_link) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ststicket2525@gmail.com';
        $mail->Password = 'qyel ldbr mfxe ctww';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('ststicket2525@gmail.com', 'Support System');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = '<h2>Password Reset</h2>' .
            '<p>We received a request to reset your password. Click the link below to set a new password:</p>' .
            '<p><a href="' . htmlspecialchars($reset_link) . '">Reset Password</a></p>' .
            '<p>If you did not request this, you can ignore this email.</p>';
        $mail->AltBody = 'We received a request to reset your password. Visit this link to reset: ' . $reset_link;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Password reset email failed: ' . $mail->ErrorInfo);
        return false;
    }
}
?> 