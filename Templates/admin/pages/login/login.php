<?php
function getCurrentUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function logoutSimple(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    setcookie('UserAcces', '', time() - 3600, '/');
    setcookie('token', '', time() - 3600, '/');

    session_destroy();
}
logoutSimple();
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>QuizDigo — Logare</title>

    <link href="<?= getCurrentUrl(); ?>/Templates/admin/dist/css/style.css" rel="stylesheet" type="text/css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        :root{
            --qd-blue:#2E85C7;
            --qd-text:#202124;
            --qd-muted:#5f6368;
            --qd-border:#dadce0;
            --qd-bg:#ffffff;
            --qd-soft:#f8f9fa;
        }
        html,body{height:100%;margin:0;}
        body{
            background: var(--qd-bg);
            color: var(--qd-text);
            font-family: Inter, sans-serif;
        }
        .qd-auth{
            min-height: 100vh;
            display:flex;
            align-items:stretch;
        }
        .qd-left{
            flex: 0 0 44%;
            min-width: 420px;
            display:flex;
            flex-direction:column;
            padding: 40px 72px 32px;
            overflow-y:auto;
        }
        .qd-right{
            flex: 1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 40px;
            background:#fff;
        }
        .qd-form{
            max-width: 520px;
            width:100%;
        }
        .qd-h1{
            font-size: 32px;
            font-weight: 800;
            margin: 0 0 8px;
            color:#0A5084;
        }
        .qd-sub{
            font-size: 18px;
            color:#202124;
            margin: 0 0 20px;
        }
        .qd-label{
            font-size: 12px;
            color: var(--qd-muted);
            margin: 10px 0 4px;
            display:block;
        }
        .qd-input{
            width:100%;
            height:42px;
            border:1px solid var(--qd-border);
            border-radius:6px;
            padding:0 12px;
            font-size:14px;
            outline:none;
            transition:0.2s;
        }
        .qd-input:focus{
            border-color: var(--qd-blue);
            box-shadow: 0 0 0 3px rgba(46,133,199,.18);
        }
        .qd-btn-primary{
            width:100%;
            max-width:220px;
            height:44px;
            border:0;
            border-radius:6px;
            background: var(--qd-blue);
            color:#fff;
            font-weight:700;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-top:18px;
            transition:0.3s;
        }
        .qd-btn-primary:disabled{
            background:#a0aec0;
            cursor:not-allowed;
        }
        .qd-actions{
            display:flex;
            justify-content:left;
            margin:25px 0;
        }
        .hidden{
            display:none !important;
        }
        .alert{
            padding:12px;
            margin-bottom:20px;
            border-radius:8px;
            font-size:14px;
            font-weight:600;
            border:1px solid transparent;
        }
        .alert-error{
            background-color:#fde8e8;
            color:#c81e1e;
            border-color:#f8b4b4;
        }
        .alert-success{
            background-color:#defadb;
            color:#1e5e1a;
            border-color:#bcf0da;
        }
        .qd-social-wrap{
            display:flex;
            flex-direction:column;
            gap:10px;
            max-width:340px;
        }
        .qd-btn-social{
            width:100%;
            height:44px;
            border-radius:999px;
            border:1px solid var(--qd-border);
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            font-weight:700;
            cursor:pointer;
        }
        .qd-btn-social:disabled{
            opacity:.7;
            cursor:not-allowed;
        }
        .qd-illustration{
            max-width:860px;
            width:100%;
            height:auto;
            object-fit:contain;
        }
        @media (max-width: 992px){
            .qd-right{display:none;}
            .qd-left{
                flex:1;
                min-width:unset;
                padding:40px 24px;
            }
        }
    </style>
</head>
<body>

