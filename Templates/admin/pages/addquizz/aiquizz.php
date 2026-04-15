<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /public/login');
    exit;
}
?>


<style>
    :root{
        --bg:#f6f8fc;
        --card:#ffffff;
        --text:#191b23;
        --muted:#6b7280;
        --line:#e5e7eb;
        --primary:#1267F2;
        --primary2:#962BFF;
        --orange:#eb670f;
        --shadow:0 24px 60px rgba(15,23,42,.12);
        --radius:24px;
    }

    *{box-sizing:border-box}
    .aiq-body{
        margin:0;
        font-family:Inter,Arial,sans-serif;
        background:linear-gradient(180deg,#f7f9ff 0%,#eef4ff 100%);
        color:var(--text);

    }



    .dialog{
        background:var(--card);
        border-radius:32px;
        box-shadow:var(--shadow);
        overflow:hidden;
        border:1px solid #eef2ff;
    }

    .top{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:16px;
        padding:22px 24px;
        border-bottom:1px solid var(--line);
    }

    .title{
        display:flex;
        align-items:center;
        gap:12px;
        font-size:30px;
        font-weight:800;
        flex-wrap:wrap;
    }

    .tag{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:8px 12px;
        border-radius:999px;
        background:#f5f3ff;
        color:#4c1d95;
        font-size:13px;
        font-weight:700;
    }

    .close-x{
        border:none;
        background:#fff;
        width:42px;
        height:42px;
        border-radius:12px;
        cursor:pointer;
        font-size:20px;
        box-shadow:0 6px 18px rgba(0,0,0,.08);
    }

    .main{
        display:grid;
        grid-template-columns:minmax(0,1.2fr) 360px;
        gap:0;
    }

    .left{
        padding:26px;
        border-right:1px solid var(--line);
    }

    .right{
        padding:26px;
        background:#fafbff;
    }

    .source-row{
        display:grid;
        grid-template-columns:180px 1fr auto;
        gap:14px;
        align-items:start;
    }

    .select,
    .textarea,
    .input,
    .file{
        width:100%;
        border:1px solid #dbe2f0;
        border-radius:16px;
        background:#fff;
        padding:14px 16px;
        font-size:15px;
        outline:none;
    }

    .textarea{
        min-height:120px;
        resize:vertical;
    }

    .generate-btn{
        border:none;
        border-radius:16px;
        padding:14px 18px;
        background:linear-gradient(135deg,var(--primary),var(--primary2));
        color:#fff;
        font-weight:800;
        cursor:pointer;
        min-width:140px;
        box-shadow:0 14px 34px rgba(18,103,242,.28);
    }

    .generate-btn:disabled{
        opacity:.6;
        cursor:not-allowed;
    }

    .examples{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:18px;
    }

    .chip{
        border:none;
        cursor:pointer;
        padding:10px 14px;
        border-radius:999px;
        background:#fff;
        border:1px solid #e6eaf2;
        font-weight:700;
        color:#3f4b63;
    }

    .section-title{
        font-size:24px;
        font-weight:800;
        margin:0 0 18px;
        display:flex;
        align-items:center;
        gap:10px;
    }

    .other-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
        margin-top:24px;
    }

    .other-card{
        border:1px solid #e9edf5;
        border-radius:20px;
        padding:18px;
        background:#fff;
        cursor:pointer;
        transition:.2s ease;
    }

    .other-card:hover{
        transform:translateY(-2px);
        box-shadow:0 12px 24px rgba(0,0,0,.06);
    }

    .other-card h4{
        margin:0 0 6px;
        font-size:18px;
    }

    .other-card p{
        margin:0;
        color:var(--muted);
        font-size:14px;
        line-height:1.45;
    }

    .field{
        margin-bottom:18px;
    }

    .field label{
        display:block;
        margin-bottom:8px;
        font-size:14px;
        font-weight:700;
        color:#374151;
    }

    .helper{
        margin-top:14px;
        border-radius:18px;
        padding:14px 16px;
        background:#fff7ed;
        color:#9a3412;
        font-size:13px;
        font-weight:700;
        border:1px solid #fed7aa;
        line-height:1.5;
    }

    .status{
        margin-top:16px;
        padding:14px 16px;
        border-radius:16px;
        display:none;
        font-weight:700;
        font-size:14px;
    }

    .status.ok{
        display:block;
        background:#ecfdf5;
        color:#166534;
        border:1px solid #bbf7d0;
    }

    .status.err{
        display:block;
        background:#fef2f2;
        color:#991b1b;
        border:1px solid #fecaca;
    }

    .footer-actions{
        margin-top:18px;
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }

    .ghost-btn{
        border:none;
        border-radius:14px;
        padding:12px 16px;
        background:#eef2ff;
        color:#3730a3;
        font-weight:800;
        cursor:pointer;
    }

    .loading-box{
        margin-top:18px;
        display:none;
        gap:12px;
        align-items:center;
        color:#334155;
        font-weight:700;
    }

    .loading-box.show{
        display:flex;
    }

    .spinner{
        width:18px;
        height:18px;
        border:3px solid #dbeafe;
        border-top-color:#2563eb;
        border-radius:50%;
        animation:spin .8s linear infinite;
    }

    @keyframes spin{
        to{transform:rotate(360deg)}
    }

    @media (max-width: 1100px){
        .main{
            grid-template-columns:1fr;
        }
        .left{
            border-right:none;
            border-bottom:1px solid var(--line);
        }
    }

    @media (max-width: 720px){
        .source-row{
            grid-template-columns:1fr;
        }
        .other-grid{
            grid-template-columns:1fr;
        }
        .title{
            font-size:24px;
        }
    }
