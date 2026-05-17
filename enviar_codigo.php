<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function enviarCodigo($correo, $codigo){

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'gehbdnsnhs@gmail.com';
        $mail->Password = 'rpnttoudsvlbgacz';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom('gehbdnsnhs@gmail.com', 'Gym Store');
        $mail->addAddress($correo);

        $mail->isHTML(true);
        $mail->Subject = 'Codigo de verificacion';

        $mail->Body = "
            <h2>Tu código es:</h2>
            <h1>$codigo</h1>
            <p>Este código es válido por tiempo limitado.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {

        echo "Error al enviar correo: " . $e->getMessage();
        return false;
    }
}