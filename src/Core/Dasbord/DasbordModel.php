<?php

namespace Evasystem\Core\Dasbord;

use Evasystem\Core\AdvancedCRUD;

class DasbordModel
{
    public static function getDasbordsAll()
    {
        return AdvancedCRUD::select('dasbord');
    }

    public static function getDasbordsId($id, $db = 'dasbord', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'dasbord')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('dasbord', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'dasbord', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'dasbord')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
