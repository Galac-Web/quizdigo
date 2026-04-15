<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Userslist;

use Evasystem\Core\Userslist\UserslistModel;

class UserslistService
{
    private array $arrayadd = [];

    public function getArrayadd(): array
    {
        return $this->arrayadd;
    }

    public function getAllUserslists(): array
    {
        return UserslistModel::getUserslistsAll();
    }

    public function getIdUserslists($id): array
    {
        return UserslistModel::getUserslistsId($id);
    }

    public function deleteUserslist($id): array
    {
        return UserslistModel::del($id);
    }

    public function setArrayadd($arrayadd = null, $additionalData = [], $exceptions = []): void
    {
        if (!is_array($arrayadd)) {
            $arrayadd = [];
        }

        $filteredPost = array_filter(
            array_diff_key($arrayadd, array_flip($exceptions)),
            function ($value, $key) {
                if ($key === '') {
                    return false;
                }

                if (is_array($value)) {
                    return !empty($value);
                }

                return trim((string)$value) !== '';
            },
            ARRAY_FILTER_USE_BOTH
        );

        $taskData = array_merge($filteredPost, $additionalData);
        $this->arrayadd = $taskData;
    }

    public function addUser(array $data, string $db, array $ex = []): bool
    {
        $taskData = [
            'randomn_id' => $data['randomn_id'] ?? (string)rand(20, 1000000),
        ];

        if (!empty($data['connect_id'])) {
            $taskData['connect_id'] = $data['connect_id'];
        }

        if (!empty($data['id_users'])) {
            $taskData['id_users'] = $data['id_users'];
        }

        $this->setArrayadd($data, $taskData, $ex);

        $cleanedData = array_filter($this->getArrayadd(), function ($value) {
            return $value !== '' && $value !== null;
        });

        return UserslistModel::createTask($cleanedData, $db);
    }

    public function editUsers(array $options = []): bool
    {
        $data       = $options['data'] ?? [];
        $db         = $options['db'] ?? 'users_info';
        $exceptions = $options['exceptions'] ?? [];
        $randomnId  = $options['randomn_id'] ?? null;

        if (empty($randomnId)) {
            return false;
        }

        $this->setArrayadd($data, [], $exceptions);

        return UserslistModel::udape((string)$randomnId, $this->getArrayadd(), $db);
    }

    public function findByConnectId(string $connectId, string $table = 'users_info'): ?array
    {
        $rows = UserslistModel::findWhere($table, 'connect_id', $connectId);

        if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) {
            return $rows[0];
        }

        if (is_array($rows) && !empty($rows) && isset($rows['id'])) {
            return $rows;
        }

        return null;
    }

    public function findByRandomnId(string $randomnId, string $table = 'users_info'): ?array
    {
        $rows = UserslistModel::findWhere($table, 'randomn_id', $randomnId);

        if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) {
            return $rows[0];
        }

        if (is_array($rows) && !empty($rows) && isset($rows['id'])) {
            return $rows;
        }

        return null;
    }
}