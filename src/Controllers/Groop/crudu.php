<?php

declare(strict_types=1);

use Evasystem\Controllers\Groop\Groop;
use Evasystem\Controllers\Groop\GroopService;

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    $data = $_POST;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new \Exception('Metoda permisă este POST.');
    }

    $type = $data['type_product'] ?? null;
    if (!$type) {
        throw new \Exception('Lipsește tipul acțiunii (type_product).');
    }

    $service = new GroopService();
    $controller = new Groop($service);

    switch ($type) {
        case 'init_groups':
            $groups = $service->getGroupsByUserId((int)($data['id_users'] ?? 0));

            foreach ($groups as &$group) {
                $members = $service->getMembersByConnectId((string)$group['randomn_id']);
                $group['members_count'] = is_array($members) ? count($members) : 0;
            }
            unset($group);

            $response = [
                'success' => true,
                'groups' => $groups
            ];
            break;

        case 'create_group':
            $response = $controller->createGroup($data);
            break;

        case 'delete_group':
            $response = $controller->deleteGroup($data);
            break;

        case 'load_group':
            $response = $controller->loadGroup($data);
            break;

        case 'send_invite':
            $response = $controller->sendInvite($data);
            break;

        case 'accept_invite':
            $response = $controller->acceptInvite($data);
            break;

        case 'create_post':
            $response = $controller->createPost($data);
            break;

        case 'create_reply':
            $response = $controller->createReply($data);
            break;

        case 'remove_member':
            $response = $controller->removeMember($data);
            break;

        case 'like_post':
            $response = $controller->likePost($data);
            break;

        case 'delete_invite':
            $response = $controller->deleteInvite($data);
            break;
        case 'attach_quiz':
            $response = $controller->attachQuiz($data);
            break;

        case 'remove_attached_quiz':
            $response = $controller->removeAttachedQuiz($data);
            break;

        case 'activate':
            if (empty($data['id'])) {
                throw new \Exception('ID lipsă pentru activare.');
            }

            $_SESSION['groop'] = $data['id'];

            $response = [
                'success' => true,
                'message' => 'Groop activat în sesiune.',
                'data' => $_SESSION['groop']
            ];
            break;

        default:
            throw new \Exception("Acțiune necunoscută: {$type}");
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Eroare: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}