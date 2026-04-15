<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentQuizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<link href="<?echo getCurrentUrl()?>/Templates/admin/dist/css/builder.css" rel="stylesheet" type="text/css">
<style>

    .slide-tools{
        position:absolute;
        top:6px;
        right:6px;
        display:flex;
        gap:6px;
        z-index:3;
    }
    .answer-card-clean.answer-card-selected {
        border: 3px solid #53ea60;
        box-shadow: 0 0 0 4px rgba(83, 234, 96, 0.18), 0 10px 24px rgba(83, 234, 96, 0.28);
    }
    .slide-tool-btn{
        width:24px;
        height:24px;
        border:none;
        border-radius:8px;
        background:rgba(255,255,255,.92);
        color:#111;
        cursor:pointer;
        font-weight:900;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .slide-tool-btn-delete{
        background:#ff4444;
        color:#fff;
    }

    .media-library-grid{
        display:grid;
        grid-template-columns:repeat(2, 1fr);
        gap:12px;
    }

    .media-library-item{
        border:2px solid #e2e8f0;
        background:#fff;
        border-radius:14px;
        overflow:hidden;
        cursor:pointer;
        padding:0;
        text-align:left;
    }

    .media-library-item:hover{
        border-color:#2E85C7;
        transform:translateY(-2px);
        transition:.18s ease;
    }

    .media-library-item img{
        width:100%;
        height:120px;
        object-fit:cover;
        display:block;
    }

    .media-library-item span{
        display:block;
        padding:10px;
        font-weight:800;
        font-size:13px;
        color:#334155;
    }

    @media (max-width: 640px){
        .media-library-grid{
            grid-template-columns:1fr;
        }
    }
    .purple { background:#9b59b6; }
    .orange { background:#ff7f50; }
    .green { background:#2ecc71; }
    .yellow { background:#f1c40f; color:#111; }
    .blue { background:#3498db; }
    .red { background:#e74c3c; }
    .answer-purple { background:#9b59b6; }
    .answer-orange { background:#ff7f50; }
    .answer-green { background:#2ecc71; }
    .answer-yellow { background:#f1c40f; color:#111; }
    .answer-blue { background:#3498db; }
    .answer-red { background:#e74c3c; }
    .slide-type-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:14px;
    }

    .slide-type-card{
        border:2px solid #e2e8f0;
        background:#fff;
        border-radius:16px;
        padding:18px;
        cursor:pointer;
        text-align:left;
        transition:.18s ease;
    }

    .slide-type-card:hover{
        border-color:#2E85C7;
        transform:translateY(-2px);
        box-shadow:0 10px 22px rgba(0,0,0,.08);
    }

    .slide-type-icon{
        font-size:28px;
        margin-bottom:10px;
    }

    .slide-type-title{
        font-weight:900;
        font-size:16px;
        color:#0A5084;
        margin-bottom:8px;
    }

    .slide-type-text{
        font-size:13px;
        line-height:1.45;
        color:#64748b;
    }

    @media (max-width: 640px){
        .slide-type-grid{
            grid-template-columns:1fr;
        }
    }
    .slide{
        position:relative;
        height:76px;
        border-radius:12px;
        overflow:hidden;
        cursor:pointer;
        border:2px solid transparent;
        box-shadow:0 3px 10px rgba(0,0,0,.06);
        background:#eef2f7;
    }

    .slide.active{
        border-color:var(--blue);
    }

    .slide .bg{
        position:absolute;
        inset:0;
        background-size:cover;
        background-position:center;
        filter:saturate(1.05);
    }

    .thumb-media{
        position:absolute;
        left:8px;
        top:8px;
        width:42px;
        height:28px;
        border-radius:8px;
        overflow:hidden;
        border:2px solid rgba(255,255,255,.9);
        background:#fff;
        z-index:2;
    }

    .thumb-media img{
        width:100%;
        height:100%;
        object-fit:cover;
        display:block;
    }

    .thumb-overlay{
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        padding:6px 8px;
        background:linear-gradient(to top, rgba(0,0,0,.65), rgba(0,0,0,0));
        z-index:2;
    }

    .thumb-type{
        font-size:10px;
        font-weight:900;
        color:#fff;
        text-transform:uppercase;
        line-height:1.1;
    }

    .thumb-title{
        font-size:11px;
        font-weight:800;
        color:#fff;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        line-height:1.2;
        margin-top:2px;
    }

    .slide .num{
        position:absolute;
        top:6px;
        left:8px;
        color:#fff;
        font-weight:900;
        font-size:10px;
        text-shadow:0 2px 8px rgba(0,0,0,.35);
        z-index:3;
    }

    .media-dynamic-wrap{
        width:min(900px,95%);
        background:#fff;
        border-radius:16px;
        box-shadow:0 10px 24px rgba(0,0,0,.12);
        padding:14px;
    }

    .media-dynamic-text{
        width:100%;
    }

    .media-dynamic-actions{
        margin-top:12px;
        display:flex;
        justify-content:flex-start;
    }

    .media-link-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:10px 16px;
        border-radius:10px;
        background:#2E85C7;
        color:#fff;
        text-decoration:none;
        font-weight:800;
        border:none;
        cursor:pointer;
    }

    /* layout variants */
    .media-layout-classic{
        max-width:900px;
    }

    .media-layout-big{
        max-width:1040px;
    }

    .media-layout-title-text{
        max-width:720px;
    }

    /* text position variants */
    .media-text-bottom{
        margin-top:0;
    }

    .media-text-left{
        margin-right:auto;
        width:min(520px, 90%);
    }

    .media-text-right{
        margin-left:auto;
        width:min(520px, 90%);
    }
    .media-tabs{
        display:flex;
        gap:8px;
        margin-bottom:14px;
        flex-wrap:wrap;
    }

    .media-tab{
        border:1px solid #cbd5e1;
        background:#fff;
        color:#334155;
        font-weight:700;
        border-radius:8px;
        padding:8px 12px;
        cursor:pointer;
    }

    .media-tab.is-active{
        background:#2E85C7;
        color:#fff;
        border-color:#2E85C7;
    }

    .media-tab-panels{
        width:100%;
    }

    .media-tab-panel{
        display:none;
    }

    .media-tab-panel.is-active{
        display:block;
    }

    .media-mode-grid{
        display:none;
    }

    .answers-grid-clean {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        align-items: stretch;
    }

    .answer-card-clean {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 84px;
        border-radius: 22px;
        padding: 14px 18px;
        color: #fff;
        box-sizing: border-box;
    }

    .answer-left-zone {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }

    .answer-icon-box {
        width: 42px;
        min-width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .answer-content-zone {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }

    .answer-text-input-clean {
        border: none;
        outline: none;
        background: transparent;
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        width: 100%;
        min-width: 0;
    }

    .answer-text-input-clean::placeholder {
        color: rgba(255, 255, 255, 0.9);
    }

    .answer-image-thumb {
        width: 110px;
        min-width: 110px;
        height: 56px;
        border-radius: 10px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        overflow: hidden;
    }

    .answer-image-icon-button {
        border: none;
        outline: none;
        background: transparent;
        color: #fff;
        cursor: pointer;
        width: 34px;
        min-width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.92;
        padding: 0;
    }

    .answer-image-icon-button:hover {
        opacity: 1;
        transform: scale(1.06);
    }

    .answer-image-icon-button-symbol {
        font-size: 20px;
        line-height: 1;
    }

    .answer-correct-circle {
        width: 48px;
        min-width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.9);
        box-sizing: border-box;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .answer-correct-circle.active {
        background: rgba(255, 255, 255, 0.95);
    }

    .answer-correct-circle:hover {
        transform: scale(1.06);
    }

    .answer-card-clean.purple,
    .answer-card-clean.orange,
    .answer-card-clean.green,
    .answer-card-clean.yellow,
    .answer-card-clean.blue,
    .answer-card-clean.red {
        color: #fff;
    }

    .answer-card-clean.yellow .answer-text-input-clean,
    .answer-card-clean.yellow .answer-image-icon-button {
        color: #2b2b2b;
    }

    .answer-card-clean.yellow .answer-text-input-clean::placeholder {
        color: rgba(43, 43, 43, 0.8);
    }
    .answer-image-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .answer-image-thumb {
        width: 140px;
        height: 70px;
        border-radius: 12px;
        background-size: cover;
        background-position: center;
    }

    .answer-image-delete {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: none;
        background: #ff3b3b;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .answer-image-delete:hover {
        background: #ff0000;
        transform: scale(1.1);
    }
    @media (max-width: 900px) {
        .answers-grid-clean {
            grid-template-columns: 1fr;
        }
    }

</style>
<main class="workspace" style="width: 100%;">
    <!-- LEFT: SLIDES -->
    <aside class="slides">
        <button class="add-slide" data-action="add-slide" type="button">+</button>
        <div class="slides-list" id="slides-list"></div>
    </aside>

    <!-- CENTER: CANVAS -->
    <section class="canvas">
        <div class="card" id="card-canvas">
            <div class="title-box">
                <input id="question-title" class="input-title" type="text" placeholder="Add question title..." />
                <div class="input-counter">
                    <span id="question-title-count">0/130</span>
                </div>
            </div>

            <div class="media-center" id="media-center" data-action="open-media" data-media="center">
                <div class="media-center-inner" id="media-center-inner">
                    <div class="media-icon">+</div>
                    <div class="media-hint">Click pentru imagine/video (URL sau upload)</div>
                </div>
            </div>

            <div class="answers" id="answers"></div>
        </div>
    </section>

    <!-- RIGHT: SETTINGS -->
    <aside class="settings">
        <!-- THEME -->
        <div class="settings-card" data-action="open-theme-popup">
            <div class="settings-title">Alege o temă</div>
            <div class="settings-preview">
                <div class="theme-preview" id="theme-preview"></div>
                <div class="settings-sub" id="theme-name">standart</div>
            </div>
        </div>

        <!-- QUESTION SETTINGS -->
        <div class="settings-card">
            <div class="settings-title">Tip întrebare</div>
            <button class="select-like" data-action="open-popup" data-popup="type" type="button">
                <img id="type-icon" alt="" />
                <span id="type-name">Quiz (Grilă)</span>
            </button>

            <div class="divider"></div>

            <!-- DYNAMIC SETTINGS FROM JSON -->
            <div class="settings-title">Setări tip întrebare</div>
            <div id="type-settings-dynamic" class="type-settings-dynamic">
                <div class="hint">
                    Alege un tip de întrebare pentru a vedea setările disponibile din JSON.
                </div>
            </div>

            <div class="divider"></div>

            <!-- TYPE RULES / META -->
            <div class="settings-title">Reguli tip</div>
            <div id="type-rules-info" class="type-rules-info">
                <div class="hint">
                    Aici vor apărea limitele, media permisă, scorul și alte reguli ale tipului selectat.
                </div>
            </div>

            <div class="divider"></div>

            <div class="settings-title">Scor si validare</div>
            <div id="answers-meta-info" class="type-rules-info">
                <div class="hint">
                    Aici apar scorul, bonusul, penalizarea si butoanele de confirmare / reset.
                </div>
            </div>

            <div class="divider"></div>

            <!-- MULTIPLE ANSWERS -->
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:10px">
                <div style="font-weight:800;color:#475569;font-size:12px">Multiple answers</div>
                <label class="switch" title="Permite mai multe răspunsuri corecte (doar pentru Quiz)">
                    <input type="checkbox" id="select-multiple">
                    <span class="switch-ui"></span>
                </label>
            </div>

            <div class="divider"></div>

            <!-- TIME LIMIT -->
            <div class="settings-title">Limită de timp</div>
            <div class="time-grid" id="time-grid">
                <label class="time-item"><input type="radio" name="time" value="5s"><span>5s</span></label>
                <label class="time-item"><input type="radio" name="time" value="10s" checked><span>10s</span></label>
                <label class="time-item"><input type="radio" name="time" value="20s"><span>20s</span></label>
                <label class="time-item"><input type="radio" name="time" value="30s"><span>30s</span></label>
                <label class="time-item"><input type="radio" name="time" value="1m"><span>1m</span></label>
                <label class="time-item"><input type="radio" name="time" value="90s"><span>1m 30s</span></label>
                <label class="time-item"><input type="radio" name="time" value="2m"><span>2m</span></label>
                <label class="time-item"><input type="radio" name="time" value="4m"><span>4m</span></label>
                <label class="time-item"><input type="radio" name="time" value="5m"><span>5m</span></label>
            </div>

            <div class="divider"></div>

            <!-- BONUS SPEED -->
            <div class="settings-title">Bonus viteză</div>
            <div class="bonus-row">
                <label class="switch">
                    <input type="checkbox" id="bonus-toggle" checked />
                    <span class="switch-ui"></span>
                </label>
                <span class="badge" id="bonus-status">ON</span>
                <span class="badge gray" id="bonus-time-label">5s</span>
            </div>

            <input id="bonus-range" class="range" type="range" min="1" max="60" value="5" />
            <div class="hint" id="bonus-hint">(+ puncte pentru răspuns rapid)</div>

            <div class="divider"></div>

            <!-- MUSIC -->
            <div class="settings-title">Muzică</div>
            <button class="select-like" data-action="open-music" type="button">
                <img id="music-icon" alt="" src="https://quizdigo.live/Musical Note.png">
                <span id="music-name">Fără muzică</span>
            </button>

            <div class="divider"></div>

            <!-- EXTRA AUDIO SETTINGS -->
            <div class="settings-title">Sunete răspuns</div>
            <div id="audio-settings-dynamic" class="audio-settings-dynamic">
                <div class="hint">
                    Aici pot fi afișate dinamic opțiunile pentru corect / greșit / gong, conform JSON.
                </div>
            </div>

            <div class="divider"></div>

            <!-- ACTIONS -->
            <div class="topbar-right" id="top-actions" style="margin-top:26px;">
                <button class="btn btn-green" data-action="preview" type="button" style="width:50%;">Preview</button>
                <button class="btn btn-blue" data-action="save" type="button" style="width:45%;">Save</button>
                <button class="btn btn-orange" data-action="open-quiz-settings" type="button" style="width:100%;">Adaugă Denumirea</button>
            </div>
        </div>
    </aside>
</main>

<!-- POPUP (THEME / TYPE / MEDIA) -->
<div class="modal" id="popup" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="popup-title">
        <div class="modal-head">
            <div class="modal-title" id="popup-title">Popup</div>
            <button class="x" type="button" data-popup-close>✕</button>
        </div>

        <div class="modal-body" id="popup-body">
            <!-- content dinamic -->
        </div>

        <div class="modal-foot" id="popup-foot">
            <button class="btn btn-gray" type="button" data-popup-close>Închide</button>
        </div>
    </div>
</div>
<!---
<button type="button" class="btn btn-blue" data-action="open-test-popup">
    Deschide Popup
</button> -->

<script type="module" src="<?=getCurrentUrl();?>/Templates/admin/dist/js/src/bootstrap.js"></script>
