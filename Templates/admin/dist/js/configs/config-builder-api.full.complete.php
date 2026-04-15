<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$repoRoot = realpath(__DIR__ . '/../../../../../');
$publicRoot = $repoRoot . DIRECTORY_SEPARATOR . 'public';
$configDir = __DIR__;
$uploadBaseDir = $publicRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'quiz-builder';
$logDir = $uploadBaseDir . DIRECTORY_SEPARATOR . 'logs';
$logFile = $logDir . DIRECTORY_SEPARATOR . 'config-builder-debug.log';

if ($repoRoot === false || !is_dir($publicRoot)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Structura proiectului nu a putut fi detectata.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function debugLog(string $message, array $context = []): void
{
    global $logDir, $logFile;

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $line = '[' . date('c') . '] ' . $message;
    if (!empty($context)) {
        $line .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $line .= PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function requestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function requestAction(): string
{
    return trim((string)($_GET['action'] ?? $_POST['action'] ?? ''));
}

function requestAssetType(): string
{
    $body = parseJsonBody();
    return normalizeAssetType((string)($_GET['type'] ?? $_POST['type'] ?? $body['type'] ?? ''));
}

function parseJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function normalizeAssetType(string $type): string
{
    $type = strtolower(trim($type));
    return in_array($type, ['image', 'audio'], true) ? $type : '';
}

function sanitizeBaseName(string $name): string
{
    $name = pathinfo($name, PATHINFO_FILENAME);
    $name = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $name ?? '');
    $name = trim((string)$name, '-_');
    return $name !== '' ? strtolower($name) : 'asset';
}

function createPublicUrl(string $relativePath): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
    if (strpos($normalizedPath, 'public/') !== 0) {
        $normalizedPath = 'public/' . $normalizedPath;
    }
    return $scheme . '://' . $host . '/' . $normalizedPath;
}

function assetConfig(string $type): array
{
    if ($type === 'image') {
        return [
            'dir' => 'images',
            'allowed' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'],
        ];
    }

    return [
        'dir' => 'audio',
        'allowed' => ['mp3', 'wav', 'ogg', 'm4a'],
    ];
}

function ensureDir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        respond(['success' => false, 'message' => 'Nu s-a putut crea directorul: ' . $dir], 500);
    }

    @chmod($dir, 0777);
}

function listAssets(string $uploadBaseDir, string $type): array
{
    $cfg = assetConfig($type);
    $dir = $uploadBaseDir . DIRECTORY_SEPARATOR . $cfg['dir'];
    ensureDir($dir);

    $items = [];
    foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
        if (!is_file($path)) {
            continue;
        }

        $fileName = basename($path);
        $items[] = [
            'name' => $fileName,
            'id' => sanitizeBaseName($fileName),
            'size' => filesize($path) ?: 0,
            'modifiedAt' => date('c', filemtime($path) ?: time()),
            'url' => createPublicUrl('uploads/quiz-builder/' . $cfg['dir'] . '/' . $fileName),
            'publicPath' => 'public/uploads/quiz-builder/' . $cfg['dir'] . '/' . $fileName,
            'serverPath' => $path,
        ];
    }

    usort($items, static function (array $a, array $b): int {
        return strcmp($b['modifiedAt'], $a['modifiedAt']);
    });

    return $items;
}

function ensureUploadDirectories(string $uploadBaseDir): array
{
    $targets = [
        $uploadBaseDir,
        $uploadBaseDir . DIRECTORY_SEPARATOR . 'images',
        $uploadBaseDir . DIRECTORY_SEPARATOR . 'audio',
    ];

    $results = [];
    foreach ($targets as $dir) {
        $existsBefore = is_dir($dir);
        ensureDir($dir);
        $results[] = [
            'path' => $dir,
            'created' => !$existsBefore && is_dir($dir),
            'exists' => is_dir($dir),
            'writable' => is_writable($dir),
        ];
    }

    return $results;
}

