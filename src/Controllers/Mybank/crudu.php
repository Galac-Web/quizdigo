<?php

declare(strict_types=1);

use Evasystem\Controllers\Mybank\Mybank;
use Evasystem\Controllers\Mybank\MybankService;

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metoda permisă este POST.');
    }

    $data = $_POST;

    if (empty($data)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = $json;
        }
    }

    $type = $data['type_product'] ?? '';
    if ($type === '') {
        throw new Exception('Lipsește type_product.');
    }

    $service = new MybankService();
    $controller = new Mybank($service);

    switch ($type) {
        case 'save_billing':
            $accountRandomnId = trim((string)($data['account_randomn_id'] ?? ''));
            if ($accountRandomnId === '') {
                throw new Exception('Lipsește account_randomn_id.');
            }

            $response = $controller->saveBilling($accountRandomnId, $data);
            break;

        case 'add_card':
            $accountRandomnId = trim((string)($data['account_randomn_id'] ?? ''));
            if ($accountRandomnId === '') {
                throw new Exception('Lipsește account_randomn_id.');
            }

            $response = $controller->addCard($accountRandomnId, $data);
            break;
        case 'purchase_subscription':
            $userRandomnId = trim((string)($data['user_randomn_id'] ?? ''));
            if ($userRandomnId === '') {
                throw new Exception('Lipsește user_randomn_id.');
            }

            $response = $controller->purchaseSubscription($userRandomnId, $data);
            break;
        case 'remove_card':
            $accountRandomnId = trim((string)($data['account_randomn_id'] ?? ''));
            if ($accountRandomnId === '') {
                throw new Exception('Lipsește account_randomn_id.');
            }

            $response = $controller->removeCard($accountRandomnId);
            break;

        default:
            throw new Exception('Acțiune necunoscută: ' . $type);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}