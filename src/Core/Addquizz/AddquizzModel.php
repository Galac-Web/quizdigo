<?php
declare(strict_types=1);

namespace Evasystem\Core\Addquizz;

use PDO;
use PDOException;

class AddquizzModel
{
    protected static ?PDO $pdo = null;

    public static function db(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = 'localhost';
        $db   = 'lilit2';
        $user = 'lilit2';
        $pass = 'aM1xN7kS3w';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }

    public static function generateRandomnId(string $prefix = 'quiz_'): string
    {
        try {
            return $prefix . bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            return $prefix . uniqid();
        }
    }

    public static function getQuizById(int $quizId, int $idUser): ?array
    {
        $pdo = self::db();

        $stmt = $pdo->prepare("
            SELECT *
            FROM quizzes
            WHERE id = ? AND id_user = ?
            LIMIT 1
        ");
        $stmt->execute([$quizId, $idUser]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getQuizByRandomnId(string $randomnId, int $idUser): ?array
    {
        $pdo = self::db();

        $stmt = $pdo->prepare("
            SELECT *
            FROM quizzes
            WHERE randomn_id = ? AND id_user = ?
            LIMIT 1
        ");
        $stmt->execute([$randomnId, $idUser]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function createQuiz(array $data): int
    {
        $pdo = self::db();

        $stmt = $pdo->prepare("
            INSERT INTO quizzes
            (
                randomn_id,
                id_user,
                titlu,
                continut_json,
                id_folder,
                title,
                visibility,
                lang,
                cover_image,
                theme_url,
                music_url,
                created_at,
                updated_at
            )
            VALUES
            (
                :randomn_id,
                :id_user,
                :titlu,
                :continut_json,
                :id_folder,
                :title,
                :visibility,
                :lang,
                :cover_image,
                :theme_url,
                :music_url,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            ':randomn_id'    => $data['randomn_id'],
            ':id_user'       => $data['id_user'],
            ':titlu'         => $data['titlu'],
            ':continut_json' => $data['continut_json'],
            ':id_folder'     => $data['id_folder'],
            ':title'         => $data['title'],
            ':visibility'    => $data['visibility'],
            ':lang'          => $data['lang'],
            ':cover_image'   => $data['cover_image'],
            ':theme_url'     => $data['theme_url'],
            ':music_url'     => $data['music_url'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function updateQuiz(int $quizId, int $idUser, array $data): bool
    {
        $pdo = self::db();

        $stmt = $pdo->prepare("
            UPDATE quizzes
            SET
                titlu = :titlu,
                continut_json = :continut_json,
                id_folder = :id_folder,
                title = :title,
                visibility = :visibility,
                lang = :lang,
                cover_image = :cover_image,
                theme_url = :theme_url,
                music_url = :music_url,
                updated_at = NOW()
            WHERE id = :id AND id_user = :id_user
        ");

        return $stmt->execute([
            ':titlu'         => $data['titlu'],
            ':continut_json' => $data['continut_json'],
            ':id_folder'     => $data['id_folder'],
            ':title'         => $data['title'],
            ':visibility'    => $data['visibility'],
            ':lang'          => $data['lang'],
            ':cover_image'   => $data['cover_image'],
            ':theme_url'     => $data['theme_url'],
            ':music_url'     => $data['music_url'],
            ':id'            => $quizId,
            ':id_user'       => $idUser,
        ]);
    }
}