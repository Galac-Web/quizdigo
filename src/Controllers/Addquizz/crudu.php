<?php
declare(strict_types=1);

use Evasystem\Controllers\Addquizz\Addquizz;
use Evasystem\Controllers\Addquizz\AddquizzService;

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (empty($_SESSION['user_id'])) {
        throw new Exception('Utilizator nelogat!');
    }

    $idUser = (int)$_SESSION['user_id'];

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!is_array($payload)) {
        throw new Exception('Date invalide!');
    }

    $controller = new Addquizz(new AddquizzService());
    $result = $controller->save($payload, $idUser);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}