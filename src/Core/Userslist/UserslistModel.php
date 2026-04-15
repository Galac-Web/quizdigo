<?php

namespace Evasystem\Core\Userslist;

use Evasystem\Core\AdvancedCRUD;

class UserslistModel
{
    public static function getUserslistsAll($db = 'userslist')
    {
        return AdvancedCRUD::select($db);
    }

    public static function getUserslistsId($id, $db = 'userslist', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE {$where} = '{$id}'");
    }

    public static function createTask($taskData, $db = 'userslist')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData, $db = 'userslist')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE id = '{$taskId}'");
    }

    public static function del($taskId, $db = 'userslist', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE {$where} = '{$taskId}'");
    }

    public static function udape($taskId, $taskData, $db = 'userslist')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = '{$taskId}'");
    }

    public static function findWhere(string $db, string $field, string $value)
    {
        return AdvancedCRUD::select($db, '*', "WHERE {$field} = '{$value}'");
    }
}