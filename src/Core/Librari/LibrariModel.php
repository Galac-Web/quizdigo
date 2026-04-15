<?php

namespace Evasystem\Core\Librari;

use Evasystem\Core\AdvancedCRUD;
use PDO;

class LibrariModel
{
    private static function pdo(): PDO
    {
        return new PDO(
            "mysql:host=localhost;dbname=lilit2;charset=utf8mb4",
            "lilit2",
            "aM1xN7kS3w",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    public static function getSchedulesByQuizId(int $quizId): array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("
        SELECT
            qs.id,
            qs.randomn_id,
            qs.quiz_id,
            qs.id_user,
            qs.start_at,
            qs.end_at,
            qs.game_pin,
            qs.game_link,
            qs.status,
            qs.created_at,
            (
                SELECT COUNT(*)
                FROM quiz_schedule_participants qsp
                WHERE qsp.schedule_id = qs.id
            ) AS participants_count
        FROM quiz_schedules qs
        WHERE qs.quiz_id = ?
        ORDER BY qs.start_at DESC, qs.id DESC
    ");
        $stmt->execute([$quizId]);

        return $stmt->fetchAll();
    }

    public static function getScheduleById(int $scheduleId): ?array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("
        SELECT *
        FROM quiz_schedules
        WHERE id = ?
        LIMIT 1
    ");
        $stmt->execute([$scheduleId]);

        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function uid(string $prefix = 'lib_'): string
    {
        try {
            return $prefix . bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            return $prefix . uniqid();
        }
    }
    public static function getQuizById(int $quizId): ?array
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? LIMIT 1");
        $stmt->execute([$quizId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function createSchedule(array $data): bool
    {
        return \Evasystem\Core\AdvancedCRUD::create('quiz_schedules', $data);
    }

    public static function getLatestScheduleByQuizId(int $quizId): ?array
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare("
        SELECT *
        FROM quiz_schedules
        WHERE quiz_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
        $stmt->execute([$quizId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function getStatsByUserId(int $userId): array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE id_user = ?");
        $stmt->execute([$userId]);
        $totalQuizzes = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM folders WHERE id_user = ?");
        $stmt->execute([$userId]);
        $totalFolders = (int)$stmt->fetchColumn();

        return [
            'total_quizzes' => $totalQuizzes,
            'total_folders' => $totalFolders,
            'limit_quizzes' => 200,
            'percent' => 200 > 0 ? min(100, max(0, ($totalQuizzes / 200) * 100)) : 0,
        ];
    }

    public static function getFoldersByUserId(int $userId): array
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare("
            SELECT id, randomn_id, id_user, nume_folder
            FROM folders
            WHERE id_user = ?
            ORDER BY nume_folder ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getFolderByRandomnId(string $randomnId): ?array
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare("
            SELECT id, randomn_id, id_user, nume_folder
            FROM folders
            WHERE randomn_id = ?
            LIMIT 1
        ");
        $stmt->execute([$randomnId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getQuizzesByUserId(int $userId, ?int $folderId = null, bool $onlyWithoutFolder = false): array
    {
        $pdo = self::pdo();

        if ($folderId !== null) {
            $stmt = $pdo->prepare("
                SELECT id, randomn_id, titlu, continut_json, last_updated, id_folder
                FROM quizzes
                WHERE id_user = ? AND id_folder = ?
                ORDER BY last_updated DESC
            ");
            $stmt->execute([$userId, $folderId]);
            return $stmt->fetchAll();
        }

        if ($onlyWithoutFolder) {
            $stmt = $pdo->prepare("
                SELECT id, randomn_id, titlu, continut_json, last_updated, id_folder
                FROM quizzes
                WHERE id_user = ? AND id_folder IS NULL
                ORDER BY last_updated DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare("
            SELECT id, randomn_id, titlu, continut_json, last_updated, id_folder
            FROM quizzes
            WHERE id_user = ?
            ORDER BY last_updated DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function createFolder(array $data): bool
    {
        return AdvancedCRUD::create('folders', $data);
    }

    public static function updateFolder(string $randomnId, array $data): bool
    {
        return AdvancedCRUD::update('folders', $data, "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function deleteFolder(string $randomnId): bool
    {
        return AdvancedCRUD::delete('folders', "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function moveQuizzesFromFolderToNull(int $folderId): bool
    {
        return AdvancedCRUD::update('quizzes', ['id_folder' => null], "WHERE id_folder = " . (int)$folderId);
    }

    public static function duplicateQuiz(int $userId, int $quizId): bool
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("
            SELECT *
            FROM quizzes
            WHERE id = ? AND id_user = ?
            LIMIT 1
        ");
        $stmt->execute([$quizId, $userId]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            throw new \Exception('Quiz inexistent.');
        }

        return AdvancedCRUD::create('quizzes', [
            'randomn_id' => self::uid('qz_'),
            'id_user' => $userId,
            'id_folder' => $quiz['id_folder'] ?? null,
            'titlu' => (string)$quiz['titlu'] . ' (Copy)',
            'continut_json' => $quiz['continut_json'] ?? null,
        ]);
    }

    public static function deleteQuiz(int $userId, int $quizId): bool
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE id = ? AND id_user = ? LIMIT 1");
        $stmt->execute([$quizId, $userId]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            throw new \Exception('Quiz inexistent.');
        }

        return AdvancedCRUD::delete('quizzes', "WHERE id = " . (int)$quizId . " AND id_user = " . (int)$userId);
    }

    public static function moveQuiz(int $userId, int $quizId, ?int $folderId): bool
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE id = ? AND id_user = ? LIMIT 1");
        $stmt->execute([$quizId, $userId]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            throw new \Exception('Quiz inexistent.');
        }

        if ($folderId !== null) {
            $stmt = $pdo->prepare("SELECT id FROM folders WHERE id = ? AND id_user = ? LIMIT 1");
            $stmt->execute([$folderId, $userId]);
            $folder = $stmt->fetch();

            if (!$folder) {
                throw new \Exception('Folder inexistent.');
            }
        }

        return AdvancedCRUD::update(
            'quizzes',
            ['id_folder' => $folderId],
            "WHERE id = " . (int)$quizId . " AND id_user = " . (int)$userId
        );
    }
}