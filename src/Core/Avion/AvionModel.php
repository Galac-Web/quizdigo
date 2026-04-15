<?php

namespace Evasystem\Core\Avion;

use Evasystem\Core\AdvancedCRUD;

class AvionModel
{
    public static function getAvionsAll()
    {
        return AdvancedCRUD::select('avion');
    }

    public static function getAvionsId($id, $db = 'avion', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'avion')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('avion', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'avion', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'avion')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
