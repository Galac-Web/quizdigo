<?php

namespace Evasystem\Controllers;

class PageLoader
{
    private string $url;
    private array $routes;

    public function __construct(array $routes)
    {
        $this->url = $this->getCurrentUrl();
        $this->routes = $routes;
    }

    // Obține URL-ul curent fără query string
    private function getCurrentUrl(): string
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($url, PHP_URL_PATH);
    }

    // Ultimul segment din URL
    private function getLastSegment(): string
    {
        $segments = explode('/', trim($this->url, '/'));
        return end($segments);
    }

    // Apelare dinamică în funcție de load_type
    public function callDynamic(string $method): void
    {
        $allowed = ['loadPage', 'simplepag', 'webproeject', 'querypag', 'startpag'];

        if (in_array($method, $allowed) && method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->loadPageByFile('404.php');
        }
    }

    public function loadPage(): void
    {
        $lastSegment = $this->getLastSegment();
        $file = $this->routes[$lastSegment] ?? '404.php';
        $this->loadPageByFile($file);
    }

    public function simplepag(): void
    {
        $lastSegment = $this->getLastSegment();
        $file = $this->routes[$lastSegment] ?? '404.php';
       
        $this->renderLogin($file);
    }

    public function webproeject(): void
    {
        require_once 'webproject/index.php';
    }

    public function querypag(): void
    {
        require_once 'controllers/pages/usersveryfi.php';
    }

    public function startpag(): void
    {
        require_once 'admin/pages/startpag.php';
    }

    // Render standard
    private function loadPageByFile(string $file): void
    {
        $templates = new Templates($file);
        $templates->render();
    }

    // Render pentru pagini tip login
    private function renderLogin(string $file): void
    {
        $templates = new Templates($file);
        $templates->rederLogin();
    }
}
