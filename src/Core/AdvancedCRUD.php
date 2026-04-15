<?php
namespace Evasystem\Core;

use PDO;
use Config\Database;

class AdvancedCRUD
{
    /**
     * Select simplu din tabelă
     */
    public static function select($tableName, $columns = '*', $whereClause = '', $orderBy = '', $limit = '')
    {
        $pdo = Database::getDB();
        $sql = "SELECT $columns FROM $tableName";

        if (!empty($whereClause)) {
            $sql .= " $whereClause";
        }
        if (!empty($orderBy)) {
            $sql .= " ORDER BY $orderBy";
        }
        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function selectnew(
        string $table,
        string $columns = '*',
        string $where = '',
        string $orderBy = '',
        ?string $limit = null,
        array $params = []
    ): array {
        $pdo = \Config\Database::getDB();

        $sql = "SELECT $columns FROM $table";
        if ($where)   { $sql .= " $where"; }
        if ($orderBy) { $sql .= " ORDER BY $orderBy"; }
        if ($limit)   { $sql .= " LIMIT $limit"; }

        $stmt = $pdo->prepare($sql);
        $ok   = $stmt->execute($params);   // <-- IMPORTANT: trece $params aici!
        if (!$ok) return [];
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Inserare în tabel
     */
    public static function create($tableName, $data)
    {
        $pdo = Database::getDB();

        // Hash la parolă dacă e cazul
        if (isset($data['password']) && !isset($_GET['usid'])) {
            $data['password'] = md5($data['password']);
        }

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return $stmt->rowCount() > 0;
    }

    /**
     * Update în tabel
     */
    public static function update($tableName, $data, $whereClause)
    {
        $pdo = Database::getDB();
        $set = implode(", ", array_map(fn($key) => "$key = ?", array_keys($data)));
        $sql = "UPDATE $tableName SET $set $whereClause";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return $stmt->rowCount() > 0;
    }

    /**
     * Ștergere din tabel
     */
    public static function delete($tableName, $whereClause)
    {
        $pdo = Database::getDB();
        $sql = "DELETE FROM $tableName $whereClause";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Ștergere imagine pe baza sesiunii
     */
    public static function delete_img($tableName)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pdo = Database::getDB();
        $sql = "DELETE FROM img_bd WHERE nameImg = ? AND id_users = ?";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            $tableName,
            $_SESSION['user_id'] ?? 0
        ]);
    }
}
