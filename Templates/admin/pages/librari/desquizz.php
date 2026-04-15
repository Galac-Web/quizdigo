<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Evasystem\Controllers\Librari\Librari;
use Evasystem\Controllers\Librari\LibrariService;

$current_quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($current_quiz_id < 0) $current_quiz_id = 0;

$id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

$view = isset($_GET['view']) ? (string)$_GET['view'] : 'all';
$id_folder = isset($_GET['folder']) ? (int)$_GET['folder'] : 0;

$librariService = new LibrariService();
$librariController = new Librari($librariService);

$pageData = $librariController->descriptionPage($current_quiz_id, $id_user, $view, $id_folder);

$current_quiz = $pageData['current_quiz'] ?? null;
$slider_images = $pageData['slider_images'] ?? [];
$main_title = $pageData['main_title'] ?? 'Quiz';
$main_desc  = $pageData['main_desc'] ?? 'Nicio descriere disponibilă.';
$main_bg    = $pageData['main_bg'] ?? 'default-bg.jpg';
$quizzes    = $pageData['quizzes'] ?? [];
$schedules  = $pageData['schedules'] ?? [];

function safeStr($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}


?>
<?php
$status = (string)($schedule['status'] ?? 'scheduled');

$statusLabelMap = [
    'scheduled' => 'Programat',
    'launched'  => 'Lansat',
    'completed' => 'Finalizat',
    'cancelled' => 'Anulat',
];

