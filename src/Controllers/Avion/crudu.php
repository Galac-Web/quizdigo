<?php

declare(strict_types=1);


use Evasystem\Controllers\Avion\Avion;
use Evasystem\Controllers\Avion\AvionService;

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
        case 'add':
            $service = new AvionService();
            $controller = new Avion($service);
            $result = $controller->addProfileInfo($data);
            $response = [
            'success' => true,
            'message' => 'Avion adăugat.',
            'data' => $result
            ];
            break;
            case 'role':
                $service = new AvionService();
                $controller = new Avion($service);
                $avionrole = $controller->addroles($data);
                $response = [
                    'success' => true,
                    'message' => 'Avion adăugat.',
                    'data' => $avionrole
                ];
            break;

            case 'edit':
            $service = new AvionService();
            $controller = new Avion($service);
            $result = $controller->editStatus($data);
            $response = [
            'success' => true,
            'message' => 'Avion editat.',
            'data' => $result
            ];
            break;

            case 'delete':
            $service = new AvionService();
            $res = $service->deleteAvion($data['id']);
            $response = [
            'success' => true,
            'message' => 'Avion șters.',
            'data' => $res
            ];
            break;
        case 'activate':
        if (!isset($data['id']) || empty($data['id'])) {
        throw new \Exception('ID lipsă pentru activare.');

    }

        $_SESSION['avion'] = $data['id'];

        $response = [
        'success' => true,
        'message' => 'Avion activat în sesiune.',
        'data' => $_SESSION['avion']
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
