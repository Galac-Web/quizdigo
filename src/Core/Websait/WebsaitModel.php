<?php

namespace Evasystem\Core\Websait;

use Evasystem\Core\AdvancedCRUD;

class WebsaitModel
{
    public static function getWebsaitsAll()
    {
        return AdvancedCRUD::select('websait');
    }

    public static function getWebsaitsId($id, $db = 'websait', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'websait')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('websait', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'websait', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'websait')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
