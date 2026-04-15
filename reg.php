<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/** CONFIG */
const DB_HOST = 'localhost';
const DB_NAME = 'lilit2';
const DB_USER = 'lilit2';
const DB_PASS = 'aM1xN7kS3w';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

function json_in(): array
{
    $raw = (string) file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function safe_str($v): string
{
    return trim((string)($v ?? ''));
}

function gen_token(int $len = 32): string
{
    return bin2hex(random_bytes((int) max(8, $len / 2)));
}

function validateUsersConnectPayload(array $data, bool $isUpdate = false, bool $isGoogle = false): array
{
    $clean = [];

    foreach ($data as $key => $value) {
        if (is_scalar($value) && $value !== null) {
            $clean[$key] = trim((string) $value);
        }
    }

    if (!$isUpdate) {
        if (empty($clean['login'])) {
            throw new InvalidArgumentException('Login lipsă.');
        }

        $login = $clean['login'];

        if ($isGoogle) {
            if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Email Google invalid.');
            }
        } else {
            if (
                !filter_var($login, FILTER_VALIDATE_EMAIL) &&
                !preg_match('/^[a-zA-Z0-9._-]{3,64}$/', $login)
            ) {
                throw new InvalidArgumentException('Login invalid.');
            }
        }
    }

    if (!$isGoogle && !empty($clean['password'])) {
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $clean['password'])) {
            throw new InvalidArgumentException('Parola invalidă.');
        }

        $clean['password'] = password_hash($clean['password'], PASSWORD_DEFAULT);
    }

    if ($isGoogle && !isset($clean['password'])) {
        $clean['password'] = '';
    }

    return $clean;
}

function findUserByLogin(PDO $pdo, string $login): ?array
{
    $st = $pdo->prepare("SELECT * FROM users_connect WHERE login = ? LIMIT 1");
    $st->execute([$login]);
    $row = $st->fetch();

    return $row ?: null;
}

function findUserByGoogleId(PDO $pdo, string $googleId): ?array
{
    $st = $pdo->prepare("SELECT * FROM users_connect WHERE connect_id = ? AND closs = 'google' LIMIT 1");
    $st->execute([$googleId]);
    $row = $st->fetch();

    return $row ?: null;
}

function genUniqueRandomnId(PDO $pdo): string
{
    do {
        $randomnId = (string) random_int(100000, 999999);
        $st = $pdo->prepare("SELECT COUNT(*) FROM users_connect WHERE randomn_id = ?");
        $st->execute([$randomnId]);
        $exists = (int) $st->fetchColumn() > 0;
    } while ($exists);

    return $randomnId;
}

