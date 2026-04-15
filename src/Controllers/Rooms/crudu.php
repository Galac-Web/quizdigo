<?php

declare(strict_types=1);


use Evasystem\Controllers\Rooms\Rooms;
use Evasystem\Controllers\Rooms\RoomsService;

header('Content-Type: application/json');

// Citește JSON brut
$input = file_get_contents('php://input');
$data = json_decode($input, true);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new \Exception('Metoda permisă este POST.');
    }

    $type = $data['type_product'] ?? null;
    if (!$type) {
        throw new \Exception('Lipsește tipul acțiunii (type_product).');
    }

    switch ($type) {
        case 'list':
            $service = new RoomsService();
            $result = $service->getAllRoomss(); // trebuie să îl ai în service
            $response = [
                'success' => true,
                'data' => $result
            ];
            break;
        case 'add':
            $service = new RoomsService();
            $controller = new Rooms($service);

            $result = $controller->addProfileInfo($data);

            // NU mai forțăm success = true,
            // luăm exact ce zice controllerul.
            $response = [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'Eroare necunoscută la Rooms.',
                'data'    => $result['results'] ?? null,
            ];
            break;

            case 'edit':
            $service = new RoomsService();
            $controller = new Rooms($service);
            $result = $controller->editStatus($data);
            $response = [
            'success' => true,
            'message' => 'Rooms editat.',
            'data' => $result
            ];
            break;

        case 'delete':
            $service = new RoomsService();
            $id = isset($data['randomn_id']) ? (int)$data['randomn_id'] : 0;

            if ($id <= 0) {
                throw new \Exception('ID invalid pentru ștergere.');
            }

            $res = $service->deleteRooms($id);

            $response = [
                'success' => $res['success'] ?? false,
                'message' => ($res['success'] ?? false)
                    ? 'Rooms șters.'
                    : 'Nu s-a putut șterge Rooms (poate nu există sau a fost deja șters).',
                'data'    => $res
            ];
            break;
        case 'activate':
        if (!isset($data['id']) || empty($data['id'])) {
        throw new \Exception('ID lipsă pentru activare.');

    }

        $_SESSION['rooms'] = $data['id'];

        $response = [
        'success' => true,
        'message' => 'Rooms activat în sesiune.',
        'data' => $_SESSION['rooms']
        ];
        break;

        case 'setstatus':
        $response = [
        'success' => true,
        'message' => 'Status actualizat.',
        'data' => ''
        ];
        break;

        default:
        throw new \Exception("Acțiune necunoscută: {$type}");
    }

    echo json_encode($response);

    } catch (\Throwable $e) {
    echo json_encode([
    'success' => false,
    'message' => 'Eroare: ' . $e->getMessage()
    ]);
}
