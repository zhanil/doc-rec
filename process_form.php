<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $frontside_file = $_FILES['frontside'];
    $backside_file = $_FILES['backside'];
    $message = $_POST['message'];

    $frontside_path = "uploads/" . basename($frontside_file["name"]);
    $backside_path = "uploads/" . basename($backside_file["name"]);

    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $frontside_uploaded = move_uploaded_file($frontside_file["tmp_name"], $frontside_path);
    $backside_uploaded = move_uploaded_file($backside_file["tmp_name"], $backside_path);

    if ($frontside_uploaded && $backside_uploaded) {
        $to = "zhakaon@mail.ru";
        $subject = "Новые документы для распознавания";
        $boundary = md5(time());
        $headers = "From: no-reply@mydomain.ru\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode("Сообщение: $message"));

        $frontside_content = chunk_split(base64_encode(file_get_contents($frontside_path)));
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$frontside_file['type']}; name=\"{$frontside_file['name']}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$frontside_file['name']}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $frontside_content . "\r\n";

        $backside_content = chunk_split(base64_encode(file_get_contents($backside_path)));
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$backside_file['type']}; name=\"{$backside_file['name']}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$backside_file['name']}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $backside_content . "\r\n";
        $body .= "--{$boundary}--";

        if (mail($to, $subject, $body, $headers)) {
            echo 'Документы успешно отправлены на обработку!';
        } else {
            error_log('Ошибка при отправке почты!');
            echo 'Ошибка при отправке почты!';
        }
    } else {
        echo 'Ошибка при загрузке файлов!';
    }
} else {
    echo 'Неверный метод запроса!';
}
?>
