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

    $quizId = (int)($data['quiz_id'] ?? 0);

    $service = new LibrariService();
    $controller = new Librari($service);

    switch ($type) {
        case 'schedule_game':
            $response = $controller->scheduleGame($quizId, (int)$_SESSION['user_id'], $data);
            break;

        default:
            throw new Exception('Acțiune necunoscută.');
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}