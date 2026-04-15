<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header_remove('X-Powered-By');

/* =========================
   CONFIG
========================= */
$dbHost = 'localhost';
$dbName = 'lilit2';
$dbUser = 'lilit2';
$dbPass = 'aM1xN7kS3w';

$openAiApiKey = getenv('OPENAI_API_KEY') ?: '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';
$openAiModel  = 'gpt-5.4';

/* imagini default */
$themePool = [
    'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=1200',
    'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1200',
    'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?q=80&w=1200',
    'https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=1200',
];

$imagePool = [
    'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=800',
    'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=800',
    'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?q=80&w=800',
    'https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=800',
];

/* =========================
   HELPERS
========================= */
function ai_json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ai_db(string $host, string $name, string $user, string $pass): PDO
{
    return new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function ai_random_id(int $length = 40): string
{
    return bin2hex(random_bytes((int)ceil($length / 2)));
}

function ai_normalize_text(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function ai_safe_file_name(string $name): string
{
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?? 'file';
    return trim($name, '_') ?: 'file';
}

function ai_fetch_url_text(string $url): string
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('URL invalid.');
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'QuizDigo-AI/1.0',
    ]);

    $html = curl_exec($ch);

    if ($html === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Nu am putut încărca URL-ul: ' . $err);
    }

    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new RuntimeException('URL-ul a răspuns cu eroare HTTP ' . $httpCode);
    }

    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = ai_normalize_text($text);

    if ($text === '') {
        throw new RuntimeException('Nu s-a extras conținut text din URL.');
    }

    return mb_substr($text, 0, 15000);
}

function ai_fetch_wikipedia_text(string $topic): string
{
    $topic = trim($topic);
    if ($topic === '') {
        throw new RuntimeException('Subiect Wikipedia lipsă.');
    }

    $title = str_replace(' ', '_', $topic);
    $api = 'https://ro.wikipedia.org/api/rest_v1/page/summary/' . rawurlencode($title);

    $ch = curl_init($api);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_USERAGENT => 'QuizDigo-AI/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Eroare Wikipedia: ' . $err);
    }

    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new RuntimeException('Wikipedia nu a găsit articolul.');
    }

    $data = json_decode($raw, true);
    $extract = trim((string)($data['extract'] ?? ''));

    if ($extract === '') {
        throw new RuntimeException('Nu s-a putut extrage text din Wikipedia.');
    }

    return mb_substr($extract, 0, 15000);
}

function ai_extract_pdf_text(string $uploadedPath): string
{
    $pdftotext = trim((string)shell_exec('command -v pdftotext 2>/dev/null'));

    if ($pdftotext === '') {
        throw new RuntimeException('pdftotext nu este instalat pe server. Instalează poppler-utils.');
    }

    $tmpTxt = sys_get_temp_dir() . '/quizdigo_pdf_' . ai_random_id(10) . '.txt';
    $cmd = escapeshellcmd($pdftotext) . ' ' . escapeshellarg($uploadedPath) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1';
    shell_exec($cmd);

    if (!is_file($tmpTxt)) {
        throw new RuntimeException('Nu s-a putut extrage text din PDF.');
    }

    $text = (string)file_get_contents($tmpTxt);
    @unlink($tmpTxt);

    $text = ai_normalize_text($text);
    if ($text === '') {
        throw new RuntimeException('PDF-ul nu conține text extractibil.');
    }

    return mb_substr($text, 0, 15000);
}

function ai_call_openai(string $apiKey, string $model, array $payload): array
{
    $ch = curl_init('https://api.openai.com/v1/responses');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $raw = curl_exec($ch);

    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('OpenAI cURL error: ' . $err);
    }

    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($raw, true);

    if ($httpCode >= 400) {
        $msg = $decoded['error']['message'] ?? $raw;
        throw new RuntimeException('OpenAI error: ' . $msg);
    }

    if (!is_array($decoded)) {
        throw new RuntimeException('Răspuns invalid de la OpenAI.');
    }

    return $decoded;
}

function ai_extract_output_text(array $response): string
{
    if (!empty($response['output']) && is_array($response['output'])) {
        foreach ($response['output'] as $item) {
            if (!empty($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $content) {
                    if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) {
                        return (string)$content['text'];
                    }
                }
            }
        }
    }

    if (!empty($response['output_text'])) {
        return (string)$response['output_text'];
    }

    throw new RuntimeException('Nu s-a găsit output_text în răspunsul OpenAI.');
}

