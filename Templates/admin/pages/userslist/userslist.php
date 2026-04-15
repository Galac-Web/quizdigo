<?php
declare(strict_types=1);

use Evasystem\Controllers\Users\UsersService;
use Evasystem\Controllers\Userslist\UserslistService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die('Utilizatorul nu este autentificat.');
}

$currentSessionUserId = (int)$_SESSION['user_id'];

$usersService = new UsersService();
$usersInfoService = new UserslistService();

/**
 * users_connect
 */
$currentUserData = $usersService->getIdUserss($currentSessionUserId);
$allUsersRaw     = $usersService->getAllUserss();

$currentUser = $currentUserData;
if (is_array($currentUserData) && isset($currentUserData[0]) && is_array($currentUserData[0])) {
    $currentUser = $currentUserData[0];
}

$currentUserRandomId = $currentUser['randomn_id'] ?? null;

/**
 * Unificare users_connect + users_info
 */
$mergedUsers = [];

foreach ($allUsersRaw as $user) {
    if (
        $currentUserRandomId !== null &&
        isset($user['randomn_id']) &&
        (string)$user['randomn_id'] === (string)$currentUserRandomId
    ) {
        continue;
    }

    $connectId = (string)($user['id'] ?? '');
    $randomnId = (string)($user['randomn_id'] ?? '');

    $userInfo = null;

    if ($connectId !== '') {
        $userInfo = $usersInfoService->findByConnectId($connectId, 'users_info');
    }

    if (!$userInfo && $randomnId !== '') {
        $userInfo = $usersInfoService->findByRandomnId($randomnId, 'users_info');
    }

    if (!is_array($userInfo)) {
        $userInfo = [];
    }

    $fname     = trim((string)($userInfo['fname'] ?? ''));
    $lastname  = trim((string)($userInfo['lastname'] ?? ''));
    $fullName  = trim((string)($user['fullname'] ?? ''));
    $fromInfo  = trim($fname . ' ' . $lastname);

    if ($fullName === '' && $fromInfo !== '') {
        $fullName = $fromInfo;
    }
    if ($fullName === '') {
        $fullName = '-';
    }

    $mergedUsers[] = [
        'id'           => $user['id'] ?? '',
        'randomn_id'   => $randomnId,
        'connect_id'   => $user['connect_id'] ?? '',
        'fullname'     => $fullName,
        'first_name'   => $fname,
        'last_name'    => $lastname,
        'nikname'      => $user['nikname'] ?? '-',
        'role'         => $user['role'] ?? '-',
        'status'       => $user['status'] ?? 'inactive',
        'email'        => $userInfo['email'] ?? ($user['login'] ?? '-'),
        'phone'        => $userInfo['tel'] ?? ($user['contact'] ?? '-'),
        'city'         => $userInfo['city'] ?? '-',
        'country'      => $userInfo['countor'] ?? '-',
        'region'       => $userInfo['countor'] ?? '-',
        'designation'  => $userInfo['des'] ?? '-',
        'photo'        => $user['photo'] ?? '',
        'raw_connect'  => $user,
        'raw_info'     => $userInfo,
    ];
}

/**
 * GET params
 */
$search  = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$sort    = isset($_GET['sort']) ? trim((string)$_GET['sort']) : 'latest';
$pageNum = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$perPage = 8;

/**
 * Search
 */
$filteredUsers = array_filter($mergedUsers, function ($user) use ($search) {
    if ($search === '') {
        return true;
    }

    $needle = mb_strtolower($search);

    $fields = [
        $user['fullname'] ?? '',
        $user['first_name'] ?? '',
        $user['last_name'] ?? '',
        $user['nikname'] ?? '',
        $user['role'] ?? '',
        $user['email'] ?? '',
        $user['phone'] ?? '',
        $user['city'] ?? '',
        $user['country'] ?? '',
        $user['designation'] ?? '',
        $user['status'] ?? '',
        $user['randomn_id'] ?? '',
    ];

    foreach ($fields as $field) {
        if (mb_stripos((string)$field, $needle) !== false) {
            return true;
        }
    }

    return false;
});

/**
 * Sort
 */
