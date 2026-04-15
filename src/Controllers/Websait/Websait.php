<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Websait;

use Evasystem\Controllers\Websait\WebsaitService;

class Websait
{
    private WebsaitService $websaitService;
    private array $arrayAdd = [];

    public function __construct(WebsaitService $websaitService)
    {
        $this->websaitService = $websaitService;
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
            $results['connect'] = $this->websaitService->editUsers([
            'data' => $usersConnect,
            'db' => 'websait',
            'exceptions' => ['type', 'idusers', 'randomnid', 'type_product','duct']
            ]);
            } else {
            $results['connect'] = $this->websaitService->addUser(
            $usersConnect,
            'websait',
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
        $this->websaitService->updateTaskInfo($taskId, $this->arrayAdd);
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
            $this->websaitService->editUsers([
            'data' => $cleanData,
            'db' => 'websait',
            'id' => $postData['id'],
            'exceptions' => $exceptions
            ]);
        }
}
