<?php
// backend/lib/mailer.php

function send_mail(string $to, string $subject, string $html): bool {
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: Vite Gourmand <no-reply@vite-gourmand.test>";

    // Essai d'envoi réel (peut être bloqué en local)
    $ok = @mail($to, $subject, $html, implode("\r\n", $headers));

    // Fallback preuve: on écrit le mail dans backend/mails/
    $dir = __DIR__ . "/../mails";
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }

    $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $to);
    $file = $dir . "/" . date("Ymd_His") . "__" . $safe . ".html";

    $content =
        "<h3>TO:</h3><p>" . htmlspecialchars($to) . "</p>" .
        "<h3>SUBJECT:</h3><p>" . htmlspecialchars($subject) . "</p>" .
        "<hr>" . $html;

    @file_put_contents($file, $content);

    return $ok;
}
