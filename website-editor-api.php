<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| DB CONFIG
|--------------------------------------------------------------------------
*/
$dbHost = 'localhost';
$dbName = 'lilit2';
$dbUser = 'lilit2';
$dbPass = 'aM1xN7kS3w';

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function str_or_null(array $src, string $key): ?string
{
    if (!isset($src[$key])) {
        return null;
    }
    $value = trim((string)$src[$key]);
    return $value === '' ? null : $value;
}

function str_or_empty(array $src, string $key): string
{
    return isset($src[$key]) ? trim((string)$src[$key]) : '';
}

function int_or_zero(array $src, string $key): int
{
    return isset($src[$key]) ? (int)$src[$key] : 0;
}

function normalizeJsonString(?string $json, array $default = []): string
{
    $json = trim((string)$json);
    if ($json === '') {
        return json_encode($default, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $decoded = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return json_encode($default, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function decodeJsonField(?string $json): array
{
    if (!$json) return [];
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);

    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
    }
}

function moveItem(PDO $pdo, string $table, string $idField, int $id, string $direction, ?string $scopeField = null, ?int $scopeValue = null): bool
{
    $stmt = $pdo->prepare("SELECT id, sort_order" . ($scopeField ? ", {$scopeField}" : "") . " FROM {$table} WHERE {$idField} = ? LIMIT 1");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        return false;
    }

    $operator = $direction === 'up' ? '<' : '>';
    $orderDir = $direction === 'up' ? 'DESC' : 'ASC';

    $sql = "SELECT id, sort_order FROM {$table} WHERE sort_order {$operator} :sort_order";
    $params = [':sort_order' => (int)$current['sort_order']];

    if ($scopeField !== null) {
        $sql .= " AND {$scopeField} = :scope_value";
        $params[':scope_value'] = $scopeValue;
    }

    $sql .= " ORDER BY sort_order {$orderDir}, id {$orderDir} LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $swap = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$swap) {
        return false;
    }

    $pdo->beginTransaction();
    try {
        $stmt1 = $pdo->prepare("UPDATE {$table} SET sort_order = :sort WHERE id = :id");
        $stmt1->execute([
            ':sort' => (int)$swap['sort_order'],
            ':id'   => (int)$current['id']
        ]);

        $stmt2 = $pdo->prepare("UPDATE {$table} SET sort_order = :sort WHERE id = :id");
        $stmt2->execute([
            ':sort' => (int)$current['sort_order'],
            ':id'   => (int)$swap['id']
        ]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/*
|--------------------------------------------------------------------------
| CONNECT DB
|--------------------------------------------------------------------------
*/
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $e) {
    jsonResponse([
        'ok' => false,
        'message' => 'Eroare conectare DB: ' . $e->getMessage()
    ], 500);
}

/*
|--------------------------------------------------------------------------
| TABLES
|--------------------------------------------------------------------------
*/
$pdo->exec("
CREATE TABLE IF NOT EXISTS website_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value LONGTEXT NULL,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS website_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS website_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id INT UNSIGNED NOT NULL,
    section_key VARCHAR(120) NOT NULL,
    section_name VARCHAR(255) NOT NULL,
    section_type VARCHAR(100) NOT NULL DEFAULT 'default',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    settings_json LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_id (page_id),
    CONSTRAINT fk_website_sections_page
        FOREIGN KEY (page_id) REFERENCES website_pages(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS website_blocks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id INT UNSIGNED NOT NULL,
    block_key VARCHAR(120) NOT NULL,
    block_type VARCHAR(100) NOT NULL DEFAULT 'text',
    title VARCHAR(255) NULL,
    subtitle VARCHAR(255) NULL,
    content TEXT NULL,
    image_url VARCHAR(500) NULL,
    button_text VARCHAR(120) NULL,
    button_url VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    data_json LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_section_id (section_id),
    CONSTRAINT fk_website_blocks_section
        FOREIGN KEY (section_id) REFERENCES website_sections(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

/*
|--------------------------------------------------------------------------
| EXTRA COLUMNS FOR ADVANCED EDITOR
|--------------------------------------------------------------------------
*/
ensureColumn($pdo, 'website_sections', 'title', 'VARCHAR(255) NULL AFTER section_type');
ensureColumn($pdo, 'website_sections', 'subtitle', 'TEXT NULL AFTER title');

ensureColumn($pdo, 'website_blocks', 'badge', 'VARCHAR(255) NULL AFTER button_url');
ensureColumn($pdo, 'website_blocks', 'extra_json', 'LONGTEXT NULL AFTER data_json');

/*
|--------------------------------------------------------------------------
| HOME PAGE
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id FROM website_pages WHERE slug = 'home' LIMIT 1");
$stmt->execute();
$homePage = $stmt->fetch();

if (!$homePage) {
    $stmt = $pdo->prepare("
        INSERT INTO website_pages (slug, title, status)
        VALUES ('home', 'Homepage', 'published')
    ");
    $stmt->execute();
    $homePageId = (int)$pdo->lastInsertId();
} else {
    $homePageId = (int)$homePage['id'];
}

/*
|--------------------------------------------------------------------------
| DEFAULT SETTINGS
|--------------------------------------------------------------------------
*/
$defaultSettings = [
    'general' => [
        'site_name' => 'QuizDigo',
        'site_tagline' => 'Quizuri interactive și live',
        'site_short_desc' => 'QuizDigo este o platformă modernă pentru creare de quizuri interactive, sesiuni live și evaluări digitale.',
        'logo_url' => '/assets/logo.png',
    ],
    'hero' => [
        'hero_badge' => 'Platformă interactivă pentru educație, training și evenimente',
        'hero_title' => 'Creează quizuri interactive care implică și motivează.',
        'hero_desc' => 'QuizDigo te ajută să construiești quizuri moderne, sesiuni live și evaluări digitale într-o platformă clară, organizată și ușor de folosit.',
        'hero_btn1' => 'Creează cont gratuit',
        'hero_btn2' => 'Intră în platformă',
        'hero_btn1_link' => '/public/register',
        'hero_btn2_link' => '/public/login',
    ],
    'footer' => [
        'support_email' => 'support@quizdigo.com',
        'phone' => '+373 000 000 000',
        'address' => 'Drochia, Republica Moldova',
        'footer_desc' => 'Platformă modernă pentru quizuri interactive, sesiuni live, evaluări și experiențe digitale utile pentru educație, companii și evenimente.'
    ],
    'seo' => [
        'meta_title' => 'QuizDigo - Creează quizuri interactive',
        'meta_description' => 'QuizDigo este o platformă modernă pentru creare de quizuri interactive, sesiuni live și evaluări digitale.',
        'meta_keywords' => 'quiz, quiz live, educatie, training, jocuri, evaluare, quiz platform'
    ]
];

foreach ($defaultSettings as $group => $items) {
    foreach ($items as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT INTO website_settings (setting_key, setting_value, setting_group)
            VALUES (:k, :v, :g)
            ON DUPLICATE KEY UPDATE setting_key = setting_key
        ");
        $stmt->execute([
            ':k' => $key,
            ':v' => $value,
            ':g' => $group
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| DATA LOADERS
|--------------------------------------------------------------------------
*/
function loadSettings(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT setting_key, setting_value, setting_group FROM website_settings ORDER BY id ASC");
    $rows = $stmt->fetchAll();

    $settings = [];
    foreach ($rows as $row) {
        $group = $row['setting_group'] ?: 'general';
        if (!isset($settings[$group])) {
            $settings[$group] = [];
        }
        $settings[$group][$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

function loadSections(PDO $pdo, int $pageId): array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM website_sections
        WHERE page_id = :page_id
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([':page_id' => $pageId]);
    $sections = $stmt->fetchAll();

    foreach ($sections as &$section) {
        $section['settings'] = decodeJsonField($section['settings_json'] ?? null);

        $stmtBlocks = $pdo->prepare("
            SELECT *
            FROM website_blocks
            WHERE section_id = :section_id
            ORDER BY sort_order ASC, id ASC
        ");
        $stmtBlocks->execute([':section_id' => $section['id']]);
        $blocks = $stmtBlocks->fetchAll();

        foreach ($blocks as &$block) {
            $block['data'] = decodeJsonField($block['data_json'] ?? null);
            $block['extra'] = decodeJsonField($block['extra_json'] ?? null);
        }

        $section['blocks'] = $blocks;
    }

    return $sections;
}

/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'load_all') {
        jsonResponse([
            'ok' => true,
            'settings' => loadSettings($pdo),
            'sections' => loadSections($pdo, $homePageId)
        ]);
    }

    if ($action === 'save_group') {
        $group = str_or_empty($_POST, 'group');
        $payloadRaw = str_or_empty($_POST, 'payload');

        if ($group === '' || $payloadRaw === '') {
            jsonResponse([
                'ok' => false,
                'message' => 'Lipsește group sau payload.'
            ], 422);
        }

        $payload = json_decode($payloadRaw, true);

        if (!is_array($payload)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Payload JSON invalid.'
            ], 422);
        }

        $stmt = $pdo->prepare("
            INSERT INTO website_settings (setting_key, setting_value, setting_group)
            VALUES (:k, :v, :g)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                setting_group = VALUES(setting_group)
        ");

        foreach ($payload as $key => $value) {
            $stmt->execute([
                ':k' => (string)$key,
                ':v' => is_array($value)
                    ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : (string)$value,
                ':g' => $group
            ]);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Setările au fost salvate.'
        ]);
    }

    if ($action === 'add_section') {
        $sectionKey = str_or_empty($_POST, 'section_key');
        $sectionName = str_or_empty($_POST, 'section_name');
        $sectionType = str_or_empty($_POST, 'section_type');
        $sortOrder = int_or_zero($_POST, 'sort_order');
        $title = str_or_null($_POST, 'title');
        $subtitle = str_or_null($_POST, 'subtitle');
        $settingsJson = normalizeJsonString($_POST['settings_json'] ?? '', []);

        if ($sectionKey === '' || $sectionName === '') {
            jsonResponse([
                'ok' => false,
                'message' => 'Section key și section name sunt obligatorii.'
            ], 422);
        }

        $stmt = $pdo->prepare("
            INSERT INTO website_sections
            (page_id, section_key, section_name, section_type, title, subtitle, is_active, sort_order, settings_json)
            VALUES
            (:page_id, :section_key, :section_name, :section_type, :title, :subtitle, 1, :sort_order, :settings_json)
        ");

        $stmt->execute([
            ':page_id' => $homePageId,
            ':section_key' => $sectionKey,
            ':section_name' => $sectionName,
            ':section_type' => $sectionType !== '' ? $sectionType : 'default',
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':sort_order' => $sortOrder,
            ':settings_json' => $settingsJson
        ]);

        jsonResponse([
            'ok' => true,
            'message' => 'Secțiunea a fost adăugată.'
        ]);
    }

    if ($action === 'update_section') {
        $sectionId = int_or_zero($_POST, 'section_id');

        if ($sectionId <= 0) {
            jsonResponse([
                'ok' => false,
                'message' => 'section_id invalid.'
            ], 422);
        }

        $sectionKey = str_or_empty($_POST, 'section_key');
        $sectionName = str_or_empty($_POST, 'section_name');
        $sectionType = str_or_empty($_POST, 'section_type');
        $sortOrder = int_or_zero($_POST, 'sort_order');
        $title = str_or_null($_POST, 'title');
        $subtitle = str_or_null($_POST, 'subtitle');
        $isActive = int_or_zero($_POST, 'is_active') ? 1 : 0;
        $settingsJson = normalizeJsonString($_POST['settings_json'] ?? '', []);

        $stmt = $pdo->prepare("
            UPDATE website_sections
            SET
                section_key = :section_key,
                section_name = :section_name,
                section_type = :section_type,
                title = :title,
                subtitle = :subtitle,
                is_active = :is_active,
                sort_order = :sort_order,
                settings_json = :settings_json
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':section_key' => $sectionKey,
            ':section_name' => $sectionName,
            ':section_type' => $sectionType !== '' ? $sectionType : 'default',
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':is_active' => $isActive,
            ':sort_order' => $sortOrder,
            ':settings_json' => $settingsJson,
            ':id' => $sectionId
        ]);

        jsonResponse([
            'ok' => true,
            'message' => 'Secțiunea a fost actualizată.'
        ]);
    }

    if ($action === 'move_section') {
        $sectionId = int_or_zero($_POST, 'section_id');
        $direction = str_or_empty($_POST, 'direction');

        if ($sectionId <= 0 || !in_array($direction, ['up', 'down'], true)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Parametri invalizi.'
            ], 422);
        }

        $moved = moveItem($pdo, 'website_sections', 'id', $sectionId, $direction, 'page_id', $homePageId);

        jsonResponse([
            'ok' => $moved,
            'message' => $moved ? 'Secțiunea a fost mutată.' : 'Secțiunea nu poate fi mutată.'
        ], $moved ? 200 : 422);
    }

    if ($action === 'delete_section') {
        $sectionId = int_or_zero($_POST, 'section_id');

        if ($sectionId <= 0) {
            jsonResponse([
                'ok' => false,
                'message' => 'section_id invalid.'
            ], 422);
        }

        $stmt = $pdo->prepare("DELETE FROM website_sections WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $sectionId]);

        jsonResponse([
            'ok' => true,
            'message' => 'Secțiunea a fost ștearsă.'
        ]);
    }

    if ($action === 'add_block') {
        $sectionId = int_or_zero($_POST, 'section_id');
        $blockKey = str_or_empty($_POST, 'block_key');
        $blockType = str_or_empty($_POST, 'block_type');
        $sortOrder = int_or_zero($_POST, 'sort_order');

        if ($sectionId <= 0 || $blockKey === '') {
            jsonResponse([
                'ok' => false,
                'message' => 'section_id și block_key sunt obligatorii.'
            ], 422);
        }

        $title = str_or_null($_POST, 'title');
        $subtitle = str_or_null($_POST, 'subtitle');
        $content = str_or_null($_POST, 'content');
        $imageUrl = str_or_null($_POST, 'image_url');
        $buttonText = str_or_null($_POST, 'button_text');
        $buttonUrl = str_or_null($_POST, 'button_url');
        $badge = str_or_null($_POST, 'badge');
        $dataJson = normalizeJsonString($_POST['data_json'] ?? '', []);
        $extraJson = normalizeJsonString($_POST['extra_json'] ?? '', []);

        $stmt = $pdo->prepare("
            INSERT INTO website_blocks
            (section_id, block_key, block_type, title, subtitle, content, image_url, button_text, button_url, badge, is_active, sort_order, data_json, extra_json)
            VALUES
            (:section_id, :block_key, :block_type, :title, :subtitle, :content, :image_url, :button_text, :button_url, :badge, 1, :sort_order, :data_json, :extra_json)
        ");

        $stmt->execute([
            ':section_id' => $sectionId,
            ':block_key' => $blockKey,
            ':block_type' => $blockType !== '' ? $blockType : 'text',
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':content' => $content,
            ':image_url' => $imageUrl,
            ':button_text' => $buttonText,
            ':button_url' => $buttonUrl,
            ':badge' => $badge,
            ':sort_order' => $sortOrder,
            ':data_json' => $dataJson,
            ':extra_json' => $extraJson
        ]);

        jsonResponse([
            'ok' => true,
            'message' => 'Blocul a fost adăugat.'
        ]);
    }

    if ($action === 'update_block') {
        $blockId = int_or_zero($_POST, 'block_id');

        if ($blockId <= 0) {
            jsonResponse([
                'ok' => false,
                'message' => 'block_id invalid.'
            ], 422);
        }

        $sectionId = int_or_zero($_POST, 'section_id');
        $blockKey = str_or_empty($_POST, 'block_key');
        $blockType = str_or_empty($_POST, 'block_type');
        $sortOrder = int_or_zero($_POST, 'sort_order');
        $title = str_or_null($_POST, 'title');
        $subtitle = str_or_null($_POST, 'subtitle');
        $content = str_or_null($_POST, 'content');
        $imageUrl = str_or_null($_POST, 'image_url');
        $buttonText = str_or_null($_POST, 'button_text');
        $buttonUrl = str_or_null($_POST, 'button_url');
        $badge = str_or_null($_POST, 'badge');
        $isActive = int_or_zero($_POST, 'is_active') ? 1 : 0;
        $dataJson = normalizeJsonString($_POST['data_json'] ?? '', []);
        $extraJson = normalizeJsonString($_POST['extra_json'] ?? '', []);

        $stmt = $pdo->prepare("
            UPDATE website_blocks
            SET
                section_id = :section_id,
                block_key = :block_key,
                block_type = :block_type,
                title = :title,
                subtitle = :subtitle,
                content = :content,
                image_url = :image_url,
                button_text = :button_text,
                button_url = :button_url,
                badge = :badge,
                is_active = :is_active,
                sort_order = :sort_order,
                data_json = :data_json,
                extra_json = :extra_json
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':section_id' => $sectionId,
            ':block_key' => $blockKey,
            ':block_type' => $blockType !== '' ? $blockType : 'text',
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':content' => $content,
            ':image_url' => $imageUrl,
            ':button_text' => $buttonText,
            ':button_url' => $buttonUrl,
            ':badge' => $badge,
            ':is_active' => $isActive,
            ':sort_order' => $sortOrder,
            ':data_json' => $dataJson,
            ':extra_json' => $extraJson,
            ':id' => $blockId
        ]);

        jsonResponse([
            'ok' => true,
            'message' => 'Blocul a fost actualizat.'
        ]);
    }

    if ($action === 'move_block') {
        $blockId = int_or_zero($_POST, 'block_id');
        $direction = str_or_empty($_POST, 'direction');

        if ($blockId <= 0 || !in_array($direction, ['up', 'down'], true)) {
            jsonResponse([
                'ok' => false,
                'message' => 'Parametri invalizi.'
            ], 422);
        }

        $stmt = $pdo->prepare("SELECT section_id FROM website_blocks WHERE id = ? LIMIT 1");
        $stmt->execute([$blockId]);
        $sectionId = (int)$stmt->fetchColumn();

        if ($sectionId <= 0) {
            jsonResponse([
                'ok' => false,
                'message' => 'Blocul nu există.'
            ], 404);
        }

        $moved = moveItem($pdo, 'website_blocks', 'id', $blockId, $direction, 'section_id', $sectionId);

        jsonResponse([
            'ok' => $moved,
            'message' => $moved ? 'Blocul a fost mutat.' : 'Blocul nu poate fi mutat.'
        ], $moved ? 200 : 422);
    }

    if ($action === 'delete_block') {
        $blockId = int_or_zero($_POST, 'block_id');

        if ($blockId <= 0) {
            jsonResponse([
                'ok' => false,
                'message' => 'block_id invalid.'
            ], 422);
        }

        $stmt = $pdo->prepare("DELETE FROM website_blocks WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $blockId]);

        jsonResponse([
            'ok' => true,
            'message' => 'Blocul a fost șters.'
        ]);
    }

    if ($action === 'load') {
        $settings = loadSettings($pdo);
        $flat = [];
        foreach ($settings as $group => $items) {
            foreach ($items as $k => $v) {
                $flat[$k] = $v;
            }
        }

        jsonResponse([
            'ok' => true,
            'data' => $flat
        ]);
    }

    if ($action === 'save') {
        $allowedFields = [
            'site_name',
            'site_tagline',
            'site_short_desc',
            'hero_badge',
            'hero_title',
            'hero_desc',
            'hero_btn1',
            'hero_btn2',
            'hero_btn1_link',
            'hero_btn2_link',
            'support_email',
            'phone',
            'address',
            'footer_desc',
            'meta_title',
            'meta_description',
            'meta_keywords'
        ];

        $stmt = $pdo->prepare("
            INSERT INTO website_settings (setting_key, setting_value, setting_group)
            VALUES (:k, :v, :g)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                setting_group = VALUES(setting_group)
        ");

        foreach ($allowedFields as $field) {
            $value = isset($_POST[$field]) ? trim((string)$_POST[$field]) : '';

            $group = 'general';
            if (str_starts_with($field, 'hero_')) {
                $group = 'hero';
            } elseif (str_starts_with($field, 'meta_')) {
                $group = 'seo';
            } elseif (in_array($field, ['support_email', 'phone', 'address', 'footer_desc'], true)) {
                $group = 'footer';
            }

            $stmt->execute([
                ':k' => $field,
                ':v' => $value,
                ':g' => $group
            ]);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Datele au fost salvate.'
        ]);
    }

    jsonResponse([
        'ok' => false,
        'message' => 'Acțiune invalidă: ' . $action
    ], 400);

} catch (Throwable $e) {
    jsonResponse([
        'ok' => false,
        'message' => 'Eroare server: ' . $e->getMessage()
    ], 500);
}