function normalizeFilesArray(array $files): array
{
    if (!isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    foreach ($files['name'] as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalized;
}

function uploadErrorMessage(int $code): string
{
    $map = [
        UPLOAD_ERR_INI_SIZE => 'Fisierul depaseste limita upload_max_filesize din PHP.',
        UPLOAD_ERR_FORM_SIZE => 'Fisierul depaseste limita MAX_FILE_SIZE din formular.',
        UPLOAD_ERR_PARTIAL => 'Fisierul a fost incarcat doar partial.',
        UPLOAD_ERR_NO_FILE => 'Nu a fost selectat niciun fisier.',
        UPLOAD_ERR_NO_TMP_DIR => 'Lipseste folderul temporar PHP pentru upload.',
        UPLOAD_ERR_CANT_WRITE => 'Serverul nu poate scrie fisierul pe disc.',
        UPLOAD_ERR_EXTENSION => 'O extensie PHP a oprit upload-ul.',
    ];

    return $map[$code] ?? ('Cod necunoscut de upload: ' . $code . '.');
}

function extractUploadedFiles(): array
{
    if (!empty($_FILES['upload_files']) && is_array($_FILES['upload_files'])) {
        return $_FILES['upload_files'];
    }

    if (!empty($_FILES['upload_files[]']) && is_array($_FILES['upload_files[]'])) {
        return $_FILES['upload_files[]'];
    }

    if (!empty($_FILES['files']) && is_array($_FILES['files'])) {
        return $_FILES['files'];
    }

    if (!empty($_FILES['files[]']) && is_array($_FILES['files[]'])) {
        return $_FILES['files[]'];
    }

    foreach ($_FILES as $file) {
        if (is_array($file) && isset($file['name'])) {
            return $file;
        }
    }

    return [];
}

function uploadAssets(string $uploadBaseDir, string $type): array
{
    $rawFiles = extractUploadedFiles();
    if (empty($rawFiles)) {
        debugLog('Upload failed: no files payload detected', [
            'files' => $_FILES,
            'post' => $_POST,
            'get' => $_GET,
        ]);
        respond(['success' => false, 'message' => 'Nu ai selectat niciun fisier.'], 422);
    }

    $cfg = assetConfig($type);
    $targetDir = $uploadBaseDir . DIRECTORY_SEPARATOR . $cfg['dir'];
    ensureDir($targetDir);

    $uploaded = [];
    $errors = [];

    foreach (normalizeFilesArray($rawFiles) as $file) {
        $errorCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = 'Fisierul "' . ($file['name'] ?? 'necunoscut') . '" nu a putut fi incarcat. Motiv: ' . uploadErrorMessage($errorCode);
            continue;
        }

        $originalName = (string)($file['name'] ?? '');
        $tmpName = (string)($file['tmp_name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $cfg['allowed'], true)) {
            $errors[] = 'Extensie nepermisa pentru "' . $originalName . '".';
            continue;
        }

        $safeName = sanitizeBaseName($originalName);
        $finalName = $safeName . '-' . date('Ymd-His') . '-' . substr(md5((string)microtime(true) . $originalName), 0, 6) . '.' . $ext;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $finalName;

        if ($tmpName === '' || !file_exists($tmpName)) {
            $errors[] = 'Fisierul "' . $originalName . '" nu are fisier temporar valid. tmp_name=' . $tmpName;
            continue;
        }

        if (!is_uploaded_file($tmpName)) {
            $errors[] = 'Fisierul "' . $originalName . '" nu este recunoscut de PHP ca upload valid. tmp_name=' . $tmpName;
            continue;
        }

        if (!is_dir($targetDir)) {
            $errors[] = 'Folderul tinta lipseste pentru "' . $originalName . '": ' . $targetDir;
            continue;
        }

        if (!is_writable($targetDir)) {
            $errors[] = 'Folderul tinta nu este inscriptibil pentru "' . $originalName . '": ' . $targetDir;
            continue;
        }

        if (!move_uploaded_file($tmpName, $targetPath)) {
            $errors[] = 'Nu s-a putut salva fisierul "' . $originalName . '" pe server. target=' . $targetPath . ' tmp=' . $tmpName . ' writable=' . (is_writable($targetDir) ? 'yes' : 'no');
            continue;
        }

        $uploaded[] = [
            'name' => $finalName,
            'id' => sanitizeBaseName($finalName),
            'url' => createPublicUrl('uploads/quiz-builder/' . $cfg['dir'] . '/' . $finalName),
            'publicPath' => 'public/uploads/quiz-builder/' . $cfg['dir'] . '/' . $finalName,
            'serverPath' => $targetPath,
        ];
    }

    return ['uploaded' => $uploaded, 'errors' => $errors];
}

function deleteAsset(string $uploadBaseDir, string $type, string $name): void
{
    $cfg = assetConfig($type);
    $fileName = basename($name);
    if ($fileName === '') {
        respond(['success' => false, 'message' => 'Lipseste numele fisierului.'], 422);
    }

    $path = $uploadBaseDir . DIRECTORY_SEPARATOR . $cfg['dir'] . DIRECTORY_SEPARATOR . $fileName;
    if (!is_file($path)) {
        respond(['success' => false, 'message' => 'Fisierul nu exista pe server. path=' . $path], 404);
    }

    if (!is_writable($path)) {
        respond(['success' => false, 'message' => 'Fisierul exista, dar nu poate fi sters. Nu este inscriptibil. path=' . $path], 500);
    }

    if (!unlink($path)) {
        respond(['success' => false, 'message' => 'Fisierul nu a putut fi sters. path=' . $path], 500);
    }
}

function clearDirectoryFiles(string $dir): array
{
    $deleted = [];
    $errors = [];

    if (!is_dir($dir)) {
        return ['deleted' => $deleted, 'errors' => $errors];
    }

    foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
        if (!is_file($path)) {
            continue;
        }

        if (@unlink($path)) {
            $deleted[] = $path;
        } else {
            $errors[] = $path;
        }
    }

    return ['deleted' => $deleted, 'errors' => $errors];
}

function readJsonFile(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJsonFile(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($path, $json . PHP_EOL, LOCK_EX) === false) {
        respond(['success' => false, 'message' => 'Nu s-a putut salva fisierul ' . basename($path) . '.'], 500);
    }
}

function upsertById(array &$items, array $entry): void
{
    $id = (string)($entry['id'] ?? '');
    if ($id === '') {
        return;
    }

    foreach ($items as $index => $item) {
        if ((string)($item['id'] ?? '') === $id) {
            $items[$index] = array_merge($item, $entry);
            return;
        }
    }

    $items[] = $entry;
}

function upsertNamedMap(array &$target, array $entry): void
{
    foreach ($entry as $key => $value) {
        if (!is_string($key) || $key === '' || !is_array($value)) {
            continue;
        }
        $target[$key] = $value;
    }
}

function mergeLibraryItems(array &$target, array $items): void
{
    foreach ($items as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $id = (string)($entry['id'] ?? '');
        if ($id === '') {
            continue;
        }

        $matched = false;
        foreach ($target as $index => $current) {
            if ((string)($current['id'] ?? '') === $id) {
                $target[$index] = array_merge($current, $entry);
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            $target[] = $entry;
        }
    }
}

function fetchRemoteJson(string $url): array
{
    $body = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode >= 400) {
            $message = $error !== '' ? $error : ('HTTP ' . $httpCode);
            respond(['success' => false, 'message' => 'Nu am putut citi raspunsul remote. ' . $message], 502);
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 60,
                'header' => "Accept: application/json\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            respond(['success' => false, 'message' => 'Nu am putut citi raspunsul remote.'], 502);
        }
    }

    $decoded = json_decode((string)$body, true);
    if (!is_array($decoded)) {
        respond(['success' => false, 'message' => 'Raspunsul primit nu este JSON valid.'], 422);
    }

    return $decoded;
}