<div class="qd-auth">
    <section class="qd-left">
        <div class="qd-form">
            <div id="formAlert" class="hidden"></div>

            <form id="loginForm" data-endpoint="/public/addusersadd" data-method="POST">
                <div class="qd-h1">Logare</div>
                <div class="qd-sub">Intră în contul tău QuizDigo</div>
                <div class="qd-sub">
                    <a href="https://quizdigo.com/public/reg" style="font-size:16px;">Înregistrare</a>
                </div>

                <label class="qd-label">Login / Email</label>
                <input
                        class="qd-input"
                        id="val_login"
                        name="login"
                        type="text"
                        placeholder="Ex: email@exemplu.com"
                        autocomplete="username"
                        required
                >

                <label class="qd-label">Parolă</label>
                <input
                        class="qd-input"
                        id="val_password"
                        name="password"
                        type="password"
                        placeholder="Introdu parola"
                        autocomplete="current-password"
                        required
                >

                <div class="qd-actions">
                    <button type="submit" id="btnLogin" class="qd-btn-primary">Login</button>
                </div>
            </form>
        </div>

        <div class="qd-social-wrap" id="social-wrap">
            <div style="text-align:center; border-top:1px solid #eee; margin:20px 0; position:relative;">
                <span style="background:#fff; padding:0 10px; position:absolute; top:-10px; left:50%; transform:translateX(-50%); color:#ccc; font-size:12px;">SAU</span>
            </div>

            <button type="button" class="qd-btn-social" id="btnGoogle">
                <span style="width:18px;height:18px;border-radius:50%;display:inline-block;background:conic-gradient(#ea4335 0 25%, #fbbc05 0 50%, #34a853 0 75%, #4285f4 0 100%);"></span>
                Sign in with Google
            </button>
        </div>
    </section>

    <section class="qd-right">
        <img
                class="qd-illustration"
                src="<?= getCurrentUrl(); ?>/Templates/admin/dist/ChatGPT Image 5 янв. 2026 г., 16_14_25 1.png"
                alt="QuizDigo"
        >
    </section>
</div>

<script>
    const alertBox = document.getElementById('formAlert');

    function showAlert(type, msg) {
        alertBox.classList.remove('hidden', 'alert-error', 'alert-success');
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = msg;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function clearAlert() {
        alertBox.className = 'hidden';
        alertBox.textContent = '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('loginForm');
        const btnLogin = document.getElementById('btnLogin');
        const btnGoogle = document.getElementById('btnGoogle');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearAlert();

            const data = {
                type_product: 'login',
                login: document.getElementById('val_login').value.trim(),
                password: document.getElementById('val_password').value
            };

            if (!data.login || !data.password) {
                showAlert('error', 'Completează login și parola.');
                return;
            }

            btnLogin.disabled = true;
            btnLogin.textContent = 'Se verifică...';

            try {
                const res = await fetch(form.dataset.endpoint, {
                    method: form.dataset.method || 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data),
                    credentials: 'include'
                });

                const raw = await res.text();
                let json;

                try {
                    json = JSON.parse(raw.match(/\{.*\}/s)[0]);
                } catch (e) {
                    throw new Error('Serverul nu a întors JSON valid.');
                }

                if (!res.ok || !json.success) {
                    throw new Error(json.message || 'Date de autentificare incorecte.');
                }

                showAlert('success', 'Autentificare reușită. Redirecționare...');
                setTimeout(() => {
                    window.location.href = json.redirect || '/public/homepages';
                }, 800);

            } catch (err) {
                console.error('[Login error]', err);
                showAlert('error', err.message || 'Eroare la logare.');
            } finally {
                btnLogin.disabled = false;
                btnLogin.textContent = 'Login';
            }
        });

        document.getElementById('btnGoogle')?.addEventListener('click', () => {
            clearAlert();

            if (!googleTokenClient) {
                showAlert('error', 'Google client nu este inițializat încă. Reîncarcă pagina.');
                return;
            }

            googleTokenClient.requestAccessToken();
        });
    });

    let googleTokenClient = null;

    window.addEventListener('load', () => {
        if (!window.google || !google.accounts || !google.accounts.oauth2) {
            console.error('Google GSI nu s-a încărcat.');
            return;
        }

        googleTokenClient = google.accounts.oauth2.initTokenClient({
            client_id: '235448384789-q7f6panjassagne069edqinr3qt4054j.apps.googleusercontent.com',
            scope: 'openid email profile',
            callback: async (tokenResponse) => {
                try {
                    if (!tokenResponse.access_token) {
                        showAlert('error', 'Google login error.');
                        return;
                    }

                    const res = await fetch('https://quizdigo.com/reg.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'include',
                        body: JSON.stringify({
                            provider: 'google',
                            access_token: tokenResponse.access_token
                        })
                    });

                    const raw = await res.text();
                    let json;

                    try {
                        json = JSON.parse(raw.match(/\{.*\}/s)[0]);
                    } catch (e) {
                        throw new Error('reg.php nu a returnat JSON valid.');
                    }

                    if (!res.ok || !json.success) {
                        throw new Error(json.message || 'Google login failed.');
                    }

                    showAlert('success', 'Autentificare Google reușită. Redirecționare...');
                    setTimeout(() => {
                        window.location.href = json.redirect || '/public/homepages';
                    }, 600);

                } catch (err) {
                    console.error('[Google login error]', err);
                    showAlert('error', err.message || 'Eroare la autentificarea Google.');
                }
            }
        });
    });
</script>

</body>
</html>