function ai_build_quiz_schema(): array
{
    return [
        'type' => 'object',
        'additionalProperties' => false,
        'required' => ['settings', 'slides'],
        'properties' => [
            'settings' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => [
                    'theme',
                    'themeUrl',
                    'timeLimit',
                    'bonusSpeed',
                    'bonusTime',
                    'title',
                    'description',
                    'visibility',
                    'lang',
                    'coverImage',
                    'musicUrl',
                    'correctSound',
                    'wrongSound'
                ],
                'properties' => [
                    'theme' => ['type' => 'string'],
                    'themeUrl' => ['type' => 'string'],
                    'timeLimit' => ['type' => 'string'],
                    'bonusSpeed' => ['type' => 'boolean'],
                    'bonusTime' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'visibility' => ['type' => 'string'],
                    'lang' => ['type' => 'string'],
                    'coverImage' => ['type' => 'string'],
                    'musicUrl' => ['type' => 'string'],
                    'correctSound' => ['type' => 'string'],
                    'wrongSound' => ['type' => 'string'],
                ]
            ],
            'slides' => [
                'type' => 'array',
                'minItems' => 5,
                'maxItems' => 20,
                'items' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'id',
                        'type',
                        'title',
                        'background',
                        'imageCenter',
                        'correctAnswerIndex',
                        'answers',
                        'answerImages',
                        'musicUrl',
                        'slider',
                        'pins',
                        'selectType',
                        'correctAnswerIndexes'
                    ],
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'type' => ['type' => 'string'],
                        'title' => ['type' => 'string'],
                        'background' => ['type' => 'string'],
                        'imageCenter' => ['type' => 'string'],
                        'correctAnswerIndex' => ['type' => ['integer', 'null']],
                        'answers' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ],
                        'answerImages' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ],
                        'musicUrl' => ['type' => 'string'],
                        'slider' => [
                            'type' => ['object', 'null'],
                            'additionalProperties' => false,
                            'properties' => [
                                'min' => ['type' => 'number'],
                                'max' => ['type' => 'number'],
                                'correct' => ['type' => 'number']
                            ],
                            'required' => ['min', 'max', 'correct']
                        ],
                        'pins' => [
                            'type' => ['array', 'null'],
                            'items' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'required' => ['x', 'y', 'label', 'correct'],
                                'properties' => [
                                    'x' => ['type' => 'number'],
                                    'y' => ['type' => 'number'],
                                    'label' => ['type' => 'string'],
                                    'correct' => ['type' => 'boolean']
                                ]
                            ]
                        ],
                        'selectType' => ['type' => 'string'],
                        'correctAnswerIndexes' => [
                            'type' => 'array',
                            'items' => ['type' => 'integer']
                        ]
                    ]
                ]
            ]
        ]
    ];
}

function ai_pick_random(array $items): string
{
    return $items[array_rand($items)];
}

