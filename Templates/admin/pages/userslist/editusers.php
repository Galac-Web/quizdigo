<?php
declare(strict_types=1);

use Evasystem\Controllers\Users\UsersService;

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
    die('Utilizator neautentificat.');
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    die('ID invalid.');
}

$usersService = new UsersService();
$userRaw = $usersService->getIdUserss($userId);

if (!$userRaw) {
    die('Utilizatorul nu a fost găsit.');
}

/**
 * Normalizare dacă metoda întoarce [0 => [...]]
 */
$user = $userRaw;
if (is_array($userRaw) && isset($userRaw[0]) && is_array($userRaw[0])) {
    $user = $userRaw[0];
}

/**
 * Date users_connect
 */
$fullname   = trim((string)($user['fullname'] ?? ''));
$role       = (string)($user['role'] ?? '');
$photo      = trim((string)($user['photo'] ?? ''));
$randomnId  = trim((string)($user['randomn_id'] ?? ''));

/**
 * fullname -> first/last fallback
 */
$nameParts  = preg_split('/\s+/', $fullname, 2);
$firstName  = $nameParts[0] ?? '';
$lastName   = $nameParts[1] ?? '';

/**
 * Fallback-uri din aceeași structură, dacă există
 * Dacă tu încarci ulterior și users_info separat, aceste valori vor trebui luate din users_info
 */
$email       = (string)($user['email'] ?? '');
$phone       = (string)($user['phone'] ?? '');
$city        = (string)($user['city'] ?? '');
$country     = (string)($user['country'] ?? '');
$designation = (string)($user['designation'] ?? '');
$levelGroup  = (string)($user['level_group'] ?? '');

