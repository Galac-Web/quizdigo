<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Avion;

use Evasystem\Controllers\Avion\AvionService;

class Avion
{
    private AvionService $avionService;
    private array $arrayAdd = [];

    public function __construct(AvionService $avionService)
    {
        $this->avionService = $avionService;
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
            $results['connect'] = $this->avionService->editUsers([
            'data' => $usersConnect,
            'db' => 'avion',
            'exceptions' => ['type', 'idusers', 'randomnid', 'type_product','duct']
            ]);
            } else {
            $results['connect'] = $this->avionService->addUser(
            $usersConnect,
            'avion',
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
    public function addroles(array $data = []): array
    {
        // luăm userul din sesiune
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Userul nu este autentificat.'
            ];
        }

        // Filtrăm datele și adăugăm user_id automat
        $usersConnect = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value) && $value !== '') {
                $usersConnect[$key] = trim((string) $value);
            }
        }

        // suprascriem orice valoare existentă — user_id vine DOAR din sesiune
        $usersConnect['user_id'] = $userId;

        try {
            $results = [];

            if (!empty($usersConnect)) {

                // dacă există ridusers => UPDATE
                if (!empty($data['ridusers'])) {
                    $results['connect'] = $this->avionService->editUsers([
                        'data' => $usersConnect,
                        'db' => 'avion_room_participants',
                        'exceptions' => ['type', 'idusers', 'randomnid', 'type_product','duct']
                    ]);

                    // dacă nu există => INSERT
                } else {
                    $results['connect'] = $this->avionService->addUser(
                        $usersConnect,
                        'avion_room_participants',
                        ['type', 'idusers', 'randomnid', 'type_product']
                    );
                }
            }

            return [
                'success' => true,
                'message' => 'Role saved.',
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
        $this->avionService->updateTaskInfo($taskId, $this->arrayAdd);
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
            $this->avionService->editUsers([
            'data' => $cleanData,
            'db' => 'avion',
            'id' => $postData['id'],
            'exceptions' => $exceptions
            ]);
        }
}
