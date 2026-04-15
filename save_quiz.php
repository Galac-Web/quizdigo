<?php
session_start();
header('Content-Type: application/json');

// 1. Verificăm sesiunea (asigură-te că folosești cheia corectă: user_id sau id_users)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilizator nelogat!']);
    exit;
}

$id_user = $_SESSION['user_id'];

// 2. Primim datele JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Date invalide!']);
    exit;
}

// 3. Conexiune DB
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

    // Preluăm ID-ul quiz-ului din JSON (poate fi null dacă e quiz nou)
    $id_quiz = isset($data['id_quiz']) ? $data['id_quiz'] : null;

    // Titlul din setările noi (sau fallback la primul slide)
    $quiz_title = !empty($data['settings']['title']) ? $data['settings']['title'] :
        (!empty($data['slides'][0]['title']) ? $data['slides'][0]['title'] : 'Quiz fără titlu');

    $quiz_content = json_encode($data);

    if ($id_quiz) {
        // UPDATE: Dacă avem ID, actualizăm quiz-ul existent al utilizatorului
        $sql = "UPDATE quizzes SET titlu = ?, continut_json = ? WHERE id = ? AND id_user = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quiz_title, $quiz_content, $id_quiz, $id_user]);
        $final_id = $id_quiz;
    } else {
        // INSERT: Dacă NU avem ID, creăm un rând nou
        $sql = "INSERT INTO quizzes (id_user, titlu, continut_json) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user, $quiz_title, $quiz_content]);
        $final_id = $pdo->lastInsertId();
    }

    // Returnăm succes și ID-ul (foarte important pentru JS)
    echo json_encode([
        'success' => true,
        'message' => 'Salvat cu succes!',
        'id_quiz' => $final_id
    ]);

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare DB: ' . $e->getMessage()]);
}
?>