function firstStringValue(array $item, array $keys): string
{
    foreach ($keys as $key) {
        $value = $item[$key] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
    }
    return '';
}

function mapApifyItems(array $items, string $type): array
{
    $urlKeys = $type === 'audio'
        ? ['audio', 'audioUrl', 'audio_url', 'fileUrl', 'file_url', 'url', 'src']
        : ['image', 'imageUrl', 'image_url', 'originalUrl', 'original_url', 'url', 'src', 'thumbnailUrl', 'thumbnail_url'];

    $nameKeys = ['name', 'title', 'label', 'fileName', 'filename'];
    $idKeys = ['id', 'slug', 'name', 'title'];
    $result = [];

    foreach ($items as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $url = firstStringValue($item, $urlKeys);
        if ($url === '') {
            continue;
        }

        $name = firstStringValue($item, $nameKeys);
        $id = firstStringValue($item, $idKeys);
        if ($id === '') {
            $id = sanitizeBaseName(($type === 'audio' ? 'audio-' : 'image-') . ($name !== '' ? $name : (string)($index + 1)));
        }

        $result[] = [
            'id' => $id,
            'name' => $name !== '' ? $name : ucfirst($type) . ' ' . ($index + 1),
            'url' => $url,
            'category' => $type === 'audio' ? 'music' : null,
        ];
    }

    return $result;
}

