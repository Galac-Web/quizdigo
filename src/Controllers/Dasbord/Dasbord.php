<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Dasbord;

use Evasystem\Controllers\Dasbord\DasbordService;

class Dasbord
{
    private DasbordService $dasbordService;
    private array $arrayAdd = [];

    public function __construct(DasbordService $dasbordService)
    {
        $this->dasbordService = $dasbordService;
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
            $results['connect'] = $this->dasbordService->editUsers([
            'data' => $usersConnect,
            'db' => 'dasbord',
            'exceptions' => ['type', 'idusers', 'randomnid', 'type_product','duct']
            ]);
            } else {
            $results['connect'] = $this->dasbordService->addUser(
            $usersConnect,
            'dasbord',
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
        $this->dasbordService->updateTaskInfo($taskId, $this->arrayAdd);
    }

    public function editStatus(array $postData): void
    {
        $cleanData = [];
        foreach ($postData as $key => $value) {
        if (is_scalar($value) && $value !== '' && $key !== 'id') {
        $cleanData[$key] = trim((string) $value);
        }
    }

    $exceptions = ['type', 'idusers', 'randomnid', 'type_product', 'id'];
            $this->dasbordService->editUsers([
            'data' => $cleanData,
            'db' => 'dasbord',
            'id' => $postData['id'],
            'exceptions' => $exceptions
            ]);
        }
}
