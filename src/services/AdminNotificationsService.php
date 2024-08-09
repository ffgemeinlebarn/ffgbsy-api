<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    final class AdminNotificationsService extends BaseService
    {
        public function sendMessage($title, $message){

            $to = "ffgbsy@j4k0b.xyz";

            $message = "
            <html>
            <head>
            <title>Admin Notification</title>
            </head>
            <body>
                <h2>$title</h2>  
                <p>$message</p>
            </body>
            </html>
            ";

            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
            $headers[] = 'To: Admin <ffgbsy@j4k0b.xyz>';
            $headers[] = 'From: FFGBSY <ffgbsy@ff-gemeinlebarn.at>';

            return mail($to, $title, $message, implode("\r\n", $headers));
        }
    }