function scanApifyAssets(array $payload): array
{
    $token = trim((string)($payload['token'] ?? ''));
    $datasetId = trim((string)($payload['datasetId'] ?? ''));
    $apiUrl = trim((string)($payload['apiUrl'] ?? ''));
    $type = normalizeAssetType((string)($payload['type'] ?? 'image'));

    if ($type === '') {
        respond(['success' => false, 'message' => 'Tipul pentru scanare lipseste.'], 422);
    }

    if ($apiUrl === '' && $datasetId === '') {
        respond(['success' => false, 'message' => 'Completeaza un dataset ID sau un URL API.'], 422);
    }

    if ($apiUrl === '') {
        $apiUrl = 'https://api.apify.com/v2/datasets/' . rawurlencode($datasetId) . '/items?clean=true&format=json';
    }

    if ($token !== '' && strpos($apiUrl, 'token=') === false) {
        $apiUrl .= (strpos($apiUrl, '?') === false ? '?' : '&') . 'token=' . rawurlencode($token);
    }

    $decoded = fetchRemoteJson($apiUrl);
    $items = array_values(array_filter(is_array($decoded) ? $decoded : [], static fn($item): bool => is_array($item)));
    $mapped = mapApifyItems($items, $type);

    return [
        'success' => true,
        'message' => 'Scanare Apify terminata. Elemente gasite: ' . count($mapped),
        'items' => $mapped,
        'sourceUrl' => $apiUrl,
    ];
}

