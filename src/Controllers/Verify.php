<?php
declare(strict_types=1);

namespace Evasystem\Controllers;

use Evasystem\Controllers\Users\UsersService;

class Verify
{
    private UsersService $users;

    public function __construct(?UsersService $users = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->users = $users ?? new UsersService();
    }

    public function verifyusers(): void
    {
        if (\PHP_SAPI === 'cli') {
            return;
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // TOT ce trebuie accesibil fără login
        $publicPrefixes = [
            '/public/login',
            '/public/reg',
            '/public/addusersadd',
            '/public/pages',

            // Google auth / callback
            '/public/googleauth',
            '/public/auth/google',
            '/public/auth/google/callback',
        ];

        foreach ($publicPrefixes as $prefix) {
            if (\strncmp($path, $prefix, \strlen($prefix)) === 0) {
                return;
            }
        }

        $id = $_SESSION['user_id'] ?? null;

        if (!$id || !is_numeric($id)) {
            $this->clearAuthData();
            $this->respondUnauthorized();
        }

        try {
            $user = $this->users->getIdUserss((int)$id);
        } catch (\Throwable $e) {
            error_log('[Verify] DB error: ' . $e->getMessage());
            $this->clearAuthData();
            $this->respondUnauthorized();
        }

        $row = null;

        if (is_array($user)) {
            if (array_key_exists(0, $user) && is_array($user[0])) {
                $row = $user[0];
            } else {
                $row = $user;
            }
        }

        if (empty($row) || empty($row['id'])) {
            error_log('[Verify] User not found for session user_id=' . (string)$id);
            $this->clearAuthData();
            $this->respondUnauthorized();
        }

        // opțional: verificare status
        if (!empty($row['status']) && !in_array((string)$row['status'], ['active', '1'], true)) {
            error_log('[Verify] User inactive. user_id=' . (string)$id);
            $this->clearAuthData();
            $this->respondUnauthorized();
        }

        // user valid -> continuă
    }

    private function wantsJson(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (stripos($accept, 'application/json') !== false) {
            return true;
        }

        $ctype = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        if (stripos($ctype, 'application/json') !== false) {
            return true;
        }

        return false;
    }

    private function clearAuthData(): void
    {
        unset($_SESSION['user_id'], $_SESSION['login']);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        setcookie('UserAcces', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        setcookie('token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function respondUnauthorized(): never
    {
        $loginUrl = '/public/login';
        $next = $_SERVER['REQUEST_URI'] ?? '/';
        $loginWithNext = $loginUrl ;

        if ($this->wantsJson()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'       => false,
                'redirect' => $loginUrl,
                'next'     => $next,
                'message'  => 'Necesită autentificare.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!headers_sent()) {
            header('Location: ' . $loginWithNext, true, 302);
            exit;
        }

        $safe = htmlspecialchars($loginWithNext, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Redirect...</title></head><body>';
        echo '<script>try{window.location.assign("'.$safe.'");}catch(e){location.href="'.$safe.'";}</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url='.$safe.'"></noscript>';
        echo '</body></html>';
        exit;
    }
}