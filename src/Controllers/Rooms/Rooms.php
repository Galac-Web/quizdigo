<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Rooms;

use Evasystem\Controllers\Rooms\RoomsService;

class Rooms
{
    private RoomsService $roomsService;
    private array $arrayAdd = [];

    public function __construct(RoomsService $roomsService)
    {
        $this->roomsService = $roomsService;
    }
    public function setArrayAdd(array $postData = [], array $additionalData = []): void
    {

        $excludedKeys = ['type', 'idusers', 'randomnid', 'usersveryfi', 'experiences'];
        $filteredData = array_diff_key($postData, array_flip($excludedKeys));
        $this->arrayAdd = array_merge($filteredData, $additionalData);

    }

    public function getArrayAdd(): array
    {
        return $this->arrayAdd;
    }

    public function addProfileInfo(array $data = []): array
    {
        $usersConnect = [];
        foreach ($data as $key => $value) {
        if (is_scalar($value) && $value !== '') {
        $usersConnect[$key] = trim((string) $value);
        }
    }

    try {
        $results = [];
            if (!empty($usersConnect)) {
            if (!empty($data['ridusers'])) {
            $results['connect'] = $this->roomsService->editUsers([
            'data' => $usersConnect,
            'db' => 'rooms',
            'exceptions' => ['type', 'idusers', 'randomnid', 'type_product','duct']
            ]);
            } else {
            $results['connect'] = $this->roomsService->addUser(
            $usersConnect,
            'rooms',
            ['type', 'idusers', 'randomnid', 'type_product']
            );
        }
    }

    return [
    'success' => true,
    'message' => 'Company profile saved.',
    'results' => $results
    ];

    } catch (\Throwable $e) {
            return [
            'success' => false,
            'message' => 'Update error: ' . $e->getMessage()
            ];
        }
    }

    public function edit(string $taskId, array $postData): void
    {
        $this->setArrayAdd($postData);
        $this->roomsService->updateTaskInfo($taskId, $this->arrayAdd);
    }

    public function editStatus(array $postData): void
    {
        $cleanData = [];
        foreach ($postData as $key => $value) {
        if (is_scalar($value) && $value !== '' && $key !== 'randomn_id') {
        $cleanData[$key] = trim((string) $value);
        }
    }

    $exceptions = ['type', 'idusers', 'randomnid', 'type_product', 'id'];
            $this->roomsService->editUsers([
            'data' => $cleanData,
            'db' => 'rooms',
            'id' => $postData['randomn_id'],
            'exceptions' => $exceptions
            ]);
        }
}
