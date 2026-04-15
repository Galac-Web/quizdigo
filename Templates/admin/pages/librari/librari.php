<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /public/login');
    exit;
}

use Evasystem\Controllers\Librari\Librari;
use Evasystem\Controllers\Librari\LibrariService;

$id_user = (int)$_SESSION['user_id'];

$librariService = new LibrariService();
$librariController = new Librari($librariService);

$folderRandomnId = isset($_GET['folder']) ? (string)$_GET['folder'] : null;
$folderIdFromGet = isset($_GET['id']) ? (int)$_GET['id'] : null;
$view = isset($_GET['view']) ? (string)$_GET['view'] : 'all';

$data = $librariController->index($id_user, $folderRandomnId, $view);

$stats = $data['stats'] ?? [];
$folders = $data['folders'] ?? [];
$quizzes = $data['quizzes'] ?? [];
$activeFolder = $data['active_folder'] ?? null;

$total_quizzes = (int)($stats['total_quizzes'] ?? 0);
$limit = (int)($stats['limit_quizzes'] ?? 200);
$procent = (float)($stats['percent'] ?? 0);

/**
 * Compatibilitate cu layoutul vechi:
 * dacă vine ?id=12&name=Folder, atunci marcăm folderul activ după id
 */
$activeFolderId = null;
if ($activeFolder && isset($activeFolder['id'])) {
    $activeFolderId = (int)$activeFolder['id'];
} elseif ($folderIdFromGet) {
    $activeFolderId = $folderIdFromGet;
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<style>
    .bgbox{
        background: #12a356ad;
        border-radius: 20px;
    }
    .actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        padding: 40px;
        font-family: sans-serif;
    }

    .action {
        position: relative; /* Esențial pentru poziționarea butonului */
        border-radius: 25px;
        padding: 30px;
        min-height: 160px;
        color: white;
        display: flex;
        align-items: center;
        overflow: visible; /* Permite butonului să iasă în afară */
        /* Fundal cu valuri discrete (ca în imagine) */
        background-image: radial-gradient(circle at top left, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    /* Culorile Cardurilor */
    .a-green { background-color: #66bb6a; }
    .a-purple { background-color: #ab47bc; }
    .a-orange { background-color: #ff7043; }

    .left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .icon-box img {
        width: 80px;
        height: auto;
    }

    .txt .big {
        font-size: 42px;
        font-weight: 800;
        line-height: 1;
    }

    .txt .sub {
        font-size: 20px;
        font-weight: 600;
        opacity: 0.9;
        margin-top: 5px;
    }

    /* Stilul Butoanelor */
    .btn-action {
        position: absolute;
        bottom: -15px; /* Îl scoate în afară jos */
        right: 20px;
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        color: white;
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: transform 0.2s;
        z-index: 10;
    }

    .btn-action:hover {
        transform: translateY(-3px);
    }

    /* Culorile Butoanelor */
    .b-orange { background-color: #ff9800; }
    .b-red { background-color: #b71c1c; }
    .b-blue { background-color: #2196f3; }
    /* Fundalul modalului (Overlay) */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.7); /* Albastru închis transparent */
        backdrop-filter: blur(8px); /* Efect de sticlă înghețată */
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }

    /* Containerul Popup-ului */
    .modal-content {
        background: #ffffff;
        width: 90%;
        max-width: 420px;
        padding: 35px;
        border-radius: 30px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        transform: translateY(0);
        animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    /* Titlul */
    #popup-title {
        font-family: 'Inter', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: #0A5084;
        text-align: center;
        margin-bottom: 25px;
        letter-spacing: -0.5px;
    }

    /* Input-ul pentru numele mapei */
    .settings-input {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #eef2f7;
        border-radius: 15px;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
        outline: none;
        background: #f8fafc;
    }

    .settings-input:focus {
        border-color: #2E85C7;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(46, 133, 199, 0.1);
    }

    /* Butoanele */
    .btn-done, .btn-cancel {
        padding: 14px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        border: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-done {
        background: #2E85C7;
        color: white;
        box-shadow: 0 4px 12px rgba(46, 133, 199, 0.3);
    }

    .btn-done:hover {
        background: #1a6399;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(46, 133, 199, 0.4);
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #64748b;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #475569;
    }

    /* Animații */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .btn-add-folder{
        border: none;
        background: transparent;
        /* color: white; */
        /* padding: 12px 25px; */
        border-radius: 12px;
        text-decoration: none;
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
        /* box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); */
        transition: transform 0.2s;
        cursor: pointer;
    }
    .deleteFolder{
        color: red;
        font-weight: bold;
        font-size: 21px;
        position: absolute;
        margin-top: -24px;
        margin-left: -9px;
        cursor: pointer;
    }
    .edit-folder-btn {
        color: #fff;
        background: rgba(255,255,255,0.2);
        padding: 5px 8px;
        border-radius: 8px;
        margin-right: 5px;
        font-size: 12px;
        transition: 0.2s;
        margin-top: 13px;
    }

    .edit-folder-btn:hover {
        background: #2E85C7;
    }
    .folder{
        cursor: pointer;
    }

    .folder-actions {
        display: flex;
        gap: 5px;
        margin-left: 63px;
    }
    /* Containerul pentru butoane */
    .folder-actions {

        display: flex;
        gap: 8px;
        opacity: 0; /* Ascunse până la hover pe folder */
        transition: all 0.3s ease;
        z-index: 5;
    }
    .folder_active{
        border: 2px dashed #b71c1c !important;
        background: rgb(255 120 78 / 57%) !important;
        position: relative !important;
    }

    /* Afișare la hover */
    .folder:hover .folder-actions {
        opacity: 1;
    }

    /* Stilul butoanelor rotunde */
    .edit-folder-btn, .deleteFolder {
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(4px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.3);

    }

    /* Controlul dimensiunii imaginii din interior */
    .edit-folder-btn img, .deleteFolder img {
        width: 16px;  /* Ajustează dimensiunea iconiței aici */
        height: 16px;
        object-fit: contain;
        transition: transform 0.2s ease;
    }

    /* Efecte de Hover pe butoane */
    .edit-folder-btn:hover {
        background: #ffffff;
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(46, 133, 199, 0.3);
    }

    .deleteFolder:hover {
        background: #ffeded; /* Un roșu foarte pal la fundal */
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(225, 29, 72, 0.3);
    }

    /* Mică animație pentru imagine la hover */
    .edit-folder-btn:hover img, .deleteFolder:hover img {
        transform: rotate(5deg);
    }
    .quiz-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .quiz {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid #f0f0f0;
        position: relative;
    }

    .quiz:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(46, 133, 199, 0.15);
    }

    .quiz .cover {
        height: 160px;
        width: 100%;
        background-color: #eee;
    }

    .quiz .badge-circle {
        position: absolute;
        top: 140px;
        right: 20px;
        width: 45px;
        height: 45px;
        background: #fff;
        border-radius: 50%;
        padding: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .quiz .body {
        padding: 25px 20px 20px;
    }

    .quiz .title {
        font-weight: 900;
        font-size: 18px;
        color: #0A5084;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .quiz .desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
    }

    .quiz-footer {
        padding: 10px 20px;
        border-top: 1px solid #f8fafc;
        background: #fcfcfc;
    }
    .button_joca{
        margin: 0 auto;
        text-align: center;
        margin-top: 23px;
        margin-bottom: 9px;
    }
    .button_joca a{
        display: inline-block;
        border-radius: 8px;
        padding: 8px;
        width: 127px;
        text-align: center;
        color: #ffffff;
        background: #9E0F11;
        border: 1px solid #2E85C7;
    }
    .list_datte{
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        align-items: center;
        margin-left: 0;
        padding-left: 6px;
        padding-right: 12px;
    }
    .list_datte li{
        list-style: none;
        margin-bottom: 5px;
    }

    /* Container Profil */
    .profile {
        position: relative; /* Esențial pentru poziționarea absolută a dropdown-ului */
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 15px;
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.3s ease;
        user-select: none;
    }

    .profile:hover {
        background: #f8fafc;
    }

    .avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        background-color: #e2e8f0;
    }

    .chev {
        font-size: 12px;
        color: #64748b;
        transition: transform 0.3s ease;
    }

    /* Meniul Dropdown */
    .dropdown-menu {
        position: absolute;
        top: calc(100% + 10px); /* Apare la 10px sub profil */
        right: 0;
        width: 200px;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        display: none; /* Ascuns implicit */
        flex-direction: column;
        padding: 8px;
        z-index: 9999;
        animation: dropdownFade 0.2s ease-out;
    }

    .dropdown-menu.show {
        display: flex; /* Afișat prin JS */
    }

    /* Elemente din Dropdown */
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        text-decoration: none;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
        border-radius: 10px;
        transition: background 0.2s;
    }

    .dropdown-item:hover {
        background: #f1f5f9;
        color: #1e3a8a;
    }

    .dropdown-item img {
        width: 18px;
        height: 18px;
        opacity: 0.7;
    }

    .dropdown-item.logout {
        color: #ef4444;
    }

    .dropdown-item.logout:hover {
        background: #fef2f2;
    }

    hr {
        border: 0;
        border-top: 1px solid #f1f5f9;
        margin: 6px 0;
    }

    /* Animație */
    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #move-folder-select{
        margin-top: 20px;
        width: 100%;
        background: white;
        border: 1px solid #ccc;
        padding: 9px;
        margin-bottom: 16px;
        border-radius: 13px;
    }
</style>
<!-- SIDEBAR -->


<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>


    <div class="content">

        <!-- ACTIONS -->
        <div class="actions">
            <div class="action a-green">
                <div class="left">
                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Account Male.png" alt="">
                    <div class="txt">
                        <div class="big"><?php echo (int)$total_quizzes; ?> / <?php echo (int)$limit; ?></div>
                        <div class="sub">temele mele</div>
                    </div>
                </div>
                <a href="/public/my-library" class="btn-action b-orange">DESCHIDE</a>
            </div>

            <div class="action a-purple">
                <div class="left">
                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Add.png" alt="">
                    <div class="txt">
                        <div class="big"><?php echo max(0, (int)$limit - (int)$total_quizzes); ?> libere</div>
                        <div class="sub">Crează un Quizz</div>
                    </div>
                </div>
                <a href="/public/addquizz" class="btn-action b-red">CREAZĂ</a>
            </div>

            <div class="action a-orange">
                <div class="left">
                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/AI.png" alt="">
                    <div class="txt">
                        <div class="big">AI Ready</div>
                        <div class="sub">Crează Cu AI</div>
                    </div>
                </div>
                <a href="/public/aiquizz" class="btn-action b-blue"  return false;">CREAZĂ CU AI</a>
            </div>
        </div>

        <!-- MAPS -->
        <section class="section">
            <div class="sec-head">
                <?php
                if(isset($_GET['name']) && trim($_GET['name']) !== ''){
                    $brek  = '<a href="https://quizdigo.com/public/librari" style="
    text-transform: lowercase;
    font-size: 12px;
    margin-left: 9px;
    color: #2a6aad;
"><span class="arrow">←</span> Inapoi</a>';
                }else{
                    $brek = '';
                }
                ?>
                <div class="sec-title"><?=$brek;?> Mapele Mele </div>
                <div class="sec-right">
                    <button class="btn-add-folder" onclick="openFolderModal()">
                        <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Add Folder.png" alt="">
                    </button>
                    <div class="mini-search">
                        <input type="text" id="searchFolder" placeholder="Cauta mapa..." onkeyup="filterFolders()" />
                    </div>
                </div>
            </div>

            <div class="grid-folders" id="folderGrid">
                <?php foreach ($folders as $f): ?>
                    <?php
                    $fid = (int)$f['id'];
                    $fname = (string)$f['nume_folder'];
                    $fname_class = '';

                    if ($activeFolderId !== null && $activeFolderId === $fid) {
                        $fname_class = 'folder_active';
                    }
                    ?>
                    <div class="folder" id="folder-<?php echo $fid; ?>">
                        <div class="box <?=$fname_class;?>"
                             data-randomn-id="<?php echo h((string)($f['randomn_id'] ?? '')); ?>"
                             onclick="goToFolder(<?php echo $fid; ?>, '<?php echo h($fname); ?>')">
                            <div class="folder-actions" style="display:flex;">
                                <div class="edit-folder-btn"
                                     onclick="openRenameModal(event, <?php echo $fid; ?>, '<?php echo h($fname); ?>')">
                                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/edit.png" alt="">
                                </div>
                                <div class="deleteFolder" onclick="deleteFolder(event, <?php echo $fid; ?>)">
                                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/remove.png" alt="">
                                </div>
                            </div>
                        </div>
                        <div class="name"><?php echo h($fname); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Rename modal -->
            <div id="rename-modal" class="modal-overlay" style="display:none;">
                <div class="modal-content">
                    <h3 id="popup-title">Redenumește Mapa</h3>
                    <input type="hidden" id="rename-folder-id">
                    <input type="text" id="rename-folder-name" class="settings-input" placeholder="Nume nou...">
                    <div class="d-flex gap-3 mt-4" style="margin-top: 20px;">
                        <button onclick="closeRenameModal()" class="btn-cancel w-50">Anulează</button>
                        <button onclick="saveRenameFolder()" class="btn-done w-50">Actualizează</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- QUIZZES -->
        <section class="section">
            <div class="sec-head">
                <div class="sec-title">Quizurile Mele</div>
                <div></div>
            </div>

            <div class="quiz-row">
                <?php if (empty($quizzes)): ?>
                <div class="block_conten">
                    <p class="text-muted p-4">Nu ai adăugat niciun quiz aici încă.</p>
                    <div class="button_joca">
                        <a href="https://quizdigo.com/public/librari" class="b-blue"  style="background: #2464a6;width: 100%;">Vezi toate Quizzurile</a>
                    </div>

                </div>
                <?php endif; ?>

                <?php foreach ($quizzes as $q): ?>
                    <?php
                    $qid = (int)$q['id'];

                    $content = json_decode((string)$q['continut_json'], true) ?: [];
                    $settings = $content['settings'] ?? [];

                    $coverImg = !empty($settings['themeUrl']) ? (string)$settings['themeUrl'] : 'default-cover.png';
                    $titluQuiz = !empty($settings['title']) ? (string)$settings['title'] : (string)$q['titlu'];

                    $descriere = !empty($settings['description']) ? (string)$settings['description'] : 'Nicio descriere disponibilă.';
                    $descriereShort = mb_strimwidth($descriere, 0, 100, "...");
                    $lastUpdated = !empty($q['last_updated']) ? date('d.m.Y', strtotime($q['last_updated'])) : '-';
                    ?>
                    <article class="quiz" onclick="window.location.href='desquizz?id=<?php echo $qid; ?>'">
                        <div class="cover" style="background-image:url('<?php echo h($coverImg); ?>'); background-size:cover; background-position:center;"></div>

                        <div class="badge-circle">
                            <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Mask Group.png" alt="">
                        </div>

                        <div class="body">
                            <div class="box_content" style="display:flex; flex-wrap:wrap; justify-content:space-between">
                                <div class="left_content">
                                    <div class="title"><?php echo h($titluQuiz); ?></div>
                                    <div class="desc"><?php echo h($descriereShort); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="ring_content">
                            <ul class="list_datte">
                                <li onclick="event.stopPropagation(); window.location.href='edit_quiz.php?id=<?php echo $qid; ?>'">
                                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Edit (1).png" alt=""> <span>Edit</span>
                                </li>

                                <li onclick="duplicateQuiz(event, <?php echo $qid; ?>)">
                                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Group 1000004207.png" alt=""><span>Dubliat</span>
                                </li>

                                <li onclick="deleteQuiz(event, <?php echo $qid; ?>)">
                                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Delete.png" alt=""><span>Delete</span>
                                </li>

                                <li onclick="openMoveModal(event, <?php echo $qid; ?>)">
                                    <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Move.png" alt=""><span>Move</span>
                                </li>
                            </ul>
                        </div>

                        <div class="quiz-footer">
                            <small>Ultima editare: <?php echo h($lastUpdated); ?></small>
                            <div class="button_joca">
                                <a href="https://quizdigo.com/public/desquizz?id=<?php echo $qid; ?>'" class="b-blue" >Joaca</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</main>

<!-- Create folder modal -->
<div id="folder-modal" class="modal-overlay">
    <div class="modal-content">
        <h3 id="popup-title">Crează Mapă Nouă</h3>
        <p class="text-center text-muted small mb-4">Organizează-ți quiz-urile mai eficient.</p>

        <input type="text" id="new-folder-name" class="settings-input" placeholder="Ex: Matematică clasa a 10-a">

        <div class="d-flex gap-3 mt-5" style="margin-top: 20px;">
            <button onclick="closeFolderModal()" class="btn-cancel w-50">Înapoi</button>
            <button onclick="saveFolder()" class="btn-done w-50">Creează</button>
        </div>
    </div>
</div>

<!-- Move quiz modal -->
<div id="move-quiz-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width: 400px;">
        <h3 id="popup-title">Mută în Folder</h3>
        <input type="hidden" id="move-quiz-id">
        <select id="move-folder-select" class="settings-select" style="margin-top:20px;">
            <option value="">Fără Folder (Principal)</option>
            <?php foreach($folders as $f): ?>
                <option value="<?php echo (int)$f['id']; ?>"><?php echo h((string)$f['nume_folder']); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="d-flex gap-2 mt-4">
            <button onclick="closeMoveModal()" class="btn-cancel w-50">Anulează</button>
            <button onclick="confirmMoveQuiz()" class="btn-done w-50">Mută acum</button>
        </div>
    </div>
</div>

<script>
    (function () {
        "use strict";

        function $(id) { return document.getElementById(id); }
        function show(el) { if (el) el.style.display = "flex"; }
        function hide(el) { if (el) el.style.display = "none"; }

        async function postForm(url, dataObj) {
            const body = new URLSearchParams();
            Object.keys(dataObj).forEach((k) => body.append(k, dataObj[k] ?? ""));

            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
                credentials: "same-origin",
            });

            let json;
            try {
                json = await response.json();
            } catch (e) {
                throw new Error("Răspuns invalid de la server.");
            }

            if (!response.ok) {
                throw new Error(json?.message || "Eroare HTTP " + response.status);
            }

            return json;
        }

        const API_LIBRARI = "/public/crudlibrari";

        window.openFolderModal = function () {
            show($("folder-modal"));
        };

        window.closeFolderModal = function () {
            hide($("folder-modal"));
            const inp = $("new-folder-name");
            if (inp) inp.value = "";
        };

        window.saveFolder = async function () {
            const inp = $("new-folder-name");
            const name = (inp ? inp.value : "").trim();

            if (!name) {
                alert("Introdu un nume!");
                return;
            }

            try {
                const data = await postForm(API_LIBRARI, {
                    type_product: "create_folder",
                    nume_folder: name
                });

                if (!data.success) {
                    alert(data.message || "Nu s-a putut crea mapa.");
                    return;
                }

                location.reload();
            } catch (err) {
                alert("Eroare: " + err.message);
            }
        };

        window.deleteFolder = async function (event, id) {
            if (event) event.stopPropagation();

            const box = document.querySelector("#folder-" + id + " .box");
            const randomnId = box ? box.getAttribute("data-randomn-id") : "";

            if (!randomnId) {
                alert("randomn_id folder lipsă.");
                return;
            }

            if (!confirm("Ești sigur că vrei să ștergi această mapă? Quiz-urile vor rămâne, dar nu vor mai fi grupate.")) {
                return;
            }

            try {
                const data = await postForm(API_LIBRARI, {
                    type_product: "delete_folder",
                    randomn_id: randomnId
                });

                if (!data.success) {
                    alert(data.message || "Nu s-a putut șterge mapa.");
                    return;
                }

                location.reload();
            } catch (err) {
                alert("Eroare: " + err.message);
            }
        };

        window.goToFolder = function (id, name) {
            const box = document.querySelector("#folder-" + id + " .box");
            const randomnId = box ? box.getAttribute("data-randomn-id") : "";

            if (!randomnId) {
                alert("randomn_id folder lipsă.");
                return;
            }

            window.location.href = `librari?view=folder&folder=${encodeURIComponent(randomnId)}&name=${encodeURIComponent(name || "")}&id=${encodeURIComponent(id)}`;
        };

        window.openRenameModal = function (event, id, currentName) {
            if (event) event.stopPropagation();

            const box = document.querySelector("#folder-" + id + " .box");
            const randomnId = box ? box.getAttribute("data-randomn-id") : "";

            if (!randomnId) {
                alert("randomn_id folder lipsă.");
                return;
            }

            const idEl = $("rename-folder-id");
            const nameEl = $("rename-folder-name");

            if (idEl) idEl.value = randomnId;
            if (nameEl) nameEl.value = currentName || "";

            show($("rename-modal"));
        };

        window.closeRenameModal = function () {
            hide($("rename-modal"));
        };

        window.saveRenameFolder = async function () {
            const randomnId = ($("rename-folder-id")?.value || "").trim();
            const newName = ($("rename-folder-name")?.value || "").trim();

            if (!randomnId) return alert("ID folder lipsă!");
            if (!newName) return alert("Numele nu poate fi gol!");

            try {
                const data = await postForm(API_LIBRARI, {
                    type_product: "update_folder",
                    randomn_id: randomnId,
                    nume_folder: newName
                });

                if (!data.success) {
                    alert(data.message || "Nu s-a putut redenumi mapa.");
                    return;
                }

                location.reload();
            } catch (err) {
                alert("Eroare: " + err.message);
            }
        };

        window.duplicateQuiz = async function (event, id) {
            if (event) event.stopPropagation();
            if (!confirm("Vrei să creezi o copie a acestui quiz?")) return;

            try {
                const data = await postForm(API_LIBRARI, {
                    type_product: "duplicate_quiz",
                    quiz_id: String(id)
                });

                if (!data.success) {
                    alert(data.message || "Nu s-a putut duplica quiz-ul.");
                    return;
                }

                location.reload();
            } catch (err) {
                alert("Eroare: " + err.message);
            }
        };

        window.deleteQuiz = async function (event, id) {
            if (event) event.stopPropagation();
            if (!confirm("Ești sigur că vrei să ștergi acest quiz definitiv?")) return;

            try {
                const data = await postForm(API_LIBRARI, {
                    type_product: "delete_quiz",
                    quiz_id: String(id)
                });

                if (!data.success) {
                    alert(data.message || "Nu s-a putut șterge quiz-ul.");
                    return;
                }

                location.reload();
            } catch (err) {
                alert("Eroare: " + err.message);
            }
        };

        window.openMoveModal = function (event, id) {
            if (event) event.stopPropagation();
            const inp = $("move-quiz-id");
            if (inp) inp.value = id;
            show($("move-quiz-modal"));
        };

        window.closeMoveModal = function () {
            hide($("move-quiz-modal"));
        };

        window.confirmMoveQuiz = async function () {
            const id = ($("move-quiz-id")?.value || "").trim();
            const folderId = $("move-folder-select")?.value ?? "";

            if (!id) return alert("ID quiz lipsă!");

            try {
                const data = await postForm(API_LIBRARI, {
                    type_product: "move_quiz",
                    quiz_id: id,
                    folder_id: folderId
                });

                if (!data.success) {
                    alert(data.message || "Nu s-a putut muta quiz-ul.");
                    return;
                }

                location.reload();
            } catch (err) {
                alert("Eroare: " + err.message);
            }
        };

        window.filterFolders = function () {
            const input = $("searchFolder");
            const query = (input ? input.value : "").trim().toLowerCase();

            const folderGrid = $("folderGrid");
            if (!folderGrid) return;

            const cards = folderGrid.querySelectorAll(".folder");
            cards.forEach((card) => {
                const title = (card.querySelector(".name")?.textContent || "").toLowerCase();
                const ok = !query || title.includes(query);
                card.style.display = ok ? "" : "none";
            });
        };
    })();
</script>