if ($photo === '') {
    $photo = getCurrentUrl() . '/logo_new.png';
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #6b7280;
        --line: #e9eef5;
        --primary: #5570f1;
        --primary-dark: #4057d6;
        --primary-soft: rgba(85, 112, 241, 0.12);
        --accent: #ed2590;
        --success: #16a34a;
        --danger: #dc2626;
        --shadow: 0 12px 35px rgba(21, 34, 50, 0.08);
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 12px;
        --input-border: #d9dee7;
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
        max-width: 100%;
        width: 100%;
        margin: 0 auto;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
        align-items: start;
    }

    .card {
        background: var(--card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
    }

    .profile-sidebar { padding: 28px 24px; }

    .profile-avatar-wrap {
        text-align: center;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--line);
    }

    .profile-avatar {
        width: 168px;
        height: 168px;
        border-radius: 50%;
        margin: 0 auto 18px;
        padding: 5px;
        background: linear-gradient(135deg, var(--accent), #ff78bb);
    }

    .profile-avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        background: #eef2ff;
    }

    .profile-avatar-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .profile-name {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #111827;
    }

    .profile-city,
    .profile-country {
        margin: 6px 0 0;
        font-size: 15px;
        color: var(--muted);
    }

    .profile-country { margin-top: 2px; }

    .profile-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-top: 18px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
    }

    .badge.role {
        background: #eef2ff;
        color: #4338ca;
    }

    .badge.project {
        background: #fdf2f8;
        color: #be185d;
    }

    .profile-info {
        padding-top: 22px;
        display: grid;
        gap: 16px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--line);
    }

    .info-item:last-child { border-bottom: none; }

    .info-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 12px;
        background: var(--primary-soft);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-icon svg {
        width: 20px;
        height: 20px;
        stroke: var(--primary);
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .info-text small {
        display: block;
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .info-text div {
        font-size: 15px;
        font-weight: 500;
        color: #111827;
        word-break: break-word;
    }

    .profile-main { padding: 30px; }

    .profile-main-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 26px;
    }

    .profile-main-title {
        margin: 0;
        font-size: 30px;
        font-weight: 700;
        color: #0f172a;
    }

    .profile-main-subtitle {
        margin: 6px 0 0;
        font-size: 14px;
        color: var(--muted);
    }

    .section-divider {
        height: 1px;
        background: var(--line);
        margin: 34px 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group.full { grid-column: 1 / -1; }

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .form-input,
    .form-select {
        width: 100%;
        height: 52px;
        border: 1px solid var(--input-border);
        border-radius: 14px;
        padding: 0 16px;
        font-size: 15px;
        font-family: inherit;
        color: #111827;
        background: #fff;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(85, 112, 241, 0.12);
    }

    .password-row { position: relative; }

    .password-hint {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        color: var(--primary);
        font-weight: 600;
        pointer-events: none;
        background: #fff;
        padding-left: 8px;
    }

    .form-actions {
        margin-top: 28px;
        display: flex;
        justify-content: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        border-radius: 14px;
        height: 54px;
        padding: 0 28px;
        font-family: inherit;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-primary {
        background: var(--primary);
        color: #fff;
        min-width: 220px;
    }

    .btn-primary:hover { background: var(--primary-dark); }

    .btn-secondary {
        background: #eef2ff;
        color: var(--primary);
    }

    .btn-secondary:hover { background: #e0e7ff; }

    .ajax-message {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 14px;
        display: none;
    }

    .ajax-message.success {
        display: block;
        background: rgba(22, 163, 74, 0.08);
        color: var(--success);
    }

    .ajax-message.error {
        display: block;
        background: rgba(220, 38, 38, 0.08);
        color: var(--danger);
    }

    @media (max-width: 1024px) {
        .profile-layout { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        body { padding: 16px; }
        .profile-sidebar, .profile-main { padding: 20px; }
        .profile-main-title { font-size: 24px; }
        .form-grid { grid-template-columns: 1fr; }
        .btn-primary { width: 100%; }
    }



    .profile-avatar {
        position: relative;
        transition: 0.2s ease;
    }

    .profile-avatar:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
    }

    .profile-avatar::after {
        content: "Schimbă";
        position: absolute;
        left: 50%;
        bottom: -8px;
        transform: translateX(-50%);
        background: rgba(17,24,39,0.82);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 10px;
        border-radius: 999px;
        opacity: 0;
        transition: 0.2s ease;
        pointer-events: none;
    }

    .profile-avatar:hover::after {
        opacity: 1;
    }

</style>

<div class="page">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>
    <div class="profile-layout" style="margin-top: 20px;">

        <aside class="card profile-sidebar">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar" id="avatarTrigger" style="cursor:pointer;" title="Apasă pentru a schimba imaginea">
                    <div class="profile-avatar-inner">
                        <img src="<?= htmlspecialchars($photo) ?>" alt="Foto profil" id="sidebarPhoto">
                    </div>
                </div>

                <input type="file" id="avatarInput" accept="image/*" style="display:none;">
                <div id="avatarAjaxMessage" class="ajax-message"></div>

                <h2 class="profile-name" id="sidebarName"><?= htmlspecialchars($fullname ?: '-') ?></h2>
                <p class="profile-city" id="sidebarCity"><?= htmlspecialchars($city ?: '-') ?></p>
                <p class="profile-country" id="sidebarCountry"><?= htmlspecialchars($country ?: '-') ?></p>

                <div class="profile-tags">
                    <span class="badge role" id="sidebarRole"><?= htmlspecialchars($role ?: 'User') ?></span>
                    <span class="badge project" id="sidebarDesignation"><?= htmlspecialchars($designation ?: 'Project') ?></span>
                </div>
            </div>

            <div class="profile-info">
                <div class="info-item">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.63 2.62a2 2 0 0 1-.45 2.11L8 9.91a16 16 0 0 0 6.09 6.09l1.46-1.29a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.62.63A2 2 0 0 1 22 16.92z"></path></svg>
                    </div>
                    <div class="info-text">
                        <small>Telefon</small>
                        <div id="sidebarPhone"><?= htmlspecialchars($phone ?: '-') ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"></path><path d="M22 6l-10 7L2 6"></path></svg>
                    </div>
                    <div class="info-text">
                        <small>Email</small>
                        <div id="sidebarEmail"><?= htmlspecialchars($email ?: '-') ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24"><path d="M3 7h18"></path><path d="M6 3h12v18H6z"></path><path d="M9 11h6"></path><path d="M9 15h4"></path></svg>
                    </div>
                    <div class="info-text">
                        <small>Nivel / grup</small>
                        <div id="sidebarLevel"><?= htmlspecialchars($levelGroup ?: '-') ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <section class="card profile-main">

            <div class="profile-main-header">
                <div>
                    <h1 class="profile-main-title">Editează profilul</h1>
                    <p class="profile-main-subtitle">Actualizează informațiile generale ale utilizatorului.</p>
                </div>
            </div>

            <form id="editProfileInfoForm">
                <input type="hidden" name="id" value="<?= (int)$userId ?>">
                <input type="hidden" name="randomn_id" value="<?= htmlspecialchars($randomnId) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="first_name">Prenume</label>
                        <input class="form-input" type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Nume</label>
                        <input class="form-input" type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Telefon</label>
                        <input class="form-input" type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">Oraș</label>
                        <input class="form-input" type="text" id="city" name="city" value="<?= htmlspecialchars($city) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="country">Țară</label>
                        <select class="form-select" id="country" name="country">
                            <option value="">Selectează</option>
                            <option value="Moldova" <?= $country === 'Moldova' ? 'selected' : '' ?>>Moldova</option>
                            <option value="România" <?= $country === 'România' ? 'selected' : '' ?>>România</option>
                            <option value="India" <?= $country === 'India' ? 'selected' : '' ?>>India</option>
                            <option value="United States" <?= $country === 'United States' ? 'selected' : '' ?>>United States</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="designation">Specializare</label>
                        <select class="form-select" id="designation" name="designation">
                            <option value="">Selectează</option>
                            <option value="UI Intern" <?= $designation === 'UI Intern' ? 'selected' : '' ?>>UI Intern</option>
                            <option value="Frontend Developer" <?= $designation === 'Frontend Developer' ? 'selected' : '' ?>>Frontend Developer</option>
                            <option value="Backend Developer" <?= $designation === 'Backend Developer' ? 'selected' : '' ?>>Backend Developer</option>
                            <option value="Project Manager" <?= $designation === 'Project Manager' ? 'selected' : '' ?>>Project Manager</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">Salvează profilul</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Anulează</button>
                </div>

                <div id="profileAjaxMessage" class="ajax-message"></div>
            </form>

            <div class="section-divider"></div>

            <div class="profile-main-header">
                <div>
                    <h2 class="profile-main-title" style="font-size:24px;">Date de acces</h2>
                    <p class="profile-main-subtitle">Modifică emailul de login, rolul și parola.</p>
                </div>
            </div>

            <form id="editSecurityForm">
                <input type="hidden" name="id" value="<?= (int)$userId ?>">
                <input type="hidden" name="randomn_id" value="<?= htmlspecialchars($randomnId) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-input" type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">Rol</label>
                        <select class="form-select" id="role" name="role">
                            <option value="">Selectează</option>
                            <option value="Student" <?= $role === 'Student' ? 'selected' : '' ?>>Student</option>
                            <option value="Profesor" <?= $role === 'Profesor' ? 'selected' : '' ?>>Profesor</option>
                            <option value="Instituție" <?= $role === 'Instituție' ? 'selected' : '' ?>>Instituție</option>
                            <option value="Administrator" <?= $role === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="password">Parolă nouă</label>
                        <div class="password-row">
                            <input class="form-input" type="password" id="password" name="password" value="">
                            <span class="password-hint">Opțional</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="saveSecurityBtn">Salvează accesul</button>
                </div>

                <div id="securityAjaxMessage" class="ajax-message"></div>
            </form>

        </section>

    </div>
</div>
<script>
    const avatarTrigger = document.getElementById('avatarTrigger');
    const avatarInput = document.getElementById('avatarInput');
    const avatarAjaxMessage = document.getElementById('avatarAjaxMessage');
    const sidebarPhoto = document.getElementById('sidebarPhoto');

    if (avatarTrigger && avatarInput) {
        avatarTrigger.addEventListener('click', function () {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', async function () {
            if (!avatarInput.files || !avatarInput.files[0]) {
                return;
            }

            const file = avatarInput.files[0];

            avatarAjaxMessage.className = 'ajax-message';
            avatarAjaxMessage.style.display = 'none';

            const formData = new FormData();
            formData.append('type_product', 'edit_avatar');
            formData.append('id', '<?= (int)$userId ?>');
            formData.append('randomn_id', '<?= htmlspecialchars($randomnId, ENT_QUOTES) ?>');
            formData.append('avatar', file);

            try {
                console.log('========== AVATAR AJAX START ==========');
                console.log('URL:', '/public/cruduserslist');
                console.log('FILE:', file);

                const response = await fetch('/public/cruduserslist', {
                    method: 'POST',
                    body: formData
                });

                console.log('AVATAR HTTP STATUS:', response.status);

                const rawText = await response.text();
                console.log('AVATAR RAW RESPONSE TEXT:', rawText);

                let result = null;
                try {
                    result = JSON.parse(rawText);
                    console.log('AVATAR PARSED JSON:', result);
                } catch (jsonError) {
                    console.error('AVATAR JSON PARSE ERROR:', jsonError);
                    throw new Error('Serverul nu a returnat JSON valid pentru avatar.');
                }

                if (result.success) {
                    avatarAjaxMessage.textContent = result.message || 'Imaginea a fost actualizată.';
                    avatarAjaxMessage.className = 'ajax-message success';
                    avatarAjaxMessage.style.display = 'block';

                    if (result.photo_url) {
                        sidebarPhoto.src = result.photo_url + '?t=' + Date.now();
                    }
                } else {
                    avatarAjaxMessage.textContent = result.message || 'Eroare la încărcarea imaginii.';
                    avatarAjaxMessage.className = 'ajax-message error';
                    avatarAjaxMessage.style.display = 'block';
                }

                console.log('========== AVATAR AJAX END ==========');
            } catch (error) {
                console.error('AVATAR AJAX ERROR:', error);
                avatarAjaxMessage.textContent = 'Eroare la upload imagine. Vezi consola.';
                avatarAjaxMessage.className = 'ajax-message error';
                avatarAjaxMessage.style.display = 'block';
            } finally {
                avatarInput.value = '';
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileForm = document.getElementById('editProfileInfoForm');
        const securityForm = document.getElementById('editSecurityForm');

        async function sendAjaxForm(form, typeProduct, buttonId, messageId, onSuccess) {
            const saveBtn = document.getElementById(buttonId);
            const ajaxMessage = document.getElementById(messageId);

            ajaxMessage.className = 'ajax-message';
            ajaxMessage.style.display = 'none';

            saveBtn.disabled = true;
            const oldText = saveBtn.textContent;
            saveBtn.textContent = 'Se salvează...';

            try {
                const formData = new FormData(form);
                formData.append('type_product', typeProduct);

                console.log('========== AJAX SEND START ==========');
                console.log('URL:', '/public/cruduserslist');
                console.log('Method:', 'POST');
                console.log('TYPE PRODUCT:', typeProduct);

                for (const [key, value] of formData.entries()) {
                    console.log('FORM DATA =>', key, value);
                }

                const response = await fetch('/public/cruduserslist', {
                    method: 'POST',
                    body: formData
                });

                console.log('HTTP STATUS:', response.status);
                console.log('HTTP OK:', response.ok);
                console.log('CONTENT-TYPE:', response.headers.get('content-type'));

                const rawText = await response.text();
                console.log('RAW RESPONSE TEXT:', rawText);

                let result = null;
                try {
                    result = JSON.parse(rawText);
                    console.log('PARSED JSON:', result);
                } catch (jsonError) {
                    console.error('JSON PARSE ERROR:', jsonError);
                    throw new Error('Serverul nu a returnat JSON valid.');
                }

                if (result.success) {
                    ajaxMessage.textContent = result.message || 'Datele au fost salvate cu succes.';
                    ajaxMessage.className = 'ajax-message success';

                    if (typeof onSuccess === 'function') {
                        onSuccess(result);
                    }
                } else {
                    ajaxMessage.textContent = result.message || 'A apărut o eroare.';
                    ajaxMessage.className = 'ajax-message error';
                }

                ajaxMessage.style.display = 'block';
                console.log('========== AJAX SEND END ==========');
            } catch (error) {
                console.error('AJAX ERROR:', error);
                ajaxMessage.textContent = 'Eroare de conexiune sau răspuns invalid. Vezi consola.';
                ajaxMessage.className = 'ajax-message error';
                ajaxMessage.style.display = 'block';
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = oldText;
            }
        }

        if (profileForm) {
            profileForm.addEventListener('submit', function (e) {
                e.preventDefault();

                sendAjaxForm(
                    profileForm,
                    'edit_profile',
                    'saveProfileBtn',
                    'profileAjaxMessage',
                    function () {
                        const firstName = document.getElementById('first_name').value.trim();
                        const lastName = document.getElementById('last_name').value.trim();
                        const phone = document.getElementById('phone').value.trim();
                        const city = document.getElementById('city').value.trim();
                        const country = document.getElementById('country').value.trim();
                        const designation = document.getElementById('designation').value.trim();

                        document.getElementById('sidebarName').textContent = (firstName + ' ' + lastName).trim() || '-';
                        document.getElementById('sidebarPhone').textContent = phone || '-';
                        document.getElementById('sidebarCity').textContent = city || '-';
                        document.getElementById('sidebarCountry').textContent = country || '-';
                        document.getElementById('sidebarDesignation').textContent = designation || 'Project';
                    }
                );
            });
        }

        if (securityForm) {
            securityForm.addEventListener('submit', function (e) {
                e.preventDefault();

                sendAjaxForm(
                    securityForm,
                    'edit_security',
                    'saveSecurityBtn',
                    'securityAjaxMessage',
                    function () {
                        const email = document.getElementById('email').value.trim();
                        const role = document.getElementById('role').value.trim();

                        document.getElementById('sidebarEmail').textContent = email || '-';
                        document.getElementById('sidebarRole').textContent = role || 'User';

                        document.getElementById('password').value = '';
                    }
                );
            });
        }
    });
</script>