function saveAllConfig(string $configDir, array $payload): void
{
    $builderLayoutsPath = $configDir . DIRECTORY_SEPARATOR . 'builder-layouts.json';
    $builderThemesPath = $configDir . DIRECTORY_SEPARATOR . 'builder-themes.json';
    $builderAudioPath = $configDir . DIRECTORY_SEPARATOR . 'builder-audio.json';
    $questionTypesPath = $configDir . DIRECTORY_SEPARATOR . 'question-types.json';
    $builderCorePath = $configDir . DIRECTORY_SEPARATOR . 'builder-core.json';
    $quizzesPath = $configDir . DIRECTORY_SEPARATOR . 'quizzes.json';

    $layoutPayload = is_array($payload['layoutPayload'] ?? null) ? $payload['layoutPayload'] : [];
    $themePayload = is_array($payload['themePayload'] ?? null) ? $payload['themePayload'] : [];
    $questionTypePayload = is_array($payload['questionTypePayload'] ?? null) ? $payload['questionTypePayload'] : [];
    $quizBindingPayload = is_array($questionTypePayload['quizBindingItem'] ?? null) ? $questionTypePayload['quizBindingItem'] : [];

    $layouts = readJsonFile($builderLayoutsPath);
    $layouts['builder'] = is_array($layouts['builder'] ?? null) ? $layouts['builder'] : [];
    $layouts['builder']['titleVariants'] = is_array($layouts['builder']['titleVariants'] ?? null) ? $layouts['builder']['titleVariants'] : [];
    $layouts['builder']['mediaVariants'] = is_array($layouts['builder']['mediaVariants'] ?? null) ? $layouts['builder']['mediaVariants'] : [];
    $layouts['builder']['answersVariants'] = is_array($layouts['builder']['answersVariants'] ?? null) ? $layouts['builder']['answersVariants'] : [];
    $layouts['builder']['layoutRegistry'] = is_array($layouts['builder']['layoutRegistry'] ?? null) ? $layouts['builder']['layoutRegistry'] : [];
    $layouts['builder']['defaultLayoutBySlideType'] = is_array($layouts['builder']['defaultLayoutBySlideType'] ?? null) ? $layouts['builder']['defaultLayoutBySlideType'] : [];

    upsertNamedMap($layouts['builder']['titleVariants'], (array)($layoutPayload['titleVariantEntry'] ?? []));
    upsertNamedMap($layouts['builder']['mediaVariants'], (array)($layoutPayload['mediaVariantEntry'] ?? []));
    upsertNamedMap($layouts['builder']['answersVariants'], (array)($layoutPayload['answersVariantEntry'] ?? []));
    if (is_array($layoutPayload['layoutRegistryItem'] ?? null)) {
        upsertById($layouts['builder']['layoutRegistry'], $layoutPayload['layoutRegistryItem']);
        $slideType = (string)($layoutPayload['layoutRegistryItem']['slideType'] ?? '');
        $layoutId = (string)($layoutPayload['layoutRegistryItem']['id'] ?? '');
        if ($slideType !== '' && $layoutId !== '') {
            $layouts['builder']['defaultLayoutBySlideType'][$slideType] = $layoutId;
        }
    }

    $themes = readJsonFile($builderThemesPath);
    $themes['themes'] = is_array($themes['themes'] ?? null) ? $themes['themes'] : [];
    $themes['themeImageLibrary'] = is_array($themes['themeImageLibrary'] ?? null) ? $themes['themeImageLibrary'] : [];
    if (is_array($themePayload['themeItem'] ?? null)) {
        upsertById($themes['themes'], $themePayload['themeItem']);
        mergeLibraryItems($themes['themeImageLibrary'], (array)($themePayload['themeItem']['imageLibrary'] ?? []));
    }

    $audio = readJsonFile($builderAudioPath);
    $audio['audioLibrary'] = is_array($audio['audioLibrary'] ?? null) ? $audio['audioLibrary'] : [];
    mergeLibraryItems($audio['audioLibrary'], (array)($questionTypePayload['audioLibraryItems'] ?? []));

    $questionTypes = readJsonFile($questionTypesPath);
    $questionTypes['questionTypes'] = is_array($questionTypes['questionTypes'] ?? null) ? $questionTypes['questionTypes'] : [];
    if (is_array($questionTypePayload['questionTypeItem'] ?? null)) {
        upsertById($questionTypes['questionTypes'], $questionTypePayload['questionTypeItem']);
    }

    $quizzes = readJsonFile($quizzesPath);
    $quizzes['quizzes'] = is_array($quizzes['quizzes'] ?? null) ? $quizzes['quizzes'] : [];
    if (!empty($quizBindingPayload)) {
        upsertById($quizzes['quizzes'], $quizBindingPayload);
    }

    $core = readJsonFile($builderCorePath);
    $core['common'] = is_array($core['common'] ?? null) ? $core['common'] : [];
    $core['common']['defaults'] = is_array($core['common']['defaults'] ?? null) ? $core['common']['defaults'] : [];
    $core['common']['defaults']['theme'] = is_array($core['common']['defaults']['theme'] ?? null) ? $core['common']['defaults']['theme'] : [];
    $core['common']['defaults']['timer'] = is_array($core['common']['defaults']['timer'] ?? null) ? $core['common']['defaults']['timer'] : [];
    $core['common']['defaults']['scoring'] = is_array($core['common']['defaults']['scoring'] ?? null) ? $core['common']['defaults']['scoring'] : [];
    $core['common']['defaults']['scoring']['speedBonus'] = is_array($core['common']['defaults']['scoring']['speedBonus'] ?? null) ? $core['common']['defaults']['scoring']['speedBonus'] : [];
    $core['common']['defaults']['imageReveal'] = is_array($core['common']['defaults']['imageReveal'] ?? null) ? $core['common']['defaults']['imageReveal'] : [];

    $themeItem = is_array($themePayload['themeItem'] ?? null) ? $themePayload['themeItem'] : [];
    $defaults = is_array($questionTypePayload['defaults'] ?? null) ? $questionTypePayload['defaults'] : [];

    if (!empty($themeItem['id'])) {
        $core['common']['defaults']['theme']['themeId'] = $themeItem['id'];
    }
    if (!empty($themeItem['url'])) {
        $core['common']['defaults']['theme']['backgroundUrl'] = $themeItem['url'];
    }
    if (!empty($defaults['timer'])) {
        $core['common']['defaults']['timer']['selected'] = $defaults['timer'];
    }
    if (array_key_exists('basePoints', $defaults)) {
        $core['common']['defaults']['scoring']['basePoints'] = (int)$defaults['basePoints'];
    }
    if (array_key_exists('speedBonusEnabled', $defaults)) {
        $core['common']['defaults']['scoring']['speedBonus']['enabled'] = (bool)$defaults['speedBonusEnabled'];
    }
    if (array_key_exists('speedBonusWithinSeconds', $defaults)) {
        $core['common']['defaults']['scoring']['speedBonus']['withinSeconds'] = (int)$defaults['speedBonusWithinSeconds'];
    }

    $questionTypeItem = is_array($questionTypePayload['questionTypeItem'] ?? null) ? $questionTypePayload['questionTypeItem'] : [];
    $imageReveal = (array)($questionTypeItem['settings']['imageReveal'] ?? []);
    if (array_key_exists('enabled', $imageReveal)) {
        $core['common']['defaults']['imageReveal']['enabled'] = (bool)$imageReveal['enabled'];
    }
    if (!empty($imageReveal['mode'])) {
        $core['common']['defaults']['imageReveal']['mode'] = (string)$imageReveal['mode'];
    }

    writeJsonFile($builderLayoutsPath, $layouts);
    writeJsonFile($builderThemesPath, $themes);
    writeJsonFile($builderAudioPath, $audio);
    writeJsonFile($questionTypesPath, $questionTypes);
    writeJsonFile($builderCorePath, $core);
    writeJsonFile($quizzesPath, $quizzes);
}

