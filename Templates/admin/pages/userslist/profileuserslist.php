<?php
declare(strict_types=1);

use Evasystem\Controllers\Users\UsersService;
use Evasystem\Controllers\Userslist\UserslistService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('getCurrentUrl')) {
    function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
}

if (!isset($_SESSION['user_id'])) {
    die('Utilizatorul nu este autentificat.');
}

$userId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
if ($userId === '') {
    die('ID utilizator invalid.');
}

/**
 * users_connect
 */
$usersService = new UsersService();
$userRaw = $usersService->getIdUserss($userId);

if (!$userRaw) {
    die('Utilizatorul nu a fost găsit.');
}

$user = $userRaw;
if (is_array($userRaw) && isset($userRaw[0]) && is_array($userRaw[0])) {
    $user = $userRaw[0];
}

/**
 * users_info
 * căutăm după connect_id sau randomn_id
 */
$usersInfoService = new UserslistService();

$userInfo = null;

if (!empty($user['id'])) {
    $userInfo = $usersInfoService->findByConnectId((string)$user['id'], 'users_info');
}

if (!$userInfo && !empty($user['randomn_id'])) {
    $userInfo = $usersInfoService->findByRandomnId((string)$user['randomn_id'], 'users_info');
}

if (!is_array($userInfo)) {
    $userInfo = [];
}

/**
 * Documente / quizuri dacă există în service
 */
$userDocuments = method_exists($usersService, 'getUserDocuments')
    ? (array)$usersService->getUserDocuments($userId)
    : [];

$userQuizzes = method_exists($usersService, 'getUserLastQuizzes')
    ? (array)$usersService->getUserLastQuizzes($userId)
    : [];

/**
 * users_connect
 */
$fullName   = trim((string)($user['fullname'] ?? 'Fără nume'));
$role       = trim((string)($user['role'] ?? '-'));
$status     = trim((string)($user['status'] ?? 'inactive'));
$randomnId  = trim((string)($user['randomn_id'] ?? ''));
$profileImage = trim((string)($user['photo'] ?? ''));

/**
 * users_info
 */
$fname       = trim((string)($userInfo['fname'] ?? ''));
$lastname    = trim((string)($userInfo['lastname'] ?? ''));
$email       = trim((string)($userInfo['email'] ?? '-'));
$phone       = trim((string)($userInfo['tel'] ?? '-'));
$city        = trim((string)($userInfo['city'] ?? '-'));
$country     = trim((string)($userInfo['countor'] ?? '-'));
$address     = trim((string)($userInfo['adress'] ?? '-'));
$gender      = trim((string)($userInfo['gender'] ?? '-'));
$birthDate   = trim((string)($userInfo['dob'] ?? '-'));
$designation = trim((string)($userInfo['des'] ?? '-'));
$region      = trim((string)($userInfo['region'] ?? '-'));
$nationality = trim((string)($userInfo['nationality'] ?? '-'));
$accountType = trim((string)($userInfo['account_type'] ?? 'Etapa 1'));
$currentStep = (int)($userInfo['account_step'] ?? 1);

if ($currentStep <= 0) {
    $currentStep = 1;
}

/**
 * Fallback pentru nume dacă fullname lipsește
 */
if ($fullName === 'Fără nume') {
    $fromInfoName = trim($fname . ' ' . $lastname);
    if ($fromInfoName !== '') {
        $fullName = $fromInfoName;
    }
}

if ($profileImage === '') {
    $profileImage = getCurrentUrl() . '/logo_new.png';
}

$statusLower = strtolower($status);
$isApproved = in_array($statusLower, ['active', 'approved', 'activ', '1'], true);

