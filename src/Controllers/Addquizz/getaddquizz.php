<?php
declare(strict_types=1);

use Evasystem\Core\Addquizz\AddquizzModel;

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (empty($_SESSION['user_id'])) {
        throw new Exception('Utilizator nelogat!');
    }

    $idUser = (int)$_SESSION['user_id'];
    $quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($quizId <= 0) {
        throw new Exception('ID quiz invalid.');
    }

    $quiz = AddquizzModel::getQuizById($quizId, $idUser);
    if (!$quiz) {
        throw new Exception('Quiz inexistent.');
    }

    $data = json_decode((string)$quiz['continut_json'], true);
    if (!is_array($data)) {
        $data = [];
    }

    $data['id_quiz'] = (int)$quiz['id'];

    echo json_encode([
        'success' => true,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}