$statusLabel = $statusLabelMap[$status] ?? ucfirst($status);
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?php echo safeStr($main_title); ?> - QuizDigo</title>

    <style>
        /* RESET & MODAL BASE */
        .game-details-modal {
            background: #fff;
            border-radius: 24px;
            margin: 20px auto;
            overflow: hidden;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            position: relative;
            width: 100%;
        }

        .modal-header-qd {
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .user-info { display: flex; align-items: center; gap: 10px; font-weight: 700; color: #1e3a8a; }
        .mini-avatar-placeholder { width: 30px; height: 30px; background: #ddd; border-radius: 50%; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #64748b; text-decoration:none; }

        /* FLEX LAYOUT */
        .flex-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            padding: 30px;
        }

        /* LEFT COL - SLIDER */
        .left-col { flex: 0 0 42%; min-width: 350px; }
        .main-slide {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .main-slide img { width: 100%; display: block; object-fit: cover; }

        .thumb-nav { display: flex; align-items: center; gap: 10px; }
        .thumbs-wrapper { display: flex; gap: 10px; overflow: hidden; scroll-behavior:smooth; }
        .t-img {
            width: 75px; height: 60px; border-radius: 10px;
            cursor: pointer; border: 3px solid transparent;
            transition: 0.2s; object-fit: cover;
        }
        .t-img.active { border-color: #2E85C7; }
        .arrow-btn {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 50%;
            width: 30px; height: 30px; cursor: pointer; font-weight: bold;
        }

        /* RIGHT COL - INFO */
        .right-col { flex: 1; min-width: 350px; }
        .info-header { display: flex; justify-content: space-between; align-items: start; gap:18px; }
        .game-title { font-size: 32px; font-weight: 900; color: #1e3a8a; margin: 0; }
        .badge-row { display: flex; gap: 8px; margin-top: 10px; flex-wrap:wrap; justify-content:flex-end; }
        .badge-star { background: #fff7ed; color: #c2410c; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        .badge-users { background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 13px; }

        .recommend-msg { color: #10b981; font-size: 13px; margin: 15px 0; }

        .description-section h3 { font-size: 18px; font-weight: 800; color: #334155; margin-bottom: 10px; }
        .description-section p { font-size: 14px; color: #64748b; line-height: 1.6; }

        /* SCHEDULER */
        .scheduler { margin-top: 25px; }
        .scheduler label { font-size: 14px; font-weight: 600; color: #64748b; display: block; margin-bottom: 8px; }
        .input-group-custom { display: flex; gap: 10px; max-width: 450px; }
        .input-group-custom input {
            flex: 1; padding: 12px 15px; border-radius: 10px;
            border: 1px solid #cbd5e0; outline: none;
        }
        .btn-schedule {
            background: #2E85C7; color: #fff; border: none;
            padding: 0 25px; border-radius: 10px; font-weight: 700; cursor: pointer;
        }

        /* ACTION BUTTONS */
        .button-group-actions { display: flex; gap: 12px; margin-top: 30px; flex-wrap:wrap; }
        .btn-p {
            flex: 1; padding: 14px; border: none; border-radius: 12px;
            color: #fff; font-weight: 800; display: flex; align-items: center;
            justify-content: center; gap: 8px; cursor: pointer; transition: 0.2s;
            min-width: 180px;
        }
        .btn-p:hover { filter: brightness(1.1); transform: translateY(-2px); }
        .bg-green { background: #10b981; }
        .bg-orange { background: #f97316; }
        .bg-yellow { background: #fbbf24; color:#1f2937; }

        /* Library */
        .bottom-grid-container {
            background: #f8fafc;
            padding: 40px 30px;
            border-top: 1px solid #e2e8f0;
        }
        .section-subtitle {
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 25px;
            text-transform: uppercase;
            font-size: 18px;
        }
        .quiz-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .quiz {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            cursor: pointer;
            position: relative;
            border: 1px solid #f0f0f0;
        }
        .quiz:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .quiz .cover { height: 140px; width: 100%; }
        .quiz .badge-circle {
            position: absolute; top: 120px; right: 15px;
            width: 40px; height: 40px; background: #fff;
            border-radius: 50%; padding: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .quiz .body { padding: 25px 20px 15px; }
        .quiz .title { font-weight: 900; color: #0A5084; font-size: 16px; margin-bottom: 5px; text-transform: uppercase; }
        .quiz .desc { font-size: 12px; color: #64748b; line-height: 1.4; }
        .quiz-footer {
            padding: 12px 20px; background: #fcfcfc;
            border-top: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; align-items: center;
            gap:12px;
        }
        .quiz-footer small { font-size: 10px; color: #cbd5e0; font-weight: 600; }
        .b-blue-play {
            background: #2E85C7; color: #fff; padding: 6px 15px;
            border-radius: 8px; font-size: 12px; font-weight: 800;
            text-decoration: none; transition: 0.2s;
            display:inline-block;
        }
        .b-blue-play:hover { background: #1a6399; }

        /* POPUP overlay */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        .modal-content-small {
            background: #fff;
            padding: 10px;
            border-radius: 25px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            position: relative;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            animation: popupAnimation 0.3s ease-out;
        }
        @keyframes popupAnimation {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .popup-blue-title { color: #2E3A8C; font-weight: 800; font-size: 26px; margin-bottom: 20px; }
        .popup-date { color: #1e293b; font-weight: 700; font-size: 15px; margin: 5px 0; }
        .popup-row-item { display:flex; align-items:center; justify-content:space-between; margin-top:25px; gap:15px; }
        .qr-container img { width: 80px; border-radius: 8px; }
        .row-text, .row-label { font-size: 18px; color: #2E3A8C; font-weight: 700; flex: 1; text-align: left; }
        .row-value { font-family: monospace; font-weight: 700; color: #1e293b; font-size: 14px; }
        .btn-green-sm { background: #10b981; color: #fff; border: none; padding: 10px 15px; border-radius: 10px; font-weight: 800; cursor: pointer; }
        .btn-red-sm { background: #f87171; color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; }
        .close-popup-x { position:absolute; top: 12px; right: 16px; font-size: 28px; border:none; background:none; cursor:pointer; color:#94a3b8; }

        /* Mobile */
        @media (max-width: 720px){
            .flex-container{ padding:18px; gap:18px; }
            .left-col, .right-col{ min-width: 100%; }
            .game-title{ font-size:24px; }
            .bottom-grid-container{ padding:22px 16px; }
            .modal-header-qd{ padding:12px 16px; }
        }

        .scheduled-games-section{
            margin: 41px 0 0 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 20px;
        }

        .scheduled-games-title{
            font-size: 18px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0 0 14px;
        }

        .scheduled-games-wrap{
            overflow-x: auto;
        }

        .scheduled-games-table{
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
        }

        .scheduled-games-table th,
        .scheduled-games-table td{
            text-align: left;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            font-size: 14px;
        }

        .scheduled-games-table th{
            background: #f8fafc;
            color: #475569;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .3px;
        }

        .scheduled-games-table td{
            color: #334155;
        }

        .schedule-status{
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .schedule-status.scheduled{
            background: #dbeafe;
            color: #1d4ed8;
        }

        .schedule-status.launched{
            background: #dcfce7;
            color: #15803d;
        }

        .schedule-launch-btn{
            display: inline-block;
            background: #10b981;
            color: #fff;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            transition: .2s ease;
        }

        .schedule-launch-btn:hover{
            background: #059669;
        }

        .schedule-empty{
            padding: 14px 0 2px;
            color: #64748b;
            font-size: 14px;
        }
        .settings-box {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-top: 10px;
        }

        .settings-row { display: flex; gap: 15px; margin-bottom: 20px; }
        .setting-item { flex: 1; }
        .setting-item input { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e0; }

        .option-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .option-toggle span { font-size: 14px; font-weight: 600; color: #475569; }

        /* Switch Slider */
        .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc; transition: .4s; border-radius: 34px;
        }
        .slider:before {
            position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
            background-color: white; transition: .4s; border-radius: 50%;
        }
        input:checked + .slider { background-color: #2E85C7; }
        input:checked + .slider:before { transform: translateX(22px); }

        .full-width { width: 100%; margin-top: 20px; padding: 12px; }
        @media (max-width: 720px){
            .scheduled-games-section{
                margin: 20px 16px 0;
            }
        }


        .invite-dashboard {
            margin-top: 30px;
            background: #f1f5f9;
            padding: 25px;
            border-radius: 16px;
        }

        .invite-main-card {
            display: flex;
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            gap: 30px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            flex-wrap: wrap;
        }

        /* Cercul de participanți */
        .participants-circle-box {
            flex: 1;
            min-width: 200px;
            text-align: center;
        }
        .circle-progress {
            width: 120px;
            height: 120px;
            border: 8px solid #2E85C7;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .count-num { font-size: 32px; font-weight: 800; color: #1e293b; }
        .count-label { font-size: 12px; color: #64748b; }

        /* Share controls */
        .share-details { flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 12px; }
        .field-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
        }
        .field-content label { display: block; font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; }
        .field-value { font-family: monospace; font-weight: 700; color: #1e3a8a; }

        /* Butoane acțiune */
        .action-sidebar { flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 15px; }
        .btn-blue-main { background: #2E85C7; color: #fff; border: none; padding: 12px; border-radius: 8px 0 0 8px; font-weight: 700; flex: 2; }
        .btn-white-side { background: #fff; border: 1px solid #e2e8f0; padding: 12px; border-radius: 0 8px 8px 0; font-weight: 700; flex: 1; }
        .btn-green-play-self {
            background: #10b981; color: #fff; border: none; padding: 15px; border-radius: 8px;
            font-weight: 800; text-transform: uppercase; cursor: pointer;
        }

        /* Rapoarte */
        .report-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px; }
        .report-card {
            background: #fff; padding: 40px 20px; border-radius: 12px; text-align: center;
            color: #94a3b8; font-size: 14px; border: 1px dashed #cbd5e0;
        }
    </style>

    <script>
        /* quizId vine corect din PHP (nu din URLSearchParams) */
        const quizId = <?php echo (int)($current_quiz_id ?: 1); ?>;
    </script>
</head>

<body>

<section class="game-details-modal">
    <div class="modal-header-qd">
        <div class="user-info">
            <div class="mini-avatar-placeholder"></div>
            <span><?php echo $id_user ? "User #".$id_user : "Guest"; ?></span>
        </div>
        <a class="close-btn" href="https://quizdigo.live/public/librari">×</a>
    </div>

    <!-- Popup Programare -->
    <div id="schedule-success-popup" class="modal-overlay">
        <div class="modal-content-small">
            <button class="close-popup-x" type="button" onclick="closePopup('schedule-success-popup')">×</button>
            <h2 class="popup-blue-title">Quizul e programat</h2>

            <p class="popup-date">Start date: <span id="display-start">—</span></p>
            <p class="popup-date">End date: <span id="display-end">—</span></p>

            <div class="popup-row-item">
                <div class="qr-container">
                    <img id="qr-img" style="display: none;" src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://quizdigo.live" alt="QR">
                </div>
                <div class="row-text">QR Code</div>
                <button class="btn-green-sm" type="button">DOWNLOAD</button>
            </div>

            <div class="popup-row-item">
                <div class="row-label">Game Pin</div>
                <div class="row-value" id="pin-value">1586488</div>
                <button class="btn-red-sm" type="button" onclick="copyText(document.getElementById('pin-value').textContent)">COPY</button>
            </div>

            <div class="popup-row-item">
                <div class="row-label">Link</div>
                <div class="row-value" id="link-value">https://quizdigo.live</div>
                <button class="btn-red-sm" type="button" onclick="copyText(document.getElementById('link-value').textContent)">COPY</button>
            </div>
        </div>
    </div>

    <div class="modal-body-qd">
        <div class="flex-container">

            <div class="left-col">
                <div class="main-slide">
                    <img id="main-preview-img" src="<?php echo safeStr($main_bg); ?>" alt="Quiz Preview">
                </div>

                <div class="thumb-nav">
                    <button class="arrow-btn" type="button" onclick="moveCarousel(-1)">&lt;</button>

                    <div class="thumbs-wrapper" id="thumbs-container">
                        <?php foreach ($slider_images as $index => $img_url): ?>
                            <img
                                    src="<?php echo safeStr($img_url); ?>"
                                    class="t-img <?php echo $index === 0 ? 'active' : ''; ?>"
                                    alt="thumb"
                                    onclick="changePreview(this)">
                        <?php endforeach; ?>
                    </div>

                    <button class="arrow-btn" type="button" onclick="moveCarousel(1)">&gt;</button>
                </div>
                <div class="scheduled-games-section">
                    <h3 class="scheduled-games-title">Jocuri programate</h3>

                    <?php if (empty($schedules)): ?>
                        <div class="schedule-empty">Nu există jocuri programate pentru acest quiz.</div>
                    <?php else: ?>
                        <div class="scheduled-games-wrap">
                            <table class="scheduled-games-table">
                                <thead>
                                <tr>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>PIN</th>
                                    <th>Participanți</th>
                                    <th>Status</th>
                                    <th>Lansare</th>
                                    <th>QR / Join</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <?php
                                    $scheduleId = (int)($schedule['id'] ?? 0);
                                    $schedulePin = (string)($schedule['game_pin'] ?? '-');
                                    $participantsCount = (int)($schedule['participants_count'] ?? 0);
                                    $status = (string)($schedule['status'] ?? 'Active');

                                    $startAt = !empty($schedule['start_at'])
                                        ? date('d.m.Y H:i', strtotime((string)$schedule['start_at']))
                                        : '-';

                                    $endAt = !empty($schedule['end_at'])
                                        ? date('d.m.Y H:i', strtotime((string)$schedule['end_at']))
                                        : '-';

                                    $launchUrl = !empty($schedule['game_link'])
                                        ? (string)$schedule['game_link']
                                        : ('https://quizdigo.live/game/play.php?');
                                    ?>
                                    <tr>
                                        <td><?php echo safeStr($startAt); ?></td>
                                        <td><?php echo safeStr($endAt); ?></td>
                                        <td><?php echo safeStr($schedulePin); ?></td>
                                        <td><?php echo (int)$participantsCount; ?></td>
                                        <td>
                           <span class="schedule-status <?php echo safeStr($status); ?>">
    <?php echo safeStr($statusLabel); ?>
</span>
                                        </td>
                                        <td>
                                            <a class="schedule-launch-btn"
                                               href="<?php echo safeStr($launchUrl); ?>"
                                               target="_blank">
                                                Lansează jocul
                                            </a>
                                        </td>
                                        <td>
                                            <button class="schedule-launch-btn"
                                                    onclick="openSchedulePopup(
                                                            '<?php echo safeStr($schedulePin); ?>',
                                                            '<?php echo safeStr($launchUrl); ?>'
                                                            )">
                                                QR / Join
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="right-col">
                <div class="info-header">
                    <div>
                        <h1 class="game-title"><?php echo safeStr($main_title); ?></h1>
                        <div class="badge-row" style="justify-content:flex-start;">
                            <span class="badge-star">★ 4.8</span>
                            <span class="badge-users">👤 67 utilizatori</span>
                        </div>
                    </div>
                </div>

                <p class="recommend-msg"><strong>93%</strong> of buyers have recommended this.</p>

                <div class="description-section">
                    <h3>Product Description</h3>
                    <p><?php echo nl2br(safeStr($main_desc)); ?></p>
                </div>

                <div class="scheduler">
                    <div class="invite-dashboard" style="width: 100%;">
                        <div class="invite-main-card">
                            <div class="participants-circle-box">
                                <div class="circle-progress">
                                    <span class="count-num">0</span>
                                    <span class="count-label">participanți</span>
                                </div>
                                <p class="limit-info">(Max. 50 participanți)</p>
                                <button class="schedule-launch-btn"
                                        onclick="open_participanti(
                                                '<?php echo safeStr($schedulePin); ?>',
                                                '<?php echo safeStr($launchUrl); ?>'
                                                )">
                                    Adaugă participanți
                                </button>

                            </div>

                            <div class="share-details">
                                <div class="btn-split-group">
                                    <button class="btn-blue-main">Schimbă deadline</button>
                                    <button class="btn-white-side">Oprește acum</button>
                                </div>
                                <div class="share-row">
                                    <div class="qr-preview" id="dash-qr-box">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://quizdigo.live" alt="QR">
                                    </div>
                                    <div class="share-info">
                                        <label>QR Code</label>
                                        <button class="btn-outline-sm" onclick="downloadQR()">Download</button>
                                    </div>
                                </div>

                                <div class="share-row field-box">
                                    <div class="field-content">
                                        <label>Game PIN</label>
                                        <span class="field-value" id="dash-pin">08551842</span>
                                    </div>
                                    <button class="btn-copy-icon" onclick="copyText('08551842')">Copy</button>
                                </div>

                                <div class="share-row field-box">
                                    <div class="field-content">
                                        <label>URL</label>
                                        <span class="field-value">https://quizdigo.live/join...</span>
                                    </div>
                                    <button class="btn-copy-icon" onclick="copyText('https://quizdigo.live/join')">Copy</button>
                                </div>
                            </div>

                            <div class="action-sidebar">

                                <div class="button-group-actions">
                                    <button class="btn-p bg-green" type="button">Play Games 🎮</button>
                                    <button class="btn-p bg-orange" type="button">Live Games 🎥</button>
                                    <button class="btn-p bg-yellow" type="button">Solo Games 👤</button>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="settings-box">
                        <div class="settings-row">
                            <div class="setting-item">
                                <label>Data limită</label>
                                <input type="date" id="game-date">
                            </div>
                            <div class="setting-item">
                                <label>Ora</label>
                                <input type="time" id="game-time" value="12:00">
                            </div>
                        </div>

                        <div class="options-list">
                            <div class="option-toggle">
                                <span>Cronometru întrebări</span>
                                <label class="switch">
                                    <input type="checkbox" id="setting-timer" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="option-toggle">
                                <span>Randomizare răspunsuri</span>
                                <label class="switch">
                                    <input type="checkbox" id="setting-random" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="option-toggle">
                                <span>Generator de porecle</span>
                                <label class="switch">
                                    <input type="checkbox" id="setting-nickname">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <button class="btn-schedule full-width" type="button" id="btn-create-assigned">Creează Joc Programat</button>
                    </div>
                </div>
            </div>

        </div>
        <div id="schedule-join-popup" class="modal-overlay">
            <div class="modal-content-small">
                <button class="close-popup-x" onclick="closePopup('schedule-join-popup')">×</button>

                <h2 class="popup-blue-title">Acces joc</h2>

                <div class="popup-row-item">
                    <div class="qr-container">
                        <img id="popup-qr-img" style="display: none;" src="" alt="QR">
                    </div>
                    <div class="row-text">QR Code</div>
                    <button class="btn-green-sm" onclick="downloadQR()">DOWNLOAD</button>
                </div>

                <div class="popup-row-item">
                    <div class="row-label">Game PIN</div>
                    <div class="row-value" id="popup-pin">—</div>
                    <button class="btn-red-sm" onclick="copyText(document.getElementById('popup-pin').textContent)">COPY</button>
                </div>

                <div class="popup-row-item">
                    <div class="row-label">Link</div>
                    <div class="row-value" id="popup-link">—</div>
                    <button class="btn-red-sm" onclick="copyText(document.getElementById('popup-link').textContent)">COPY</button>
                </div>
            </div>
        </div>
        <!-- Alte quiz-uri -->
        <div class="bottom-grid-container">
            <h3 class="section-subtitle">Alte Quiz-uri din librăria ta</h3>

            <div class="quiz-row">
                <?php if (empty($quizzes)): ?>
                    <p style="padding:16px; color:#64748b;">Nu ai adăugat niciun quiz aici încă.</p>
                <?php endif; ?>

                <?php foreach ($quizzes as $q):
                    $content = json_decode((string)$q['continut_json'], true);
                    if (!is_array($content)) $content = [];
                    $coverImg = !empty($content['settings']['themeUrl']) ? (string)$content['settings']['themeUrl'] : 'default-cover.png';
                    $descriere = !empty($content['settings']['description']) ? (string)$content['settings']['description'] : 'Nicio descriere disponibilă.';
                    ?>
                    <article class="quiz" onclick="window.location.href='desquizz?id=<?php echo (int)$q['id']; ?>'">
                        <div class="cover" style="background-image:url('<?php echo safeStr($coverImg); ?>'); background-size:cover; background-position:center;"></div>

                        <div class="badge-circle">
                            <img src="https://quizdigo.live/Templates/admin/dist/img/Mask%20Group.png" alt="">
                        </div>

                        <div class="body">
                            <div class="title"><?php echo safeStr($q['titlu']); ?></div>
                            <div class="desc"><?php echo safeStr(mb_strimwidth($descriere, 0, 100, "...")); ?></div>
                        </div>

                        <div class="quiz-footer">
                            <small>Modificat: <?php echo safeStr(date('d.m.Y', strtotime((string)$q['last_updated']))); ?></small>
                            <div class="button_joca">
                                <a href="#" class="b-blue-play" onclick="event.stopPropagation(); window.location.href='desquizz?id=<?php echo (int)$q['id']; ?>'; return false;">Joaca</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="hidden" id="page-quiz-id" value="<?php echo (int)$current_quiz_id; ?>">
    </div>
</section>

<!-- =========================
     JS FOOTER (ALL HERE)
     ========================= -->
<script>
    window.latestScheduledGameLink = <?php
    echo json_encode(
        !empty($schedules[0]['game_link'])
            ? (string)$schedules[0]['game_link']
            : ''
    );
    ?>;
</script>
<script>
    (function () {
        "use strict";

        function getQuizId() {
            if (typeof window.quizId !== 'undefined' && Number(window.quizId) > 0) {
                return Number(window.quizId);
            }

            const hidden = document.getElementById('page-quiz-id');
            if (hidden && Number(hidden.value) > 0) {
                return Number(hidden.value);
            }

            const params = new URLSearchParams(window.location.search);
            const urlId = Number(params.get('id') || 0);
            if (urlId > 0) {
                return urlId;
            }

            return 0;
        }

        function exists(el) {
            return el !== null && el !== undefined;
        }

        window.changePreview = function (element) {
            const main = document.getElementById('main-preview-img');
            if (!exists(main) || !exists(element)) return;

            main.src = element.src;

            document.querySelectorAll('.t-img').forEach(img => {
                img.classList.remove('active');
            });

            element.classList.add('active');
        };

        window.moveCarousel = function (direction) {
            const container = document.getElementById('thumbs-container');
            if (!exists(container)) return;

            const scrollAmount = 85;
            container.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        };

        window.openPopup = function (id) {
            const el = document.getElementById(id);
            if (!exists(el)) return;
            el.style.display = 'flex';
        };

        window.closePopup = function (id) {
            const el = document.getElementById(id);
            if (!exists(el)) return;
            el.style.display = 'none';
        };

        window.copyText = function (text) {
            navigator.clipboard.writeText(String(text)).then(() => {
                alert("Copiat: " + text);
            }).catch(() => {
                alert("Nu s-a putut copia textul.");
            });
        };

        async function postForm(url, dataObj) {
            const body = new URLSearchParams();
            Object.keys(dataObj).forEach((k) => body.append(k, dataObj[k] ?? ""));

            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
                credentials: "same-origin",
            });

            const rawText = await response.text();
            console.log('RAW RESPONSE:', rawText);

            let json;
            try {
                json = JSON.parse(rawText);
            } catch (e) {
                throw new Error("Serverul nu a întors JSON valid. Răspuns: " + rawText);
            }

            if (!response.ok) {
                throw new Error(json?.message || "Eroare HTTP " + response.status);
            }

            if (json.success === false) {
                throw new Error(json?.message || "Request eșuat.");
            }

            return json;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const quizId = getQuizId();
            console.log('QUIZ ID =', quizId);

            const scheduleBtn = document.querySelector('.btn-schedule');

            if (exists(scheduleBtn)) {
                scheduleBtn.addEventListener('click', async function () {
                    const dateEl = document.getElementById('game-date');
                    const selectedDate = exists(dateEl) ? dateEl.value : '';

                    if (!selectedDate) {
                        alert("Te rog alege o dată!");
                        return;
                    }

                    if (!quizId || quizId <= 0) {
                        alert("ID quiz invalid: " + quizId);
                        return;
                    }

                    const oldText = scheduleBtn.textContent;
                    scheduleBtn.disabled = true;
                    scheduleBtn.textContent = 'Se programează...';

                    try {
                        const result = await postForm('/public/crudlibrari_description', {
                            type_product: 'schedule_game',
                            quiz_id: String(quizId),
                            date: selectedDate
                        });

                        document.getElementById('display-start').textContent = result.start_at || '—';
                        document.getElementById('display-end').textContent = result.end_at || '—';
                        document.getElementById('pin-value').textContent = result.game_pin || '—';
                        document.getElementById('link-value').textContent = result.game_link || '—';
                        document.getElementById('qr-img').src = result.qr_link || '';

                        window.openPopup('schedule-success-popup');
                    } catch (e) {
                        console.error('PROGRAMARE ERROR:', e);
                        alert('Eroare la programare: ' + (e.message || e));
                    } finally {
                        scheduleBtn.disabled = false;
                        scheduleBtn.textContent = oldText;
                    }
                });
            }

            const bindGo = (selector, url) => {
                const el = document.querySelector(selector);
                if (!exists(el)) return;

                el.addEventListener('click', function () {
                    if (!quizId || quizId <= 0) {
                        alert("ID quiz invalid.");
                        return;
                    }
                    window.location.href = url;
                });
            };

            if (quizId > 0) {
                bindGo('.bg-green', window.latestScheduledGameLink && window.latestScheduledGameLink !== ''
                    ? window.latestScheduledGameLink
                    : `https://quizdigo.live/game/play.php?quiz_id=${quizId}`);
                bindGo('.bg-orange', `https://quizdigo.live/game/live.php?quiz_id=${quizId}`);
                bindGo('.bg-yellow', `https://quizdigo.live/game/training.php?quiz_id=${quizId}`);
            }

            const firstThumb = document.querySelector('.t-img.active') || document.querySelector('.t-img');
            const mainImg = document.getElementById('main-preview-img');

            if (exists(firstThumb) && exists(mainImg)) {
                if (!mainImg.getAttribute('src') || mainImg.getAttribute('src').trim() === '') {
                    mainImg.src = firstThumb.src;
                }
            }
        });
    })();
    window.open_participanti = function(pin, link) {
        if (!pin || !link) {
            alert("Date invalide pentru joc.");
            return;
        }

        document.getElementById('popup-pin').textContent = pin;
        document.getElementById('popup-link').textContent = link;

        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(link);
        document.getElementById('popup-qr-img').src = qrUrl;

        openPopup('schedule-join-popup');
    };
    window.openSchedulePopup = function(pin, link) {
        if (!pin || !link) {
            alert("Date invalide pentru joc.");
            return;
        }

        document.getElementById('popup-pin').textContent = pin;
        document.getElementById('popup-link').textContent = link;

        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(link);
        document.getElementById('popup-qr-img').src = qrUrl;

        openPopup('schedule-join-popup');
    };

    window.downloadQR = function() {
        const img = document.getElementById('popup-qr-img');
        if (!img || !img.src) return;

        const link = document.createElement('a');
        link.href = img.src;
        link.download = 'qr-code.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
</script>

</body>
</html>