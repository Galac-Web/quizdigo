<style>
        :root{
            --bg:#f4f7fb;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --line:#e2e8f0;
            --primary:#0A5084;
            --primary2:#2E85C7;
            --success:#16a34a;
            --warning:#f59e0b;
            --danger:#dc2626;
            --violet:#7c3aed;
            --shadow:0 18px 40px rgba(15,23,42,.08);
            --radius-xl:28px;
            --radius-lg:20px;
            --radius-md:14px;
        }

        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            font-family:'Inter', Arial, sans-serif;
            background:
                    radial-gradient(circle at top left, rgba(46,133,199,.08), transparent 22%),
                    linear-gradient(180deg,#f8fbff 0%,#f3f6fb 100%);
            color:var(--text);
        }

        .page{
            max-width:1450px;
            margin:0 auto;

        }


        .title-wrap h1{
            margin:0;
            font-size:36px;
            line-height:1.05;
            color:var(--primary);
            font-weight:900;
            letter-spacing:-.8px;
        }

        .title-wrap p{
            margin:10px 0 0;
            color:var(--muted);
            font-size:15px;
            max-width:780px;
        }

        .top-actions{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
        }

        .btn{
            border:none;
            outline:none;
            cursor:pointer;
            border-radius:14px;
            padding:12px 18px;
            font-size:14px;
            font-weight:800;
            transition:.25s ease;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            text-decoration:none;
        }

        .btn:hover{transform:translateY(-2px)}
        .btn-primary{
            color:#fff;
            background:linear-gradient(135deg,var(--primary),var(--primary2));
            box-shadow:0 12px 24px rgba(46,133,199,.22);
        }
        .btn-light{
            color:var(--primary);
            background:#fff;
            border:1px solid var(--line);
        }
        .btn-success{
            color:#fff;
            background:linear-gradient(135deg,#16a34a,#22c55e);
        }
        .btn-danger{
            color:#fff;
            background:linear-gradient(135deg,#ef4444,#dc2626);
        }

        .stats-grid{
            display:grid;
            grid-template-columns:repeat(4,minmax(0,1fr));
            gap:20px;
            margin-bottom:24px;
        }

        .stat-card{
            background:var(--card);
            border-radius:var(--radius-xl);
            border:1px solid #edf2f7;
            box-shadow:var(--shadow);
            padding:22px;
            min-height:138px;
            position:relative;
            overflow:hidden;
        }

        .stat-card:before{
            content:"";
            position:absolute;
            right:-32px;
            top:-32px;
            width:120px;
            height:120px;
            border-radius:50%;
            background:linear-gradient(135deg,rgba(46,133,199,.10),rgba(10,80,132,.03));
        }

        .stat-label{
            font-size:13px;
            color:var(--muted);
            margin-bottom:12px;
            position:relative;
            z-index:1;
            font-weight:700;
        }

        .stat-value{
            font-size:34px;
            font-weight:900;
            line-height:1;
            margin-bottom:10px;
            position:relative;
            z-index:1;
        }

        .stat-meta{
            font-size:13px;
            color:var(--muted);
            position:relative;
            z-index:1;
        }

        .tabs-wrap{
            background:var(--card);
            border:1px solid #edf2f7;
            border-radius:var(--radius-xl);
            box-shadow:var(--shadow);
            padding:18px;
            margin-bottom:24px;
        }

        .tabs{
            display:flex;
            flex-wrap:wrap;
            gap:12px;
        }

        .tab-btn{
            border:none;
            background:#f1f5f9;
            color:#334155;
            padding:13px 18px;
            border-radius:14px;
            cursor:pointer;
            font-size:14px;
            font-weight:800;
            transition:.25s ease;
        }

        .tab-btn.active{
            background:linear-gradient(135deg,var(--primary),var(--primary2));
            color:#fff;
            box-shadow:0 12px 20px rgba(46,133,199,.22);
        }

        .tab-pane{
            display:none;
            animation:fadeIn .35s ease;
        }

        .tab-pane.active{
            display:block;
        }

        @keyframes fadeIn{
            from{opacity:0;transform:translateY(10px)}
            to{opacity:1;transform:translateY(0)}
        }

        .panel{
            background:var(--card);
            border-radius:var(--radius-xl);
            border:1px solid #edf2f7;
            box-shadow:var(--shadow);
            padding:24px;
            margin-bottom:24px;
        }

        .panel-head{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:18px;
            margin-bottom:18px;
            flex-wrap:wrap;
        }

        .panel-head h2{
            margin:0;
            font-size:24px;
            color:var(--primary);
            font-weight:900;
        }

        .panel-head p{
            margin:8px 0 0;
            color:var(--muted);
            font-size:14px;
        }

        .filters{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:18px;
        }

        .select{
            background:#f8fbff;
            border:1px solid var(--line);
            border-radius:14px;
            padding:12px 14px;
            min-height:48px;
            font-size:14px;
            color:#0f172a;
            outline:none;
        }


        .select{min-width:180px}

        .table-wrap{
            overflow:auto;
            border:1px solid var(--line);
            border-radius:20px;
            background:#fff;
        }

        table{
            width:100%;
            border-collapse:collapse;
            min-width:980px;
        }

        th, td{
            padding:16px 18px;
            border-bottom:1px solid #eef2f7;
            text-align:left;
            vertical-align:middle;
        }

        th{
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.7px;
            color:#64748b;
            font-weight:800;
            background:#f8fbff;
            position:sticky;
            top:0;
            z-index:1;
        }

        td{
            font-size:14px;
            color:#1e293b;
        }

        tbody tr{
            transition:.2s ease;
        }

        tbody tr:hover{
            background:#f8fbff;
        }

        .user{
            display:flex;
            align-items:center;
            gap:12px;
        }



        .mini{
            display:block;
            font-size:12px;
            color:var(--muted);
            margin-top:3px;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:8px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:800;
            white-space:nowrap;
        }

        .badge-success{background:rgba(22,163,74,.10); color:var(--success)}
        .badge-warning{background:rgba(245,158,11,.13); color:var(--warning)}
        .badge-danger{background:rgba(220,38,38,.10); color:var(--danger)}
        .badge-primary{background:rgba(46,133,199,.11); color:var(--primary2)}
        .badge-violet{background:rgba(124,58,237,.10); color:var(--violet)}

        .actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .icon-btn{
            border:none;
            background:#eff6ff;
            color:var(--primary);
            border-radius:12px;
            padding:10px 12px;
            font-weight:800;
            cursor:pointer;
            transition:.2s ease;
        }

        .icon-btn:hover{
            background:#dbeafe;
            transform:translateY(-1px);
        }

        .reports-grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:20px;
            margin-bottom:20px;
        }

        .report-card{
            border:1px solid var(--line);
            background:linear-gradient(180deg,#fff 0%,#f8fbff 100%);
            border-radius:22px;
            padding:22px;
            transition:.25s ease;
            cursor:pointer;
        }

        .report-card:hover{
            transform:translateY(-4px);
            box-shadow:0 14px 28px rgba(15,23,42,.08);
        }

        .report-icon{
            width:58px;
            height:58px;
            border-radius:18px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:24px;
            color:#fff;
            margin-bottom:16px;
            background:linear-gradient(135deg,var(--primary),var(--primary2));
        }

        .report-card h3{
            margin:0 0 10px;
            font-size:19px;
            color:#0f172a;
        }

        .report-card p{
            margin:0;
            font-size:14px;
            color:var(--muted);
            line-height:1.65;
        }

        .subgrid{
            display:grid;
            grid-template-columns:1.1fr .9fr;
            gap:20px;
        }

        .insight-list{
            display:grid;
            gap:12px;
        }

        .insight-item{
            border:1px solid var(--line);
            border-radius:16px;
            padding:14px 16px;
            background:#fff;
        }

        .insight-item strong{
            display:block;
            margin-bottom:6px;
            color:#0f172a;
        }

        .progress-bar{
            width:100%;
            height:10px;
            border-radius:999px;
            background:#e2e8f0;
            overflow:hidden;
            margin-top:8px;
        }

        .progress-bar > span{
            display:block;
            height:100%;
            border-radius:999px;
            background:linear-gradient(135deg,var(--primary),var(--primary2));
            transform-origin:left center;
            animation:growBar 1s ease;
        }

        @keyframes growBar{
            from{transform:scaleX(.2)}
            to{transform:scaleX(1)}
        }

        .certificate-card{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            border:1px solid var(--line);
            background:#fff;
            border-radius:18px;
            padding:18px;
            margin-bottom:14px;
            transition:.25s ease;
        }

        .certificate-card:hover{
            transform:translateY(-3px);
            box-shadow:0 14px 28px rgba(15,23,42,.08);
        }

        .cert-left{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .cert-icon{
            width:58px;
            height:58px;
            border-radius:18px;
            background:linear-gradient(135deg,#f59e0b,#fb923c);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:22px;
            font-weight:900;
        }

        .modal-overlay{
            position:fixed;
            inset:0;
            background:rgba(15,23,42,.55);
            display:none;
            align-items:center;
            justify-content:center;
            z-index:1000;
            padding:20px;
        }

        .modal-overlay.show{
            display:flex;
            animation:fadeModal .2s ease;
        }

        @keyframes fadeModal{
            from{opacity:0}
            to{opacity:1}
        }

        .modal-box{
            width:min(100%, 860px);
            max-height:90vh;
            overflow:auto;
            background:#fff;
            border-radius:26px;
            box-shadow:0 24px 60px rgba(15,23,42,.25);
            padding:24px;
            transform:translateY(10px);
            animation:upModal .25s ease forwards;
        }

        @keyframes upModal{
            to{transform:translateY(0)}
        }

        .modal-head{
            display:flex;
            justify-content:space-between;
            gap:16px;
            align-items:flex-start;
            margin-bottom:18px;
        }

        .modal-head h3{
            margin:0;
            font-size:24px;
            color:var(--primary);
            font-weight:900;
        }

        .modal-close{
            border:none;
            background:#eff6ff;
            color:var(--primary);
            width:42px;
            height:42px;
            border-radius:12px;
            font-size:18px;
            cursor:pointer;
            font-weight:900;
        }

        .modal-content-area{
            color:#334155;
            line-height:1.7;
            font-size:15px;
        }

        .modal-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
            margin-top:18px;
        }

        .modal-card{
            border:1px solid var(--line);
            border-radius:16px;
            padding:14px 16px;
            background:#f8fbff;
        }

        .modal-card strong{
            display:block;
            margin-bottom:6px;
        }

        .toast{
            position:fixed;
            right:20px;
            bottom:20px;
            background:#0f172a;
            color:#fff;
            padding:14px 18px;
            border-radius:14px;
            box-shadow:0 18px 40px rgba(15,23,42,.25);
            opacity:0;
            pointer-events:none;
            transform:translateY(14px);
            transition:.25s ease;
            z-index:1200;
            font-size:14px;
            font-weight:700;
        }

        .toast.show{
            opacity:1;
            pointer-events:auto;
            transform:translateY(0);
        }

        @media (max-width: 1200px){
            .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .reports-grid{grid-template-columns:1fr 1fr}
            .subgrid{grid-template-columns:1fr}
        }

        @media (max-width: 760px){
            .page{padding:18px}
            .stats-grid{grid-template-columns:1fr}
            .reports-grid{grid-template-columns:1fr}
            .title-wrap h1{font-size:28px}
            .filters{flex-direction:column}
            .search,.select{width:100%; min-width:100%}
            .certificate-card{flex-direction:column; align-items:flex-start}
            .modal-grid{grid-template-columns:1fr}
        }
    </style>
<div class="page" style="width: 100%;max-width: 100%;">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>

    <div class="topbar">
        <div class="title-wrap">
            <h1>Sessions</h1>
            <p>Aici vezi participanții, istoricul jocurilor, rapoartele și certificatele generate pentru fiecare quiz sau sesiune live.</p>
        </div>

        <div class="top-actions">
            <button class="btn btn-light" id="refreshBtn">Refresh</button>
            <button class="btn btn-primary" id="exportReportBtn">Export raport</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Participanți</div>
            <div class="stat-value" id="statParticipants">47</div>
            <div class="stat-meta">Participări înregistrate în sesiuni</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Games Played</div>
            <div class="stat-value" id="statGames">12</div>
            <div class="stat-meta">Sesiuni jucate live sau solo</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Reports</div>
            <div class="stat-value">3</div>
            <div class="stat-meta">Raport pe quiz, participant și întrebare</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Certificates</div>
            <div class="stat-value" id="statCertificates">18</div>
            <div class="stat-meta">Certificate generate și gata de download</div>
        </div>
    </div>

    <div class="tabs-wrap">
        <div class="tabs">
            <button class="tab-btn active" data-tab="participants">Participants</button>
            <button class="tab-btn" data-tab="games">Games Played</button>
            <button class="tab-btn" data-tab="reports">Reports</button>
            <button class="tab-btn" data-tab="certificates">Certificates</button>
        </div>
    </div>

    <div class="tab-pane active" id="tab-participants">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Lista de participanți</h2>
                    <p>Vezi numele participantului, quiz-ul, scorul, locul ocupat, data și statusul completării.</p>
                </div>
            </div>

            <div class="filters">
                <input class="search" id="participantsSearch" type="text" placeholder="Caută participant sau quiz...">
                <select class="select" id="participantsStatus">
                    <option value="">Toate statusurile</option>
                    <option value="completed">Completed</option>
                    <option value="incomplete">Incomplete</option>
                </select>
            </div>

            <div class="table-wrap">
                <table id="participantsTable">
                    <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Quiz</th>
                        <th>Scor</th>
                        <th>Loc</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Acțiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr data-name="Ana Popa" data-email="ana.popa@email.com" data-quiz="Geografie Europa" data-score="850" data-rank="1" data-date="12.03.2026" data-status="completed" data-time="06:42" data-correct="11" data-wrong="1" data-omitted="0">
                        <td><div class="user"><div class="avatar">A</div><div><strong>Ana Popa</strong><span class="mini">ana.popa@email.com</span></div></div></td>
                        <td>Geografie Europa</td>
                        <td><strong>850</strong></td>
                        <td>1</td>
                        <td>12.03.2026</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>
                            <div class="actions">
                                <button class="icon-btn btn-participant-view">View</button>
                                <button class="icon-btn btn-participant-report">Report</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-name="Ion Rusu" data-email="ion.rusu@email.com" data-quiz="Istorie clasa 7" data-score="620" data-rank="3" data-date="12.03.2026" data-status="completed" data-time="08:05" data-correct="8" data-wrong="4" data-omitted="0">
                        <td><div class="user"><div class="avatar">I</div><div><strong>Ion Rusu</strong><span class="mini">ion.rusu@email.com</span></div></div></td>
                        <td>Istorie clasa 7</td>
                        <td><strong>620</strong></td>
                        <td>3</td>
                        <td>12.03.2026</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>
                            <div class="actions">
                                <button class="icon-btn btn-participant-view">View</button>
                                <button class="icon-btn btn-participant-report">Report</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-name="Maria G." data-email="maria.g@email.com" data-quiz="Training vânzări" data-score="910" data-rank="1" data-date="11.03.2026" data-status="completed" data-time="05:55" data-correct="14" data-wrong="1" data-omitted="0">
                        <td><div class="user"><div class="avatar">M</div><div><strong>Maria G.</strong><span class="mini">maria.g@email.com</span></div></div></td>
                        <td>Training vânzări</td>
                        <td><strong>910</strong></td>
                        <td>1</td>
                        <td>11.03.2026</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>
                            <div class="actions">
                                <button class="icon-btn btn-participant-view">View</button>
                                <button class="icon-btn btn-participant-report">Report</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-name="Elena Toma" data-email="elena.toma@email.com" data-quiz="Biologie umană" data-score="340" data-rank="8" data-date="10.03.2026" data-status="incomplete" data-time="03:14" data-correct="5" data-wrong="3" data-omitted="4">
                        <td><div class="user"><div class="avatar">E</div><div><strong>Elena Toma</strong><span class="mini">elena.toma@email.com</span></div></div></td>
                        <td>Biologie umană</td>
                        <td><strong>340</strong></td>
                        <td>8</td>
                        <td>10.03.2026</td>
                        <td><span class="badge badge-warning">Incomplete</span></td>
                        <td>
                            <div class="actions">
                                <button class="icon-btn btn-participant-view">View</button>
                                <button class="icon-btn btn-participant-report">Report</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-name="Vlad Ceban" data-email="vlad.ceban@email.com" data-quiz="Matematică test" data-score="770" data-rank="2" data-date="09.03.2026" data-status="completed" data-time="07:21" data-correct="10" data-wrong="2" data-omitted="0">
                        <td><div class="user"><div class="avatar">V</div><div><strong>Vlad Ceban</strong><span class="mini">vlad.ceban@email.com</span></div></div></td>
                        <td>Matematică test</td>
                        <td><strong>770</strong></td>
                        <td>2</td>
                        <td>09.03.2026</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>
                            <div class="actions">
                                <button class="icon-btn btn-participant-view">View</button>
                                <button class="icon-btn btn-participant-report">Report</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="tab-games">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Jocurile care sunt jucate</h2>
                    <p>Vezi ce jocuri au fost jucate, tipul lor, câți participanți au intrat, statusul și cine le-a creat.</p>
                </div>
            </div>

            <div class="filters">
                <input class="search" id="gamesSearch" type="text" placeholder="Caută joc sau creator...">
                <select class="select" id="gamesType">
                    <option value="">Toate tipurile</option>
                    <option value="live">Live</option>
                    <option value="solo">Solo</option>
                </select>
                <select class="select" id="gamesStatus">
                    <option value="">Toate statusurile</option>
                    <option value="finished">Finished</option>
                    <option value="progress">In progress</option>
                </select>
            </div>

            <div class="table-wrap">
                <table id="gamesTable">
                    <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Tip</th>
                        <th>Participanți</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Creator</th>
                        <th>Acțiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr data-quiz="Geografie Europa" data-type="live" data-status="finished" data-participants="24" data-date="12.03.2026" data-creator="Prof. Lungu">
                        <td>Geografie Europa</td>
                        <td><span class="badge badge-primary">Live</span></td>
                        <td>24</td>
                        <td><span class="badge badge-success">Finished</span></td>
                        <td>12.03.2026</td>
                        <td>Prof. Lungu</td>
                        <td><div class="actions"><button class="icon-btn btn-game-open">Open</button><button class="icon-btn btn-game-results">Results</button></div></td>
                    </tr>
                    <tr data-quiz="Istorie clasa 7" data-type="solo" data-status="finished" data-participants="8" data-date="12.03.2026" data-creator="Prof. Bivol">
                        <td>Istorie clasa 7</td>
                        <td><span class="badge badge-violet">Solo</span></td>
                        <td>8</td>
                        <td><span class="badge badge-success">Finished</span></td>
                        <td>12.03.2026</td>
                        <td>Prof. Bivol</td>
                        <td><div class="actions"><button class="icon-btn btn-game-open">Open</button><button class="icon-btn btn-game-results">Results</button></div></td>
                    </tr>
                    <tr data-quiz="Training vânzări" data-type="live" data-status="progress" data-participants="15" data-date="11.03.2026" data-creator="Admin Team">
                        <td>Training vânzări</td>
                        <td><span class="badge badge-primary">Live</span></td>
                        <td>15</td>
                        <td><span class="badge badge-warning">In progress</span></td>
                        <td>11.03.2026</td>
                        <td>Admin Team</td>
                        <td><div class="actions"><button class="icon-btn btn-game-open">Open</button><button class="icon-btn btn-game-monitor">Monitor</button></div></td>
                    </tr>
                    <tr data-quiz="Biologie umană" data-type="solo" data-status="finished" data-participants="11" data-date="10.03.2026" data-creator="Prof. Vasile">
                        <td>Biologie umană</td>
                        <td><span class="badge badge-violet">Solo</span></td>
                        <td>11</td>
                        <td><span class="badge badge-success">Finished</span></td>
                        <td>10.03.2026</td>
                        <td>Prof. Vasile</td>
                        <td><div class="actions"><button class="icon-btn btn-game-open">Open</button><button class="icon-btn btn-game-results">Results</button></div></td>
                    </tr>
                    <tr data-quiz="Matematică test" data-type="live" data-status="finished" data-participants="19" data-date="09.03.2026" data-creator="Prof. Cojocaru">
                        <td>Matematică test</td>
                        <td><span class="badge badge-primary">Live</span></td>
                        <td>19</td>
                        <td><span class="badge badge-success">Finished</span></td>
                        <td>09.03.2026</td>
                        <td>Prof. Cojocaru</td>
                        <td><div class="actions"><button class="icon-btn btn-game-open">Open</button><button class="icon-btn btn-game-results">Results</button></div></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="tab-reports">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Raportul</h2>
                    <p>Aici vezi cele 3 tipuri principale de rapoarte: pe quiz, pe participant și pe întrebare.</p>
                </div>
            </div>

            <div class="reports-grid">
                <div class="report-card" data-report-type="quiz">
                    <div class="report-icon">📘</div>
                    <h3>Raport pe quiz</h3>
                    <p>Participanți, scor mediu, rata de completare și întrebările la care s-a greșit cel mai des.</p>
                </div>
                <div class="report-card" data-report-type="participant">
                    <div class="report-icon">👤</div>
                    <h3>Raport pe participant</h3>
                    <p>Scorul fiecărui participant, răspunsurile date, timpul folosit și locul ocupat.</p>
                </div>
                <div class="report-card" data-report-type="question">
                    <div class="report-icon">❓</div>
                    <h3>Raport pe întrebare</h3>
                    <p>Câte răspunsuri corecte și greșite există și care întrebări sunt problematice.</p>
                </div>
            </div>

            <div class="subgrid">
                <div class="panel" style="margin:0">
                    <div class="panel-head">
                        <div>
                            <h2 style="font-size:20px">Raport pe quiz</h2>
                            <p>Geografie Europa · 12.03.2026</p>
                        </div>
                    </div>

                    <div class="insight-list">
                        <div class="insight-item">
                            <strong>Participanți</strong>
                            24 participanți au intrat în quiz.
                        </div>
                        <div class="insight-item">
                            <strong>Scor mediu</strong>
                            Scorul mediu al clasei este <b>742</b> puncte.
                        </div>
                        <div class="insight-item">
                            <strong>Rata de completare</strong>
                            92% dintre participanți au terminat quizul.
                            <div class="progress-bar"><span style="width:92%"></span></div>
                        </div>
                        <div class="insight-item">
                            <strong>Întrebări greșite des</strong>
                            Întrebarea 7 și întrebarea 12 au avut cele mai multe răspunsuri greșite.
                        </div>
                    </div>
                </div>

                <div class="panel" style="margin:0">
                    <div class="panel-head">
                        <div>
                            <h2 style="font-size:20px">Raport pe participant</h2>
                            <p>Ana Popa · Geografie Europa</p>
                        </div>
                    </div>

                    <div class="insight-list">
                        <div class="insight-item">
                            <strong>Scor</strong>
                            850 puncte
                        </div>
                        <div class="insight-item">
                            <strong>Timp total</strong>
                            06:42 minute
                        </div>
                        <div class="insight-item">
                            <strong>Loc ocupat</strong>
                            Locul 1 din 24
                        </div>
                        <div class="insight-item">
                            <strong>Răspunsuri</strong>
                            11 corecte, 1 greșit, 0 omise
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel" style="margin-top:20px; margin-bottom:0">
                <div class="panel-head">
                    <div>
                        <h2 style="font-size:20px">Raport pe întrebare</h2>
                        <p>Întrebările care au pus cele mai multe probleme.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Întrebare</th>
                            <th>Corect</th>
                            <th>Greșit</th>
                            <th>Rată corectă</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Care este capitala Portugaliei?</td>
                            <td>9</td>
                            <td>15</td>
                            <td>37%</td>
                            <td><span class="badge badge-danger">Problematică</span></td>
                        </tr>
                        <tr>
                            <td>Ce fluviu traversează Budapesta?</td>
                            <td>12</td>
                            <td>12</td>
                            <td>50%</td>
                            <td><span class="badge badge-warning">Mediu</span></td>
                        </tr>
                        <tr>
                            <td>Care este capitala Franței?</td>
                            <td>23</td>
                            <td>1</td>
                            <td>96%</td>
                            <td><span class="badge badge-success">Bună</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="tab-certificates">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Certificatele pentru participare</h2>
                    <p>Vezi certificatele generate, descarcă PDF-ul sau retrimite certificatul pe email.</p>
                </div>
            </div>

            <div class="filters">
                <input class="search" id="certSearch" type="text" placeholder="Caută participant sau quiz...">
            </div>

            <div id="certificatesList">
                <div class="certificate-card" data-name="Ana Popa" data-quiz="Geografie Europa" data-date="12.03.2026">
                    <div class="cert-left">
                        <div class="cert-icon">🏅</div>
                        <div>
                            <strong>Ana Popa</strong>
                            <span class="mini">Quiz: Geografie Europa</span>
                            <span class="mini">Data: 12.03.2026</span>
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-light btn-download-cert">Download PDF</button>
                        <button class="btn btn-primary btn-resend-cert">Resend Email</button>
                    </div>
                </div>

                <div class="certificate-card" data-name="Ion Rusu" data-quiz="Istorie clasa 7" data-date="12.03.2026">
                    <div class="cert-left">
                        <div class="cert-icon">🏅</div>
                        <div>
                            <strong>Ion Rusu</strong>
                            <span class="mini">Quiz: Istorie clasa 7</span>
                            <span class="mini">Data: 12.03.2026</span>
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-light btn-download-cert">Download PDF</button>
                        <button class="btn btn-primary btn-resend-cert">Resend Email</button>
                    </div>
                </div>

                <div class="certificate-card" data-name="Maria G." data-quiz="Training vânzări" data-date="11.03.2026">
                    <div class="cert-left">
                        <div class="cert-icon">🏅</div>
                        <div>
                            <strong>Maria G.</strong>
                            <span class="mini">Quiz: Training vânzări</span>
                            <span class="mini">Data: 11.03.2026</span>
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-light btn-download-cert">Download PDF</button>
                        <button class="btn btn-primary btn-resend-cert">Resend Email</button>
                    </div>
                </div>

                <div class="certificate-card" data-name="Vlad Ceban" data-quiz="Matematică test" data-date="09.03.2026">
                    <div class="cert-left">
                        <div class="cert-icon">🏅</div>
                        <div>
                            <strong>Vlad Ceban</strong>
                            <span class="mini">Quiz: Matematică test</span>
                            <span class="mini">Data: 09.03.2026</span>
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-light btn-download-cert">Download PDF</button>
                        <button class="btn btn-primary btn-resend-cert">Resend Email</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="mainModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitle">Detalii</h3>
            <button class="modal-close" id="modalClose">×</button>
        </div>
        <div class="modal-content-area" id="modalContent"></div>
    </div>
</div>

<div class="toast" id="toastBox">Acțiune executată.</div>

<script>
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const modal = document.getElementById('mainModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const modalClose = document.getElementById('modalClose');
    const toastBox = document.getElementById('toastBox');

    function showToast(text){
        toastBox.textContent = text;
        toastBox.classList.add('show');
        setTimeout(() => toastBox.classList.remove('show'), 2200);
    }

    function openModal(title, html){
        modalTitle.textContent = title;
        modalContent.innerHTML = html;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(){
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    function activateTab(tabName){
        tabButtons.forEach(x => x.classList.remove('active'));
        tabPanes.forEach(x => x.classList.remove('active'));

        const btn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
        const pane = document.getElementById('tab-' + tabName);

        if (btn) btn.classList.add('active');
        if (pane) pane.classList.add('active');
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn.dataset.tab));
    });

    function filterTable(tableId, searchId, extraSelectIds = []) {
        const search = document.getElementById(searchId);
        const table = document.getElementById(tableId);
        if (!search || !table) return;

        const run = () => {
            const q = search.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                let ok = text.includes(q);

                extraSelectIds.forEach(id => {
                    const select = document.getElementById(id);
                    if (!select) return;

                    const value = select.value.trim().toLowerCase();
                    if (!value) return;

                    if (id === 'participantsStatus') ok = ok && row.dataset.status === value;
                    if (id === 'gamesType') ok = ok && row.dataset.type === value;
                    if (id === 'gamesStatus') ok = ok && row.dataset.status === value;
                });

                row.style.display = ok ? '' : 'none';
            });
        };

        search.addEventListener('input', run);
        extraSelectIds.forEach(id => {
            const select = document.getElementById(id);
            if (select) select.addEventListener('change', run);
        });
    }

    filterTable('participantsTable', 'participantsSearch', ['participantsStatus']);
    filterTable('gamesTable', 'gamesSearch', ['gamesType', 'gamesStatus']);

    const certSearch = document.getElementById('certSearch');
    const certCards = document.querySelectorAll('.certificate-card');

    certSearch.addEventListener('input', () => {
        const q = certSearch.value.trim().toLowerCase();
        certCards.forEach(card => {
            const text = (card.dataset.name + ' ' + card.dataset.quiz).toLowerCase();
            card.style.display = text.includes(q) ? '' : 'none';
        });
    });

    document.getElementById('refreshBtn').addEventListener('click', () => {
        document.body.animate([
            { opacity: 1, transform: 'translateY(0px)' },
            { opacity: .96, transform: 'translateY(2px)' },
            { opacity: 1, transform: 'translateY(0px)' }
        ], {
            duration: 350,
            easing: 'ease'
        });
        showToast('Datele demo au fost reîmprospătate.');
    });

    document.getElementById('exportReportBtn').addEventListener('click', () => {
        openModal('Export raport', `
            <p>Exportul de raport a fost pregătit în mod demo.</p>
            <div class="modal-grid">
                <div class="modal-card">
                    <strong>Format</strong>
                    Excel / PDF
                </div>
                <div class="modal-card">
                    <strong>Conținut</strong>
                    Participanți, jocuri, rapoarte și certificate
                </div>
            </div>
            <div style="margin-top:18px">
                <button class="btn btn-primary" onclick="showToast('Fișierul demo a fost exportat.')">Descarcă demo</button>
            </div>
        `);
    });

    document.querySelectorAll('.btn-participant-view').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            openModal('Detalii participant', `
                <div class="modal-grid">
                    <div class="modal-card"><strong>Nume</strong>${row.dataset.name}</div>
                    <div class="modal-card"><strong>Email</strong>${row.dataset.email}</div>
                    <div class="modal-card"><strong>Quiz</strong>${row.dataset.quiz}</div>
                    <div class="modal-card"><strong>Status</strong>${row.dataset.status}</div>
                    <div class="modal-card"><strong>Scor</strong>${row.dataset.score}</div>
                    <div class="modal-card"><strong>Loc</strong>${row.dataset.rank}</div>
                    <div class="modal-card"><strong>Data</strong>${row.dataset.date}</div>
                    <div class="modal-card"><strong>Timp</strong>${row.dataset.time} min</div>
                </div>
                <div style="margin-top:18px">
                    <button class="btn btn-light" onclick="showToast('Profil participant deschis.')">Deschide profil</button>
                    <button class="btn btn-primary" onclick="showToast('Mesaj demo trimis participantului.')">Trimite mesaj</button>
                </div>
            `);
        });
    });

    document.querySelectorAll('.btn-participant-report').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            activateTab('reports');
            openModal('Raport participant', `
                <p><b>${row.dataset.name}</b> · ${row.dataset.quiz}</p>
                <div class="modal-grid">
                    <div class="modal-card"><strong>Scor</strong>${row.dataset.score}</div>
                    <div class="modal-card"><strong>Loc</strong>${row.dataset.rank}</div>
                    <div class="modal-card"><strong>Timp total</strong>${row.dataset.time}</div>
                    <div class="modal-card"><strong>Data</strong>${row.dataset.date}</div>
                    <div class="modal-card"><strong>Răspunsuri corecte</strong>${row.dataset.correct}</div>
                    <div class="modal-card"><strong>Răspunsuri greșite</strong>${row.dataset.wrong}</div>
                    <div class="modal-card"><strong>Omise</strong>${row.dataset.omitted}</div>
                    <div class="modal-card"><strong>Status</strong>${row.dataset.status}</div>
                </div>
            `);
        });
    });

    document.querySelectorAll('.btn-game-open').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            openModal('Detalii joc', `
                <div class="modal-grid">
                    <div class="modal-card"><strong>Quiz</strong>${row.dataset.quiz}</div>
                    <div class="modal-card"><strong>Tip</strong>${row.dataset.type}</div>
                    <div class="modal-card"><strong>Participanți</strong>${row.dataset.participants}</div>
                    <div class="modal-card"><strong>Status</strong>${row.dataset.status}</div>
                    <div class="modal-card"><strong>Data</strong>${row.dataset.date}</div>
                    <div class="modal-card"><strong>Creator</strong>${row.dataset.creator}</div>
                </div>
                <div style="margin-top:18px">
                    <button class="btn btn-primary" onclick="showToast('Sesiunea demo a fost deschisă.')">Open session</button>
                </div>
            `);
        });
    });

    document.querySelectorAll('.btn-game-results').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            activateTab('reports');
            openModal('Rezultate joc', `
                <p><b>${row.dataset.quiz}</b></p>
                <div class="modal-grid">
                    <div class="modal-card"><strong>Participanți</strong>${row.dataset.participants}</div>
                    <div class="modal-card"><strong>Scor mediu</strong>742</div>
                    <div class="modal-card"><strong>Rată completare</strong>92%</div>
                    <div class="modal-card"><strong>Status</strong>${row.dataset.status}</div>
                </div>
                <div style="margin-top:18px">
                    <button class="btn btn-light" onclick="showToast('Rezultatele au fost exportate demo.')">Export results</button>
                </div>
            `);
        });
    });

    document.querySelectorAll('.btn-game-monitor').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            openModal('Monitor sesiune live', `
                <p><b>${row.dataset.quiz}</b> este în desfășurare.</p>
                <div class="modal-grid">
                    <div class="modal-card"><strong>Participanți activi</strong>${row.dataset.participants}</div>
                    <div class="modal-card"><strong>Status</strong>Live now</div>
                    <div class="modal-card"><strong>Întrebarea curentă</strong>Întrebarea 8 din 15</div>
                    <div class="modal-card"><strong>Timp rămas</strong>00:18 sec</div>
                </div>
                <div style="margin-top:18px">
                    <button class="btn btn-danger" onclick="showToast('Sesiunea demo a fost oprită.')">Stop session</button>
                </div>
            `);
        });
    });

    document.querySelectorAll('.report-card').forEach(card => {
        card.addEventListener('click', () => {
            const type = card.dataset.reportType;
            let title = 'Raport';
            let html = '';

            if (type === 'quiz') {
                title = 'Raport pe quiz';
                html = `
                    <div class="modal-grid">
                        <div class="modal-card"><strong>Quiz</strong>Geografie Europa</div>
                        <div class="modal-card"><strong>Participanți</strong>24</div>
                        <div class="modal-card"><strong>Scor mediu</strong>742</div>
                        <div class="modal-card"><strong>Rată completare</strong>92%</div>
                        <div class="modal-card"><strong>Întrebări problematice</strong>Întrebarea 7, Întrebarea 12</div>
                        <div class="modal-card"><strong>Status</strong>Finalizat</div>
                    </div>
                `;
            } else if (type === 'participant') {
                title = 'Raport pe participant';
                html = `
                    <div class="modal-grid">
                        <div class="modal-card"><strong>Nume</strong>Ana Popa</div>
                        <div class="modal-card"><strong>Quiz</strong>Geografie Europa</div>
                        <div class="modal-card"><strong>Scor</strong>850</div>
                        <div class="modal-card"><strong>Loc</strong>1</div>
                        <div class="modal-card"><strong>Timp</strong>06:42</div>
                        <div class="modal-card"><strong>Corecte / Greșite</strong>11 / 1</div>
                    </div>
                `;
            } else if (type === 'question') {
                title = 'Raport pe întrebare';
                html = `
                    <div class="modal-grid">
                        <div class="modal-card"><strong>Întrebare</strong>Care este capitala Portugaliei?</div>
                        <div class="modal-card"><strong>Corecte</strong>9</div>
                        <div class="modal-card"><strong>Greșite</strong>15</div>
                        <div class="modal-card"><strong>Rată corectă</strong>37%</div>
                        <div class="modal-card"><strong>Status</strong>Problematică</div>
                    </div>
                `;
            }

            openModal(title, html);
        });
    });

    document.querySelectorAll('.btn-download-cert').forEach(btn => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.certificate-card');
            openModal('Certificat PDF', `
                <p>Certificatul pentru <b>${card.dataset.name}</b> la quizul <b>${card.dataset.quiz}</b> este pregătit pentru download.</p>
                <div class="modal-grid">
                    <div class="modal-card"><strong>Participant</strong>${card.dataset.name}</div>
                    <div class="modal-card"><strong>Quiz</strong>${card.dataset.quiz}</div>
                    <div class="modal-card"><strong>Data</strong>${card.dataset.date}</div>
                    <div class="modal-card"><strong>Format</strong>PDF</div>
                </div>
                <div style="margin-top:18px">
                    <button class="btn btn-primary" onclick="showToast('Certificatul PDF demo a fost descărcat.')">Download PDF</button>
                </div>
            `);
        });
    });

    document.querySelectorAll('.btn-resend-cert').forEach(btn => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.certificate-card');
            openModal('Retrimitere certificat', `
                <p>Vrei să retrimiți certificatul pentru <b>${card.dataset.name}</b>?</p>
                <div class="modal-grid">
                    <div class="modal-card"><strong>Participant</strong>${card.dataset.name}</div>
                    <div class="modal-card"><strong>Quiz</strong>${card.dataset.quiz}</div>
                    <div class="modal-card"><strong>Data</strong>${card.dataset.date}</div>
                    <div class="modal-card"><strong>Acțiune</strong>Resend pe email</div>
                </div>
                <div style="margin-top:18px">
                    <button class="btn btn-primary" onclick="showToast('Certificatul a fost retrimis în mod demo.')">Trimite din nou</button>
                </div>
            `);
        });
    });

    document.querySelectorAll('.report-card, .certificate-card, .stat-card, .panel').forEach((el, i) => {
        el.animate([
            { opacity: 0, transform: 'translateY(14px)' },
            { opacity: 1, transform: 'translateY(0)' }
        ], {
            duration: 420 + i * 20,
            easing: 'ease',
            fill: 'both'
        });
    });
</script>
