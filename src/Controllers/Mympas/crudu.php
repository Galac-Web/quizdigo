<?php

declare(strict_types=1);

use Evasystem\Controllers\Mympas\Mympas;
use Evasystem\Controllers\Mympas\MympasService;
use Evasystem\Controllers\Users\UsersService;

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Utilizator neautentificat.');
    }

    $usersService = new UsersService();
    $currentUserData = $usersService->getIdUserss((int)$_SESSION['user_id']);
    $currentUser = (is_array($currentUserData) && isset($currentUserData[0])) ? $currentUserData[0] : $currentUserData;
    $userRandomnId = (string)($currentUser['randomn_id'] ?? '');

    if ($userRandomnId === '') {
        throw new Exception('randomn_id lipsă.');
    }

    $data = $_POST;
    if (empty($data)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = $json;
        }
    }

    $type = trim((string)($data['type_product'] ?? ''));
    if ($type === '') {
        throw new Exception('Lipsește type_product.');
    }

    $service = new MympasService();
    $controller = new Mympas($service);

    switch ($type) {
        case 'save_card':
            $response = $controller->saveCard($userRandomnId, $data);
            break;

        case 'save_point':
            $response = $controller->savePoint($userRandomnId, $data);
            break;

        case 'save_week':
            $response = $controller->saveWeekActivity($userRandomnId, $data);
            break;

        case 'save_planning':
            $response = $controller->savePlanning($userRandomnId, $data);
            break;

        case 'delete_item':
            $itemType = trim((string)($data['item_type'] ?? ''));
            $randomnId = trim((string)($data['randomn_id'] ?? ''));
            if ($itemType === '' || $randomnId === '') {
                throw new Exception('Date lipsă pentru delete.');
            }

            $response = $controller->deleteItem($itemType, $randomnId);
            break;

        default:
            throw new Exception('Acțiune necunoscută.');
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}