</style>

<div class="box_glogal">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>
    <div class="aiq-body">
        <div class="wrap">
            <div class="dialog">
                <div class="top">
                    <div class="title">
                        <span>Generează</span>
                        <span class="tag">⚡ Asistat de AI</span>
                    </div>
                    <button type="button" class="close-x" onclick="window.location.href='/public/addquizz'">✕</button>
                </div>

                <div class="main">
                    <div class="left">
                        <form id="aiQuizForm" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="generate_ai_quiz">

                            <div class="source-row">
                                <div>
                                    <select class="select" name="ai_source" id="ai_source">
                                        <option value="topic">Subiect</option>
                                        <option value="file">PDF</option>
                                        <option value="url">Link URL</option>
                                        <option value="wikipedia">Wikipedia</option>
                                        <option value="kahoot">Textele mele</option>
                                    </select>
                                </div>

                                <div id="inputWrap">
                                <textarea
                                        class="textarea"
                                        name="ai_input"
                                        id="ai_input"
                                        placeholder="Introdu subiectul sau lipește textul"
                                        maxlength="4000"
                                ></textarea>

                                    <input
                                            class="file"
                                            type="file"
                                            name="pdf_file"
                                            id="pdf_file"
                                            accept=".pdf"
                                            style="display:none;margin-top:10px;"
                                    >
                                </div>

                                <div>
                                    <button type="submit" class="generate-btn" id="submitBtn">Generează</button>
                                </div>
                            </div>
                        </form>

                        <div class="examples">
                            <button class="chip" type="button" data-example="Mitologia greacă">Mitologia greacă</button>
                            <button class="chip" type="button" data-example="Animale sălbatice">Animale sălbatice</button>
                            <button class="chip" type="button" data-example="Sărbători de iarnă">Sărbători de iarnă</button>
                            <button class="chip" type="button" data-example="Istoria Moldovei">Istoria Moldovei</button>
                            <button class="chip" type="button" data-example="Europa și capitalele">Europa și capitalele</button>
                            <button class="chip" type="button" data-example="Corpul uman">Corpul uman</button>
                            <button class="chip" type="button" data-example="Sistemul solar">Sistemul solar</button>
                            <button class="chip" type="button" data-example="Invenții celebre">Invenții celebre</button>
                            <button class="chip" type="button" data-example="Filme din anii 90">Filme din anii 90</button>
                            <button class="chip" type="button" data-example="Sportivi celebri">Sportivi celebri</button>
                        </div>

                        <div class="section-title" style="margin-top:28px;">✨ Alte moduri de generare</div>

                        <div class="other-grid">
                            <button class="other-card" type="button" data-source-switch="file">
                                <h4>PDF</h4>
                                <p>Generează quiz dintr-un document PDF.</p>
                            </button>

                            <button class="other-card" type="button" data-source-switch="url">
                                <h4>Link URL</h4>
                                <p>Generează quiz dintr-o pagină web.</p>
                            </button>

                            <button class="other-card" type="button" data-source-switch="wikipedia">
                                <h4>Wikipedia</h4>
                                <p>Generează quiz dintr-un articol Wikipedia.</p>
                            </button>

                            <button class="other-card" type="button" data-source-switch="kahoot">
                                <h4>Textele mele</h4>
                                <p>Lipește textul tău și AI-ul face quizul.</p>
                            </button>
                        </div>

                        <div id="statusBox" class="status"></div>

                        <div id="loadingBox" class="loading-box">
                            <div class="spinner"></div>
                            <div>Se generează quizul cu AI. Te rog așteaptă...</div>
                        </div>

                        <div class="footer-actions">
                            <button type="button" class="ghost-btn" id="clearBtn">Curăță</button>
                            <button type="button" class="ghost-btn" onclick="window.location.href='/public/librari'">Înapoi la librărie</button>
                        </div>
                    </div>

                    <div class="right">
                        <div class="field">
                            <label for="language">Limba</label>
                            <select class="select" form="aiQuizForm" name="language" id="language">
                                <option value="Română" selected>Română</option>
                                <option value="English">Engleză</option>
                                <option value="Russian">Rusă</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="skill_level">Nivelul</label>
                            <select class="select" form="aiQuizForm" name="skill_level" id="skill_level">
                                <option value="Beginner">Începător</option>
                                <option value="Intermediate" selected>Intermediar</option>
                                <option value="Advanced">Avansat</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="tone">Tonul</label>
                            <select class="select" form="aiQuizForm" name="tone" id="tone">
                                <option value="Conversational" selected>Conversațional</option>
                                <option value="Professional">Profesional</option>
                                <option value="Friendly">Prietenos</option>
                                <option value="Playful">Jucăuș</option>
                                <option value="Academic">Academic</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="question_count">Lungimea quizului</label>
                            <select class="select" form="aiQuizForm" name="question_count" id="question_count">
                                <option value="Around 5 questions">Aproximativ 5 întrebări</option>
                                <option value="Around 10 questions" selected>Aproximativ 10 întrebări</option>
                                <option value="Around 15 questions">Aproximativ 15 întrebări</option>
                                <option value="Around 20 questions">Aproximativ 20 întrebări</option>
                            </select>
                        </div>

                        <div class="helper">
                            Pentru sursa <strong>PDF</strong>, fișierul trebuie să conțină text extractibil.<br>
                            Dacă serverul nu are <strong>pdftotext</strong>, extragerea din PDF nu va funcționa.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    (function () {
        const sourceSelect = document.getElementById('ai_source');
        const textArea = document.getElementById('ai_input');
        const pdfInput = document.getElementById('pdf_file');
        const form = document.getElementById('aiQuizForm');
        const submitBtn = document.getElementById('submitBtn');
        const statusBox = document.getElementById('statusBox');
        const loadingBox = document.getElementById('loadingBox');
        const clearBtn = document.getElementById('clearBtn');

        function setStatus(message, type) {
            statusBox.className = 'status ' + type;
            statusBox.textContent = message;
        }

        function clearStatus() {
            statusBox.className = 'status';
            statusBox.textContent = '';
        }

        function showLoading(show) {
            if (show) {
                loadingBox.classList.add('show');
            } else {
                loadingBox.classList.remove('show');
            }
        }

        function updateSourceUI() {
            const source = sourceSelect.value;

            if (source === 'file') {
                textArea.style.display = 'none';
                textArea.removeAttribute('required');
                pdfInput.style.display = 'block';
            } else {
                textArea.style.display = 'block';
                textArea.setAttribute('required', 'required');
                pdfInput.style.display = 'none';
            }

            if (source === 'topic') {
                textArea.placeholder = 'Introdu subiectul quizului';
            } else if (source === 'url') {
                textArea.placeholder = 'https://exemplu.com/articol';
            } else if (source === 'wikipedia') {
                textArea.placeholder = 'Ex: Sistemul solar';
            } else if (source === 'kahoot') {
                textArea.placeholder = 'Lipește aici textul tău';
            }
        }

        sourceSelect.addEventListener('change', updateSourceUI);
        updateSourceUI();

        document.querySelectorAll('[data-example]').forEach(btn => {
            btn.addEventListener('click', function () {
                sourceSelect.value = 'topic';
                updateSourceUI();
                textArea.value = this.getAttribute('data-example') || '';
                textArea.focus();
            });
        });

        document.querySelectorAll('[data-source-switch]').forEach(btn => {
            btn.addEventListener('click', function () {
                sourceSelect.value = this.getAttribute('data-source-switch');
                updateSourceUI();
                if (sourceSelect.value !== 'file') {
                    textArea.focus();
                }
            });
        });

        clearBtn.addEventListener('click', function () {
            textArea.value = '';
            pdfInput.value = '';
            clearStatus();
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            clearStatus();
            showLoading(true);
            submitBtn.disabled = true;
            submitBtn.textContent = 'Se generează...';

            try {
                const formData = new FormData(form);

                const response = await fetch('https://quizdigo.com/aigenerator.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'A apărut o eroare la generare.');
                }

                setStatus('Quiz generat cu succes. Se deschide editorul...', 'ok');

                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                }
            } catch (err) {
                console.error(err);
                setStatus(err.message || 'A apărut o eroare necunoscută.', 'err');
            } finally {
                showLoading(false);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Generează';
            }
        });
    })();
</script>