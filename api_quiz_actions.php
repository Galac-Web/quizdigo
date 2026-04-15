<?php
session_start();
header('Content-Type: application/json');
$id_user = $_SESSION['user_id'];
$pdo = new PDO("mysql:host=localhost;dbname=lilit2;charset=utf8mb4", "lilit2", "aM1xN7kS3w");

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

switch ($action) {
    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ? AND id_user = ?");
        $stmt->execute([$id, $id_user]);
        echo json_encode(['success' => true]);
        break;

    case 'duplicate':
        // Luăm datele vechi
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND id_user = ?");
        $stmt->execute([$id, $id_user]);
        $old = $stmt->fetch();

        if($old) {
            $newTitle = $old['titlu'] . " (Copie)";
            $stmt = $pdo->prepare("INSERT INTO quizzes (id_user, id_folder, titlu, continut_json) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_user, $old['id_folder'], $newTitle, $old['continut_json']]);
            echo json_encode(['success' => true]);
        }
        break;

    case 'move':
        $folder_id = !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : null;
        $stmt = $pdo->prepare("UPDATE quizzes SET id_folder = ? WHERE id = ? AND id_user = ?");
        $stmt->execute([$folder_id, $id, $id_user]);
        echo json_encode(['success' => true]);
        break;
}
?>