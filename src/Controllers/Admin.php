<?php
declare(strict_types=1);

namespace Evasystem\Controllers;

use Config\Database;
use Evasystem\Controllers\Redirector;

class Admin
{
    private string $dir = '';

    public function login(): void
    {
        $data = ['title' => 'Welcome', 'message' => 'Hello, welcome to our website!'];
        $this->renderadmin('login', $data, 'pages/user');
    }

    public function getDir(): string { return $this->dir; }
    public function setDir(string $dir): void { $this->dir = $dir ?: $this->dir; }

    /** ÎNLOCUIT: randează pagini pe baza rutei GET din DB */
    public function index(string $dir = ''): void
    {
        [$method, $path, $slug] = $this->getRequest();
        // Forțăm GET aici, index e pentru pagini
        $route = $this->findRoute('GET', $path, $slug);

        // dir din DB sau parametru
        $this->dir = $dir ?: ($route['dir'] ?? '/Templates/admin/pages/');
        $this->renderFromRoute($route, $slug);
    }

    /** ÎNLOCUIT: execută rootFunction (CRUD/API) pe baza rutei POST din DB */
    public function rootFunction(string $dir = ''): void
    {
        [$method, $path, $slug] = $this->getRequest();
        // rootFunction e, în mod normal, POST
        $route = $this->findRoute('POST', $path, $slug) ?? $this->findRoute($method, $path, $slug);

        if (!$route) {
            http_response_code(404);
            echo json_encode(['success'=>false, 'message'=>'Rută neconfigurată în DB']);
            return;
        }

        $action   = $route['action']    ?? '';
        $loadType = $route['load_type'] ?? '';

        if ($action === 'rootFunction' || $loadType === 'rootFunction') {
            $file = $this->docrootPath($route['dir'] ?? '');
            if (!$file || !is_file($file)) {
                http_response_code(500);
                echo json_encode(['success'=>false, 'message'=>'Fișier rootFunction inexistent: '.($route['dir'] ?? '')]);
                return;
            }
            require $file;
            return;
        }

        // Dacă nu e rootFunction, îl tratăm ca pagină
        $this->dir = $route['dir'] ?? $this->dir;
        $this->renderFromRoute($route, $slug);
    }

    /* ================== PRIVATE: rendering pagini ================== */

    private function renderFromRoute(?array $route, string $slug): void
    {
        $redirector  = new Redirector();
        $currentPage = $redirector->thispagesurl(); // ex: 'firms', 'addfirms', 'login', 'crudusers' etc.

        $loadType = $route['load_type'] ?? 'loadPage';

        // Dir poate fi folder (ex: /Templates/admin/pages/firms/) sau fișier (ex: ./webproject/index.php)
        $pageDirectory = $this->getDir() ?: '/Templates/admin/pages/';
        $pageDirectory = $this->normalizeDirLike($pageDirectory);

        // Map pentru PageLoader:
        // - dacă $pageDirectory este DOSAR -> {slug => <dosar>/<slug>.php}
        // - dacă este FIȘIER -> {slugSauIndex => <fișier>}
        $routes = [];

        if (is_dir($this->docrootPath($pageDirectory, false))) {
            $slugForFile = $currentPage ?: ($slug ?: 'index');
            $routes[$currentPage] = $pageDirectory . $slugForFile . '.php';
        } else {
            // tratăm ca fișier direct (de ex. './webproject/index' sau '/something/file.php')
            $file = $this->resolveFileFromDir($pageDirectory, $currentPage ?: ($slug ?: 'index'));
            if ($file) {
                $routes[$currentPage ?: 'index'] = $file;
            } else {
                // fallback: folder standard de pagini
                $pageDirectory = '/Templates/admin/pages/';
                $routes[$currentPage] = $pageDirectory . $currentPage . '.php';
            }
        }

        $pageLoader = new PageLoader($routes);

        // dacă vrei să forțezi simplepag pentru anume rute, pune 'simplepag' în DB
        if ($currentPage === 'login' || $loadType === 'simplepag' ) {
            $pageLoader->simplepag();
        } else {
            $pageLoader->loadPage();
        }
    }

    /* ================== PRIVATE: routing din DB ================== */

    private function getRequest(): array
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path   = rtrim($path, '/') ?: '/';
        $slug   = $path === '/' ? '' : trim(basename($path), '/');
        return [$method, $path, $slug];
    }

    private function findRoute(string $method, string $path, ?string $slug = null): ?array
    {
        $pdo = Database::getDB();
        $method = strtoupper($method);

        // 1) match exact method+path
        $stmt = $pdo->prepare("SELECT * FROM routes WHERE is_active=1 AND method=:m AND path=:p LIMIT 1");
        $stmt->execute([':m' => $method, ':p' => $path]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) return $row;

        // 2) fallback pe basename(path) = slug
        if ($slug) {
            $stmt = $pdo->prepare("SELECT * FROM routes WHERE is_active=1 AND method=:m");
            $stmt->execute([':m' => $method]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $base = trim(basename($r['path'] ?? ''), '/');
                if ($base !== '' && $base === $slug) return $r;
            }
        }

        return null;
    }

    /* ================== PRIVATE: căi & securitate ================== */

    private function normalizeDirLike(string $d): string
    {
        // Acceptă valori ca './webproject/index', '/Templates/admin/pages/firms/' etc.
        $d = trim($d);
        if ($d === '') return '/Templates/admin/pages/';
        $d = str_replace('\\','/',$d);
        // scoate prefixul './'
        if (str_starts_with($d, './')) $d = substr($d, 1);
        return $d;
    }

    private function docrootPath(string $rel, bool $mustExist = true): ?string
    {
        $rel  = $this->normalizeDirLike($rel);
        $root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $path = $root . '/' . ltrim($rel, '/');

        $real = realpath($path);
        if ($real === false) return $mustExist ? null : $path;
        if (strpos($real, $root) !== 0) return null; // securitate
        return $real;
    }

    private function resolveFileFromDir(string $dirLike, string $slug): ?string
    {
        // dacă $dirLike e fișier -> întoarce-l; dacă e director -> caută <slug>.php sau index.php
        $maybeFile = $this->docrootPath($dirLike);
        if ($maybeFile && is_file($maybeFile)) return $maybeFile;

        $maybeDir  = $this->docrootPath(rtrim($dirLike,'/').'/', false);
        if ($maybeDir && is_dir($maybeDir)) {

            $cand1 = rtrim($maybeDir,'/').'/'.$slug.'.php';
            $cand2 = rtrim($maybeDir,'/').'/index.php';
            if (is_file($cand1)) return $cand1;
            if (is_file($cand2)) return $cand2;
        }
        return null;
    }
}
