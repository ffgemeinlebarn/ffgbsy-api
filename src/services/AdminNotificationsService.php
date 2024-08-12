<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use Psr\Container\ContainerInterface;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    final class AdminNotificationsService extends BaseService
    {
        private $notificationSettings;

        public function __construct(ContainerInterface $container)
        {
            $this->notificationSettings = $container->get('settings')['notifications'];
        }

        public function sendMessage($title, $message){

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = $this->notificationSettings['smtpHost'];                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = $this->notificationSettings['smtpUser'];                     //SMTP username
                $mail->Password   = $this->notificationSettings['smtpPassword'];                               //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom($this->notificationSettings['smtpUser'], 'FFGBSY Notification');
                $mail->addAddress($this->notificationSettings['receiver'], 'Admin');

                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = $title;
                $mail->Body    = "<p>$message</p>";
                $mail->AltBody = $message;

                $mail->send();
                return 'Message has been sent';
            } catch (Exception $e) {
                return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
