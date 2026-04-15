<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Evasystem\Controllers\Users\UsersService;
use Evasystem\Controllers\Mympas\Mympas;
use Evasystem\Controllers\Mympas\MympasService;

if (!isset($_SESSION['user_id'])) {
    die('Utilizatorul nu este autentificat.');
}

$usersService = new UsersService();
$currentUserData = $usersService->getIdUserss((int)$_SESSION['user_id']);
$currentUser = (is_array($currentUserData) && isset($currentUserData[0])) ? $currentUserData[0] : $currentUserData;
$userRandomnId = (string)($currentUser['randomn_id'] ?? '');

$mympasService = new MympasService();
$mympasController = new Mympas($mympasService);
$mapsData = $mympasController->index($userRandomnId);

$dashboardCards = $mapsData['cards'] ?? [];
$mapPoints = $mapsData['points'] ?? [];
$weekData = $mapsData['week'] ?? [];
$planningItems = $mapsData['planning'] ?? [];
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<style>
    /* =========================
    DASHBOARD GRID (2x2)
 ========================= */
    .dashboard-grid{
        display:grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: auto auto;
        gap:30px;

        width:100%;
        box-sizing:border-box;
    }

    @media (max-width:1024px){
        .dashboard-grid{ grid-template-columns:1fr; }
    }

    /* =========================
       ZONA 1: CARDURI (2x2)
    ========================= */
    .language-stats{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap:20px;
    }

    /* card */
    .stat-card{
        height:165px;
        border-radius:20px;
        padding:20px;
        position:relative;
        overflow:hidden;             /* IMPORTANT: nu lasa imaginea sa iasa */
        box-sizing:border-box;
        color:#fff;
    }

    /* demo culori (schimba dupa design) */
    .stat-card.blue{
        background-image: url("<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/img_prime.png");
        background-repeat: no-repeat;
        background-size: cover;
    }
    .stat-card.orange {
        background-image: url("<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/img_2.png");
        background-repeat: no-repeat;
        background-size: cover;
    }
    .stat-card.green  {
        background-image: url("<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/img_3.png");
        background-repeat: no-repeat;
        background-size: cover;
    }
    .stat-card.yellow {
        background-image: url("<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/img_4.png");
        background-repeat: no-repeat;
        background-size: cover;
    }

    /* =========================
       CARD CONTENT (FIX: FARA fixed!)
    ========================= */
    .box{
        position:relative;
        width:100%;
        height:100%;
    }

    /* AICI era problema: fixed. Trebuie sa fie in card */
    .box .group{
        position:absolute;           /* NU fixed */
        inset:0;                     /* top/right/bottom/left = 0 */
        width:100%;
        height:100%;
    }

    /* imagine fundal */
    .box .img{
        position:absolute;
        inset:0;
        width:100%;
        height:100%;
        object-fit:cover;
        opacity:.25;                 /* sa nu acopere textul */
        pointer-events:none;
    }

    /* titlu */
    .box .text-wrapper{
        position:absolute;
        top:14px;
        left:16px;
        font-family:"Manrope", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        font-weight:700;
        font-size:20px;
        line-height:1.1;
        letter-spacing:0;
        color:#fff;
    }

    /* subtitlu */
    .box .div{
        position:absolute;
        top:42px;
        left:16px;
        font-family:"Manrope", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        font-weight:500;
        font-size:12px;
        line-height:1.2;
        letter-spacing:0;
        color:rgba(255,255,255,.75);
    }

    /* frame/icon dreapta */
    .box .frame{
        position:absolute;
        top:18px;
        right:14px;
        width:50px;
        height:110px;
        object-fit:contain;
    }

    /* progres + procent */
    .box .group-2{
        position:absolute;
        left:16px;
        bottom:-10px;
        width:56px;
        height:56px;
    }

    /* procent centrat */
    .box .text-wrapper-2{
        position:absolute;
        inset:0;
        display:grid;
        place-items:center;
        font-family:"Manrope", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        font-weight:600;
        font-size:14px;
        color: #111111;
        z-index:2;
    }

    /* cerc (fallback simplu). Daca vrei 75% real, iti fac varianta cu conic-gradient */
    .box .group-3{
        position:absolute;
        inset:0;
        border-radius:50%;
        border:4px solid rgba(0,109,211,.35);
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.15), rgba(255,255,255,0) 60%);
        z-index:1;
    }

    /* =========================
       ZONA 2: GRAFIC
    ========================= */
    .activity-chart{
        background: white;
        border-radius:24px;
        padding:30px;
        box-sizing:border-box;
        color:#fff;
    }

    /* =========================
       ZONA 3: HARTA
    ========================= */
    .world-map-container{
        background:#fff;
        border-radius:24px;
        padding:20px;
        box-sizing:border-box;
        overflow:hidden;
    }

    /* daca #map nu are inaltime, nu se vede */
    #map{
        width:100%;
        min-height:260px;
    }

    /* =========================
       ZONA 4: PLANNING
    ========================= */
    .planning-section{
        display:flex;
        flex-direction:column;
        gap:15px;
        background:#fff;
        border-radius:24px;
        padding:20px;
        box-sizing:border-box;
    }

    .project-list{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:15px;
    }

    .project-item{
        background:#f6f7fb;
        border-radius:15px;
        padding:15px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        box-sizing:border-box;
    }


    .box2{
        position: relative;
        width: 53%;
        height: 315px;
        margin: 0 auto;
    }

    /* container intern */
    .box2 .group{
        position: absolute;     /* FIX: nu fixed */
        inset: 0;
        width: 100%;
        height: 100%;
    }

    /* zilele */
    .box2 .text-wrapper,
    .box2 .div,
    .box2 .text-wrapper-2,
    .box2 .text-wrapper-3,
    .box2 .text-wrapper-4,
    .box2 .text-wrapper-5,
    .box2 .text-wrapper-6{
        position: absolute;
        top: 296px;
        font-family: "Manrope", sans-serif;
        font-weight: 500;
        color: #000;
        font-size: 14px;
    }

    /* poziții zile */
    .box2 .text-wrapper   { left: 0; }
    .box2 .div            { left: 58px; }
    .box2 .text-wrapper-2 { left: 120px; }
    .box2 .text-wrapper-3 { left: 175px; }
    .box2 .text-wrapper-4 { left: 246px; }
    .box2 .text-wrapper-5 { left: 303px; }
    .box2 .text-wrapper-6 { left: 361px; }

    /* zona grafic */
    .box2 .group-2{
        position: absolute;
        top: 0;
        left: 0;
        width: 390px;
        height: 276px;
    }

    /* bare */
    .box2 .rectangle,
    .box2 .rectangle-2,
    .box2 .rectangle-3,
    .box2 .rectangle-4,
    .box2 .rectangle-5,
    .box2 .rectangle-6,
    .box2 .rectangle-7{
        position: absolute;
        height: 30px;
        background-color: #369eff;
        border-radius: 20px;
        transform: rotate(-90deg);
    }

    /* individual */
    .box2 .rectangle{
        top: 156px;
        left: -90px;
        width: 210px;
        opacity: 0.2;
    }

    .box2 .rectangle-2{
        top: 167px;
        left: -19px;
        width: 188px;
        opacity: 0.2;
    }

    .box2 .rectangle-3{
        top: 195px;
        left: 69px;
        width: 132px;
        opacity: 0.2;
    }

    .box2 .rectangle-4{
        top: 123px;
        left: 57px;
        width: 276px;
    }

    .box2 .rectangle-5{
        top: 178px;
        left: 172px;
        width: 167px;
        opacity: 0.2;
    }

    .box2 .rectangle-6{
        top: 145px;
        left: 199px;
        width: 232px;
        opacity: 0.2;
    }

    .box2 .rectangle-7{
        top: 162px;
        left: 276px;
        width: 199px;
        opacity: 0.2;
    }
    /* =========================
   MOBILE ONLY (nu atinge desktop)
   Pune la FINAL
========================= */
    @media (max-width: 768px){

        /* 1) Grid -> o singura coloana + padding mai mic */
        .dashboard-grid{
            grid-template-columns: 1fr !important;
            gap: 16px !important;
            padding: 12px !important;
        }

        /* 2) Cardurile (language-stats) -> 1 coloana */
        .language-stats{
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }

        /* 3) Card height + spacing (sa nu fie prea inalt) */
        .stat-card{
            height: 150px !important;
            padding: 16px !important;
            border-radius: 18px !important;
            background-position: center !important;
        }

        /* 4) Textul din carduri putin mai compact */
        .box .text-wrapper{
            font-size: 18px !important;
            top: 12px !important;
            left: 12px !important;
        }
        .box .div{
            top: 38px !important;
            left: 12px !important;
        }
        .box .frame{
            right: 10px !important;
            top: 14px !important;
            width: 44px !important;
            height: 100px !important;
        }
        .box .group-2{
            left: 12px !important;
            bottom: 10px !important; /* sa nu iasa in jos */
        }

        /* 5) Activity chart -> full width, fara 53% */
        .activity-chart{
            padding: 16px !important;
            border-radius: 18px !important;
        }
        .box2{
            width: 100% !important;
            max-width: 390px;         /* pastreaza design-ul */
            height: 315px !important;
            margin: 0 auto !important;
        }

        /* Daca telefonul e mic, scaleaza usor graficul */
        @media (max-width: 420px){
            .box2{
                transform: scale(0.9);
                transform-origin: top center;
                height: 285px !important;
            }
        }

        /* 6) Map + Planning -> spacing corect */
        .world-map-container,
        .planning-section{
            border-radius: 18px !important;
            padding: 16px !important;
        }

        #map{
            min-height: 220px !important;
        }

        /* 7) Planning list -> 1 coloana */
        .project-list{
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }

        .project-item{
            padding: 12px !important;
        }

        .planning-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
        }
        .planning-header h3{
            margin:0;
            font-size:16px;
        }
        .planning-header span{
            font-size:12px;
            opacity:.8;
        }
    }
    /* =========================
   Planning Card (SAFE VERSION)
========================= */

    .planning-card{
        position: relative;
        width: 100%;
        height: 64px;
    }

    /* FIX CRITIC */
    .planning-card .group{
        position: relative;      /* NU fixed */
        width: 100%;
        height: 100%;
    }

    .planning-card .rectangle{
        position: absolute;
        inset: 0;
        background-color: #f7f7f7;
        border-radius: 16px;
        border: 1px solid #0a5084cf;
    }

    /* meniul vertical */
    .planning-card .more-vertical{
        position: absolute;
        top: 23px;
        right: 12px;
        width: 4px;
        height: 18px;
    }

    .planning-card .vector,
    .planning-card .img,
    .planning-card .vector-2{
        position: absolute;
        width: 100%;
        left: 0;
    }

    .planning-card .vector{ height: 61%; top: 39%; }
    .planning-card .img{ height: 100%; top: 0; }
    .planning-card .vector-2{ height: 22%; top: 78%; }

    /* titlu */
    .planning-card .text-wrapper{
        position: absolute;
        top: 12px;
        left: 62px;
        font-family: "Manrope", sans-serif;
        font-weight: 600;
        color: var(--black-font);
        font-size: 12px;
    }

    /* timp */
    .planning-card .div{
        position: absolute;
        top: 32px;
        left: 62px;
        font-family: "Manrope", sans-serif;
        font-weight: 400;
        color: var(--gray-4);
        font-size: 12px;
    }

    /* imagine */
    .planning-card .chatgpt-image{
        position: absolute;
        top: 11px;
        left: 14px;
        width: 41px;
        height: 41px;
        border-radius: 12px;
        object-fit: cover;
    }
    /* 2 coloane (2 rinduri pentru 4 item-uri) */
    .project-list{
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    /* item-ul să ocupe toată coloana */
    .project-item{
        width: 100%;
    }

    /* card-ul să fie full width în interior */
    .planning-card{
        width: 100%;
    }
    @media (max-width: 768px){
        .project-list{
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-grid" style="display: inline;">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>

    <div class="dashboard-grid" style="margin-top: 20px;">
        <section class="language-stats">
            <?php foreach ($dashboardCards as $card): ?>
                <div class="stat-card <?= htmlspecialchars((string)$card['card_color']) ?>">
                    <div class="box">
                        <div class="group">
                            <div class="text-wrapper"><?= htmlspecialchars((string)$card['title']) ?></div>
                            <div class="div"><?= htmlspecialchars((string)$card['subtitle']) ?></div>
                            <img class="frame" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/<?= htmlspecialchars((string)$card['icon_file']) ?>" />
                            <div class="group-2">
                                <div class="text-wrapper-2"><?= (int)$card['percent_value'] ?>%</div>
                                <div class="group-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="activity-chart">
            <div style="display:flex;align-items:flex-end;gap:16px;height:280px;justify-content:space-between;">
                <?php foreach ($weekData as $day): ?>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:10px;flex:1;">
                        <div style="
                                width:32px;
                                height: <?= max(30, (int)$day['value_number']) ?>px;
                                background:#369eff;
                                border-radius:14px 14px 10px 10px;
                                opacity:.85;
                                "></div>
                        <div style="font:500 14px Manrope,sans-serif; color:#000;">
                            <?= htmlspecialchars((string)$day['day_key']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="world-map-container">
            <div id="map"></div>
        </section>

        <section class="planning-section">
            <div class="planning-header">
                <h3>Planning</h3>
                <span><?= date('d F Y') ?></span>
            </div>

            <div class="project-list">
                <?php foreach ($planningItems as $item): ?>
                    <div class="project-item">
                        <div class="planning-card">
                            <div class="group">
                                <div class="rectangle"></div>
                                <div class="text-wrapper"><?= htmlspecialchars((string)$item['title']) ?></div>
                                <p class="div"><?= htmlspecialchars((string)$item['start_time']) ?> - <?= htmlspecialchars((string)$item['end_time']) ?></p>
                                <img class="chatgpt-image" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/<?= htmlspecialchars((string)$item['icon_file']) ?>" />
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>


</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const map = L.map('map', {
            worldCopyJump: true
        }).setView([20, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        const locations = <?= json_encode(array_map(function($point) {
            return [
                'name' => $point['point_name'],
                'coords' => [(float)$point['lat'], (float)$point['lng']],
                'popup' => $point['popup_text'] ?: $point['point_name'],
            ];
        }, $mapPoints), JSON_UNESCAPED_UNICODE) ?>;

        locations.forEach(loc => {
            L.marker(loc.coords)
                .addTo(map)
                .bindPopup(loc.popup);
        });
    });
</script>