function deleteThemeConfig(string $configDir, string $id): void
{
    if ($id === '') {
        respond(['success' => false, 'message' => 'Lipseste ID-ul temei.'], 422);
    }

    $builderThemesPath = $configDir . DIRECTORY_SEPARATOR . 'builder-themes.json';
    $themes = readJsonFile($builderThemesPath);
    $themes['themes'] = is_array($themes['themes'] ?? null) ? $themes['themes'] : [];
    $before = count($themes['themes']);
    $themes['themes'] = array_values(array_filter($themes['themes'], static fn(array $item): bool => (string)($item['id'] ?? '') !== $id));

    if ($before === count($themes['themes'])) {
        respond(['success' => false, 'message' => 'Tema nu a fost gasita in fisier.'], 404);
    }

    writeJsonFile($builderThemesPath, $themes);
}

function deleteLayoutConfig(string $configDir, string $id): void
{
    if ($id === '') {
        respond(['success' => false, 'message' => 'Lipseste ID-ul layoutului.'], 422);
    }

    $builderLayoutsPath = $configDir . DIRECTORY_SEPARATOR . 'builder-layouts.json';
    $layouts = readJsonFile($builderLayoutsPath);
    $layouts['builder'] = is_array($layouts['builder'] ?? null) ? $layouts['builder'] : [];
    $layouts['builder']['layoutRegistry'] = is_array($layouts['builder']['layoutRegistry'] ?? null) ? $layouts['builder']['layoutRegistry'] : [];
    $layouts['builder']['defaultLayoutBySlideType'] = is_array($layouts['builder']['defaultLayoutBySlideType'] ?? null) ? $layouts['builder']['defaultLayoutBySlideType'] : [];

    $before = count($layouts['builder']['layoutRegistry']);
    $layouts['builder']['layoutRegistry'] = array_values(array_filter(
        $layouts['builder']['layoutRegistry'],
        static fn(array $item): bool => (string)($item['id'] ?? '') !== $id
    ));

    if ($before === count($layouts['builder']['layoutRegistry'])) {
        respond(['success' => false, 'message' => 'Layoutul nu a fost gasit in fisier.'], 404);
    }

    foreach ($layouts['builder']['defaultLayoutBySlideType'] as $slideType => $layoutId) {
        if ((string)$layoutId === $id) {
            unset($layouts['builder']['defaultLayoutBySlideType'][$slideType]);
        }
    }

    writeJsonFile($builderLayoutsPath, $layouts);
}