usort($filteredUsers, function ($a, $b) use ($sort) {
    switch ($sort) {
        case 'name_asc':
            return strcasecmp((string)($a['fullname'] ?? ''), (string)($b['fullname'] ?? ''));
        case 'name_desc':
            return strcasecmp((string)($b['fullname'] ?? ''), (string)($a['fullname'] ?? ''));
        case 'active':
            return strcasecmp((string)($b['status'] ?? ''), (string)($a['status'] ?? ''));
        case 'inactive':
            return strcasecmp((string)($a['status'] ?? ''), (string)($b['status'] ?? ''));
        case 'latest':
        default:
            return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
    }
});

/**
 * Stats
 */
$totalUsers = count($filteredUsers);

$activeUsers = count(array_filter($filteredUsers, function ($user) {
    $status = strtolower((string)($user['status'] ?? ''));
    return in_array($status, ['active', 'activ', '1', 'online'], true);
}));

$blockedUsers = count(array_filter($filteredUsers, function ($user) {
    $status = strtolower((string)($user['status'] ?? ''));
    return in_array($status, ['blocked', 'block', 'inactive', '0', 'disabled'], true);
}));

/**
 * Pagination
 */
$totalPages = max(1, (int)ceil($totalUsers / $perPage));
if ($pageNum > $totalPages) {
    $pageNum = $totalPages;
}

$offset = ($pageNum - 1) * $perPage;
$usersForPage = array_slice($filteredUsers, $offset, $perPage);

function buildUsersUrl(array $params = []): string
{
    $query = array_merge($_GET, $params);
    return '?' . http_build_query($query);
}
?>

