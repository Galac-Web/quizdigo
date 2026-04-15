<?php

declare(strict_types=1);


use Evasystem\Controllers\Participanti\Participanti;
use Evasystem\Controllers\Participanti\ParticipantiService;

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
            $service = new ParticipantiService();
            $controller = new Participanti($service);
            $result = $controller->addProfileInfo($data);
            $response = [
            'success' => true,
            'message' => 'Participanti adăugat.',
            'data' => $result
            ];
            break;

            case 'edit':
            $service = new ParticipantiService();
            $controller = new Participanti($service);
            $result = $controller->editStatus($data);
            $response = [
            'success' => true,
            'message' => 'Participanti editat.',
            'data' => $result
            ];
            break;

            case 'delete':
            $service = new ParticipantiService();
            $res = $service->deleteParticipanti($data['id']);
            $response = [
            'success' => true,
            'message' => 'Participanti șters.',
            'data' => $res
            ];
            break;
        case 'activate':
        if (!isset($data['id']) || empty($data['id'])) {
        throw new \Exception('ID lipsă pentru activare.');

    }

        $_SESSION['participanti'] = $data['id'];

        $response = [
        'success' => true,
        'message' => 'Participanti activat în sesiune.',
        'data' => $_SESSION['participanti']
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
