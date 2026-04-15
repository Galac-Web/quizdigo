<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id_user = $_SESSION['user_id'];

// Conexiune DB (Folosește aceleași date ca la salvare)
$pdo = new PDO("mysql:host=localhost;dbname=lilit2;charset=utf8mb4", "lilit2", "aM1xN7kS3w");

$stmt = $pdo->prepare("SELECT continut_json FROM quizzes WHERE id_user = ? ORDER BY last_updated DESC LIMIT 1");
$stmt->execute([$id_user]);
$result = $stmt->fetch();

if ($result) {
    // Trimitem datele direct ca obiect JSON
    echo $result['continut_json'];
} else {
    echo json_encode(['success' => false, 'message' => 'Nu există quiz-uri salvate']);
}
?>