<style>
    :root {
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #7c8595;
        --muted-light: #acacac;
        --line: #eef1f5;
        --line-2: #edf0f5;
        --primary: #5932ea;
        --success: #008767;
        --success-strong: #00ac4f;
        --success-bg: rgba(22, 192, 152, 0.15);
        --danger: #df0404;
        --danger-strong: #d0004a;
        --danger-bg: #ffc5c5;
        --warning: #f59e0b;
        --warning-bg: rgba(245, 158, 11, 0.12);
        --input-bg: #f9fbff;
        --icon-bg: linear-gradient(201deg, #d3ffe7 0%, #effff6 100%);
        --shadow: 0 12px 35px rgba(21, 34, 50, 0.08);
        --radius: 20px;
        --radius-lg: 24px;
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

    .stats-wrapper,
    .users-card {
        background: var(--card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
    }

    .stats-wrapper {
        padding: 30px 34px;
        margin-bottom: 28px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 18px;
        position: relative;
    }

    .stat-card:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 8px;
        right: -12px;
        width: 1px;
        height: calc(100% - 16px);
        background: var(--line-2);
    }

    .stat-icon {
        width: 84px;
        height: 84px;
        min-width: 84px;
        border-radius: 50%;
        background: var(--icon-bg);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon svg {
        width: 36px;
        height: 36px;
        stroke: #22c55e;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .stat-label {
        font-size: 14px;
        font-weight: 400;
        color: var(--muted-light);
    }

    .stat-value {
        font-size: 32px;
        line-height: 1;
        font-weight: 700;
        color: #333333;
    }

    .stat-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        margin-top: 2px;
        color: #292d32;
        flex-wrap: wrap;
    }

    .users-card { padding: 28px; }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .users-title h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #111827;
    }

    .users-title p {
        margin: 6px 0 0;
        color: #16c098;
        font-size: 14px;
        font-weight: 500;
    }

    .users-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        width: 100%;
    }

    .search-form {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        width: 100%;
    }

    .search-box,
    .sort-box {
        background: var(--input-bg);
        border: 1px solid #edf2f7;
        border-radius: 12px;
        height: 44px;
        display: flex;
        align-items: center;
    }

    .search-box {
        width: 280px;
        padding: 0 14px;
        gap: 10px;
    }

    .search-box svg {
        width: 18px;
        height: 18px;
        fill: #9aa3b2;
        flex-shrink: 0;
    }

    .search-box input {
        border: none;
        outline: none;
        background: transparent;
        width: 100%;
        font-size: 14px;
        color: var(--text);
        font-family: inherit;
    }

    .sort-box {
        padding: 0 14px;
        gap: 8px;
        color: #7e7e7e;
        font-size: 14px;
    }

    .sort-box select {
        border: none;
        outline: none;
        background: transparent;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        color: #3d3b41;
        cursor: pointer;
    }

    .filter-btn {
        height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        border: none;
        background: var(--primary);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    .table-wrap {
        overflow-x: auto;
        border-top: 1px solid var(--line);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1200px;
    }

    thead th {
        text-align: left;
        padding: 18px 12px;
        font-size: 14px;
        font-weight: 500;
        color: #b5b7c0;
        white-space: nowrap;
    }

    tbody td {
        padding: 18px 12px;
        border-top: 1px solid var(--line);
        font-size: 14px;
        color: #292d32;
        vertical-align: middle;
    }

    tbody tr:hover {
        background: #fafcff;
    }

    .user-link {
        color: #1f2937;
        font-weight: 600;
        text-decoration: none;
    }

    .user-link:hover {
        color: var(--primary);
    }

    .user-meta {
        display: grid;
        gap: 4px;
    }

    .user-sub {
        color: #8b93a7;
        font-size: 12px;
    }

    .status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 96px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .status.active {
        color: var(--success);
        background: var(--success-bg);
        border-color: #00b086;
    }

    .status.inactive {
        color: var(--danger);
        background: var(--danger-bg);
        border-color: var(--danger);
    }

    .status.blocked {
        color: #b45309;
        background: var(--warning-bg);
        border-color: #f59e0b;
    }

    .empty-state {
        text-align: center;
        padding: 30px 16px;
        color: var(--muted);
    }

    .footer {
        margin-top: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .footer-info {
        font-size: 14px;
        color: #b5b7c0;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid #eeeeee;
        background: #f5f5f5;
        color: #404b52;
        font-family: inherit;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: 0.2s ease;
    }

    .page-btn:hover {
        background: #ececff;
    }

    .page-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 9999;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 760px;
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(15,23,42,.18);
        overflow: hidden;
    }

    .modal-header {
        padding: 22px 24px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .modal-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #111827;
    }

    .modal-close {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 12px;
        background: #f3f4f6;
        cursor: pointer;
        font-size: 20px;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 18px;
    }

    .modal-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .modal-group.full {
        grid-column: 1 / -1;
    }

    .modal-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .modal-input,
    .modal-select {
        width: 100%;
        height: 48px;
        border: 1px solid #d9dee7;
        border-radius: 14px;
        padding: 0 14px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
        outline: none;
    }

    .modal-input:focus,
    .modal-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(89, 50, 234, 0.12);
    }

    .modal-footer {
        padding: 20px 24px 24px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .modal-btn {
        border: none;
        border-radius: 14px;
        height: 48px;
        padding: 0 18px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .modal-btn.primary {
        background: var(--primary);
        color: #fff;
    }

    .modal-btn.secondary {
        background: #eef2ff;
        color: var(--primary);
    }

    .modal-message {
        margin-top: 16px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 14px;
        display: none;
    }

    .modal-message.success {
        display: block;
        color: var(--success);
        background: var(--success-bg);
    }

    .modal-message.error {
        display: block;
        color: var(--danger);
        background: var(--danger-bg);
    }

    .actions-dropdown-wrap {
        position: relative;
        display: inline-block;
    }

    .actions-toggle-btn {
        border: none;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        background: #f1efff;
        color: var(--primary);
        transition: .2s ease;
        min-width: 110px;
    }

    .actions-toggle-btn:hover {
        background: var(--primary);
        color: #fff;
    }

    .actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 190px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(15,23,42,.14);
        padding: 8px;
        display: none;
        z-index: 99;
    }

    .actions-menu.show {
        display: block;
    }

    .actions-menu-item {
        width: 100%;
        border: none;
        background: transparent;
        text-align: left;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        color: #1f2937;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        transition: .2s ease;
    }

    .actions-menu-item:hover {
        background: #f8fafc;
    }

    .actions-menu-item.open-item {
        color: var(--primary);
    }

    .actions-menu-item.edit-item {
        color: #2563eb;
    }

    .actions-menu-item.block-item {
        color: #c2410c;
    }

    .actions-menu-item.activate-item {
        color: #047857;
    }

    .actions-menu-item.delete-item {
        color: #dc2626;
    }

    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card:not(:last-child)::after {
            display: none;
        }

        .stat-card {
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line);
        }

        .stat-card:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }
    }

    @media (max-width: 768px) {
        body { padding: 16px; }
        .stats-wrapper, .users-card { padding: 20px; }
        .users-title h1 { font-size: 22px; }
        .users-actions { width: 100%; }
        .search-box, .sort-box { width: 100%; }
        .sort-box { justify-content: space-between; }
        .footer { flex-direction: column; align-items: flex-start; }
        .modal-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="page">
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Templates/admin/static_elements/navbox.php'; ?>

    <section class="stats-wrapper" style="margin-top: 20px;">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total utilizatori</div>
                    <div class="stat-value" id="statTotalUsers"><?= (int)$totalUsers ?></div>
                    <div class="stat-meta">rezultat după filtrare și merge</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Membri activi</div>
                    <div class="stat-value" id="statActiveUsers"><?= (int)$activeUsers ?></div>
                    <div class="stat-meta">status activ / online</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="14" rx="2"></rect>
                        <path d="M8 20h8"></path>
                        <path d="M12 18v2"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Blocați / inactivi</div>
                    <div class="stat-value" id="statBlockedUsers"><?= (int)$blockedUsers ?></div>
                    <div class="stat-meta">status blocked / inactive</div>
                </div>
            </div>
        </div>
    </section>

    <section class="users-card">
        <div class="users-header">
            <div class="users-title">
                <h1>Toți utilizatorii</h1>
                <p>Listă unificată din users_connect + users_info</p>
            </div>

            <div class="users-actions">
                <form method="GET" class="search-form">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24">
                            <path d="M10 4a6 6 0 104.472 10.001l4.763 4.764 1.414-1.414-4.764-4.763A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"></path>
                        </svg>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Caută utilizator...">
                    </div>

                    <div class="sort-box">
                        <span>Sortează după:</span>
                        <select name="sort">
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Ultimii adăugați</option>
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nume A-Z</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Nume Z-A</option>
                            <option value="active" <?= $sort === 'active' ? 'selected' : '' ?>>Activi</option>
                            <option value="inactive" <?= $sort === 'inactive' ? 'selected' : '' ?>>Inactivi</option>
                        </select>
                    </div>

                    <button type="submit" class="filter-btn">Aplică</button>
                </form>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nume și utilizator</th>
                    <th>Email</th>
                    <th>Oraș / Țară</th>
                    <th>Specializare</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                </tr>
                </thead>
                <tbody id="usersTableBody">
                <?php if (!empty($usersForPage)): ?>
                    <?php foreach ($usersForPage as $user): ?>
                        <?php
                        $status = trim((string)($user['status'] ?? 'inactive'));
                        $statusLower = strtolower($status);

                        $isActive = in_array($statusLower, ['active', 'activ', '1', 'online'], true);
                        $isBlocked = in_array($statusLower, ['blocked', 'block', 'inactive', '0', 'disabled'], true);

                        $profileUrl = '/public/profileuserslist?id=' . urlencode((string)($user['randomn_id'] ?? ''));
                        ?>
                        <tr
                                data-id="<?= htmlspecialchars((string)($user['id'] ?? '')) ?>"
                                data-randomn-id="<?= htmlspecialchars((string)$user['randomn_id']) ?>"
                                data-first-name="<?= htmlspecialchars((string)$user['first_name']) ?>"
                                data-last-name="<?= htmlspecialchars((string)$user['last_name']) ?>"
                                data-role="<?= htmlspecialchars((string)$user['role']) ?>"
                                data-email="<?= htmlspecialchars((string)$user['email']) ?>"
                                data-phone="<?= htmlspecialchars((string)$user['phone']) ?>"
                                data-city="<?= htmlspecialchars((string)$user['city']) ?>"
                                data-country="<?= htmlspecialchars((string)$user['country']) ?>"
                                data-designation="<?= htmlspecialchars((string)$user['designation']) ?>"
                                data-status="<?= htmlspecialchars((string)$status) ?>"
                        >
                            <td>
                                <div class="user-meta">
                                    <a class="user-link js-col-name-link" href="<?= htmlspecialchars($profileUrl) ?>">
                                        <span class="js-col-fullname"><?= htmlspecialchars((string)($user['fullname'] ?? '-')) ?></span>
                                    </a>
                                    <div class="user-sub">
                                        @<span class="js-col-nickname"><?= htmlspecialchars((string)($user['nikname'] ?? '-')) ?></span>
                                        · ID <span class="js-col-randomn-id"><?= htmlspecialchars((string)($user['randomn_id'] ?? '-')) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td><span class="js-col-email"><?= htmlspecialchars((string)($user['email'] ?? '-')) ?></span></td>
                            <td>
                                <span class="js-col-city"><?= htmlspecialchars((string)($user['city'] ?? '-')) ?></span>
                                /
                                <span class="js-col-country"><?= htmlspecialchars((string)($user['country'] ?? '-')) ?></span>
                            </td>
                            <td><span class="js-col-designation"><?= htmlspecialchars((string)($user['designation'] ?? '-')) ?></span></td>
                            <td>
                                <span class="status js-status-badge <?= $isActive ? 'active' : ($isBlocked ? 'blocked' : 'inactive') ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions-dropdown-wrap">
                                    <button type="button" class="actions-toggle-btn js-actions-toggle">
                                        Action
                                    </button>

                                    <div class="actions-menu js-actions-menu">
                                        <a class="actions-menu-item open-item" href="<?= htmlspecialchars($profileUrl) ?>">
                                            Deschide
                                        </a>

                                        <button
                                                type="button"
                                                class="actions-menu-item edit-item js-edit-open"
                                                data-randomn-id="<?= htmlspecialchars((string)$user['randomn_id']) ?>"
                                        >
                                            Editare
                                        </button>

                                        <?php if ($isBlocked): ?>
                                            <button
                                                    type="button"
                                                    class="actions-menu-item activate-item js-user-action"
                                                    data-action="activate"
                                                    data-randomn-id="<?= htmlspecialchars((string)$user['randomn_id']) ?>"
                                            >
                                                Activează
                                            </button>
                                        <?php else: ?>
                                            <button
                                                    type="button"
                                                    class="actions-menu-item block-item js-user-action"
                                                    data-action="block"
                                                    data-randomn-id="<?= htmlspecialchars((string)$user['randomn_id']) ?>"
                                            >
                                                Blochează
                                            </button>
                                        <?php endif; ?>

                                        <button
                                                type="button"
                                                class="actions-menu-item delete-item js-user-action"
                                                data-action="delete"
                                                data-randomn-id="<?= htmlspecialchars((string)$user['randomn_id']) ?>"
                                        >
                                            Șterge
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                Nu există utilizatori pentru filtrul selectat.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div class="footer-info" id="footerInfo">
                <?php
                $from = $totalUsers > 0 ? $offset + 1 : 0;
                $to   = min($offset + $perPage, $totalUsers);
                ?>
                Afișare date <?= $from ?>–<?= $to ?> din <?= $totalUsers ?> înregistrări
            </div>

            <div class="pagination">
                <?php if ($pageNum > 1): ?>
                    <a class="page-btn" href="<?= htmlspecialchars(buildUsersUrl(['p' => $pageNum - 1])) ?>">&lt;</a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $pageNum - 2);
                $endPage   = min($totalPages, $pageNum + 2);

                if ($startPage > 1): ?>
                    <a class="page-btn" href="<?= htmlspecialchars(buildUsersUrl(['p' => 1])) ?>">1</a>
                    <?php if ($startPage > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a class="page-btn <?= $i === $pageNum ? 'active' : '' ?>" href="<?= htmlspecialchars(buildUsersUrl(['p' => $i])) ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a class="page-btn" href="<?= htmlspecialchars(buildUsersUrl(['p' => $totalPages])) ?>">
                        <?= $totalPages ?>
                    </a>
                <?php endif; ?>

                <?php if ($pageNum < $totalPages): ?>
                    <a class="page-btn" href="<?= htmlspecialchars(buildUsersUrl(['p' => $pageNum + 1])) ?>">&gt;</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<div class="modal-backdrop" id="editUserModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Editare rapidă utilizator</h3>
            <button type="button" class="modal-close" id="closeEditModal">&times;</button>
        </div>

        <div class="modal-body">
            <form id="quickEditUserForm">
                <input type="hidden" name="randomn_id" id="modal_randomn_id">
                <input type="hidden" name="id" id="modal_id">

                <div class="modal-grid">
                    <div class="modal-group">
                        <label class="modal-label" for="modal_first_name">Prenume</label>
                        <input class="modal-input" type="text" id="modal_first_name" name="first_name">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_last_name">Nume</label>
                        <input class="modal-input" type="text" id="modal_last_name" name="last_name">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_email">Email</label>
                        <input class="modal-input" type="email" id="modal_email" name="email">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_phone">Telefon</label>
                        <input class="modal-input" type="text" id="modal_phone" name="phone">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_city">Oraș</label>
                        <input class="modal-input" type="text" id="modal_city" name="city">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_country">Țară</label>
                        <input class="modal-input" type="text" id="modal_country" name="country">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_designation">Specializare</label>
                        <input class="modal-input" type="text" id="modal_designation" name="designation">
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_role">Rol</label>
                        <select class="modal-select" id="modal_role" name="role">
                            <option value="">Selectează</option>
                            <option value="Student">Student</option>
                            <option value="Profesor">Profesor</option>
                            <option value="Instituție">Instituție</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Moderator">Moderator</option>
                            <option value="User">User</option>
                        </select>
                    </div>

                    <div class="modal-group">
                        <label class="modal-label" for="modal_status">Status</label>
                        <select class="modal-select" id="modal_status" name="status">
                            <option value="active">active</option>
                            <option value="blocked">blocked</option>
                            <option value="inactive">inactive</option>
                        </select>
                    </div>

                    <div class="modal-group full">
                        <label class="modal-label" for="modal_password">Parolă nouă</label>
                        <input class="modal-input" type="password" id="modal_password" name="password" placeholder="Lasă gol dacă nu schimbi parola">
                    </div>
                </div>

                <div id="quickEditMessage" class="modal-message"></div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="modal-btn secondary" id="cancelEditModal">Anulează</button>
            <button type="button" class="modal-btn primary" id="saveQuickEditBtn">Salvează</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('editUserModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditModal = document.getElementById('cancelEditModal');
        const saveQuickEditBtn = document.getElementById('saveQuickEditBtn');
        const quickEditMessage = document.getElementById('quickEditMessage');
        const quickEditForm = document.getElementById('quickEditUserForm');

        const statTotalUsers = document.getElementById('statTotalUsers');
        const statActiveUsers = document.getElementById('statActiveUsers');
        const statBlockedUsers = document.getElementById('statBlockedUsers');

        let currentEditingRow = null;

        function openModal() {
            modal.classList.add('show');
        }

        function closeModal() {
            modal.classList.remove('show');
            quickEditMessage.className = 'modal-message';
            quickEditMessage.style.display = 'none';
            quickEditMessage.textContent = '';
            quickEditForm.reset();
            currentEditingRow = null;
        }

        function closeAllActionMenus() {
            document.querySelectorAll('.js-actions-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        function recalcStats() {
            const rows = document.querySelectorAll('#usersTableBody tr[data-randomn-id]');
            let total = 0;
            let active = 0;
            let blocked = 0;

            rows.forEach(row => {
                total++;
                const badge = row.querySelector('.js-status-badge');
                if (!badge) return;

                const status = (badge.textContent || '').trim().toLowerCase();
                if (['active', 'activ', '1', 'online'].includes(status)) active++;
                if (['blocked', 'block', 'inactive', '0', 'disabled'].includes(status)) blocked++;
            });

            if (statTotalUsers) statTotalUsers.textContent = total;
            if (statActiveUsers) statActiveUsers.textContent = active;
            if (statBlockedUsers) statBlockedUsers.textContent = blocked;
        }

        function setModalMessage(type, text) {
            quickEditMessage.textContent = text;
            quickEditMessage.className = 'modal-message ' + type;
            quickEditMessage.style.display = 'block';
        }

        document.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('.js-actions-toggle');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();

                const wrap = toggleBtn.closest('.actions-dropdown-wrap');
                const menu = wrap ? wrap.querySelector('.js-actions-menu') : null;
                if (!menu) return;

                const isOpen = menu.classList.contains('show');
                closeAllActionMenus();

                if (!isOpen) {
                    menu.classList.add('show');
                }
                return;
            }

            if (!e.target.closest('.actions-dropdown-wrap')) {
                closeAllActionMenus();
            }
        });

        document.addEventListener('click', function (e) {
            const editBtn = e.target.closest('.js-edit-open');
            if (editBtn) {
                closeAllActionMenus();

                const randomnId = editBtn.dataset.randomnId;
                const row = document.querySelector('tr[data-randomn-id="' + randomnId + '"]');
                if (!row) return;

                currentEditingRow = row;

                const fullname = row.querySelector('.js-col-fullname') ? row.querySelector('.js-col-fullname').textContent.trim() : '';
                const firstName = row.dataset.firstName || '';
                const lastName = row.dataset.lastName || '';

                document.getElementById('modal_randomn_id').value = randomnId || '';
                document.getElementById('modal_id').value = row.dataset.id || '';
                document.getElementById('modal_first_name').value = firstName;
                document.getElementById('modal_last_name').value = lastName;
                document.getElementById('modal_email').value = row.dataset.email || '';
                document.getElementById('modal_phone').value = row.dataset.phone || '';
                document.getElementById('modal_city').value = row.dataset.city || '';
                document.getElementById('modal_country').value = row.dataset.country || '';
                document.getElementById('modal_designation').value = row.dataset.designation || '';
                document.getElementById('modal_role').value = row.dataset.role || '';
                document.getElementById('modal_status').value = (row.dataset.status || '').toLowerCase() || 'inactive';
                document.getElementById('modal_password').value = '';

                if (!firstName && !lastName && fullname && fullname !== '-') {
                    const parts = fullname.split(' ');
                    document.getElementById('modal_first_name').value = parts.shift() || '';
                    document.getElementById('modal_last_name').value = parts.join(' ');
                }

                openModal();
            }
        });

        closeEditModal.addEventListener('click', closeModal);
        cancelEditModal.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        saveQuickEditBtn.addEventListener('click', async function () {
            if (!currentEditingRow) return;

            const formData = new FormData(quickEditForm);
            formData.append('type_product', 'edit');

            saveQuickEditBtn.disabled = true;
            const oldText = saveQuickEditBtn.textContent;
            saveQuickEditBtn.textContent = 'Se salvează...';

            try {
                const response = await fetch('/public/cruduserslist', {
                    method: 'POST',
                    body: formData
                });

                const rawText = await response.text();
                console.log('QUICK EDIT RAW RESPONSE =', rawText);

                let result = null;
                try {
                    result = JSON.parse(rawText);
                } catch (e) {
                    throw new Error('Serverul nu a returnat JSON valid.');
                }

                if (!result.success) {
                    setModalMessage('error', result.message || 'A apărut o eroare.');
                    return;
                }

                const firstName = document.getElementById('modal_first_name').value.trim();
                const lastName = document.getElementById('modal_last_name').value.trim();
                const email = document.getElementById('modal_email').value.trim();
                const phone = document.getElementById('modal_phone').value.trim();
                const city = document.getElementById('modal_city').value.trim();
                const country = document.getElementById('modal_country').value.trim();
                const designation = document.getElementById('modal_designation').value.trim();
                const role = document.getElementById('modal_role').value.trim();
                const status = document.getElementById('modal_status').value.trim();
                const randomnId = document.getElementById('modal_randomn_id').value.trim();

                const fullname = (firstName + ' ' + lastName).trim() || '-';

                currentEditingRow.dataset.firstName = firstName;
                currentEditingRow.dataset.lastName = lastName;
                currentEditingRow.dataset.email = email;
                currentEditingRow.dataset.phone = phone;
                currentEditingRow.dataset.city = city;
                currentEditingRow.dataset.country = country;
                currentEditingRow.dataset.designation = designation;
                currentEditingRow.dataset.role = role;
                currentEditingRow.dataset.status = status;

                const colFullname = currentEditingRow.querySelector('.js-col-fullname');
                const colEmail = currentEditingRow.querySelector('.js-col-email');
                const colCity = currentEditingRow.querySelector('.js-col-city');
                const colCountry = currentEditingRow.querySelector('.js-col-country');
                const colDesignation = currentEditingRow.querySelector('.js-col-designation');
                const badge = currentEditingRow.querySelector('.js-status-badge');

                if (colFullname) colFullname.textContent = fullname;
                if (colEmail) colEmail.textContent = email || '-';
                if (colCity) colCity.textContent = city || '-';
                if (colCountry) colCountry.textContent = country || '-';
                if (colDesignation) colDesignation.textContent = designation || '-';

                if (badge) {
                    badge.textContent = status;
                    badge.classList.remove('active', 'inactive', 'blocked');

                    if (status === 'active') badge.classList.add('active');
                    else if (status === 'blocked') badge.classList.add('blocked');
                    else badge.classList.add('inactive');
                }

                const actionsMenu = currentEditingRow.querySelector('.js-actions-menu');
                if (actionsMenu) {
                    const oldToggleActionBtn = actionsMenu.querySelector('.js-user-action[data-action="block"], .js-user-action[data-action="activate"]');
                    if (oldToggleActionBtn) {
                        if (status === 'blocked') {
                            oldToggleActionBtn.dataset.action = 'activate';
                            oldToggleActionBtn.textContent = 'Activează';
                            oldToggleActionBtn.classList.remove('block-item');
                            oldToggleActionBtn.classList.add('activate-item');
                        } else {
                            oldToggleActionBtn.dataset.action = 'block';
                            oldToggleActionBtn.textContent = 'Blochează';
                            oldToggleActionBtn.classList.remove('activate-item');
                            oldToggleActionBtn.classList.add('block-item');
                        }
                    }
                }

                recalcStats();
                setModalMessage('success', result.message || 'Utilizator actualizat cu succes.');

                setTimeout(() => {
                    closeModal();
                }, 700);

            } catch (error) {
                console.error(error);
                setModalMessage('error', 'Eroare la salvare.');
            } finally {
                saveQuickEditBtn.disabled = false;
                saveQuickEditBtn.textContent = oldText;
            }
        });

        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.js-user-action');
            if (!btn) return;

            closeAllActionMenus();

            const action = btn.dataset.action;
            const randomnId = btn.dataset.randomnId;

            if (!action || !randomnId) {
                alert('Date lipsă pentru acțiune.');
                return;
            }

            if (action === 'delete') {
                const ok = confirm('Sigur vrei să ștergi acest utilizator?');
                if (!ok) return;
            }

            let typeProduct = '';
            let newStatus = '';

            if (action === 'delete') {
                typeProduct = 'delete';
            } else if (action === 'block') {
                typeProduct = 'setstatus';
                newStatus = 'blocked';
            } else if (action === 'activate') {
                typeProduct = 'setstatus';
                newStatus = 'active';
            }

            const formData = new FormData();
            formData.append('type_product', typeProduct);
            formData.append('randomn_id', randomnId);

            if (newStatus !== '') {
                formData.append('status', newStatus);
            }

            btn.disabled = true;
            const oldText = btn.textContent;
            btn.textContent = '...';

            try {
                const response = await fetch('/public/cruduserslist', {
                    method: 'POST',
                    body: formData
                });

                const rawText = await response.text();
                console.log('USER ACTION RAW RESPONSE =', rawText);

                let result = null;
                try {
                    result = JSON.parse(rawText);
                } catch (e) {
                    throw new Error('Serverul nu a returnat JSON valid.');
                }

                if (!result.success) {
                    alert(result.message || 'A apărut o eroare.');
                    return;
                }

                const row = document.querySelector('tr[data-randomn-id="' + randomnId + '"]');

                if (!row) {
                    window.location.reload();
                    return;
                }

                if (action === 'delete') {
                    row.remove();
                    recalcStats();
                    return;
                }

                if (action === 'block' || action === 'activate') {
                    const badge = row.querySelector('.js-status-badge');
                    const toggleBtn = row.querySelector('.js-user-action[data-randomn-id="' + randomnId + '"][data-action="' + action + '"]');

                    row.dataset.status = newStatus;

                    if (badge) {
                        badge.textContent = newStatus;
                        badge.classList.remove('active', 'inactive', 'blocked');

                        if (newStatus === 'active') {
                            badge.classList.add('active');
                        } else if (newStatus === 'blocked') {
                            badge.classList.add('blocked');
                        } else {
                            badge.classList.add('inactive');
                        }
                    }

                    if (toggleBtn) {
                        if (newStatus === 'blocked') {
                            toggleBtn.dataset.action = 'activate';
                            toggleBtn.textContent = 'Activează';
                            toggleBtn.classList.remove('block-item');
                            toggleBtn.classList.add('activate-item');
                        } else {
                            toggleBtn.dataset.action = 'block';
                            toggleBtn.textContent = 'Blochează';
                            toggleBtn.classList.remove('activate-item');
                            toggleBtn.classList.add('block-item');
                        }
                    }

                    recalcStats();
                }

            } catch (error) {
                console.error(error);
                alert('Eroare la executarea acțiunii.');
            } finally {
                btn.disabled = false;
                if (btn.textContent === '...') {
                    btn.textContent = oldText;
                }
            }
        });
    });
</script>