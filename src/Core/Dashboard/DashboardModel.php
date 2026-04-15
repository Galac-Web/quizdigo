<?php

namespace Evasystem\Core\Dashboard;

use Evasystem\Core\AdvancedCRUD;

class DashboardModel
{
    public static function getDashboardsAll()
    {
        return AdvancedCRUD::select('dashboard');
    }

    public static function getDashboardsId($id, $db = 'dashboard', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'dashboard')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('dashboard', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'dashboard', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'dashboard')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
