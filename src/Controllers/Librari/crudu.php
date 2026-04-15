<?php

declare(strict_types=1);

use Evasystem\Controllers\Librari\Librari;
use Evasystem\Controllers\Librari\LibrariService;

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (empty($_SESSION['user_id'])) {
        throw new Exception('Utilizator neautentificat.');
    }

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

    $type = trim((string)($data['type_product'] ?? ''));
    if ($type === '') {
        throw new Exception('Lipsește type_product.');
    }

    $service = new LibrariService();
    $controller = new Librari($service);
    $userId = (int)$_SESSION['user_id'];

    switch ($type) {
        case 'create_folder':
            $response = $controller->createFolder($userId, $data);
            break;

        case 'update_folder':
            $response = $controller->updateFolder($userId, $data);
            break;

        case 'delete_folder':
            $response = $controller->deleteFolder($userId, $data);
            break;

        case 'duplicate_quiz':
            $response = $controller->duplicateQuiz($userId, $data);
            break;

        case 'delete_quiz':
            $response = $controller->deleteQuiz($userId, $data);
            break;

        case 'move_quiz':
            $response = $controller->moveQuiz($userId, $data);
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