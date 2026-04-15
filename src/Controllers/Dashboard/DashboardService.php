<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Dashboard;

use Evasystem\Core\Dashboard\DashboardModel;

class DashboardService
{
    private $arrayadd;

    public function getArrayadd()
    {
        return $this->arrayadd;
    }

    public function getAllDashboards(): array
    {
        return DashboardModel::getDashboardsAll();
    }

    public function getIdDashboards($id): array
    {
        return DashboardModel::getDashboardsId($id);
    }

    public function deleteDashboard($id): array
    {
        return DashboardModel::del($id);
    }

    public function updateTaskInfo(string $taskId, array $data): void
    {
        DashboardModel::updateTaskInfo($taskId, $data);
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
        return DashboardModel::createTask($cleanedData, $db);
    }

    public function editUsers(array $options = []): bool
    {
        $data       = $options['data'] ?? [];
        $db         = $options['db'] ?? 'users_connect';
        $exceptions = $options['exceptions'] ?? [];

        $this->setArrayadd($data, [], $exceptions);

        return DashboardModel::udape($options['id'], $this->getArrayadd(), $db);
    }
}
