<?php
declare(strict_types=1);

use Evasystem\Controllers\Users\Users;
use Evasystem\Controllers\Users\UsersService;

// === JSON only: colectăm orice zgomot și nu-l trimitem clientului ===
ob_start();
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

function json_response(array $payload, int $status = 200): void {
    $noise = ob_get_clean();
    if ($noise !== '' && $noise !== false) {
        error_log('[NOISE][crudu_users] ' . preg_replace('/\s+/', ' ', $noise));
    }
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_response(['success' => false, 'message' => 'Metoda permisă este POST.'], 405);
    }

    // Citește JSON brut sau fallback pe POST (în caz că vine form-urlencoded)
    $raw  = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = $_POST ?: [];

    $type = $data['type_product'] ?? $data['type'] ?? null;
    if (!$type) {
        json_response(['success' => false, 'message' => 'Lipsește tipul acțiunii (type_product).'], 400);
    }

    // —— aici adunăm rezultatul, iar la final facem un singur echo
    $payload = null;
    $status  = 200;

    switch ($type) {
        case 'register_step1': {
            $service    = new UsersService();
            $controller = new Users($service);


            // Salvăm codul în sesiune pentru a-l verifica la pasul următor
            // Sau îl poți salva în baza de date în tabelul users cu status 0
            $_SESSION['pending_email'] = $data['login'];

            // Datele pentru crearea profilului temporar
            $data['status'] = '0'; // Neconfirmat
            $data['connect_id'] = $_SESSION['confirm_code']; // Salvăm codul undeva în DB pentru verificare

            $result = $controller->addProfileInfo($data);

            if ($result['success']) {
                // Aici trimiți email-ul folosind mail() sau alt serviciu
                $to = $data['login'];
                $subject = "Codul tău QuizDigo";
                $message = "Salut! Codul tău de verificare este: " . $_SESSION['confirm_code'];
                $headers = "From: noreply@quizdigo.live";

                mail($to, $subject, $message, $headers);

                $payload = [
                    'success' => true,
                    'message' => 'Codul a fost trimis pe email.'
                ];
            } else {
                $payload = $result;
                $status = 422;
            }
            break;
        }

        case 'register_finalize': {
            // 1. Ce a scris omul în căsuța de input (vine din $data)
            $cod_introdus = $data['confirm_code'] ?? '';

// 2. Ce i-ai trimis tu pe email la Pasul 1 (este salvat în $_SESSION)
            $cod_real = $_SESSION['confirm_code'] ?? '';

            if ($cod_introdus === $cod_real && !empty($cod_real)) {
                $payload = ['success' => true, 'message' => "Codul Corect"];
                $status  = 200;
            } else {
                $payload = ['success' => false, 'message' => "Codul nu coicide"];
                $status  = 422;
            }

            break;
        }
        case 'add': {
            $service    = new UsersService();
            $controller = new Users($service);

            $result = $controller->addProfileInfo($data);
            if (!is_array($result)) {
                $payload = ['success' => false, 'message' => 'Răspuns invalid de la controller.'];
                $status  = 500;
            } else {
                // propagăm exact succes/eroare din controller
                $payload = $result;
                $status  = !empty($result['success']) ? 200 : 422; // validare eșuată => 422
            }
            break;
        }
        case 'login': {
            $service    = new UsersService();
            $controller = new Users($service);

            $result_login = $controller->login($data);
            if (!is_array($result_login)) {
                $payload = ['success' => false, 'message' => 'Răspuns invalid de la controller.'];
                $status  = 500;
            } else {
                // propagăm exact succes/eroare din controller
                $payload = $result_login;
                $status  = !empty($result_login['success']) ? 200 : 422; // validare eșuată => 422
            }
            break;
        }

        case 'edit': {
            $service    = new UsersService();
            $controller = new Users($service);

            // dacă metoda nu returnează array, considerăm succes dacă nu a aruncat
            $res     = $controller->editStatus($data);
            $payload = is_array($res) ? $res : ['success' => true, 'message' => 'Users editat.'];
            $status  = !empty($payload['success']) ? 200 : 422;
            break;
        }

        case 'delete': {
            if (empty($data['id'])) {
                $payload = ['success' => false, 'message' => 'ID lipsă pentru ștergere.'];
                $status  = 400;
                break;
            }
            $service = new UsersService();
            $res     = $service->deleteUsers((int)$data['id']);

            // normalizează răspunsul venind din model
            $success = is_array($res) ? (bool)($res['success'] ?? true) : true;
            $msg     = is_array($res) ? ($res['message'] ?? 'Users șters.') : 'Users șters.';

            $payload = ['success' => $success, 'message' => $msg, 'data' => $res];
            $status  = $success ? 200 : 400;
            break;
        }

        case 'activate': {
            if (empty($data['id'])) {
                $payload = ['success' => false, 'message' => 'ID lipsă pentru activare.'];
                $status  = 400;
                break;
            }
            $_SESSION['users'] = (int)$data['id'];
            $payload = [
                'success' => true,
                'message' => 'Users activat în sesiune.',
                'data'    => $_SESSION['users']
            ];
            $status = 200;
            break;
        }

        case 'setstatus': {
            $payload = [
                'success' => true,
                'message' => 'Status actualizat.',
                'data'    => null
            ];
            $status = 200;
            break;
        }

        default: {
            $payload = ['success' => false, 'message' => "Acțiune necunoscută: {$type}"];
            $status  = 400;
            break;
        }
    }

    // un singur echo aici:
    json_response($payload ?? ['success' => false, 'message' => 'Fără payload generat.'], $status);

} catch (\Throwable $e) {
    json_response(['success' => false, 'message' => 'Eroare: ' . $e->getMessage()], 500);
}
