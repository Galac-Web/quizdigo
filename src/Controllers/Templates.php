<?php

namespace Evasystem\Controllers;

class Templates
{

    protected $basePath = '/Templates/admin/static_elements/';
    protected $basPathcontent = '/Templates/admin/pages/';

    protected $partials = [
        'head'      => 'heade.php',
        'nav_top'   => 'nav_top.php',
        'left_nav'  => 'left_nav.php',
        'footer'    => 'footer.php',
        'testpag'    => 'testpag.php',
    ];
    public function __construct($contentFile)
    {
        $this->contentFile = $contentFile;
    }

    public function getCurrentUrl()
    {
        // Определяем протокол
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        // Формируем URL
        $currentUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
        return $currentUrl;
    }

    public function rederLogin()
    {
        $file = trim($this->contentFile); // elimină newline și spații la început/sfârșit
        $files = $_SERVER['DOCUMENT_ROOT'] . $file;
        if (file_exists($files)) {
            require $files;
        } else {
            echo "<!-- ⚠️ Partial  -->";
        }

    }



    public function render()
    {
        echo '<!doctype html>
<html lang="en">
<head>
    <!-- Meta Tags -->
	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jampack - Admin CRM Dashboard Template</title>
    <meta name="description" content="A modern CRM Dashboard Template with reusable and flexible components for your SaaS web applications by hencework. Based on Bootstrap."/>';

        $this->includePartial('head');
        echo '    <title>Rocker - Bootstrap 5 Admin Dashboard Template</title>
</head>
<body>
<!--wrapper-->
<div class="wrap">
';
$this->includePartial('nav_top');
$this->includePartial('left_nav');
$this->includeContent();


        echo '
    

';
        $this->includePartial('footer');
        echo '</div></body></html>';

    }
    protected function includeContent()
    {
        $file = trim($this->contentFile); // elimină newline și spații la început/sfârșit

        $files = $_SERVER['DOCUMENT_ROOT'] . $file;
        if (file_exists($files)) {
            require $files;
        } else {
            echo "<!-- ⚠️ Partial  -->";
        }
    }
    protected function includePartial(string $key)
    {
        if (!isset($this->partials[$key])) {
            echo "<!-- ⚠️ Partial key '{$key}' nu există în listă -->";
            return;
        }

        $file = $_SERVER['DOCUMENT_ROOT'] . $this->basePath . $this->partials[$key];

        if (file_exists($file)) {
            require $file;
        } else {
            echo "<!-- ⚠️ Partial '{$key}' missing at {$file} -->";
        }
    }

}