<?php
function getCurrentUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function logoutSimple(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
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
    <title>QuizDigo — Înregistrare</title>
    <link href="<?= getCurrentUrl(); ?>/Templates/admin/dist/css/style.css" rel="stylesheet" type="text/css">
    <style>
        :root{ --qd-blue:#2E85C7; --qd-text:#202124; --qd-muted:#5f6368; --qd-border:#dadce0; --qd-bg:#ffffff; --qd-soft:#f8f9fa; }
        html,body{height:100%; margin:0;}
        body{ background: var(--qd-bg); color: var(--qd-text); font-family: Inter, sans-serif; }
        .qd-auth{ min-height: 100vh; display:flex; align-items:stretch; }
        .qd-left{ flex: 0 0 44%; min-width: 420px; display:flex; flex-direction:column; padding: 40px 72px 32px; overflow-y: auto; }
        .qd-right{ flex: 1; display:flex; align-items:center; justify-content:center; padding: 40px; background:#fff; }
        .qd-form{ max-width: 520px; width:100%; }
        .qd-h1{ font-size: 32px; font-weight: 800; margin: 0 0 8px; color:#0A5084; }
        .qd-sub{ font-size: 18px; color: #202124; margin: 0 0 20px; }
        .qd-label{ font-size: 12px; color: var(--qd-muted); margin: 10px 0 4px; display:block; }
        .qd-input{ width:100%; height: 42px; border: 1px solid var(--qd-border); border-radius: 6px; padding: 0 12px; font-size: 14px; outline: none; transition: 0.2s; }
        .qd-input:focus{ border-color: var(--qd-blue); box-shadow: 0 0 0 3px rgba(46,133,199,.18); }
        .qd-btn-primary{ width: 100%; max-width: 200px; height: 44px; border: 0; border-radius: 6px; background: var(--qd-blue); color:#fff; font-weight: 700; cursor: pointer; display:inline-flex; align-items:center; justify-content:center; margin-top: 18px; transition: 0.3s; }
        .qd-btn-primary:disabled{ background: #a0aec0; cursor: not-allowed; }
        .qd-actions{ display:flex; justify-content:left; margin: 25px 0; }
        .hidden { display: none !important; }
        .form-step { display: none; }
        .form-step.active { display: block; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; font-weight: 600; border: 1px solid transparent; }
        .alert-error { background-color: #fde8e8; color: #c81e1e; border-color: #f8b4b4; }
        .alert-success { background-color: #defadb; color: #1e5e1a; border-color: #bcf0da; }
        .qd-social-wrap{ display: flex; flex-direction: column; gap: 10px; max-width: 340px; }
        .qd-btn-social{ width:100%; height: 44px; border-radius: 999px; border:1px solid var(--qd-border); background:#fff; display:flex; align-items:center; justify-content:center; gap:10px; font-weight: 700; cursor:pointer; }
        @media (max-width: 992px){ .qd-right{ display:none; } .qd-left{ flex:1; min-width:unset; padding: 40px 24px; } }
    </style>
</head>

<body>

<div class="qd-auth">
    <section class="qd-left">
        <div class="qd-form">
            <div id="formAlert" class="hidden"></div>

            <form id="addusers" data-endpoint="/public/addusersadd" data-method="POST">

                <div class="form-step active" id="step1">
                    <div class="qd-h1">Înregistrare 1/2</div>
                    <div class="qd-sub">Creează-ți contul QuizDigo</div>
                    <div class="qd-sub"><a href="https://quizdigo.live/public/login" style="
    font-size: 16px;
">Logarea </a></div>
                    <label class="qd-label">Numele Prenumele</label>
                    <input class="qd-input" id="val_fullname" name="fullname" type="text" placeholder="Ex: Ion Popescu" required>

                    <label class="qd-label">Email* (aici vei primi codul)</label>
                    <input class="qd-input" id="val_email" name="login" type="email" placeholder="email@exemplu.com" required>

                    <label class="qd-label">Parolă*</label>
                    <input class="qd-input" id="val_password" name="password" type="password" placeholder="Minim 8 caractere" required>

                    <label class="qd-label">Confirmă Parola*</label>
                    <input class="qd-input" id="val_confirm_password" type="password" placeholder="Repetă parola" required>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="qd-label">Telefon</label>
                            <input class="qd-input" name="contact" type="text" placeholder="+373...">
                        </div>
                        <div>
                            <label class="qd-label">Specialitatea</label>
                            <input class="qd-input" name="nikname" type="text" placeholder="Ex: Profesor">
                        </div>
                    </div>

                    <div class="qd-actions">
                        <button type="button" id="btnNextStep" class="qd-btn-primary">Next (Trimite Cod)</button>
                    </div>
                </div>

                <div class="form-step" id="step2">
                    <div class="qd-h1">Înregistrare 2/2</div>
                    <div class="qd-sub">Verifică email-ul pentru codul de acces</div>
                    <div class="qd-sub"><a href="https://quizdigo.com/public/login" style="
    font-size: 16px;
">Logarea </a></div>

                    <label class="qd-label" style="font-weight: bold; color: #2E85C7; text-align:center;">Cod de confirmare (6 cifre)</label>
                    <input class="qd-input" id="val_code" name="confirm_code" type="text" placeholder="000000" maxlength="6" style="font-size: 24px; letter-spacing: 8px; text-align: center; height: 60px;">

                    <p style="font-size: 12px; color: var(--qd-muted); margin-top: 15px; text-align: center;">
                        Am trimis un cod de securitate pe adresa ta de email. Introdu-l mai sus pentru a finaliza înregistrarea.
                    </p>

                    <div class="qd-actions" style="justify-content: center;">
                        <button type="button" class="qd-btn-primary" style="background: #5f6368; margin-right: 12px; max-width: 120px;" onclick="backToStep1()">Înapoi</button>
                        <button type="submit" class="qd-btn-primary" id="btnFinalizeaza" style="max-width: 150px;">Finalizează</button>
                    </div>
                </div>


            </form>

        </div>
        <div class="qd-social-wrap" id="social-wrap" >
            <div style="text-align: center; border-top: 1px solid #eee; margin: 20px 0; position: relative;">
                <span style="background: #fff; padding: 0 10px; position: absolute; top: -10px; left: 50%; transform: translateX(-50%); color: #ccc; font-size: 12px;">SAU</span>
            </div>
            <button type="button" class="qd-btn-social" id="btnFacebook" >
                <span style="width:18px;height:18px;border-radius:50%;display:inline-block;background: conic-gradient(#ea4335 0 25%, #fbbc05 0 50%, #34a853 0 75%, #4285f4 0 100%);"></span>
                Sign in with Google
            </button>
        </div>
    </section>

    <section class="qd-right">
        <img class="qd-illustration" src="<?= getCurrentUrl(); ?>/Templates/admin/dist/ChatGPT Image 5 янв. 2026 г., 16_14_25 1.png" alt="QuizDigo">
    </section>
</div>
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>

    const alertBox = document.getElementById('formAlert');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const social = document.getElementById('social-wrap');

    function showAlert(type, msg) {
        alertBox.classList.remove('hidden', 'alert-error', 'alert-success');
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = msg;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function backToStep1() {
        step2.classList.remove('active');
        step1.classList.add('active');
        social.classList.remove('hidden');
        alertBox.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('#addusers');
        const btnNext = document.getElementById('btnNextStep');

        // --- PASUL 1: TRIMITE DATE + CERERE COD ---
        btnNext.addEventListener('click', async () => {
            const data = {
                type_product: 'register_step1',
                fullname: document.getElementById('val_fullname').value,
                login: document.getElementById('val_email').value,
                password: document.getElementById('val_password').value,
                contact: form.querySelector('[name="contact"]').value,
                nikname: form.querySelector('[name="nikname"]').value
            };

            if (!data.fullname || !data.login || !data.password) {
                showAlert('error', 'Numele, Email-ul și Parola sunt obligatorii!');
                return;
            }

            if (data.password !== document.getElementById('val_confirm_password').value) {
                showAlert('error', 'Parolele nu coincid!');
                return;
            }

            btnNext.disabled = true;
            btnNext.textContent = 'Se trimite...';

            try {
                const res = await fetch(form.dataset.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                    credentials: 'include' // <--- ADAUGĂ ACEASTA (esențial pentru sesiune)
                });

                const raw = await res.text();
                const json = JSON.parse(raw.match(/\{.*\}/s)[0]);

                if (json.success) {
                    showAlert('success', 'Codul a fost trimis!');
                    step1.classList.remove('active');
                    step2.classList.add('active');
                    social.classList.add('hidden');
                } else {
                    showAlert('error', json.message || 'Eroare la Pasul 1');
                }
            } catch (err) {
                showAlert('error', 'Eroare conexiune server la Pasul 1');
            } finally {
                btnNext.disabled = false;
                btnNext.textContent = 'Next (Trimite Cod)';
            }
        });

        // --- PASUL 2: FINALIZARE + VERIFICARE COD ---
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btnFinal = document.getElementById('btnFinalizeaza');
            const data = {
                type_product: 'register_finalize',
                login: document.getElementById('val_email').value,
                confirm_code: document.getElementById('val_code').value,
                password: document.getElementById('val_password').value
            };

            if (data.confirm_code.length < 6) {
                showAlert('error', 'Introdu codul de 6 cifre!');
                return;
            }

            btnFinal.disabled = true;
            btnFinal.textContent = 'Verificare...';

            try {
                const res = await fetch(form.dataset.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                    credentials: 'include' // <--- ADAUGĂ ACEASTA (esențial pentru sesiune)
                });

                const raw = await res.text();
                const json = JSON.parse(raw.match(/\{.*\}/s)[0]);

                if (json.success) {
                    showAlert('success', 'Cont activat! Redirecționare...');
                    setTimeout(() => window.location.href = '/public/homepages', 1500);
                } else {
                    showAlert('error', json.message || 'Cod incorect.');
                }
            } catch (err) {
                showAlert('error', 'Eroare la procesarea finală.');
            } finally {
                btnFinal.disabled = false;
                btnFinal.textContent = 'Finalizează';
            }
        });
        document.getElementById('btnFacebook')?.addEventListener('click', () => {
            if (!googleTokenClient) {
                showAlert('error', 'Google client nu e inițializat încă. Reîncarcă pagina.');
                return;
            }
            googleTokenClient.requestAccessToken();
        });
    });
    let googleTokenClient = null;

    // 1) Inițializează clientul după ce Google library e încărcată
    window.addEventListener('load', () => {
        if (!window.google || !google.accounts || !google.accounts.oauth2) {
            console.error('Google GSI nu s-a încărcat.');
            return;
        }

        googleTokenClient = google.accounts.oauth2.initTokenClient({
            client_id: '235448384789-q7f6panjassagne069edqinr3qt4054j.apps.googleusercontent.com',
            scope: 'openid email profile',
            callback: async (tokenResponse) => {

                if (!tokenResponse.access_token) {
                    showAlert('error', 'Google login error');
                    return;
                }

                const res = await fetch('/reg.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    credentials:'include',
                    body: JSON.stringify({
                        provider:'google',
                        access_token: tokenResponse.access_token
                    })
                });

                const json = await res.json();

                if(json.success){
                   console.log(json.redirect);
                    window.location = json.redirect;
                }

            }
        });
    });

</script>
</body>
</html>