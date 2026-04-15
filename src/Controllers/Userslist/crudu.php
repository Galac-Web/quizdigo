<?php

declare(strict_types=1);

use Evasystem\Controllers\Userslist\Userslist;
use Evasystem\Controllers\Userslist\UserslistService;
use Evasystem\Core\Userslist\UserslistModel;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new \Exception('Metoda permisă este POST.');
    }

    $data = $_POST;

    if (empty($data)) {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);

        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    if (!is_array($data) || empty($data)) {
        throw new \Exception('Nu s-au primit date valide.');
    }

    $type = trim((string)($data['type_product'] ?? ''));
    if ($type === '') {
        throw new \Exception('Lipsește tipul acțiunii (type_product).');
    }

    $service = new UserslistService();
    $controller = new Userslist($service);

    switch ($type) {
        case 'add':
            $result = $controller->addProfileInfo($data);

            $response = [
                'success'  => (bool)($result['success'] ?? false),
                'message'  => $result['message'] ?? 'Userslist procesat.',
                'data'     => $result,
                'received' => $data
            ];
            break;

        case 'edit':
            $result = $controller->saveProfileInfo($data);

            /**
             * dacă în request a venit și status, îl salvăm separat în users_connect
             */
            $status = trim((string)($data['status'] ?? ''));
            if ($status !== '') {
                $randomnId = trim((string)($data['randomn_id'] ?? ''));
                if ($randomnId !== '') {
                    $service->editUsers([
                        'data' => ['status' => $status],
                        'db' => 'users_connect',
                        'randomn_id' => $randomnId,
                        'exceptions' => []
                    ]);
                }
            }

            $response = [
                'success'  => (bool)($result['success'] ?? false),
                'message'  => $result['message'] ?? 'Utilizator actualizat.',
                'data'     => $result,
                'received' => $data
            ];
            break;

        case 'edit_profile':
            $result = $controller->saveOnlyProfileInfo($data);

            $response = [
                'success'  => (bool)($result['success'] ?? false),
                'message'  => $result['message'] ?? 'Profil procesat.',
                'data'     => $result,
                'received' => $data
            ];
            break;

        case 'edit_security':
            $result = $controller->saveOnlySecurityInfo($data);

            $response = [
                'success'  => (bool)($result['success'] ?? false),
                'message'  => $result['message'] ?? 'Date acces procesate.',
                'data'     => $result,
                'received' => $data
            ];
            break;

        case 'edit_avatar':
            $result = $controller->saveAvatar($data, $_FILES);

            $response = [
                'success'   => (bool)($result['success'] ?? false),
                'message'   => $result['message'] ?? 'Avatar procesat.',
                'photo_url' => $result['photo_url'] ?? '',
                'data'      => $result,
                'received'  => $data
            ];
            break;

        case 'setstatus':
            $randomnId = trim((string)($data['randomn_id'] ?? ''));
            $status    = trim((string)($data['status'] ?? ''));

            if ($randomnId === '' || $status === '') {
                throw new \Exception('Lipsesc randomn_id sau status.');
            }

            $ok = $service->editUsers([
                'data' => [
                    'status' => $status
                ],
                'db' => 'users_connect',
                'randomn_id' => $randomnId,
                'exceptions' => []
            ]);

            $response = [
                'success'  => (bool)$ok,
                'message'  => $ok ? 'Status actualizat.' : 'Status neactualizat.',
                'status'   => $status,
                'received' => $data
            ];
            break;

        case 'delete':
            $randomnId = trim((string)($data['randomn_id'] ?? ($data['id'] ?? '')));

            if ($randomnId === '') {
                throw new \Exception('ID lipsă pentru ștergere.');
            }

            $deletedConnect = UserslistModel::del($randomnId, 'users_connect', 'randomn_id');
            $deletedInfo    = UserslistModel::del($randomnId, 'users_info', 'randomn_id');

            $response = [
                'success'  => true,
                'message'  => 'Utilizator șters.',
                'data'     => [
                    'users_connect' => $deletedConnect,
                    'users_info'    => $deletedInfo
                ],
                'received' => $data
            ];
            break;

        case 'activate':
            $activateId = $data['randomn_id'] ?? ($data['id'] ?? '');

            if ($activateId === '') {
                throw new \Exception('ID lipsă pentru activare.');
            }

            $_SESSION['userslist'] = $activateId;

            $response = [
                'success'  => true,
                'message'  => 'Userslist activat în sesiune.',
                'data'     => $_SESSION['userslist'],
                'received' => $data
            ];
            break;

        default:
            throw new \Exception("Acțiune necunoscută: {$type}");
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Eroare: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}