function ai_normalize_generated_quiz(array $quizData, int $userId, array $themePool, array $imagePool): array
{
    $defaultTheme = ai_pick_random($themePool);
    $defaultCover = ai_pick_random($imagePool);

    $quizData['settings'] = $quizData['settings'] ?? [];
    $quizData['settings']['theme'] = trim((string)($quizData['settings']['theme'] ?? 'Educație'));
    $quizData['settings']['themeUrl'] = trim((string)($quizData['settings']['themeUrl'] ?? $defaultTheme));
    $quizData['settings']['timeLimit'] = trim((string)($quizData['settings']['timeLimit'] ?? '10s'));
    $quizData['settings']['bonusSpeed'] = (bool)($quizData['settings']['bonusSpeed'] ?? true);
    $quizData['settings']['bonusTime'] = (int)($quizData['settings']['bonusTime'] ?? 5);
    $quizData['settings']['title'] = trim((string)($quizData['settings']['title'] ?? 'Quiz generat AI'));
    $quizData['settings']['description'] = trim((string)($quizData['settings']['description'] ?? 'Quiz generat automat.'));
    $quizData['settings']['visibility'] = trim((string)($quizData['settings']['visibility'] ?? 'private'));
    $quizData['settings']['lang'] = trim((string)($quizData['settings']['lang'] ?? 'ro'));
    $quizData['settings']['coverImage'] = trim((string)($quizData['settings']['coverImage'] ?? $defaultCover));
    $quizData['settings']['musicUrl'] = trim((string)($quizData['settings']['musicUrl'] ?? ''));
    $quizData['settings']['correctSound'] = trim((string)($quizData['settings']['correctSound'] ?? ''));
    $quizData['settings']['wrongSound'] = trim((string)($quizData['settings']['wrongSound'] ?? ''));

    $quizData['slides'] = is_array($quizData['slides'] ?? null) ? $quizData['slides'] : [];

    if (!$quizData['slides']) {
        throw new RuntimeException('AI nu a generat slide-uri.');
    }

    foreach ($quizData['slides'] as $i => &$slide) {
        $slide['id'] = isset($slide['id']) ? (int)$slide['id'] : ((int)(microtime(true) * 1000) + $i);
        $slide['type'] = trim((string)($slide['type'] ?? 'quiz'));
        $slide['title'] = trim((string)($slide['title'] ?? ('Întrebarea ' . ($i + 1))));
        $slide['background'] = trim((string)($slide['background'] ?? ai_pick_random($themePool)));
        $slide['imageCenter'] = trim((string)($slide['imageCenter'] ?? ai_pick_random($imagePool)));
        $slide['musicUrl'] = trim((string)($slide['musicUrl'] ?? ''));
        $slide['slider'] = $slide['slider'] ?? null;
        $slide['pins'] = $slide['pins'] ?? null;
        $slide['selectType'] = trim((string)($slide['selectType'] ?? 'single'));

        if ($slide['type'] === 'quiz') {
            $answers = is_array($slide['answers'] ?? null) ? $slide['answers'] : [];
            $answers = array_values(array_slice($answers, 0, 4));
            $answers = array_pad($answers, 4, '');
            $slide['answers'] = $answers;

            $images = is_array($slide['answerImages'] ?? null) ? $slide['answerImages'] : [];
            $images = array_values(array_slice($images, 0, 4));
            $images = array_pad($images, 4, '');
            $slide['answerImages'] = $images;

            $correct = isset($slide['correctAnswerIndex']) ? (int)$slide['correctAnswerIndex'] : 0;
            if ($correct < 0 || $correct > 3) {
                $correct = 0;
            }

            $slide['correctAnswerIndex'] = $correct;
            $slide['correctAnswerIndexes'] = [$correct];
            $slide['slider'] = null;
            $slide['pins'] = null;
            $slide['selectType'] = 'single';
        } elseif ($slide['type'] === 'true-false') {
            $slide['answers'] = ['True', 'False'];
            $slide['answerImages'] = ['', ''];
            $correct = isset($slide['correctAnswerIndex']) ? (int)$slide['correctAnswerIndex'] : 0;
            if (!in_array($correct, [0, 1], true)) {
                $correct = 0;
            }
            $slide['correctAnswerIndex'] = $correct;
            $slide['correctAnswerIndexes'] = [$correct];
            $slide['slider'] = null;
            $slide['pins'] = null;
            $slide['selectType'] = 'single';
        } elseif ($slide['type'] === 'jumble') {
            $answers = is_array($slide['answers'] ?? null) ? $slide['answers'] : [];
            $answers = array_values(array_slice($answers, 0, 4));
            $answers = array_pad($answers, 4, '');
            $slide['answers'] = $answers;
            $slide['answerImages'] = ['', '', '', ''];
            $slide['correctAnswerIndex'] = null;
            $slide['correctAnswerIndexes'] = [];
            $slide['slider'] = null;
            $slide['pins'] = [];
            $slide['selectType'] = 'single';
        } elseif ($slide['type'] === 'open-ended') {
            $answers = is_array($slide['answers'] ?? null) ? $slide['answers'] : [];
            $first = trim((string)($answers[0] ?? ''));
            $slide['answers'] = [$first];
            $slide['answerImages'] = [''];
            $slide['correctAnswerIndex'] = 0;
            $slide['correctAnswerIndexes'] = [0];
            $slide['slider'] = null;
            $slide['pins'] = null;
            $slide['selectType'] = 'single';
        } else {
            $slide['type'] = 'quiz';
            $answers = is_array($slide['answers'] ?? null) ? $slide['answers'] : [];
            $answers = array_values(array_slice($answers, 0, 4));
            $answers = array_pad($answers, 4, '');
            $slide['answers'] = $answers;
            $slide['answerImages'] = ['', '', '', ''];
            $slide['correctAnswerIndex'] = 0;
            $slide['correctAnswerIndexes'] = [0];
            $slide['slider'] = null;
            $slide['pins'] = null;
            $slide['selectType'] = 'single';
        }

        if ($slide['imageCenter'] === '') {
            $slide['imageCenter'] = ai_pick_random($imagePool);
        }

        if ($slide['background'] === '') {
            $slide['background'] = ai_pick_random($themePool);
        }
    }
    unset($slide);

    $quizData['currentSlideId'] = (int)($quizData['slides'][0]['id'] ?? 0);
    $quizData['id_quiz'] = null;
    $quizData['id_user'] = $userId;

    return $quizData;
}

