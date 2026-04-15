<?php
// statistici.php (refactor: toată logica PHP sus, fără dublări)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /public/login');
    exit;
}

$id_user = (int)$_SESSION['user_id'];

/** DB (recomand: mută credențialele în config/env) */
$dsn  = "mysql:host=localhost;dbname=lilit2;charset=utf8mb4";
$user = "lilit2";
$pass = "aM1xN7kS3w";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// 1) Total quiz-uri
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE id_user = ?");
$stmt->execute([$id_user]);
$total_quizzes = (int)$stmt->fetchColumn();

// 2) Limită
$limit = 200;

// 3) Procent
$procent = $limit > 0 ? min(100, max(0, ($total_quizzes / $limit) * 100)) : 0;

// 4) Foldere
$stmt = $pdo->prepare("SELECT id, nume_folder FROM folders WHERE id_user = ? ORDER BY nume_folder ASC");
$stmt->execute([$id_user]);
$folders = $stmt->fetchAll();

// 5) Quiz-uri (all / folder)
$id_folder = isset($_GET['id']) ? (int)$_GET['id'] : null;
$view      = isset($_GET['view']) ? (string)$_GET['view'] : 'all';

if ($view === 'folder' && $id_folder) {
    $stmt = $pdo->prepare("SELECT id, titlu, titlu as titlu_fallback, continut_json, last_updated, id_folder
                           FROM quizzes
                           WHERE id_user = ? AND id_folder = ?
                           ORDER BY last_updated DESC");
    $stmt->execute([$id_user, $id_folder]);
} else {
    // doar quiz-urile fără folder (cum aveai tu)
    $stmt = $pdo->prepare("SELECT id, titlu, titlu as titlu_fallback, continut_json, last_updated, id_folder
                           FROM quizzes
                           WHERE id_user = ? AND id_folder IS NULL
                           ORDER BY last_updated DESC");
    $stmt->execute([$id_user]);
}
$quizzes = $stmt->fetchAll();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$stmt = $pdo->prepare("SELECT * FROM users_connect WHERE role != 'super_ambassador'");
$stmt->execute(); // lipsa aici

$udesrsall = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
   <style>
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
   </style>

    <main class="main">

        <!-- TOPBAR -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>


        <div class="content">

            <!-- STATS -->
            <div class="stats">
                <div class="stat">
                    <div class="bgbox">
                        <img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/ChatGPT Image 22 дек. 2025 г., 08_59_22 1.png" alt="">
                    </div>

                    <div class="meta">
                        <div class="num"><?=$total_quizzes;?></div>
                        <div class="lbl">Quizz</div>
                    </div>
                </div>
                <div class="stat">
                    <div class="bgbox">
                        <img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/map.png" alt="">
                    </div>
                    <div class="meta">
                        <div class="num">0</div>
                        <div class="lbl">Locati</div>
                    </div>
                </div>
                <div class="stat">
                    <div class="bgbox">
                        <img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/isometric cityscape with tall buildings, modern city planning.png" alt="">
                    </div>
                    <div class="meta">
                        <div class="num">0</div>
                        <div class="lbl">Tari/Regiuni</div>
                    </div>
                </div>
                <div class="stat">
                    <div class="bgbox">
                        <img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/people users.png" alt="">
                    </div>
                    <div class="meta">
                        <div class="num"><?=count($udesrsall)?></div>
                        <div class="lbl">Utilizatori</div>
                    </div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="actions">
                <div class="action a-green">
                    <div class="left">
                        <div class=""><img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/Account Male.png" alt=""> </div>
                        <div class="txt">
                            <div class="big"><?php echo (int)$total_quizzes; ?> / <?php echo (int)$limit; ?></div>
                            <div class="sub">temele mele</div>
                        </div>
                    </div>

                </div>

                <div class="action a-purple">
                    <div class="left">
                        <div class=""><img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/Add.png" alt=""> </div>
                        <div class="txt">
                            <div class="big"><?php echo max(0, (int)$limit - (int)$total_quizzes); ?></div>
                            <div class="sub">Creaza un Quizz</div>
                        </div>
                    </div>

                </div>

                <div class="action a-orange">
                    <div class="left">
                        <div class=""><img src="<?echo getCurrentUrl()?>/Templates/admin/dist/img/AI.png" alt=""> </div>
                        <div class="txt">
                            <div class="big">0</div>
                            <div class="sub">Creaza Cu AI</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- MAPS -->
            <section class="section">
                <div class="sec-head">
                    <div class="sec-title">Mapele Mele</div>
                    <div class="sec-right">
                        <div class="mini-search">
                            <input type="text" placeholder="Search" />
                        </div>
                        <div class="filter" title="Filter"><i></i></div>
                    </div>
                </div>

                <div class="grid-folders" id="folderGrid">
                    <?php foreach ($folders as $f): ?>
                        <?php
                        $fid = (int)$f['id'];
                        $fname = (string)$f['nume_folder'];
                        $fname_class = '';
                        $fname_class = (
                            isset($_GET['name']) &&
                            trim($_GET['name']) !== '' &&
                            $_GET['name'] === $fname
                        ) ? 'folder_active' : '';
                        ?>
                        <div class="folder" id="folder-<?php echo $fid; ?>">
                            <!-- folder_active-->
                            <div class="box <?=$fname_class;?>" onclick="goToFolder(<?php echo $fid; ?>, '<?php echo h($fname); ?>')">
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
                                <a href="https://quizdigo.live/public/librari" class="b-blue"  style="background: #2464a6;width: 100%;">Vezi toate Quizzurile</a>
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
                                    <a href="https://quizdigo.live/public/desquizz?id=<?php echo $qid; ?>'" class="b-blue" >Joaca</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
    </main>
