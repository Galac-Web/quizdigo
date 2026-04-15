<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Librari;

class Librari
{
    private LibrariService $librariService;

    public function __construct(LibrariService $librariService)
    {
        $this->librariService = $librariService;
    }

    public function index(int $userId, ?string $folderRandomnId = null, string $view = 'all'): array
    {
        return $this->librariService->getDashboardData($userId, $folderRandomnId, $view);
    }
    public function descriptionPage(int $quizId, int $userId, string $view = 'all', int $folderId = 0): array
    {
        return $this->librariService->getDescriptionPageData($quizId, $userId, $view, $folderId);
    }

    public function scheduleGame(int $quizId, int $userId, array $data): array
    {
        return $this->librariService->scheduleGame(
            $quizId,
            $userId,
            (string)($data['date'] ?? '')
        );
    }
    public function createFolder(int $userId, array $data): array
    {
        $ok = $this->librariService->createFolder($userId, (string)($data['nume_folder'] ?? ''));
        return [
            'success' => $ok,
            'message' => $ok ? 'Folder creat.' : 'Eroare la creare folder.'
        ];
    }

    public function updateFolder(int $userId, array $data): array
    {
        $ok = $this->librariService->updateFolder(
            $userId,
            (string)($data['randomn_id'] ?? ''),
            (string)($data['nume_folder'] ?? '')
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Folder actualizat.' : 'Eroare la actualizare folder.'
        ];
    }

    public function deleteFolder(int $userId, array $data): array
    {
        $ok = $this->librariService->deleteFolder(
            $userId,
            (string)($data['randomn_id'] ?? '')
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Folder șters.' : 'Eroare la ștergere folder.'
        ];
    }

    public function duplicateQuiz(int $userId, array $data): array
    {
        $ok = $this->librariService->duplicateQuiz(
            $userId,
            (int)($data['quiz_id'] ?? 0)
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Quiz duplicat.' : 'Eroare la duplicare quiz.'
        ];
    }

    public function deleteQuiz(int $userId, array $data): array
    {
        $ok = $this->librariService->deleteQuiz(
            $userId,
            (int)($data['quiz_id'] ?? 0)
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Quiz șters.' : 'Eroare la ștergere quiz.'
        ];
    }

    public function moveQuiz(int $userId, array $data): array
    {
        $ok = $this->librariService->moveQuiz(
            $userId,
            (int)($data['quiz_id'] ?? 0),
            isset($data['folder_id']) && $data['folder_id'] !== '' ? (int)$data['folder_id'] : null
        );

        return [
            'success' => $ok,
            'message' => $ok ? 'Quiz mutat.' : 'Eroare la mutare quiz.'
        ];
    }
}