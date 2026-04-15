<?php
declare(strict_types=1);

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
| DB CONNECT
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
    die('Eroare DB: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getJsonArray(?string $json): array
{
    if (!$json) return [];
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function getSettings(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM website_settings");
    $rows = $stmt->fetchAll();

    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function getPage(PDO $pdo, string $slug = 'home'): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM website_pages WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();

    return $page ?: null;
}

function getSectionsWithBlocks(PDO $pdo, int $pageId): array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM website_sections
        WHERE page_id = :page_id AND is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([':page_id' => $pageId]);
    $sections = $stmt->fetchAll();

    foreach ($sections as &$section) {
        $section['settings'] = getJsonArray($section['settings_json'] ?? null);

        $stmtBlocks = $pdo->prepare("
            SELECT *
            FROM website_blocks
            WHERE section_id = :section_id AND is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        $stmtBlocks->execute([':section_id' => $section['id']]);
        $blocks = $stmtBlocks->fetchAll();

        foreach ($blocks as &$block) {
            $block['data'] = getJsonArray($block['data_json'] ?? null);
            $block['extra'] = getJsonArray($block['extra_json'] ?? null);
        }

        $section['blocks'] = $blocks;
    }

    return $sections;
}

function mapSectionsByKey(array $sections): array
{
    $map = [];
    foreach ($sections as $section) {
        $map[$section['section_key']] = $section;
    }
    return $map;
}

function findBlockByKey(array $blocks, string $key): ?array
{
    foreach ($blocks as $block) {
        if (($block['block_key'] ?? '') === $key) {
            return $block;
        }
    }
    return null;
}

function filterBlocksByType(array $blocks, string $type): array
{
    return array_values(array_filter($blocks, static function ($block) use ($type) {
        return ($block['block_type'] ?? '') === $type;
    }));
}

function filterBlocksByVisibleGroup(array $blocks, int $group): array
{
    return array_values(array_filter($blocks, static function ($block) use ($group) {
        $visibleGroup = (int)($block['data']['visible_group'] ?? 1);
        return $visibleGroup === $group;
    }));
}

function getTranslationMap(PDO $pdo, string $langCode = 'en'): array
{
    $stmt = $pdo->prepare("
        SELECT entity_type, entity_id, field_name, translated_value
        FROM website_translations
        WHERE lang_code = ?
    ");
    $stmt->execute([$langCode]);
    $rows = $stmt->fetchAll();

    $map = [];
    foreach ($rows as $row) {
        $map[$row['entity_type']][$row['entity_id']][$row['field_name']] = $row['translated_value'];
    }

    return $map;
}

function tSection(array $section, string $field, array $translationMap, string $fallback = ''): string
{
    $id = (int)($section['id'] ?? 0);
    return $translationMap['section'][$id][$field] ?? (string)($section[$field] ?? $fallback);
}

function tBlock(array $block, string $field, array $translationMap, string $fallback = ''): string
{
    $id = (int)($block['id'] ?? 0);
    return $translationMap['block'][$id][$field] ?? (string)($block[$field] ?? $fallback);
}

/*
|--------------------------------------------------------------------------
| LOAD DATA
|--------------------------------------------------------------------------
*/
$page = getPage($pdo, 'home');
if (!$page) {
    die('Pagina home nu există în baza de date.');
}

$settings = getSettings($pdo);
$sections = getSectionsWithBlocks($pdo, (int)$page['id']);
$sectionMap = mapSectionsByKey($sections);
$translationsEn = getTranslationMap($pdo, 'en');

/*
|--------------------------------------------------------------------------
| SECTION SHORTCUTS
|--------------------------------------------------------------------------
*/
$navbarSection       = $sectionMap['navbar'] ?? ['blocks' => [], 'settings' => []];
$heroSection         = $sectionMap['hero'] ?? ['blocks' => [], 'settings' => []];
$howSection          = $sectionMap['how_it_works'] ?? ['blocks' => [], 'settings' => []];
$joinSection         = $sectionMap['join_form'] ?? ['blocks' => [], 'settings' => []];
$typesSection        = $sectionMap['question_types'] ?? ['blocks' => [], 'settings' => []];
$statsSection        = $sectionMap['stats'] ?? ['blocks' => [], 'settings' => []];
$partnersSection     = $sectionMap['partners'] ?? ['blocks' => [], 'settings' => []];
$newsSection         = $sectionMap['news'] ?? ['blocks' => [], 'settings' => []];
$subscriptionSection = $sectionMap['subscription'] ?? ['blocks' => [], 'settings' => []];
$socialSection       = $sectionMap['social'] ?? ['blocks' => [], 'settings' => []];
$infoSection         = $sectionMap['info_blocks'] ?? ['blocks' => [], 'settings' => []];
$footerSection       = $sectionMap['footer'] ?? ['blocks' => [], 'settings' => []];

/*
|--------------------------------------------------------------------------
| BLOCK SHORTCUTS
|--------------------------------------------------------------------------
*/
$navItems       = filterBlocksByType($navbarSection['blocks'], 'nav_item');
$navButtons     = filterBlocksByType($navbarSection['blocks'], 'nav_button');

$heroBtnJoin    = findBlockByKey($heroSection['blocks'], 'hero_btn_join');
$heroBtnReg     = findBlockByKey($heroSection['blocks'], 'hero_btn_register');

$howBlocks      = filterBlocksByType($howSection['blocks'], 'step_card');

$typeBlocksG1   = filterBlocksByVisibleGroup(filterBlocksByType($typesSection['blocks'], 'type_card'), 1);
$typeBlocksG2   = filterBlocksByVisibleGroup(filterBlocksByType($typesSection['blocks'], 'type_card'), 2);
$typeBlocksG3   = filterBlocksByVisibleGroup(filterBlocksByType($typesSection['blocks'], 'type_card'), 3);

$statBlocks     = filterBlocksByType($statsSection['blocks'], 'stat_card');

$partnerBlocks  = filterBlocksByType($partnersSection['blocks'], 'partner_logo');

$newsBlocksG1   = filterBlocksByVisibleGroup(filterBlocksByType($newsSection['blocks'], 'news_card'), 1);
$newsBlocksG2   = filterBlocksByVisibleGroup(filterBlocksByType($newsSection['blocks'], 'news_card'), 2);

$pricingBlocks  = filterBlocksByType($subscriptionSection['blocks'], 'pricing_card');

$socialBlocks   = filterBlocksByType($socialSection['blocks'], 'social_icon');

$infoBlocks     = filterBlocksByType($infoSection['blocks'], 'info_card');

$footerSocial   = filterBlocksByType($footerSection['blocks'], 'footer_social');
$footerLinks    = filterBlocksByType($footerSection['blocks'], 'footer_link');

/*
|--------------------------------------------------------------------------
| FOOTER GROUPS
|--------------------------------------------------------------------------
*/
$footerCols = [
    2 => [],
    3 => [],
    4 => [],
];

foreach ($footerLinks as $block) {
    $col = (int)($block['data']['column'] ?? 0);
    if (isset($footerCols[$col])) {
        $footerCols[$col][] = $block;
    }
}

/*
|--------------------------------------------------------------------------
| SETTINGS SHORTCUTS
|--------------------------------------------------------------------------
*/
$siteName            = $settings['site_name'] ?? 'QuizDigo';
$siteLogo            = $settings['site_logo'] ?? 'assets/img/logo.svg';
$siteLogoAlt         = $settings['site_logo_alt'] ?? 'QuizDigo';
$metaTitle           = $settings['meta_title'] ?? 'QuizDigo - Platformă Educațională Interactivă';
$footerCopyright     = $settings['footer_copyright'] ?? '© 2026 QuizDigo. Toate drepturile rezervate.';
$newsletterPlaceholder = $settings['footer_newsletter_placeholder'] ?? 'Email';
$newsletterTitle     = $settings['footer_newsletter_success_title'] ?? 'Newsletter';
$newsletterText      = $settings['footer_newsletter_success_text'] ?? 'Te-ai abonat cu succes la noutățile QuizDigo!';

/*
|--------------------------------------------------------------------------
| HERO SETTINGS
|--------------------------------------------------------------------------
*/
$heroImage = $heroSection['settings']['image'] ?? 'assets/img/imghome.png';

/*
|--------------------------------------------------------------------------
| JOIN SETTINGS
|--------------------------------------------------------------------------
*/
$joinFormAction = $joinSection['settings']['form_action'] ?? 'https://quizdigo.live/game/play.php';
$joinInputName  = $joinSection['settings']['input_name'] ?? 'code';
$joinInputValue = $joinSection['settings']['input_value'] ?? '9588';
$joinButtonText = $joinSection['settings']['button_text'] ?? 'ALĂTURĂ-TE';

/*
|--------------------------------------------------------------------------
| TYPES SETTINGS
|--------------------------------------------------------------------------
*/
$typesShowMoreText = $typesSection['settings']['show_more_button'] ?? 'MAI DEPARTE';

/*
|--------------------------------------------------------------------------
| PARTNERS SETTINGS
|--------------------------------------------------------------------------
*/
$partnersMainImage = $partnersSection['settings']['main_image'] ?? 'assets/img/logobox.png';

/*
|--------------------------------------------------------------------------
| NEWS SETTINGS
|--------------------------------------------------------------------------
*/
$newsShowMoreText = $newsSection['settings']['show_more_button'] ?? 'MAI DEPARTE';

/*
|--------------------------------------------------------------------------
| SOCIAL SETTINGS
|--------------------------------------------------------------------------
*/
$socialBg = $socialSection['settings']['background'] ?? '#fb923c';

/*
|--------------------------------------------------------------------------
| TRANSLATIONS OBJECT FOR JS
|--------------------------------------------------------------------------
*/
$translationsJs = [
    'ro' => [
        'hero_title' => (string)($heroSection['title'] ?? ''),
        'hero_lead' => (string)($heroSection['subtitle'] ?? ''),
        'nav_about' => isset($navItems[0]) ? (string)$navItems[0]['title'] : 'Despre noi',
        'nav_how' => isset($navItems[1]) ? (string)$navItems[1]['title'] : 'Cum funcționează',
        'btn_join' => $heroBtnJoin['button_text'] ?? 'ALĂTURĂ-TE UNUI JOC',
        'stats_title' => (string)($statsSection['title'] ?? 'Statistica'),
        'stats_users' => isset($statBlocks[0]) ? (string)$statBlocks[0]['title'] : 'Utilizatori înregistrați',
    ],
    'en' => [
        'hero_title' => tSection($heroSection, 'title', $translationsEn, (string)($heroSection['title'] ?? '')),
        'hero_lead' => tSection($heroSection, 'subtitle', $translationsEn, (string)($heroSection['subtitle'] ?? '')),
        'nav_about' => isset($navItems[0]) ? tBlock($navItems[0], 'title', $translationsEn, (string)$navItems[0]['title']) : 'About us',
        'nav_how' => isset($navItems[1]) ? tBlock($navItems[1], 'title', $translationsEn, (string)$navItems[1]['title']) : 'How it works',
        'btn_join' => $heroBtnJoin ? tBlock($heroBtnJoin, 'button_text', $translationsEn, (string)($heroBtnJoin['button_text'] ?? 'JOIN A GAME')) : 'JOIN A GAME',
        'stats_title' => tSection($statsSection, 'title', $translationsEn, (string)($statsSection['title'] ?? 'Statistics')),
        'stats_users' => isset($statBlocks[0]) ? tBlock($statBlocks[0], 'title', $translationsEn, (string)$statBlocks[0]['title']) : 'Registered Users',
    ]
];
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($metaTitle) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/style.css">
</head>

<body>

<!-- NAV -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="#">
            <img src="<?= e($siteLogo) ?>" alt="<?= e($siteLogoAlt) ?>" style="height:36px">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav mx-auto gap-lg-2">
                <?php foreach ($navItems as $index => $item): ?>
                    <?php
                    $i18nKey = '';
                    if ($index === 0) $i18nKey = 'nav_about';
                    if ($index === 1) $i18nKey = 'nav_how';
                    ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold"
                           href="<?= e($item['button_url'] ?? '#') ?>"
                            <?= $i18nKey ? 'data-i18n="'.e($i18nKey).'"' : '' ?>>
                            <?= e($item['title'] ?? '') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="fw-bold small" style="cursor: pointer;">
                    <span id="lang-ro" class="lang-switch text-primary border-bottom border-2 border-primary pb-1">RO</span>
                    <span class="text-secondary-emphasis opacity-50 px-2">|</span>
                    <span id="lang-en" class="lang-switch text-secondary opacity-50">EN</span>
                </div>

                <?php foreach ($navButtons as $button): ?>
                    <a class="<?= e($button['data']['class'] ?? 'btn btn-danger btn-pill') ?>"
                       href="<?= e($button['button_url'] ?? '#') ?>">
                        <?= e($button['button_text'] ?: ($button['title'] ?? 'Login')) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<header class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h1 class="display-5 mb-3" data-i18n="hero_title">
                    <?= $heroSection['title'] ?? '' ?>
                </h1>
                <p class="lead mb-4" data-i18n="hero_lead">
                    <?= e($heroSection['subtitle'] ?? '') ?>
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if ($heroBtnJoin): ?>
                        <a class="<?= e($heroBtnJoin['data']['class'] ?? 'btn btn-primary qd btn-pill px-4 py-3 btn_prime') ?>"
                           href="<?= e($heroBtnJoin['button_url'] ?? '#') ?>"
                           data-i18n="btn_join">
                            <?= e($heroBtnJoin['button_text'] ?? 'ALĂTURĂ-TE UNUI JOC') ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($heroBtnReg): ?>
                        <a class="<?= e($heroBtnReg['data']['class'] ?? 'btn btn-secondary qd btn-pill px-4 py-3 btn_prime_sec') ?>"
                           href="<?= e($heroBtnReg['button_url'] ?? '#') ?>">
                            <?= e($heroBtnReg['button_text'] ?? 'ÎNREGISTREAZĂ-TE') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?= e($heroImage) ?>" class="img-fluid" alt="Students learning">
            </div>
        </div>
    </div>
</header>

<!-- HOW IT WORKS -->
<section id="cum" class="section">
    <div class="container">
        <h2 class="section-title text-center"><?= e($howSection['title'] ?? 'Cum funcționează QuizDigo') ?></h2>

        <div class="row g-4">
            <?php foreach ($howBlocks as $block): ?>
                <div class="col-md-6">
                    <div class="card card-soft p-4 h-100">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="bg-white border rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:72px;height:72px;border-color:#0A5084!important;">
                                        <div class="fw-black" style="font-weight:900;font-size:34px;"><?= e($block['badge'] ?? '') ?></div>
                                    </div>
                                    <h3 class="h5 mb-0 fw-black" style="font-weight:900;"><?= e($block['title'] ?? '') ?></h3>
                                </div>
                                <p class="mb-0 text-secondary fw-medium">
                                    <?= e($block['content'] ?? '') ?>
                                </p>
                            </div>
                            <?php if (!empty($block['image_url'])): ?>
                                <img src="<?= e($block['image_url']) ?>" class="d-none d-sm-block" alt="" style="object-fit:contain;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- JOIN -->
<section id="join" class="section pt-0">
    <div class="container">
        <div class="join-box p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <form action="<?= e($joinFormAction) ?>" method="get">
                    <div class="col-lg-12 text-center text-lg-start">
                        <h2 class="fw-black text-uppercase mb-3 text-center" style="font-weight:900;">
                            <?= e($joinSection['title'] ?? 'Ai primit un cod de la profesor?') ?>
                        </h2>

                        <div class="row justify-content-center justify-content-lg-start g-2" style="width: 61%;margin: 0 auto;">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <input class="form-control code-input"
                                       inputmode="numeric"
                                       name="<?= e($joinInputName) ?>"
                                       maxlength="6"
                                       value="<?= e($joinInputValue) ?>"
                                       aria-label="Cod joc">
                            </div>
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 d-grid pt-5">
                                <button class="btn btn-secondary qd btn-pill py-3 btn_element" type="submit" style="
    width: 33%;
    border-radius: 10px;
    margin: 0px auto;
    background: #FF5722;
">
                                    <?= e($joinButtonText) ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</section>

<!-- QUESTION TYPES -->
<section id="tipuri" class="section">
    <div class="container">
        <h2 class="section-title text-center"><?= e($typesSection['title'] ?? 'Tipuri de întrebări') ?></h2>

        <div class="row g-3">
            <?php foreach ($typeBlocksG1 as $block): ?>
                <div class="col-12 col-sm-6 col-lg-4 text-center">
                    <div class="type-card p-3" style="background:<?= e($block['badge'] ?: '#8b5cf6') ?>;">
                        <img src="<?= e($block['image_url'] ?? '') ?>" alt="">
                        <div class="label"><?= e($block['title'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3 mt-1 d-none sectiuneUrmatoare">
            <?php foreach ($typeBlocksG2 as $block): ?>
                <div class="col-12 col-sm-6 col-lg-4 text-center">
                    <div class="type-card p-3" style="background:<?= e($block['badge'] ?: '#8b5cf6') ?>;">
                        <img src="<?= e($block['image_url'] ?? '') ?>" alt="">
                        <div class="label"><?= e($block['title'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3 mt-1 d-none sectiuneUrmatoare">
            <?php foreach ($typeBlocksG3 as $block): ?>
                <div class="col-12 col-sm-6 col-lg-4 text-center">
                    <div class="type-card p-3" style="background:<?= e($block['badge'] ?: '#8b5cf6') ?>;">
                        <img src="<?= e($block['image_url'] ?? '') ?>" alt="">
                        <div class="label"><?= e($block['title'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5" id="containerButon">
            <button class="btn btn-primary qd btn-pill px-5 py-3 btn_nexts" type="button" style="background: #2E85C7; border-radius: 10px; width: 21%;">
                <?= e($typesShowMoreText) ?>
            </button>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="section" style="background:#f8fafc;" id="stats-section">
    <div class="container">
        <h2 class="section-title text-center" data-i18n="stats_title"><?= e($statsSection['title'] ?? 'Statistica') ?></h2>

        <div class="row g-3">
            <?php foreach ($statBlocks as $index => $block): ?>
                <?php
                $target = (int)($block['data']['target'] ?? 0);
                $bgImage = $block['data']['background_image'] ?? null;
                $isFull = $index === 2;
                ?>
                <div class="<?= $isFull ? 'col-12' : 'col-md-6' ?>">
                    <div class="stat-card p-4 d-flex align-items-center gap-3"
                        <?= $bgImage ? 'style="background-image:url(\''.e($bgImage).'\');"' : '' ?>>
                        <img src="<?= e($block['image_url'] ?? '') ?>" alt="" style="height:220px">
                        <div>
                            <div class="stat-number counter" data-target="<?= $target ?>">0</div>
                            <div class="stat-label" <?= $index === 0 ? 'data-i18n="stats_users"' : '' ?>>
                                <?= e($block['title'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PARTNERS -->
<section class="section">
    <div class="container">
        <h2 class="section-title text-center"><?= e($partnersSection['title'] ?? 'Parteneri educaționali') ?></h2>

        <div class="partners-box p-4 p-lg-5">
            <div class="row g-4 align-items-center ">
                <div class="col-lg-5">
                    <img src="<?= e($partnersMainImage) ?>" class="img-fluid rounded-4" alt="Partners">
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        <?php foreach ($partnerBlocks as $block): ?>
                            <div class="col-6 col-md-4">
                                <div class="partner-logo">
                                    <img src="<?= e($block['image_url'] ?? '') ?>" alt="">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWS -->
<section class="section" style="background:#f9fafb;" id="news-section">
    <div class="container">
        <h2 class="section-title text-center"><?= e($newsSection['title'] ?? 'Noutăți QuizDigo') ?></h2>

        <div class="row g-3">
            <?php foreach ($newsBlocksG1 as $block): ?>
                <div class="col-md-6">
                    <div class="card news-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="news-icon"><i class="<?= e($block['badge'] ?? 'fa-solid fa-rocket') ?>"></i></div>
                            <h3 class="h5 mb-0 fw-black" style="font-weight:900;"><?= e($block['title'] ?? '') ?></h3>
                        </div>
                        <p class="text-secondary mb-3"><?= e($block['content'] ?? '') ?></p>
                        <button class="btn btn-secondary qd btn-pill align-self-start"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#modalNews"
                                data-title="<?= e($block['extra']['modal_title'] ?? ($block['title'] ?? 'Detalii')) ?>"
                                data-content="<?= e($block['extra']['modal_content'] ?? '') ?>">
                            <?= e($block['button_text'] ?? 'Detalii') ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3 mt-1 d-none news-extra">
            <?php foreach ($newsBlocksG2 as $block): ?>
                <div class="col-md-6">
                    <div class="card news-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="news-icon"><i class="<?= e($block['badge'] ?? 'fa-solid fa-rocket') ?>"></i></div>
                            <h3 class="h5 mb-0" style="font-weight:900;"><?= e($block['title'] ?? '') ?></h3>
                        </div>
                        <p class="text-secondary mb-3"><?= e($block['content'] ?? '') ?></p>
                        <button class="btn btn-secondary qd btn-pill align-self-start"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#modalNews"
                                data-title="<?= e($block['extra']['modal_title'] ?? ($block['title'] ?? 'Detalii')) ?>"
                                data-content="<?= e($block['extra']['modal_content'] ?? '') ?>">
                            <?= e($block['button_text'] ?? 'Detalii') ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4" id="containerBtnNews">
            <button class="btn btn-primary qd btn-pill px-5 py-3 btn-more-news" type="button"><?= e($newsShowMoreText) ?></button>
        </div>
    </div>
</section>

<div class="modal fade" id="modalNews" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border:none;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalNewsTitle">Titlu Noutate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalNewsContent" class="text-secondary"></p>
                <div class="p-3 bg-light rounded-4">
                    <small><strong>Info:</strong> Aceasta este o actualizare oficială din sistemul QuizDigo.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary qd btn-pill w-100" data-bs-dismiss="modal">Am înțeles</button>
            </div>
        </div>
    </div>
</div>

<!-- SUBSCRIPTION -->
<section id="abonament" class="section">
    <div class="container">
        <h2 class="section-title text-center"><?= e($subscriptionSection['title'] ?? 'Abonament QuizDigo') ?></h2>

        <div class="row g-4">
            <?php foreach ($pricingBlocks as $block): ?>
                <?php
                $features = $block['data']['features'] ?? [];
                $bgImage = $block['data']['background_image'] ?? '';
                $price = $block['data']['price'] ?? '';
                $isPro = strtolower((string)$block['title']) === 'pro';
                ?>
                <div class="col-md-6" style="position: relative;">
                    <div class="p-4 p-lg-5 text-center text-white card-round h-100"
                         style="background-image: url('<?= e($bgImage) ?>'); background-repeat: no-repeat; background-size: cover; border-radius: 52px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3"
                             style="border-radius:999px;font-weight:900;font-size: 52px;">
                            <?= e($block['title'] ?? '') ?>
                        </div>
                        <img src="<?= e($block['image_url'] ?? '') ?>" style="display: block; margin: 0 auto;">

                        <ul class="list-unstyled text-start mx-auto" style="max-width:420px;">
                            <?php foreach ($features as $feature): ?>
                                <li class="mb-3">• <?= e((string)$feature) ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <button class="btn btn-light w-100 btn-pill py-3"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAbonament"
                                data-plan="<?= e($block['extra']['modal_plan'] ?? ($block['title'] ?? 'Plan')) ?>"
                                data-price="<?= e($block['extra']['modal_price'] ?? $price) ?>"
                                data-details="<?= e($block['extra']['modal_details'] ?? '') ?>"
                                style="color:#3b82f6;position: absolute;left: 27%;bottom: -20px;width: 48% !important;border-radius: 10px;background: #2E85C7;color: white;border: 1px solid #0A5084;">
                            <?= e($block['button_text'] ?? ($isPro ? 'CUMPĂRĂ' : 'ÎNREGISTREAZĂ-TE')) ?>
                        </button>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="modalAbonament" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 30px; border: none; overflow: hidden;">
            <div class="modal-header border-0 p-4 pb-0">
                <h3 class="modal-title fw-black" id="planTitle" style="font-weight: 900; color: #1e3a8a;">Plan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <span class="badge bg-primary fs-5 py-2 px-3" id="planPrice" style="border-radius: 12px;">0 lei</span>
                </div>
                <p id="planDescription" class="text-secondary fs-5 mb-4"></p>

                <div class="p-3 bg-light rounded-4">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Ce include:</h6>
                    <ul class="list-unstyled small mb-0" id="planBulletPoints">
                        <li><i class="fa-solid fa-check me-2"></i> Acces securizat pe bază de cod</li>
                        <li><i class="fa-solid fa-check me-2"></i> Panou de control în timp real</li>
                        <li><i class="fa-solid fa-check me-2"></i> Arhivă rezultate</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-secondary btn-pill w-100 py-3" data-bs-dismiss="modal" style="border-radius: 12px; background: #64748b;">ÎNCHIDE</button>
                <a href="<?= e($settings['register_url'] ?? 'https://quizdigo.com/public/reg') ?>" id="planActionBtn" class="btn btn-primary qd btn-pill w-100 py-3" style="border-radius: 12px; background: #2E85C7;">ALEGE ACEST PLAN</a>
            </div>
        </div>
    </div>
</div>

<!-- SOCIAL -->
<section class="py-5" style="background:<?= e($socialBg) ?>; color:#fff;">
    <div class="container text-center">
        <h2 class="text-uppercase fw-black mb-3" style="letter-spacing:4px;font-weight:900;"><?= e($socialSection['title'] ?? 'Social Media') ?></h2>
        <div class="d-flex justify-content-center gap-3">
            <?php foreach ($socialBlocks as $block): ?>
                <a class="d-inline-flex align-items-center justify-content-center rounded-circle"
                   style="width:56px;height:56px;background:rgba(255,255,255,.18);color:#fff;text-decoration:none;"
                   href="<?= e($block['button_url'] ?? '#') ?>">
                    <i class="<?= e($block['badge'] ?? 'fa-solid fa-globe') ?> fs-4"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- INFO BLOCKS -->
<section id="despre" class="section">
    <div class="container">
        <h2 class="section-title text-center"><?= e($infoSection['title'] ?? 'Bloc Informativ') ?></h2>

        <div class="row g-4">
            <?php foreach ($infoBlocks as $block): ?>
                <div class="col-md-6">
                    <div class="p-4 card-round border h-100">
                        <h3 class="h4" style="font-weight:900;color:#1e3a8a;"><?= e($block['title'] ?? '') ?></h3>
                        <p class="text-secondary mb-3">
                            <?= e($block['content'] ?? '') ?>
                        </p>
                        <button class="btn btn-primary qd btn-pill"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#modalInfo"
                                data-title="<?= e($block['extra']['modal_title'] ?? ($block['title'] ?? 'Titlu')) ?>"
                                data-text="<?= e($block['extra']['modal_text'] ?? '') ?>">
                            <?= e($block['button_text'] ?? 'Detalii') ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="modalInfo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 p-4">
                <h3 class="modal-title fw-black" id="infoTitle" style="font-weight: 900; color: #1e3a8a;">Titlu</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <p id="infoText" class="text-secondary fs-5" style="line-height: 1.6;"></p>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-primary qd btn-pill px-4 py-2" data-bs-dismiss="modal" style="background: #2E85C7; border-radius: 10px;">Am înțeles</button>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="pt-5 pb-4">
    <div class="container">
        <div class="row g-4 pb-4 border-bottom">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?= e($siteLogo) ?>" alt="<?= e($siteLogoAlt) ?>" style="height:36px">
                </div>
                <div class="d-flex gap-2 mb-3">
                    <input class="form-control" type="email" placeholder="<?= e($newsletterPlaceholder) ?>" id="newsletterEmail">
                    <button class="btn btn-primary qd"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#modalFooter"
                            data-title="<?= e($newsletterTitle) ?>"
                            data-text="<?= e($newsletterText) ?>">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
                <div class="d-flex gap-3 text-secondary">
                    <?php foreach ($footerSocial as $block): ?>
                        <a class="text-secondary" href="<?= e($block['button_url'] ?? '#') ?>">
                            <i class="<?= e($block['badge'] ?? 'fa-solid fa-globe') ?> fs-5"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="fw-bold mb-2"><?= e($footerCols[2][0]['data']['column_title'] ?? 'Platformă') ?></div>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($footerCols[2] as $block): ?>
                        <a class="footer-link"
                           href="<?= e($block['button_url'] ?? '#') ?>"
                           data-bs-toggle="modal"
                           data-bs-target="#modalFooter"
                           data-title="<?= e($block['extra']['modal_title'] ?? ($block['title'] ?? 'Titlu')) ?>"
                           data-text="<?= e($block['extra']['modal_text'] ?? '') ?>">
                            <?= e($block['title'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-6 col-lg-3" id="suport">
                <div class="fw-bold mb-2"><?= e($footerCols[3][0]['data']['column_title'] ?? 'Support') ?></div>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($footerCols[3] as $block): ?>
                        <a class="footer-link"
                           href="<?= e($block['button_url'] ?? '#') ?>"
                           data-bs-toggle="modal"
                           data-bs-target="#modalFooter"
                           data-title="<?= e($block['extra']['modal_title'] ?? ($block['title'] ?? 'Titlu')) ?>"
                           data-text="<?= e($block['extra']['modal_text'] ?? '') ?>">
                            <?= e($block['title'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-12 col-lg-3">
                <div class="fw-bold mb-2"><?= e($footerCols[4][0]['data']['column_title'] ?? 'Legal & GDPR') ?></div>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($footerCols[4] as $block): ?>
                        <a class="footer-link"
                           href="<?= e($block['button_url'] ?? '#') ?>"
                           data-bs-toggle="modal"
                           data-bs-target="#modalFooter"
                           data-title="<?= e($block['extra']['modal_title'] ?? ($block['title'] ?? 'Titlu')) ?>"
                           data-text="<?= e($block['extra']['modal_text'] ?? '') ?>">
                            <?= e($block['title'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="text-center pt-3 text-secondary small"><?= e($footerCopyright) ?></div>
    </div>
</footer>

<div class="modal fade" id="modalFooter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border: none;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-black" id="footerModalTitle" style="color: #1e3a8a;">Titlu</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p id="footerModalText" class="text-secondary fs-5"></p>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary qd btn-pill w-100 py-2" data-bs-dismiss="modal" style="background: #2E85C7; border-radius: 10px;">ÎNCHIDE</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalFooter = document.getElementById('modalFooter');

        modalFooter.addEventListener('show.bs.modal', function (event) {
            const triggerElement = event.relatedTarget;
            const title = triggerElement.getAttribute('data-title');
            const text = triggerElement.getAttribute('data-text');

            modalFooter.querySelector('#footerModalTitle').textContent = title;
            modalFooter.querySelector('#footerModalText').textContent = text;
        });
    });
</script>

<script>
    document.querySelector('.btn_nexts').addEventListener('click', function() {
        const hiddenSections = document.querySelectorAll('.sectiuneUrmatoare.d-none');

        if (hiddenSections.length > 0) {
            const nextToShow = hiddenSections[0];

            nextToShow.classList.remove('d-none');

            nextToShow.style.opacity = '0';
            nextToShow.style.transform = 'translateY(20px)';

            setTimeout(() => {
                nextToShow.style.transition = 'all 0.5s ease-out';
                nextToShow.style.opacity = '1';
                nextToShow.style.transform = 'translateY(0)';
            }, 10);

            nextToShow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        const remainingHidden = document.querySelectorAll('.sectiuneUrmatoare.d-none');
        if (remainingHidden.length === 0) {
            document.getElementById('containerButon').style.display = 'none';
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 200;

        const startCounter = (counter) => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText.replace(/\./g, '');

                const inc = target / speed;

                if (count < target) {
                    const newValue = Math.ceil(count + inc);
                    counter.innerText = newValue.toLocaleString('ro-RO');
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target.toLocaleString('ro-RO');
                }
            };
            updateCount();
        };

        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    startCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnMoreNews = document.querySelector('.btn-more-news');
        btnMoreNews.addEventListener('click', function() {
            const extraNews = document.querySelector('.news-extra');
            extraNews.classList.remove('d-none');
            this.parentElement.style.display = 'none';

            extraNews.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        const modalNews = document.getElementById('modalNews');
        modalNews.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const title = button.getAttribute('data-title');
            const content = button.getAttribute('data-content');

            const modalTitle = modalNews.querySelector('#modalNewsTitle');
            const modalBody = modalNews.querySelector('#modalNewsContent');

            modalTitle.textContent = title;
            modalBody.textContent = content;
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalAbonament = document.getElementById('modalAbonament');

        modalAbonament.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const planName = button.getAttribute('data-plan');
            const planPrice = button.getAttribute('data-price');
            const planDetails = button.getAttribute('data-details');

            const title = modalAbonament.querySelector('#planTitle');
            const priceTag = modalAbonament.querySelector('#planPrice');
            const desc = modalAbonament.querySelector('#planDescription');
            const actionBtn = modalAbonament.querySelector('#planActionBtn');

            title.textContent = "Abonament " + planName;
            priceTag.textContent = planPrice;
            desc.textContent = planDetails;

            if(planName === "Pro") {
                actionBtn.textContent = "CUMPĂRĂ ACUM";
                actionBtn.style.background = "#FF5722";
            } else {
                actionBtn.textContent = "ÎNCEPE GRATUIT";
                actionBtn.style.background = "#2E85C7";
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalInfo = document.getElementById('modalInfo');

        modalInfo.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const title = button.getAttribute('data-title');
            const text = button.getAttribute('data-text');

            const modalTitle = modalInfo.querySelector('#infoTitle');
            const modalText = modalInfo.querySelector('#infoText');

            modalTitle.textContent = title;
            modalText.textContent = text;
        });
    });
</script>

<script>
    const translations = <?= json_encode($translationsJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    document.querySelectorAll('.lang-switch').forEach(btn => {
        btn.addEventListener('click', function() {
            const lang = this.id.split('-')[1];

            document.querySelectorAll('.lang-switch').forEach(el => {
                el.classList.remove('text-primary', 'border-bottom', 'border-2', 'border-primary', 'pb-1');
                el.classList.add('text-secondary', 'opacity-50');
            });

            this.classList.add('text-primary', 'border-bottom', 'border-2', 'border-primary', 'pb-1');
            this.classList.remove('text-secondary', 'opacity-50');

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (translations[lang] && translations[lang][key]) {
                    el.innerHTML = translations[lang][key];
                }
            });

            localStorage.setItem('preferredLang', lang);
        });
    });

    window.addEventListener('DOMContentLoaded', () => {
        const savedLang = localStorage.getItem('preferredLang');
        if(savedLang && document.getElementById(`lang-${savedLang}`)) {
            document.getElementById(`lang-${savedLang}`).click();
        }
    });
</script>
</body>
</html>