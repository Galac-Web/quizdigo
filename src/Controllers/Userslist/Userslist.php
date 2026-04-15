<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Userslist;

use Evasystem\Controllers\Userslist\UserslistService;

class Userslist
{
    private UserslistService $userslistService;
    private array $arrayAdd = [];

    public function __construct(UserslistService $userslistService)
    {
        $this->userslistService = $userslistService;
    }

    public function setArrayAdd(array $postData = [], array $additionalData = []): void
    {
        $excludedKeys = [
            'type',
            'idusers',
            'randomnid',
            'randomn_id',
            'usersveryfi',
            'experiences',
            'type_product'
        ];

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
                $usersConnect[$key] = trim((string)$value);
            }
        }

        try {
            $results = [];

            if (!empty($usersConnect)) {
                if (!empty($data['ridusers'])) {
                    $results['connect'] = $this->userslistService->editUsers([
                        'data' => $usersConnect,
                        'db' => 'users_info',
                        'randomn_id' => $data['ridusers'],
                        'exceptions' => ['type', 'idusers', 'randomnid', 'type_product', 'duct']
                    ]);
                } else {
                    $results['connect'] = $this->userslistService->addUser(
                        $usersConnect,
                        'users_info',
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

    /**
     * Salvează complet:
     * - users_info
     * - users_connect
     */
    public function saveProfileInfo(array $data = []): array
    {
        try {
            $cleanData = [];

            foreach ($data as $key => $value) {
                if (is_scalar($value)) {
                    $cleanData[$key] = trim((string)$value);
                }
            }

            $userId    = $cleanData['id'] ?? '';
            $randomnId = $cleanData['randomn_id'] ?? '';

            if ($userId === '' && $randomnId === '') {
                return [
                    'success' => false,
                    'message' => 'Lipsește ID-ul utilizatorului.'
                ];
            }

            $firstName   = trim((string)($cleanData['first_name'] ?? ''));
            $lastName    = trim((string)($cleanData['last_name'] ?? ''));
            $email       = trim((string)($cleanData['email'] ?? ''));
            $phone       = trim((string)($cleanData['phone'] ?? ''));
            $city        = trim((string)($cleanData['city'] ?? ''));
            $country     = trim((string)($cleanData['country'] ?? ''));
            $role        = trim((string)($cleanData['role'] ?? ''));
            $designation = trim((string)($cleanData['designation'] ?? ''));
            $password    = trim((string)($cleanData['password'] ?? ''));

            $fullName = trim($firstName . ' ' . $lastName);

            $infoData = [
                'fname'      => $firstName,
                'lastname'   => $lastName,
                'email'      => $email,
                'tel'        => $phone,
                'city'       => $city,
                'countor'    => $country,
                'des'        => $designation,
                'connect_id' => $userId,
                'randomn_id' => $randomnId,
            ];

            $connectData = [
                'fullname'   => $fullName,
                'role'       => $role,
                'randomn_id' => $randomnId,
            ];

            if ($email !== '') {
                $connectData['login'] = $email;
                $connectData['contact'] = $email;
            }

            if ($password !== '') {
                $connectData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $existingInfo = null;

            if ($userId !== '') {
                $existingInfo = $this->userslistService->findByConnectId($userId, 'users_info');
            }

            if (!$existingInfo && $randomnId !== '') {
                $existingInfo = $this->userslistService->findByRandomnId($randomnId, 'users_info');
            }

            $infoSaved = false;

            if ($existingInfo) {
                $existingInfoRandomnId = $existingInfo['randomn_id'] ?? '';

                if ($existingInfoRandomnId === '') {
                    return [
                        'success' => false,
                        'message' => 'users_info există, dar nu are randomn_id.'
                    ];
                }

                $infoSaved = $this->userslistService->editUsers([
                    'data' => $infoData,
                    'db' => 'users_info',
                    'randomn_id' => $existingInfoRandomnId,
                    'exceptions' => []
                ]);
            } else {
                $infoSaved = $this->userslistService->addUser($infoData, 'users_info', []);
            }

            $existingConnect = null;

            if ($randomnId !== '') {
                $existingConnect = $this->userslistService->findByRandomnId($randomnId, 'users_connect');
            }

            if (!$existingConnect && $userId !== '') {
                $existingConnect = $this->userslistService->findByConnectId($userId, 'users_connect');
            }

            $connectSaved = false;

            if ($existingConnect) {
                $connectRandomnId = $existingConnect['randomn_id'] ?? '';

                if ($connectRandomnId === '') {
                    return [
                        'success' => false,
                        'message' => 'users_connect există, dar nu are randomn_id.'
                    ];
                }

                $connectSaved = $this->userslistService->editUsers([
                    'data' => $connectData,
                    'db' => 'users_connect',
                    'randomn_id' => $connectRandomnId,
                    'exceptions' => []
                ]);
            } else {
                $connectData['connect_id'] = $userId;
                $connectSaved = $this->userslistService->addUser($connectData, 'users_connect', []);
            }

            return [
                'success' => ($infoSaved || $connectSaved),
                'message' => 'Profilul a fost procesat cu succes.',
                'users_info_saved' => $infoSaved,
                'users_connect_saved' => $connectSaved,
                'mode_info' => $existingInfo ? 'update' : 'insert',
                'mode_connect' => $existingConnect ? 'update' : 'insert',
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Update error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * DOAR users_info
     */
    public function saveOnlyProfileInfo(array $data = []): array
    {
        try {
            $cleanData = [];

            foreach ($data as $key => $value) {
                if (is_scalar($value)) {
                    $cleanData[$key] = trim((string)$value);
                }
            }

            $userId    = $cleanData['id'] ?? '';
            $randomnId = $cleanData['randomn_id'] ?? '';

            if ($userId === '' && $randomnId === '') {
                return [
                    'success' => false,
                    'message' => 'Lipsește ID-ul utilizatorului.'
                ];
            }

            $infoData = [
                'fname'      => trim((string)($cleanData['first_name'] ?? '')),
                'lastname'   => trim((string)($cleanData['last_name'] ?? '')),
                'tel'        => trim((string)($cleanData['phone'] ?? '')),
                'city'       => trim((string)($cleanData['city'] ?? '')),
                'countor'    => trim((string)($cleanData['country'] ?? '')),
                'des'        => trim((string)($cleanData['designation'] ?? '')),
                'connect_id' => $userId,
                'randomn_id' => $randomnId,
            ];

            $existingInfo = null;

            if ($userId !== '') {
                $existingInfo = $this->userslistService->findByConnectId($userId, 'users_info');
            }

            if (!$existingInfo && $randomnId !== '') {
                $existingInfo = $this->userslistService->findByRandomnId($randomnId, 'users_info');
            }

            if ($existingInfo) {
                $ok = $this->userslistService->editUsers([
                    'data' => $infoData,
                    'db' => 'users_info',
                    'randomn_id' => $existingInfo['randomn_id'],
                    'exceptions' => []
                ]);

                return [
                    'success' => $ok,
                    'message' => $ok ? 'Profilul a fost actualizat.' : 'Update profil nereușit.'
                ];
            }

            $ok = $this->userslistService->addUser($infoData, 'users_info', []);

            return [
                'success' => $ok,
                'message' => $ok ? 'Profilul a fost adăugat.' : 'Insert profil nereușit.'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Eroare profil: ' . $e->getMessage()
            ];
        }
    }

    /**
     * DOAR users_connect
     */
    public function saveOnlySecurityInfo(array $data = []): array
    {
        try {
            $cleanData = [];

            foreach ($data as $key => $value) {
                if (is_scalar($value)) {
                    $cleanData[$key] = trim((string)$value);
                }
            }

            $userId    = $cleanData['id'] ?? '';
            $randomnId = $cleanData['randomn_id'] ?? '';

            if ($userId === '' && $randomnId === '') {
                return [
                    'success' => false,
                    'message' => 'Lipsește ID-ul utilizatorului.'
                ];
            }

            $email    = trim((string)($cleanData['email'] ?? ''));
            $role     = trim((string)($cleanData['role'] ?? ''));
            $password = trim((string)($cleanData['password'] ?? ''));

            $existingConnect = null;

            if ($randomnId !== '') {
                $existingConnect = $this->userslistService->findByRandomnId($randomnId, 'users_connect');
            }

            if (!$existingConnect && $userId !== '') {
                $existingConnect = $this->userslistService->findByConnectId($userId, 'users_connect');
            }

            if (!$existingConnect) {
                return [
                    'success' => false,
                    'message' => 'Utilizatorul de autentificare nu a fost găsit în users_connect.'
                ];
            }

            $connectData = [
                'role'       => $role,
                'randomn_id' => $existingConnect['randomn_id'] ?? $randomnId
            ];

            if ($email !== '') {
                $connectData['login'] = $email;
                $connectData['contact'] = $email;
            }

            if ($password !== '') {
                $connectData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $ok = $this->userslistService->editUsers([
                'data' => $connectData,
                'db' => 'users_connect',
                'randomn_id' => $existingConnect['randomn_id'],
                'exceptions' => []
            ]);

            return [
                'success' => $ok,
                'message' => $ok ? 'Datele de acces au fost actualizate.' : 'Update securitate nereușit.'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Eroare securitate: ' . $e->getMessage()
            ];
        }
    }
    private function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
    public function saveAvatar(array $data = [], array $files = []): array
    {
        try {
            $userId    = trim((string)($data['id'] ?? ''));
            $randomnId = trim((string)($data['randomn_id'] ?? ''));

            if ($userId === '' && $randomnId === '') {
                return [
                    'success' => false,
                    'message' => 'Lipsește ID-ul utilizatorului.'
                ];
            }

            if (!isset($files['avatar']) || !is_array($files['avatar'])) {
                return [
                    'success' => false,
                    'message' => 'Nu a fost trimis niciun fișier.'
                ];
            }

            $avatar = $files['avatar'];

            if (($avatar['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'message' => 'Eroare la upload fișier.'
                ];
            }

            $tmpName = $avatar['tmp_name'] ?? '';
            $originalName = $avatar['name'] ?? '';

            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                return [
                    'success' => false,
                    'message' => 'Fișier invalid.'
                ];
            }

            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $allowed, true)) {
                return [
                    'success' => false,
                    'message' => 'Format imagine neacceptat. Folosește jpg, png, webp sau gif.'
                ];
            }

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/users/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                    return [
                        'success' => false,
                        'message' => 'Nu s-a putut crea directorul pentru imagini.'
                    ];
                }
            }

            $newFileName = 'user_' . ($randomnId !== '' ? $randomnId : $userId) . '_' . time() . '.' . $ext;
            $fullPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($tmpName, $fullPath)) {
                return [
                    'success' => false,
                    'message' => 'Nu s-a putut salva imaginea pe server.'
                ];
            }

            $photoUrl = $this->getCurrentUrl() . '/uploads/users/' . $newFileName;

            $existingConnect = null;
            if ($randomnId !== '') {
                $existingConnect = $this->userslistService->findByRandomnId($randomnId, 'users_connect');
            }
            if (!$existingConnect && $userId !== '') {
                $existingConnect = $this->userslistService->findByConnectId($userId, 'users_connect');
            }

            if (!$existingConnect) {
                return [
                    'success' => false,
                    'message' => 'Nu a fost găsit utilizatorul în users_connect pentru salvarea imaginii.'
                ];
            }

            $ok = $this->userslistService->editUsers([
                'data' => [
                    'photo' => $photoUrl
                ],
                'db' => 'users_connect',
                'randomn_id' => $existingConnect['randomn_id'],
                'exceptions' => []
            ]);

            if (!$ok) {
                return [
                    'success' => false,
                    'message' => 'Imaginea a fost încărcată, dar nu s-a salvat în baza de date.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Imaginea a fost actualizată cu succes.',
                'photo_url' => $photoUrl
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Eroare avatar: ' . $e->getMessage()
            ];
        }
    }
    public function edit(string $taskId, array $postData): void
    {
        $this->setArrayAdd($postData);
        $this->userslistService->updateTaskInfo($taskId, $this->arrayAdd);
    }

    public function editStatus(array $postData): array
    {
        return $this->saveProfileInfo($postData);
    }
}