function ai_save_quiz(PDO $pdo, int $userId, array $quizData): array
{
    $settings = $quizData['settings'] ?? [];
    $title = trim((string)($settings['title'] ?? 'Quiz AI'));
    $randomnId = ai_random_id(40);

    $stmt = $pdo->prepare("
        INSERT INTO quizzes
        (
            randomn_id,
            id_user,
            titlu,
            continut_json,
            title,
            visibility,
            lang,
            cover_image,
            theme_url,
            music_url,
            created_at,
            updated_at
        )
        VALUES
        (
            :randomn_id,
            :id_user,
            :titlu,
            :continut_json,
            :title,
            :visibility,
            :lang,
            :cover_image,
            :theme_url,
            :music_url,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':randomn_id' => $randomnId,
        ':id_user' => $userId,
        ':titlu' => $title,
        ':continut_json' => json_encode($quizData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':title' => $title,
        ':visibility' => (string)($settings['visibility'] ?? 'private'),
        ':lang' => (string)($settings['lang'] ?? 'ro'),
        ':cover_image' => (string)($settings['coverImage'] ?? ''),
        ':theme_url' => (string)($settings['themeUrl'] ?? ''),
        ':music_url' => (string)($settings['musicUrl'] ?? ''),
    ]);

    return [
        'id_quiz' => (int)$pdo->lastInsertId(),
        'randomn_id' => $randomnId
    ];
}

/* =========================
   HANDLE REQUEST
========================= */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Metodă invalidă.');
    }

    if (empty($_SESSION['user_id'])) {
        throw new RuntimeException('Utilizator nelogat.');
    }

    if (($openAiApiKey === '') || ($openAiApiKey === 'PUNE_AICI_OPENAI_API_KEY')) {
        throw new RuntimeException('Cheia OpenAI nu este setată.');
    }

    $action = trim((string)($_POST['action'] ?? ''));
    if ($action !== 'generate_ai_quiz') {
        throw new RuntimeException('Acțiune invalidă.');
    }

    $source      = trim((string)($_POST['ai_source'] ?? 'topic'));
    $inputText   = trim((string)($_POST['ai_input'] ?? ''));
    $language    = trim((string)($_POST['language'] ?? 'Română'));
    $skillLevel  = trim((string)($_POST['skill_level'] ?? 'Intermediate'));
    $tone        = trim((string)($_POST['tone'] ?? 'Conversational'));
    $lengthLabel = trim((string)($_POST['question_count'] ?? 'Around 10 questions'));

    $questionCount = 10;
    if (preg_match('/(\d+)/', $lengthLabel, $m)) {
        $questionCount = max(5, min(20, (int)$m[1]));
    }

    $sourceText = '';
    $sourceTitle = $inputText;

    if ($source === 'topic') {
        if ($inputText === '') {
            throw new RuntimeException('Introdu subiectul.');
        }
        $sourceText = $inputText;
    } elseif ($source === 'url') {
        if ($inputText === '') {
            throw new RuntimeException('Introdu linkul URL.');
        }
        $sourceText = ai_fetch_url_text($inputText);
    } elseif ($source === 'wikipedia') {
        if ($inputText === '') {
            throw new RuntimeException('Introdu subiectul pentru Wikipedia.');
        }
        $sourceText = ai_fetch_wikipedia_text($inputText);
    } elseif ($source === 'file') {
        if (empty($_FILES['pdf_file']['tmp_name'])) {
            throw new RuntimeException('Alege un fișier PDF.');
        }

        $tmpName = (string)$_FILES['pdf_file']['tmp_name'];
        $origName = ai_safe_file_name((string)($_FILES['pdf_file']['name'] ?? 'document.pdf'));
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            throw new RuntimeException('Doar fișiere PDF sunt acceptate.');
        }

        $sourceText = ai_extract_pdf_text($tmpName);
        $sourceTitle = $origName;
    } elseif ($source === 'kahoot') {
        if ($inputText === '') {
            throw new RuntimeException('Introdu textul pentru generare.');
        }
        $sourceText = $inputText;
    } else {
        throw new RuntimeException('Tip de sursă invalid.');
    }

    $langCode = 'ro';
    if (stripos($language, 'eng') !== false) {
        $langCode = 'en';
    } elseif (stripos($language, 'rus') !== false) {
        $langCode = 'ru';
    }

    $schema = ai_build_quiz_schema();

    $themeA = ai_pick_random($themePool);
    $themeB = ai_pick_random($imagePool);

    $systemPrompt = <<<PROMPT