if ($birthDate !== '-' && $birthDate !== '') {
    $timestamp = strtotime($birthDate);
    if ($timestamp !== false) {
        $birthDate = date('d.m.Y', $timestamp);
    }
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #111827;
        --muted: #6b7280;
        --line: #e8edf3;
        --primary: #2f80ed;
        --primary-soft: rgba(47, 128, 237, 0.08);
        --success: #198100;
        --success-bg: rgba(58, 195, 23, 0.08);
        --danger: #f93030;
        --danger-bg: rgba(249, 48, 48, 0.08);
        --shadow: 0 12px 35px rgba(21, 34, 50, 0.08);
        --radius-xl: 24px;
        --radius-lg: 18px;
        --radius-md: 14px;
        --radius-sm: 10px;
    }

    * { box-sizing: border-box; }

    html, body {
        margin: 0;
        padding: 0;
        font-family: "Poppins", sans-serif;
        background: var(--bg);
        color: var(--text);
    }

    .page {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }

    .details-card {
        background: var(--card);
        border-radius: var(--radius-xl);
        border: 1px solid #e7e7e7;
        box-shadow: var(--shadow);
        padding: 32px;
    }

    .details-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .details-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
    }

    .details-subtitle {
        margin: 6px 0 0;
        font-size: 14px;
        color: var(--muted);
    }

    .top-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .top-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
    }

    .top-badge.approved {
        color: var(--success);
        background: var(--success-bg);
    }

    .top-badge.inactive {
        color: var(--danger);
        background: var(--danger-bg);
    }

    .details-layout {
        display: grid;
        grid-template-columns: 1.1fr 360px;
        gap: 28px;
        align-items: start;
    }

    .left-column,
    .right-column {
        display: grid;
        gap: 24px;
    }

    .section-card {
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 22px;
        background: #fff;
    }

    .section-title {
        margin: 0 0 18px;
        font-size: 20px;
        font-weight: 600;
        color: var(--primary);
    }

    .profile-top {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 24px;
        align-items: start;
    }

    .profile-photo-box {
        position: relative;
    }

    .profile-photo {
        width: 100%;
        aspect-ratio: 3 / 4;
        border-radius: 18px;
        object-fit: cover;
        display: block;
        background: #eef2f7;
    }

    .change-photo-btn {
        position: absolute;
        left: 50%;
        bottom: 14px;
        transform: translateX(-50%);
        border: none;
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(8px);
        padding: 10px 18px;
        border-radius: 999px;
        font-family: inherit;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        text-decoration: none;
        color: #111827;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 24px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-label {
        font-size: 14px;
        font-weight: 600;
        color: rgba(17, 24, 39, 0.55);
    }

    .info-value {
        font-size: 17px;
        font-weight: 500;
        color: #111827;
        word-break: break-word;
    }

    .two-col-sections {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .stack-list {
        display: grid;
        gap: 18px;
    }

    .document-card {
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        background: #fff;
    }

    .document-preview {
        width: 100%;
        height: 240px;
        object-fit: cover;
        display: block;
        background: #eef2f7;
    }

    .document-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        background: rgba(255,255,255,0.92);
    }

    .document-name {
        font-size: 15px;
        font-weight: 500;
        color: #111827;
    }

    .file-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        background: #f3f4f6;
        font-size: 13px;
        color: #111827;
    }

    .quiz-item {
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 14px;
        display: grid;
        gap: 6px;
    }

    .quiz-title {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
    }

    .quiz-meta {
        font-size: 13px;
        color: var(--muted);
    }

    .empty-box {
        border: 1px dashed #d7dee8;
        border-radius: 14px;
        padding: 18px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    .mini-card {
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 18px;
        background: #fff;
    }

    .timeline {
        position: relative;
        display: grid;
        gap: 18px;
        margin-top: 8px;
    }

    .timeline::before {
        content: "";
        position: absolute;
        left: 5px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #dbe2ea;
    }

    .timeline-step {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding-left: 0;
    }

    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #c4c4c4;
        z-index: 1;
        flex-shrink: 0;
    }

    .timeline-step.active .timeline-dot {
        background: #333333;
    }

    .timeline-text {
        font-size: 16px;
        font-weight: 500;
        color: #111827;
    }

    .timeline-step.pending .timeline-text {
        opacity: 0.5;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 26px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        background: #eef2ff;
        color: #3730a3;
        white-space: nowrap;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .btn {
        border: none;
        border-radius: 999px;
        padding: 16px 28px;
        font-family: inherit;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-approve {
        background: var(--success-bg);
        color: var(--success);
    }

    .btn-approve:hover {
        background: rgba(58, 195, 23, 0.14);
    }

    .btn-decline {
        background: var(--danger-bg);
        color: var(--danger);
    }

    .btn-decline:hover {
        background: rgba(249, 48, 48, 0.14);
    }

    .btn-edit {
        background: #eef2ff;
        color: #4338ca;
    }

    .btn-edit:hover {
        background: #dfe5ff;
    }

    @media (max-width: 1100px) {
        .details-layout {
            grid-template-columns: 1fr;
        }

        .right-column {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
        }
    }

    @media (max-width: 900px) {
        .profile-top,
        .two-col-sections,
        .right-column {
            grid-template-columns: 1fr;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        body {
            padding: 16px;
        }

        .details-card {
            padding: 20px;
        }

        .details-title {
            font-size: 23px;
        }

        .actions {
            justify-content: stretch;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<div class="page">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>
    <section class="details-card" style="margin-top: 20px;">

        <div class="details-header">
            <div>
                <h1 class="details-title">Personal Details</h1>
                <p class="details-subtitle">Vizualizare completă profil, documente și acțiuni administrative.</p>
            </div>

            <div class="top-badges">
                <span class="top-badge <?= $isApproved ? 'approved' : 'inactive' ?>">
                    <?= htmlspecialchars($status) ?>
                </span>
                <span class="top-badge approved">
                    <?= htmlspecialchars($role !== '' ? $role : $accountType) ?>
                </span>
            </div>
        </div>

        <div class="details-layout">

            <div class="left-column">

                <div class="section-card">
                    <div class="profile-top">

                        <div class="profile-photo-box">
                            <img class="profile-photo" src="<?= htmlspecialchars($profileImage) ?>" alt="Imagine profil">
                            <a class="change-photo-btn" href="/public/editusers?id=<?= urlencode((string)$userId) ?>">
                                Editează profilul
                            </a>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Numele complet</div>
                                <div class="info-value"><?= htmlspecialchars($fullName) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Rol</div>
                                <div class="info-value"><?= htmlspecialchars($role !== '' ? $role : '-') ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Genul</div>
                                <div class="info-value"><?= htmlspecialchars($gender) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Data de naștere</div>
                                <div class="info-value"><?= htmlspecialchars($birthDate) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Naționalitatea</div>
                                <div class="info-value"><?= htmlspecialchars($nationality) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Specializare</div>
                                <div class="info-value"><?= htmlspecialchars($designation) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Randomn ID</div>
                                <div class="info-value"><?= htmlspecialchars($randomnId !== '' ? $randomnId : '-') ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">ID utilizator</div>
                                <div class="info-value"><?= htmlspecialchars((string)($user['id'] ?? '-')) ?></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="two-col-sections">

                    <div class="section-card">
                        <h2 class="section-title">Adresa și localizarea</h2>
                        <div class="stack-list">
                            <div class="info-item">
                                <div class="info-label">Adresa deplină</div>
                                <div class="info-value"><?= htmlspecialchars($address) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Orașul</div>
                                <div class="info-value"><?= htmlspecialchars($city) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Țara</div>
                                <div class="info-value"><?= htmlspecialchars($country) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Regiunea</div>
                                <div class="info-value"><?= htmlspecialchars($region) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <h2 class="section-title">Date de contact</h2>
                        <div class="stack-list">
                            <div class="info-item">
                                <div class="info-label">Nr. de telefon</div>
                                <div class="info-value"><?= htmlspecialchars($phone) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= htmlspecialchars($email) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status cont</div>
                                <div class="info-value"><?= htmlspecialchars($status) ?></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="right-column">

                <div class="section-card">
                    <h2 class="section-title">Ultimele quizuri jucate</h2>

                    <?php if (!empty($userQuizzes)): ?>
                        <div class="stack-list">
                            <?php foreach ($userQuizzes as $quiz): ?>
                                <div class="quiz-item">
                                    <div class="quiz-title">
                                        <?= htmlspecialchars((string)($quiz['title'] ?? 'Quiz fără titlu')) ?>
                                    </div>
                                    <div class="quiz-meta">
                                        Scor: <?= htmlspecialchars((string)($quiz['score'] ?? '-')) ?>
                                        |
                                        Data: <?= htmlspecialchars((string)($quiz['played_at'] ?? '-')) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-box">Nu există quizuri jucate momentan.</div>
                    <?php endif; ?>
                </div>

                <div class="section-card">
                    <h2 class="section-title">Documente</h2>

                    <?php if (!empty($userDocuments)): ?>
                        <div class="stack-list">
                            <?php foreach ($userDocuments as $doc): ?>
                                <?php
                                $docName  = (string)($doc['name'] ?? 'Document');
                                $docFile  = (string)($doc['file'] ?? '');
                                $docType  = (string)($doc['type'] ?? 'File');
                                $docImage = (string)($doc['preview'] ?? $docFile);

                                if ($docImage === '') {
                                    $docImage = getCurrentUrl() . '/logo_new.png';
                                }
                                ?>
                                <div class="document-card">
                                    <img class="document-preview" src="<?= htmlspecialchars($docImage) ?>" alt="<?= htmlspecialchars($docName) ?>">
                                    <div class="document-footer">
                                        <div class="document-name"><?= htmlspecialchars($docName) ?></div>
                                        <div class="file-badge"><?= htmlspecialchars($docType) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-box">Nu există documente încărcate.</div>
                    <?php endif; ?>
                </div>

                <div class="mini-card">
                    <h2 class="section-title">Etapa contului</h2>

                    <div class="timeline">
                        <div class="timeline-step <?= $currentStep >= 1 ? 'active' : 'pending' ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-text">Etapa 1</div>
                            <?php if ($currentStep === 1): ?><div class="status-chip">Current</div><?php endif; ?>
                        </div>

                        <div class="timeline-step <?= $currentStep >= 2 ? 'active' : 'pending' ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-text">Etapa 2</div>
                            <?php if ($currentStep === 2): ?><div class="status-chip">Current</div><?php endif; ?>
                        </div>

                        <div class="timeline-step <?= $currentStep >= 3 ? 'active' : 'pending' ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-text">Etapa 3</div>
                            <?php if ($currentStep === 3): ?><div class="status-chip">Current</div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a class="btn btn-decline" href="?page=user_decline&id=<?= urlencode((string)$userId) ?>">
                        Decline
                    </a>

                    <a class="btn btn-approve" href="?page=user_approve&id=<?= urlencode((string)$userId) ?>">
                        Approve
                    </a>

                    <a class="btn btn-edit" href="/public/editusers?id=<?= urlencode((string)$userId) ?>">
                        Editare
                    </a>
                </div>

            </div>

        </div>

    </section>
</div>