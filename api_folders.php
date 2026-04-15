<?php
session_start();
header('Content-Type: application/json');

// 1. Verificăm dacă utilizatorul este logat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acces interzis!']);
    exit;
}

$id_user = $_SESSION['user_id'];

// 2. Conexiune la baza de date
$host = 'localhost';
$db   = 'lilit2';
$user = 'lilit2';
$pass = 'aM1xN7kS3w';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare DB: ' . $e->getMessage()]);
    exit;
}

// 3. Preluăm acțiunea trimisă prin POST
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Numele mapei este obligatoriu!']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO folders (id_user, nume_folder) VALUES (?, ?)");
            $stmt->execute([$id_user, $name]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Eroare la creare: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        $id_folder = intval($_POST['id'] ?? 0);
        if ($id_folder <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalid!']);
            exit;
        }

        try {
            // Pasul A: "Eliberăm" quiz-urile din acea mapă (setăm id_folder la NULL)
            // Astfel, nu pierzi quiz-urile când ștergi folderul
            $stmtUpdate = $pdo->prepare("UPDATE quizzes SET id_folder = NULL WHERE id_folder = ? AND id_user = ?");
            $stmtUpdate->execute([$id_folder, $id_user]);

            // Pasul B: Ștergem folderul efectiv
            $stmtDelete = $pdo->prepare("DELETE FROM folders WHERE id = ? AND id_user = ?");
            $stmtDelete->execute([$id_folder, $id_user]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Eroare la ștergere: ' . $e->getMessage()]);
        }
        break;
    case 'rename':
        $id_folder = intval($_POST['id'] ?? 0);
        $new_name = trim($_POST['name'] ?? '');

        if ($id_folder <= 0 || empty($new_name)) {
            echo json_encode(['success' => false, 'message' => 'Date incomplete!']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE folders SET nume_folder = ? WHERE id = ? AND id_user = ?");
            $stmt->execute([$new_name, $id_folder, $id_user]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Eroare la redenumire: ' . $e->getMessage()]);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acțiune necunoscută!']);
        break;
}
?>