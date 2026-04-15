<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /public/login');
    exit;
}

$id_user = (int)$_SESSION['user_id'];

/** DB */
$dsn  = "mysql:host=localhost;dbname=lilit2;charset=utf8mb4";
$user = "lilit2";
$pass = "aM1xN7kS3w";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function currentUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

$baseUrl = currentUrl();
$limit = 200;

/** user info */
$stmt = $pdo->prepare("SELECT * FROM users_connect WHERE id = ? LIMIT 1");
$stmt->execute([$id_user]);
$currentUser = $stmt->fetch();

$userName = $currentUser['name'] ?? 'Profile name';

/** total quizuri */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE id_user = ?");
$stmt->execute([$id_user]);
$total_quizzes = (int)$stmt->fetchColumn();

/** total mape */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM folders WHERE id_user = ?");
$stmt->execute([$id_user]);
$total_folders = (int)$stmt->fetchColumn();

/** total users */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users_connect WHERE role != 'super_ambassador'");
$stmt->execute();
$total_users = (int)$stmt->fetchColumn();

/** procent utilizare */
$procent = $limit > 0 ? min(100, max(0, ($total_quizzes / $limit) * 100)) : 0;
$remaining_quizzes = max(0, $limit - $total_quizzes);

/** ultimele 4 mape */
$stmt = $pdo->prepare("
    SELECT id, nume_folder
    FROM folders
    WHERE id_user = ?
    ORDER BY id DESC
    LIMIT 4
");
$stmt->execute([$id_user]);
$folders = $stmt->fetchAll();

/** ultimele 6 quizuri */
$stmt = $pdo->prepare("
    SELECT id, titlu, continut_json, last_updated, id_folder
    FROM quizzes
    WHERE id_user = ?
    ORDER BY last_updated DESC, id DESC
    LIMIT 6
");
$stmt->execute([$id_user]);
$quizzes = $stmt->fetchAll();

/** activitate recentă simplă */
$recentActivity = [];
foreach ($quizzes as $quiz) {
    $recentActivity[] = [
        'type' => 'quiz',
        'title' => $quiz['titlu'] ?: 'Quiz fără titlu',
        'date' => !empty($quiz['last_updated']) ? date('d.m.Y H:i', strtotime($quiz['last_updated'])) : '-'
    ];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard QuizDigo</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef2f7;
            color: #1e293b;
        }



        .profile {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 10px 14px;
            border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            background-color: #dbeafe;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 58px;
            right: 0;
            width: 220px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            padding: 10px 0;
            z-index: 50;
        }

        .dropdown-menu.show { display: block; }

        .dropdown-item {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #1e293b;
            font-size: 14px;
        }

        .dropdown-item:hover { background: #f8fafc; }

        .page-title {
            margin-bottom: 18px;
        }

        .page-title h1 {
            margin: 0;
            font-size: 30px;
            color: #0A5084;
        }

        .page-title p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 120px;
        }

        .stat-card .meta .num {
            font-size: 34px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-card .meta .lbl {
            font-size: 15px;
            color: #64748b;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
        }

        .action-grid {
            display: grid;
            grid-template-columns: 2fr 2fr 2fr 1.5fr;
            gap: 18px;
            margin-bottom: 24px;
        }

        .action-card {
            border-radius: 22px;
            padding: 24px;
            color: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            min-height: 140px;
            position: relative;
            overflow: hidden;
        }

        .action-card h3 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 800;
        }

        .action-card p {
            margin: 0 0 18px;
            font-size: 15px;
        }

        .action-card a {
            display: inline-block;
            text-decoration: none;
            color: #0f172a;
            background: rgba(255,255,255,0.95);
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
        }

        .green { background: linear-gradient(135deg, #4ade80, #22c55e); }
        .purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }
        .orange { background: linear-gradient(135deg, #fb923c, #f97316); }
        .blue { background: linear-gradient(135deg, #38bdf8, #2563eb); }

        .layout-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.05);
        }

        .card h2 {
            margin: 0 0 18px;
            font-size: 22px;
            color: #0A5084;
        }

        .progress-wrap {
            margin-top: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 14px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 10px;
        }

        .progress-bar span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            border-radius: 999px;
        }

        .folders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 16px;
        }

        .folder-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px;
            text-align: center;
            transition: 0.2s ease;
            cursor: pointer;
        }

        .folder-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.06);
            border-color: #93c5fd;
        }

        .folder-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            border-radius: 16px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
        }

        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .quiz-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #f0f0f0;
        }

        .quiz-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(46, 133, 199, 0.15);
        }

        .quiz-cover {
            height: 160px;
            width: 100%;
            background-color: #e5e7eb;
            background-size: cover;
            background-position: center;
        }

        .quiz-body {
            padding: 18px;
        }

        .quiz-title {
            font-weight: 800;
            font-size: 18px;
            color: #0A5084;
            margin-bottom: 8px;
        }

        .quiz-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            min-height: 40px;
        }

        .quiz-footer {
            padding: 14px 18px 18px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .quiz-footer a {
            display: inline-block;
            text-decoration: none;
            background: #2464a6;
            color: #fff;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
        }

        .mini-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mini-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
        }

        .muted {
            color: #64748b;
            font-size: 13px;
        }

        .empty-box {
            padding: 22px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #64748b;
        }

        @media (max-width: 1200px) {
            .stats-grid,
            .action-grid,
            .layout-2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search {
                width: 100%;
            }

            .stats-grid,
            .action-grid,
            .layout-2,
            .quiz-grid,
            .folders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<main class="main">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>


    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Bine ai revenit. Aici vezi rapid starea contului, quizurile și activitatea ta.</p>
    </div>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="meta">
                <div class="num"><?php echo $total_quizzes; ?></div>
                <div class="lbl">Quizuri totale</div>
            </div>
            <div class="stat-icon">Q</div>
        </div>

        <div class="stat-card">
            <div class="meta">
                <div class="num"><?php echo $total_folders; ?></div>
                <div class="lbl">Mape create</div>
            </div>
            <div class="stat-icon">M</div>
        </div>

        <div class="stat-card">
            <div class="meta">
                <div class="num"><?php echo $total_users; ?></div>
                <div class="lbl">Utilizatori</div>
            </div>
            <div class="stat-icon">U</div>
        </div>

        <div class="stat-card">
            <div class="meta">
                <div class="num"><?php echo $remaining_quizzes; ?></div>
                <div class="lbl">Quizuri rămase</div>
            </div>
            <div class="stat-icon">+</div>
        </div>
    </section>

    <section class="action-grid">
        <div class="action-card green">
            <h3><?php echo $total_quizzes; ?> / <?php echo $limit; ?></h3>
            <p>Quizurile tale create până acum.</p>
            <a href="/public/librari">Vezi librăria</a>
        </div>

        <div class="action-card purple">
            <h3>Creează Quiz</h3>
            <p>Adaugă rapid un quiz nou și începe editarea.</p>
            <a href="/public/addquizz">Creează acum</a>
        </div>

        <div class="action-card orange">
            <h3>Quiz cu AI</h3>
            <p>Generează întrebări automat dintr-o temă.</p>
            <a href="/public/create-ai">Deschide AI</a>
        </div>

        <div class="action-card blue">
            <h3>Mapă nouă</h3>
            <p>Organizează quizurile în categorii.</p>
            <a href="/public/mympas">Vezi mapele</a>
        </div>
    </section>

    <section class="layout-2">
        <div class="card">
            <h2>Progres cont</h2>
            <div><strong><?php echo $total_quizzes; ?></strong> din <strong><?php echo $limit; ?></strong> quizuri folosite</div>
            <div class="progress-wrap">
                <div class="progress-bar">
                    <span style="width: <?php echo (float)$procent; ?>%;"></span>
                </div>
            </div>
            <p class="muted" style="margin-top: 12px;">
                Ai folosit <?php echo number_format($procent, 1); ?>% din limita disponibilă.
            </p>
        </div>

        <div class="card">
            <h2>Contul meu</h2>
            <div class="mini-list">
                <div class="mini-item">
                    <span>Nume</span>
                    <strong><?php echo h($userName); ?></strong>
                </div>
                <div class="mini-item">
                    <span>Email</span>
                    <strong><?php echo h($currentUser['email'] ?? '-'); ?></strong>
                </div>
                <div class="mini-item">
                    <span>Plan</span>
                    <strong>Free</strong>
                </div>
                <div class="mini-item">
                    <span>Status</span>
                    <strong>Activ</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="layout-2">
        <div class="card">
            <h2>Mape recente</h2>

            <?php if (empty($folders)): ?>
                <div class="empty-box">
                    Nu ai încă mape create.
                </div>
            <?php else: ?>
                <div class="folders-grid">
                    <?php foreach ($folders as $folder): ?>
                        <div class="folder-card" onclick="window.location.href='/public/librari?view=folder&id=<?php echo (int)$folder['id']; ?>'">
                            <div class="folder-icon">📁</div>
                            <div><strong><?php echo h($folder['nume_folder']); ?></strong></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Activitate recentă</h2>

            <?php if (empty($recentActivity)): ?>
                <div class="empty-box">
                    Nu există activitate recentă.
                </div>
            <?php else: ?>
                <div class="mini-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="mini-item">
                            <div>
                                <strong><?php echo h($activity['title']); ?></strong><br>
                                <span class="muted">Actualizat recent</span>
                            </div>
                            <div class="muted"><?php echo h($activity['date']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <h2>Ultimele quizuri</h2>

        <?php if (empty($quizzes)): ?>
            <div class="empty-box">
                Nu ai creat încă niciun quiz.
            </div>
        <?php else: ?>
            <div class="quiz-grid">
                <?php foreach ($quizzes as $q): ?>
                    <?php
                    $qid = (int)$q['id'];

                    $content = json_decode((string)$q['continut_json'], true) ?: [];
                    $settings = $content['settings'] ?? [];

                    $coverImg = !empty($settings['themeUrl']) ? (string)$settings['themeUrl'] : '';
                    $titluQuiz = !empty($settings['title']) ? (string)$settings['title'] : (string)($q['titlu'] ?? 'Quiz fără titlu');
                    $descriere = !empty($settings['description']) ? (string)$settings['description'] : 'Nicio descriere disponibilă.';
                    $descriereShort = mb_strimwidth($descriere, 0, 110, '...');
                    $lastUpdated = !empty($q['last_updated']) ? date('d.m.Y', strtotime($q['last_updated'])) : '-';
                    ?>
                    <article class="quiz-card" onclick="window.location.href='/public/desquizz?id=<?php echo $qid; ?>'">
                        <div
                            class="quiz-cover"
                            style="<?php echo $coverImg ? "background-image:url('" . h($coverImg) . "');" : ''; ?>"
                        ></div>

                        <div class="quiz-body">
                            <div class="quiz-title"><?php echo h($titluQuiz); ?></div>
                            <div class="quiz-desc"><?php echo h($descriereShort); ?></div>
                        </div>

                        <div class="quiz-footer">
                            <small>Ultima editare: <?php echo h($lastUpdated); ?></small>
                            <a href="/public/desquizz?id=<?php echo $qid; ?>" onclick="event.stopPropagation();">Deschide</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>



</body>
</html>