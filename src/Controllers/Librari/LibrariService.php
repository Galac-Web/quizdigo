<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Librari;

use Evasystem\Core\Librari\LibrariModel;

class LibrariService
{
    public function getDashboardData(int $userId, ?string $folderRandomnId = null, string $view = 'all'): array
    {
        $stats = LibrariModel::getStatsByUserId($userId);
        $folders = LibrariModel::getFoldersByUserId($userId);

        $folderId = null;
        $activeFolder = null;

        if ($view === 'folder' && $folderRandomnId !== null && $folderRandomnId !== '') {
            $activeFolder = LibrariModel::getFolderByRandomnId($folderRandomnId);
            if ($activeFolder && (int)$activeFolder['id_user'] === $userId) {
                $folderId = (int)$activeFolder['id'];
            }
        }

        if ($view === 'folder' && $folderId !== null) {
            $quizzes = LibrariModel::getQuizzesByUserId($userId, $folderId, false);
        } else {
            $quizzes = LibrariModel::getQuizzesByUserId($userId, null, true);
        }

        return [
            'stats' => $stats,
            'folders' => $folders,
            'quizzes' => $quizzes,
            'active_folder' => $activeFolder,
            'view' => $view,
        ];
    }
    public function getDescriptionPageData(int $quizId, int $userId, string $view = 'all', int $folderId = 0): array
    {
        $currentQuiz = \Evasystem\Core\Librari\LibrariModel::getQuizById($quizId);

        $sliderImages = [];
        $mainTitle = 'Quiz';
        $mainDesc = 'Nicio descriere disponibilă.';
        $mainBg = 'default-bg.jpg';

        if ($currentQuiz) {
            $data = json_decode((string)$currentQuiz['continut_json'], true);
            if (!is_array($data)) {
                $data = [];
            }

            $mainTitle = !empty($data['settings']['title'])
                ? (string)$data['settings']['title']
                : (string)($currentQuiz['titlu'] ?? 'Quiz');

            $mainDesc = !empty($data['settings']['description'])
                ? (string)$data['settings']['description']
                : 'Nicio descriere disponibilă.';

            $mainBg = !empty($data['settings']['themeUrl'])
                ? (string)$data['settings']['themeUrl']
                : 'default-bg.jpg';

            if (!empty($data['slides']) && is_array($data['slides'])) {
                foreach ($data['slides'] as $slide) {
                    if (!empty($slide['imageCenter'])) {
                        $sliderImages[] = (string)$slide['imageCenter'];
                    }
                    if (!empty($slide['background'])) {
                        $sliderImages[] = (string)$slide['background'];
                    }
                }
            }
        }

        $sliderImages = array_values(array_unique(array_filter($sliderImages)));
        if (empty($sliderImages)) {
            $sliderImages = [$mainBg];
        }

        $quizzes = [];
        if ($userId > 0) {
            if ($view === 'folder' && $folderId > 0) {
                $quizzes = \Evasystem\Core\Librari\LibrariModel::getQuizzesByUserId($userId, $folderId, false);
            } else {
                $quizzes = \Evasystem\Core\Librari\LibrariModel::getQuizzesByUserId($userId, null, true);
            }
        }

        $schedules = [];
        if ($quizId > 0) {
            $schedules = \Evasystem\Core\Librari\LibrariModel::getSchedulesByQuizId($quizId);
        }

        return [
            'current_quiz' => $currentQuiz,
            'slider_images' => $sliderImages,
            'main_title' => $mainTitle,
            'main_desc' => $mainDesc,
            'main_bg' => $mainBg,
            'quizzes' => $quizzes,
            'schedules' => $schedules,
        ];
    }

    public function scheduleGame(int $quizId, int $userId, string $date): array
    {
        if ($quizId <= 0) {
            throw new \Exception('Quiz invalid.');
        }

        if ($userId <= 0) {
            throw new \Exception('Utilizator neautentificat.');
        }

        if (trim($date) === '') {
            throw new \Exception('Data este obligatorie.');
        }

        $start = date('Y-m-d 10:00:00', strtotime($date));
        $end   = date('Y-m-d 12:00:00', strtotime($date));
        $pin   = (string)random_int(1000000, 9999999);
        $link  = 'https://quizdigo.live/game/play.php?quiz_id=' . $quizId . '&pin=' . $pin;

        $ok = \Evasystem\Core\Librari\LibrariModel::createSchedule([
            'randomn_id' => \Evasystem\Core\Librari\LibrariModel::uid('sch_'),
            'quiz_id' => $quizId,
            'id_user' => $userId,
            'start_at' => $start,
            'end_at' => $end,
            'game_pin' => $pin,
            'game_link' => $link,
            'status' => 'scheduled',
        ]);

        if (!$ok) {
            throw new \Exception('Nu s-a putut salva programarea.');
        }

        return [
            'success' => true,
            'message' => 'Quiz programat cu succes.',
            'start_at' => $start,
            'end_at' => $end,
            'game_pin' => $pin,
            'game_link' => $link,
            'qr_link' => 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($link),
        ];
    }
    public function createFolder(int $userId, string $folderName): bool
    {
        $folderName = trim($folderName);
        if ($folderName === '') {
            throw new \Exception('Numele folderului este gol.');
        }

        return LibrariModel::createFolder([
            'randomn_id' => LibrariModel::uid('fld_'),
            'id_user' => $userId,
            'nume_folder' => $folderName,
        ]);
    }

    public function updateFolder(int $userId, string $folderRandomnId, string $folderName): bool
    {
        $folderName = trim($folderName);
        if ($folderName === '') {
            throw new \Exception('Numele folderului este gol.');
        }

        $folder = LibrariModel::getFolderByRandomnId($folderRandomnId);
        if (!$folder || (int)$folder['id_user'] !== $userId) {
            throw new \Exception('Folder inexistent sau acces interzis.');
        }

        return LibrariModel::updateFolder($folderRandomnId, [
            'nume_folder' => $folderName
        ]);
    }

    public function deleteFolder(int $userId, string $folderRandomnId): bool
    {
        $folder = LibrariModel::getFolderByRandomnId($folderRandomnId);
        if (!$folder || (int)$folder['id_user'] !== $userId) {
            throw new \Exception('Folder inexistent sau acces interzis.');
        }

        LibrariModel::moveQuizzesFromFolderToNull((int)$folder['id']);
        return LibrariModel::deleteFolder($folderRandomnId);
    }

    public function duplicateQuiz(int $userId, int $quizId): bool
    {
        return LibrariModel::duplicateQuiz($userId, $quizId);
    }

    public function deleteQuiz(int $userId, int $quizId): bool
    {
        return LibrariModel::deleteQuiz($userId, $quizId);
    }

    public function moveQuiz(int $userId, int $quizId, ?int $folderId): bool
    {
        return LibrariModel::moveQuiz($userId, $quizId, $folderId);
    }
}