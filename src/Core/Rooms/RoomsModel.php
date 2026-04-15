<?php

namespace Evasystem\Core\Rooms;

use Evasystem\Core\AdvancedCRUD;

class RoomsModel
{
    public static function getRoomssAll()
    {
        return AdvancedCRUD::select('rooms');
    }

    public static function getRoomssId($id, $db = 'rooms', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'rooms')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('rooms', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'rooms', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'rooms')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
