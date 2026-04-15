<?php

namespace Evasystem\Core\Participanti;

use Evasystem\Core\AdvancedCRUD;

class ParticipantiModel
{
    public static function getParticipantisAll()
    {
        return AdvancedCRUD::select('participanti');
    }

    public static function getParticipantisId($id, $db = 'participanti', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE " . $where . " = '$id' ");
    }

    public static function createTask($taskData, $db = 'participanti')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData)
    {
        return AdvancedCRUD::update('participanti', $taskData, "WHERE id = $taskId");
    }

    public static function del($taskId, $db = 'participanti', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE $where = $taskId");
    }

    public static function udape($taskId, $taskData, $db = 'participanti')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = $taskId");
    }
}
