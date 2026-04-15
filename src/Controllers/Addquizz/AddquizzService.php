<?php
declare(strict_types=1);

namespace Evasystem\Controllers\Addquizz;

use Evasystem\Core\Addquizz\AddquizzModel;
use Exception;

class AddquizzService
{
    public function saveQuiz(array $payload, int $idUser): array
    {
        if ($idUser <= 0) {
            throw new Exception('Utilizator nelogat.');
        }

        if (empty($payload)) {
            throw new Exception('Date invalide.');
        }

        $settings = $payload['settings'] ?? [];
        $slides   = $payload['slides'] ?? [];

        if (!is_array($settings)) {
            $settings = [];
        }

        if (!is_array($slides)) {
            $slides = [];
        }

        $idQuiz = isset($payload['id_quiz']) ? (int)$payload['id_quiz'] : 0;
        $idFolder = isset($payload['id_folder']) && $payload['id_folder'] !== ''
            ? (int)$payload['id_folder']
            : null;

        $quizTitle = !empty($settings['title'])
            ? trim((string)$settings['title'])
            : (!empty($slides[0]['title']) ? trim((string)$slides[0]['title']) : 'Quiz fără titlu');

        $visibility = !empty($settings['visibility']) ? (string)$settings['visibility'] : 'private';
        $lang       = !empty($settings['lang']) ? (string)$settings['lang'] : 'ro';
        $coverImage = !empty($settings['coverImage']) ? (string)$settings['coverImage'] : null;
        $themeUrl   = !empty($settings['themeUrl']) ? (string)$settings['themeUrl'] : null;
        $musicUrl   = !empty($settings['musicUrl']) ? (string)$settings['musicUrl'] : null;

        $normalizedPayload = $payload;
        $normalizedPayload['id_user'] = $idUser;

        $continutJson = json_encode($normalizedPayload, JSON_UNESCAPED_UNICODE);

        if ($continutJson === false) {
            throw new Exception('Nu s-a putut genera JSON-ul quizului.');
        }

        $dataForDb = [
            'titlu'         => $quizTitle,
            'continut_json' => $continutJson,
            'id_folder'     => $idFolder,
            'title'         => $quizTitle,
            'visibility'    => $visibility,
            'lang'          => $lang,
            'cover_image'   => $coverImage,
            'theme_url'     => $themeUrl,
            'music_url'     => $musicUrl,
        ];

        if ($idQuiz > 0) {
            $existing = AddquizzModel::getQuizById($idQuiz, $idUser);
            if (!$existing) {
                throw new Exception('Quizul nu există sau nu aparține utilizatorului.');
            }

            AddquizzModel::updateQuiz($idQuiz, $idUser, $dataForDb);

            return [
                'success' => true,
                'message' => 'Quiz actualizat cu succes.',
                'id_quiz' => $idQuiz,
                'randomn_id' => $existing['randomn_id'] ?? '',
            ];
        }

        $randomnId = AddquizzModel::generateRandomnId();

        $insertId = AddquizzModel::createQuiz([
            'randomn_id'    => $randomnId,
            'id_user'       => $idUser,
            'titlu'         => $dataForDb['titlu'],
            'continut_json' => $dataForDb['continut_json'],
            'id_folder'     => $dataForDb['id_folder'],
            'title'         => $dataForDb['title'],
            'visibility'    => $dataForDb['visibility'],
            'lang'          => $dataForDb['lang'],
            'cover_image'   => $dataForDb['cover_image'],
            'theme_url'     => $dataForDb['theme_url'],
            'music_url'     => $dataForDb['music_url'],
        ]);

        return [
            'success' => true,
            'message' => 'Quiz creat cu succes.',
            'id_quiz' => $insertId,
            'randomn_id' => $randomnId,
        ];
    }
}