function deleteQuestionTypeConfig(string $configDir, string $id): void
{
    if ($id === '') {
        respond(['success' => false, 'message' => 'Lipseste ID-ul quizzului.'], 422);
    }

    $questionTypesPath = $configDir . DIRECTORY_SEPARATOR . 'question-types.json';
    $questionTypes = readJsonFile($questionTypesPath);
    $questionTypes['questionTypes'] = is_array($questionTypes['questionTypes'] ?? null) ? $questionTypes['questionTypes'] : [];
    $before = count($questionTypes['questionTypes']);
    $questionTypes['questionTypes'] = array_values(array_filter(
        $questionTypes['questionTypes'],
        static fn(array $item): bool => (string)($item['id'] ?? '') !== $id
    ));

    if ($before === count($questionTypes['questionTypes'])) {
        respond(['success' => false, 'message' => 'Quizzul nu a fost gasit in fisier.'], 404);
    }

    writeJsonFile($questionTypesPath, $questionTypes);
}

$action = requestAction();
$method = requestMethod();

debugLog('Incoming request', [
    'method' => $method,
    'action' => $action,
    'get' => $_GET,
    'post_keys' => array_keys($_POST),
    'files_keys' => array_keys($_FILES),
]);

try {
    if ($action === 'list_assets' && $method === 'GET') {
        $type = requestAssetType();
        if ($type === '') {
            respond(['success' => false, 'message' => 'Tipul de asset lipseste.'], 422);
        }

        respond([
            'success' => true,
            'items' => listAssets($uploadBaseDir, $type),
        ]);
    }

    if ($action === 'ensure_upload_dirs' && in_array($method, ['GET', 'POST'], true)) {
        $dirs = ensureUploadDirectories($uploadBaseDir);
        $allWritable = count(array_filter($dirs, static fn(array $item): bool => $item['writable'])) === count($dirs);
        respond([
            'success' => true,
            'message' => $allWritable
                ? 'Folderele necesare exista si sunt pregatite pentru upload.'
                : 'Folderele au fost create, dar cel putin unul nu este inscriptibil pentru server.',
            'directories' => $dirs,
        ]);
    }

    if ($action === 'upload_assets' && $method === 'POST') {
        $type = requestAssetType();
        if ($type === '') {
            debugLog('Upload failed: missing type', ['post' => $_POST, 'files' => array_keys($_FILES)]);
            respond(['success' => false, 'message' => 'Tipul de asset lipseste.'], 422);
        }

        ensureUploadDirectories($uploadBaseDir);
        $result = uploadAssets($uploadBaseDir, $type);
        $message = '';
        if (count($result['uploaded']) > 0) {
            $message = 'Upload realizat cu succes.';
            if (!empty($result['errors'])) {
                $message .= ' Unele fisiere au fost sarite: ' . implode(' ', $result['errors']);
            }
        } else {
            $message = !empty($result['errors'])
                ? implode(' ', $result['errors'])
                : 'Niciun fisier nu a putut fi incarcat.';
        }

        respond([
            'success' => count($result['uploaded']) > 0,
            'message' => $message,
            'uploaded' => $result['uploaded'],
            'errors' => $result['errors'],
            'items' => listAssets($uploadBaseDir, $type),
        ], count($result['uploaded']) > 0 ? 200 : 422);
    }

    if ($action === 'delete_asset' && $method === 'POST') {
        $body = parseJsonBody();
        $type = normalizeAssetType((string)($body['type'] ?? $_POST['type'] ?? $_GET['type'] ?? ''));
        $name = (string)($body['name'] ?? $_POST['name'] ?? '');
        if ($type === '') {
            respond(['success' => false, 'message' => 'Tipul de asset lipseste.'], 422);
        }

        deleteAsset($uploadBaseDir, $type, $name);
        respond([
            'success' => true,
            'items' => listAssets($uploadBaseDir, $type),
        ]);
    }

    if ($action === 'clear_uploads' && $method === 'POST') {
        $images = clearDirectoryFiles($uploadBaseDir . DIRECTORY_SEPARATOR . 'images');
        $audio = clearDirectoryFiles($uploadBaseDir . DIRECTORY_SEPARATOR . 'audio');
        $logs = clearDirectoryFiles($uploadBaseDir . DIRECTORY_SEPARATOR . 'logs');
        $errors = array_merge($images['errors'], $audio['errors'], $logs['errors']);

        debugLog('Clear uploads executed', [
            'deleted_images' => count($images['deleted']),
            'deleted_audio' => count($audio['deleted']),
            'deleted_logs' => count($logs['deleted']),
            'errors' => $errors,
        ]);

        respond([
            'success' => empty($errors),
            'message' => empty($errors)
                ? 'Toate fisierele uploadate au fost sterse.'
                : 'Unele fisiere nu au putut fi sterse.',
            'deletedCount' => count($images['deleted']) + count($audio['deleted']) + count($logs['deleted']),
            'errors' => $errors,
        ], empty($errors) ? 200 : 500);
    }

    if ($action === 'scan_apify' && $method === 'POST') {
        $payload = parseJsonBody();
        respond(scanApifyAssets($payload));
    }

    if ($action === 'save_all' && $method === 'POST') {
        $payload = parseJsonBody();
        saveAllConfig($configDir, $payload);
        respond([
            'success' => true,
            'message' => 'Configuratia a fost salvata in fisierele proiectului.',
        ]);
    }

    if ($action === 'delete_theme' && $method === 'POST') {
        $payload = parseJsonBody();
        deleteThemeConfig($configDir, trim((string)($payload['id'] ?? $_POST['id'] ?? '')));
        respond([
            'success' => true,
            'message' => 'Tema a fost stearsa din fisierul proiectului.',
        ]);
    }

    if ($action === 'delete_layout' && $method === 'POST') {
        $payload = parseJsonBody();
        deleteLayoutConfig($configDir, trim((string)($payload['id'] ?? $_POST['id'] ?? '')));
        respond([
            'success' => true,
            'message' => 'Layoutul a fost sters din fisierul proiectului.',
        ]);
    }

    if ($action === 'delete_question_type' && $method === 'POST') {
        $payload = parseJsonBody();
        deleteQuestionTypeConfig($configDir, trim((string)($payload['id'] ?? $_POST['id'] ?? '')));
        respond([
            'success' => true,
            'message' => 'Quizzul a fost sters din fisierul proiectului.',
        ]);
    }

    debugLog('Unknown action', [
        'method' => $method,
        'action' => $action,
        'get' => $_GET,
        'post' => $_POST,
        'files_keys' => array_keys($_FILES),
    ]);
    respond([
        'success' => false,
        'message' => 'Actiune necunoscuta.',
        'debug' => [
            'method' => $method,
            'action' => $action,
            'get' => $_GET,
            'post_keys' => array_keys($_POST),
            'files_keys' => array_keys($_FILES),
            'logFile' => $logFile,
        ],
    ], 404);
} catch (Throwable $e) {
    debugLog('Unhandled exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    respond([
        'success' => false,
        'message' => 'Eroare server: ' . $e->getMessage(),
        'logFile' => $logFile,
    ], 500);
}
