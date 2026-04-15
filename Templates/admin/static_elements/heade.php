<?php
function getCurrentUrl()
{
    // Определяем протокол
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    // Формируем URL
    $currentUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
    return $currentUrl;
}
?>
<!-- Favicon -->
<link rel="shortcut icon" href="favicon.ico">
<link rel="icon" href="favicon.ico" type="image/x-icon">


<!-- CSS -->
<link href="<?echo getCurrentUrl()?>/Templates/admin/dist/css/box.css" rel="stylesheet" type="text/css">
<link href="<?echo getCurrentUrl()?>/Templates/admin/dist/css/home.css" rel="stylesheet" type="text/css">

