<?php

namespace Evasystem\Core\Abonament;

use Evasystem\Core\AdvancedCRUD;

class AbonamentModel
{
    public static function getAbonamentsAll()
    {
        return AdvancedCRUD::select('abonament');
    }

    public static function getAbonamentsId($id, $db = 'abonament', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'abonament')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('abonament', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'abonament', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'abonament')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