function upsertGoogleUser(PDO $pdo, array $googleUser): array
{
    $email    = safe_str($googleUser['email'] ?? '');
    $fullname = safe_str($googleUser['name'] ?? '');
    $googleId = safe_str($googleUser['sub'] ?? '');
    $picture  = safe_str($googleUser['picture'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email Google lipsă sau invalid.');
    }

    if ($googleId === '') {
        throw new InvalidArgumentException('Google ID lipsă.');
    }

    $payload = [
        'nikname'    => '',
        'fullname'   => $fullname !== '' ? $fullname : $email,
        'login'      => $email,
        'password'   => '',
        'contact'    => '',
        'role'       => 'user',
        'closs'      => 'google',
        'status'     => 'active',
        'id_users'   => '',
        'connect_id' => $googleId,
    ];

    $payload = validateUsersConnectPayload($payload, false, true);

    $existingByGoogle = findUserByGoogleId($pdo, $googleId);
    $existingByLogin  = findUserByLogin($pdo, $email);

    $newToken = gen_token(64);

    // Caz 1: există deja cont cu același Google ID
    if ($existingByGoogle) {
        $randomnId = safe_str($existingByGoogle['randomn_id'] ?? '');

        if ($randomnId === '') {
            $randomnId = genUniqueRandomnId($pdo);

            $fix = $pdo->prepare("
                UPDATE users_connect
                SET randomn_id = :randomn_id
                WHERE connect_id = :connect_id AND closs = 'google'
                LIMIT 1
            ");
            $fix->execute([
                ':randomn_id' => $randomnId,
                ':connect_id' => $googleId,
            ]);
        }

        $upd = $pdo->prepare("
            UPDATE users_connect
            SET
                fullname   = :fullname,
                login      = :login,
                contact    = CASE WHEN contact IS NULL THEN '' ELSE contact END,
                role       = CASE WHEN role IS NULL OR role = '' THEN 'user' ELSE role END,
                status     = CASE WHEN status IS NULL OR status = '' THEN 'active' ELSE status END,
                closs      = 'google',
                connect_id = :connect_id,
                token      = :token
            WHERE randomn_id = :randomn_id
            LIMIT 1
        ");

        $upd->execute([
            ':fullname'   => $payload['fullname'],
            ':login'      => $payload['login'],
            ':connect_id' => $payload['connect_id'],
            ':token'      => $newToken,
            ':randomn_id' => $randomnId,
        ]);

        return [
            'randomn_id' => $randomnId,
            'login'      => $payload['login'],
            'token'      => $newToken,
            'mode'       => 'updated_by_google_id',
            'picture'    => $picture,
        ];
    }

    // Caz 2: există cont cu același email
    if ($existingByLogin) {
        $existingCloss     = safe_str($existingByLogin['closs'] ?? '');
        $existingConnectId = safe_str($existingByLogin['connect_id'] ?? '');
        $randomnId         = safe_str($existingByLogin['randomn_id'] ?? '');

        if ($existingCloss === 'google' && $existingConnectId !== '' && $existingConnectId !== $googleId) {
            throw new RuntimeException('Există deja un cont Google diferit pe acest email.');
        }

        if ($randomnId === '') {
            $randomnId = genUniqueRandomnId($pdo);
        }

        $upd = $pdo->prepare("
            UPDATE users_connect
            SET
                fullname   = CASE WHEN fullname IS NULL OR fullname = '' THEN :fullname ELSE fullname END,
                closs      = 'google',
                connect_id = :connect_id,
                randomn_id = CASE WHEN randomn_id IS NULL OR randomn_id = '' THEN :randomn_id ELSE randomn_id END,
                token      = :token,
                status     = CASE WHEN status IS NULL OR status = '' THEN 'active' ELSE status END,
                role       = CASE WHEN role IS NULL OR role = '' THEN 'user' ELSE role END
            WHERE login = :login
            LIMIT 1
        ");

        $upd->execute([
            ':fullname'   => $payload['fullname'],
            ':connect_id' => $payload['connect_id'],
            ':randomn_id' => $randomnId,
            ':token'      => $newToken,
            ':login'      => $payload['login'],
        ]);

        return [
            'randomn_id' => $randomnId,
            'login'      => $payload['login'],
            'token'      => $newToken,
            'mode'       => 'linked_existing_email',
            'picture'    => $picture,
        ];
    }

    // Caz 3: cont nou
    $randomnId = genUniqueRandomnId($pdo);

    $ins = $pdo->prepare("
        INSERT INTO users_connect
        (
            nikname,
            fullname,
            login,
            password,
            contact,
            role,
            closs,
            status,
            id_users,
            connect_id,
            randomn_id,
            token
        )
        VALUES
        (
            :nikname,
            :fullname,
            :login,
            :password,
            :contact,
            :role,
            :closs,
            :status,
            :id_users,
            :connect_id,
            :randomn_id,
            :token
        )
    ");

    $ins->execute([
        ':nikname'    => $payload['nikname'],
        ':fullname'   => $payload['fullname'],
        ':login'      => $payload['login'],
        ':password'   => $payload['password'],
        ':contact'    => $payload['contact'],
        ':role'       => $payload['role'],
        ':closs'      => $payload['closs'],
        ':status'     => $payload['status'],
        ':id_users'   => $payload['id_users'],
        ':connect_id' => $payload['connect_id'],
        ':randomn_id' => $randomnId,
        ':token'      => $newToken,
    ]);

    return [
        'randomn_id' => $randomnId,
        'login'      => $payload['login'],
        'token'      => $newToken,
        'mode'       => 'inserted',
        'picture'    => $picture,
    ];
}

/** INPUT */
$input       = json_in();
$provider    = safe_str($input['provider'] ?? '');
$accessToken = safe_str($input['access_token'] ?? '');

if ($provider !== 'google' || $accessToken === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Missing provider/access_token',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** GOOGLE USERINFO */
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_TIMEOUT        => 10,
]);

$resp = curl_exec($ch);
$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Curl error: ' . $err,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($http !== 200) {
    echo json_encode([
        'success' => false,
        'message' => 'Google token invalid (HTTP ' . $http . ')',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$u = json_decode($resp, true);

if (!is_array($u) || empty($u['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Google userinfo response',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $result = upsertGoogleUser($pdo, $u);

    $pdo->commit();

    session_regenerate_id(true);

    // AICI TOTUL E PE randomn_id
    $_SESSION['user_id'] = (string) $result['randomn_id'];
    $_SESSION['login']   = (string) $result['login'];

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    setcookie('token', (string) $result['token'], [
        'expires'  => time() + 60 * 60 * 24 * 30,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    setcookie('UserAcces', (string) $result['randomn_id'], [
        'expires'  => time() + 60 * 60 * 24 * 30,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    echo json_encode([
        'success'    => true,
        'user_id'    => (string) $result['randomn_id'],
        'randomn_id' => (string) $result['randomn_id'],
        'login'      => (string) $result['login'],
        'mode'       => (string) $result['mode'],
        'redirect'   => '/public/homepages',
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('[google_oauth] ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Auth error: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}