Generezi un quiz COMPLET pentru platforma QuizDigo.

Returnează STRICT JSON valid.
Nu returna markdown.
Nu returna explicații.
Nu returna text în afara JSON-ului.

Quizul trebuie să fie compatibil cu builderul QuizDigo.

Reguli:
- Folosește limba cerută de utilizator.
- Generează exact {$questionCount} slide-uri.
- Folosește tipuri mixte dacă se potrivesc subiectului:
  - quiz
  - true-false
  - jumble
  - open-ended
- Majoritatea slide-urilor să fie de tip quiz.
- Fiecare slide trebuie să aibă TOATE cheile cerute.
- Pentru tip quiz:
  - exact 4 răspunsuri
  - exact 1 răspuns corect
- Pentru tip true-false:
  - answers = ["True","False"]
- Pentru tip jumble:
  - answers trebuie să aibă pași logici în ordine
  - correctAnswerIndex = null
  - correctAnswerIndexes = []
- Pentru tip open-ended:
  - primul răspuns din answers este răspunsul corect scurt
- selectType = "single"
- bonusSpeed = true
- bonusTime = 5
- timeLimit = "10s"
- visibility = "private"
- lang = "{$langCode}"
- theme = "Educație"
- themeUrl = "{$themeA}"
- coverImage = "{$themeB}"
- musicUrl = ""
- correctSound = ""
- wrongSound = ""
- background la fiecare slide să fie URL valid de imagine
- imageCenter la fiecare slide să fie URL valid de imagine
- answerImages să fie goale dacă nu sunt necesare
- Nu lăsa slide-uri goale
- Întrebările trebuie să fie clare, corecte și utile
- Titlul quizului și descrierea trebuie să fie bune și naturale
PROMPT;

    $userPrompt = <<<PROMPT
Generează un quiz pe baza informațiilor de mai jos.

Sursa: {$source}
Titlu/Subiect: {$sourceTitle}
Limba: {$language}
Nivel: {$skillLevel}
Ton: {$tone}
Număr întrebări: {$questionCount}

Conținut:
{$sourceText}
PROMPT;

    $payload = [
        'model' => $openAiModel,
        'input' => [
            [
                'role' => 'system',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => $systemPrompt
                    ]
                ]
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => $userPrompt
                    ]
                ]
            ]
        ],
        'text' => [
            'format' => [
                'type' => 'json_schema',
                'name' => 'quizdigo_builder_quiz_schema',
                'schema' => $schema,
                'strict' => true
            ]
        ]
    ];

    $response = ai_call_openai($openAiApiKey, $openAiModel, $payload);
    $outputText = ai_extract_output_text($response);

    $quizData = json_decode($outputText, true);
    if (!is_array($quizData)) {
        throw new RuntimeException('OpenAI nu a întors JSON valid.');
    }

    $quizData = ai_normalize_generated_quiz(
        $quizData,
        (int)$_SESSION['user_id'],
        $themePool,
        $imagePool
    );

    $pdo = ai_db($dbHost, $dbName, $dbUser, $dbPass);
    $saved = ai_save_quiz($pdo, (int)$_SESSION['user_id'], $quizData);

    ai_json_response([
        'success' => true,
        'message' => 'Quiz generat și salvat cu succes.',
        'id_quiz' => $saved['id_quiz'],
        'randomn_id' => $saved['randomn_id'],
        'quiz_title' => $quizData['settings']['title'] ?? 'Quiz AI',
        'redirect' => '/public/addquizz?id=' . $saved['id_quiz']
    ]);
} catch (Throwable $e) {
    ai_json_response([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}