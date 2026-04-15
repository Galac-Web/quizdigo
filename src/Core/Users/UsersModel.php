<?php

namespace Evasystem\Core\Users;

use Evasystem\Core\AdvancedCRUD;
use PDO;
use Config\Database;
class UsersModel
{
    public static function getUserssAll()
    {
        return AdvancedCRUD::select('users_connect');
    }

    public static function getUserssId($id, $db = 'users_connect', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }
    public static function findByLogin($id, $db = 'users_connect', $where = 'login')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'users_connect')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('users_connect', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'users_connect', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'users_connect')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
