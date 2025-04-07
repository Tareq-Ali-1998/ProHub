<?php

require_once dirname(__DIR__) . '/config.php';
require_once DEPENDENCIES_PATH . '/smtp/class.phpmailer.php';
require_once DEPENDENCIES_PATH . '/smtp/class.smtp.php';

/**
 * Handles sending emails using PHPMailer with SMTP.
 *
 * This class is responsible for configuring and sending emails via SMTP.
 * It uses PHPMailer for email handling and supports HTML content.
 *
 * @throws RuntimeException If email configuration or sending fails.
 */
class EmailHandler {
    /**
     * @var PHPMailer Instance of PHPMailer for sending emails.
     */
    private $mail;

    /**
     * Initializes the EmailHandler with SMTP configuration.
     *
     * Sets up PHPMailer with Gmail's SMTP server and default credentials.
     * Configures secure TLS connection and HTML email support.
     */
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

    /**
     * Sends an email to a recipient with a verification code.
     *
     * @param string $recipientEmail      The email address of the recipient.
     * @param string $recipientFirstName  The first name of the recipient.
     * @param string $recipientLastName   The last name of the recipient.
     * @param string $verificationCode    The verification code to include in the email.
     * 
     * @return bool Returns true if the email is sent successfully, otherwise false.
     * 
     * @throws RuntimeException If email content loading or sending fails.
     */
    public function sendEmail(
        string $recipientEmail,
        string $recipientFirstName,
        string $recipientLastName,
        string $verificationCode
    ): void {
        // Step 1: Add recipient details.
        $this->mail->addAddress($recipientEmail, "Recipient");
        $this->mail->Subject = "Welcome to ProHub - Please Verify Your Email Address";

        // Step 2: Load HTML email template.
        $htmlEmailContent = "";
        error_reporting(E_ALL & ~E_WARNING); // Suppress warnings for file_get_contents.
        try {
            $htmlEmailContent = file_get_contents(PROHUB_VERIFICATION_EMAIL_PATH);
            if ($htmlEmailContent === false) {
                throw new RuntimeException("Email Error: Unable to load email template.");
            }
        } catch (Exception $exception) {
            throw new RuntimeException("Email Error: Failed to load email template.");
        }

        // Step 3: Replace placeholders in the email template.
        $htmlEmailContent = str_replace(
            array('{user_first_name}', '{user_last_name}', '{verification_code}'),
            array($recipientFirstName, $recipientLastName, $verificationCode),
            $htmlEmailContent
        );
        $this->mail->Body = $htmlEmailContent;

        // Step 4: Send the email.
        try {
            if (!$this->mail->Send()) {
                throw new RuntimeException("Email Error: Failed to send email.");
            }
        } catch (Exception $exception) {
            throw new RuntimeException("Email Error: Failed to send email.");
        }
    }
}