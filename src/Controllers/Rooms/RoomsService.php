<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Rooms;

use Evasystem\Core\Rooms\RoomsModel;

class RoomsService
{
    private $arrayadd;

    public function getArrayadd()
    {
        return $this->arrayadd;
    }

    public function getAllRoomss(): array
    {
        return RoomsModel::getRoomssAll();
    }

    public function getIdRoomss($id): array
    {
        return RoomsModel::getRoomssId($id);
    }

    public function deleteRooms($id): array
    {
        // opțional: forțezi int
        $id = (int)$id;

        // presupunem că RoomsModel::del($id) întoarce true/false
        $ok = RoomsModel::del($id);

        return [
            'success'      => (bool) $ok,
            'deleted_rows' => $ok ? 1 : 0, // dacă vrei să știi câte au fost șterse
            'id'           => $id,
        ];
    }

    public function updateTaskInfo(string $taskId, array $data): void
    {
        RoomsModel::updateTaskInfo($taskId, $data);
    }

    public function setArrayadd($arrayadd = null, $additionalData = [], $exceptions = [])
    {
        // Eliminăm cheile din excepții și cele goale
        $filteredPost = array_filter(
        array_diff_key($arrayadd, array_flip($exceptions)),
        function ($value, $key) {
        return $key !== '' && trim((string)$value) !== '';
        },
        ARRAY_FILTER_USE_BOTH
        );

        $taskData = array_merge($filteredPost, $additionalData);
        $this->arrayadd = $taskData;
    }

    public function addUser(array $data, string $db, array $ex = []): bool
    {
        $taskData = [
        "randomn_id" => rand(20, 1000),
        "id_users" => '',
        ];
        $this->setArrayadd($data, $taskData, $ex);

        $cleanedData = array_filter($this->getArrayadd(), function ($value) {
        return $value !== '' && $value !== null;
        });
        return RoomsModel::createTask($cleanedData, $db);
    }

    public function editUsers(array $options = []): bool
    {
        $data       = $options['data'] ?? [];
        $db         = $options['db'] ?? 'users_connect';
        $exceptions = $options['exceptions'] ?? [];

        $this->setArrayadd($data, [], $exceptions);

        return RoomsModel::udape($options['id'], $this->getArrayadd(), $db);
    }
}
