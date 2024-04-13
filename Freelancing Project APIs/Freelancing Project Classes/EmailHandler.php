<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/SMTP Kit/class.phpmailer.php';
include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/SMTP Kit/class.smtp.php';

class EmailHandler {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = "tls";
        $this->mail->Host = "smtp.gmail.com";
        $this->mail->Port = 587;
        $this->mail->Username = "freelancingprojectemailsender@gmail.com";
        $this->mail->Password = "njmdvdxpbsoyidlb";
        $this->mail->From = "freelancingprojectemailsender@gmail.com";
        $this->mail->FromName = "ProHub";
        $this->mail->WordWrap = 50;
        $this->mail->IsHTML(true);
    }

    public function sendEmail($recipientEmail, $recipientFirstName, $recipientLastName, $verificationCode) {
        $this->mail->addAddress($recipientEmail, "Recipient");
        $this->mail->Subject = "Welcome to ProHub - Please Verify Your Email Address";
        
        $htmlEmailContent = "";
        error_reporting(E_ALL & ~E_WARNING);
        try {
            $htmlEmailContent = file_get_contents('C:/Users/Tareq/Desktop/Freelancing Project APIs/SMTP Kit/ProHub Verification Email.html');
        }
        catch (Exception $exception) {
            return false;
        }

        $htmlEmailContent = str_replace(
            array('{user_first_name}', '{user_last_name}', '{verificationCode}'),
            array($recipientFirstName, $recipientLastName, $verificationCode),
            $htmlEmailContent
        );
        $this->mail->Body = $htmlEmailContent;

        if(!$this->mail->Send()) {
            // throw new Exception("Error sending email: " . $this->mail->ErrorInfo);
            return false;
        }
        else {
            return true;
        }
    }
}