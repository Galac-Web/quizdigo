<?php
use Evasystem\Controllers\Avion\AvionService;
use Evasystem\Controllers\Users\UsersService;
//$_SESSION['user_id']
$users = $_SESSION['user_id'];

$allroomsAvion  = new AvionService();
$useersall = new UsersService();
$allAvion = $allroomsAvion->getAllAvions(); // fiecare $avion: id, random_id, title, game_type, level, scenario etc.
$userid = $useersall->getIdUserss($users);



?>

<div class="hk-pg-wrapper">
    <div class="container-xxl">

        <!-- =============================
             HEADER PROFIL + RECOMANDĂRI
        ============================== -->
        <div class="hk-pg-header pt-7 pb-4">
            <div class="row g-4">

                <!-- PROFIL -->
                <div class="col-lg-4 mb-lg-0 mb-3">
                    <div class="card card-border mb-lg-4 mb-3">
                        <div class="card-header card-header-action">
                            <div class="media align-items-center">
                                <div class="media-head me-2">
                                    <div class="avatar avatar-sm avatar-rounded">
                                        <img src="dist/img/avatar3.jpg" alt="user" class="avatar-img">
                                    </div>
                                </div>
                                <div class="media-body">
                                    <div class="fw-medium text-dark">Kate Jones</div>
                                    <div class="fs-7">Business Manager</div>
                                </div>
                            </div>
                            <div class="card-action-wrap">
                                <a class="btn btn-icon btn-rounded btn-flush-dark flush-soft-hover dropdown-toggle no-caret"
                                   href="#" data-bs-toggle="dropdown">
                                    <span class="icon">
                                        <span class="feather-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                 class="feather feather-settings">
                                                <circle cx="12" cy="12" r="3"></circle>
                                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0
                                                2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65
                                                0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0
                                                9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0
                                                1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0
                                                0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0
                                                1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6
                                                9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83
                                                2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65
                                                1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0
                                                1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65
                                                0 0 0 1.82-.33l.06-.06a2 2 0 0 1
                                                2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65
                                                0 0 0-.33 1.82V9a1.65 1.65 0 0 0
                                                1.51 1H21a2 2 0 0 1 2 2 2 2 0 0
                                                1-2 2h-.09a1.65 1.65 0 0
                                                0-1.51 1z"></path>
                                            </svg>
                                        </span>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="d-flex text-center">
                                <div class="flex-1 border-end">
                                    <span class="d-block fs-4 text-dark mb-1">154</span>
                                    <span class="d-block fs-7">photos</span>
                                </div>
                                <div class="flex-1 border-end">
                                    <span class="d-block fs-4 text-dark mb-1">65</span>
                                    <span class="d-block fs-7">followers</span>
                                </div>
                                <div class="flex-1">
                                    <span class="d-block fs-4 text-dark mb-1">433</span>
                                    <span class="d-block fs-7">views</span>
                                </div>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0">
                                <i class="bi bi-calendar-check-fill text-disabled me-2"></i>
                                <span class="text-muted">Went to:</span>
                                <span class="ms-2">Oh, Canada</span>
                            </li>
                            <li class="list-group-item border-0">
                                <i class="bi bi-briefcase-fill text-disabled me-2"></i>
                                <span class="text-muted">Worked at:</span>
                                <span class="ms-2">Companey</span>
                            </li>
                            <li class="list-group-item border-0">
                                <i class="bi bi-house-door-fill text-disabled me-2"></i>
                                <span class="text-muted">Lives in:</span>
                                <span class="ms-2">San Francisco, CA</span>
                            </li>
                            <li class="list-group-item border-0">
                                <i class="bi bi-geo-alt-fill text-disabled me-2"></i>
                                <span class="text-muted">From:</span>
                                <span class="ms-2">Settle, WA</span>
                            </li>
                        </ul>
                    </div>

                    <!-- TWITTER BOX -->
                    <div class="card bg-primary text-center">
                        <div class="twitter-slider-wrap card-body">
                            <div class="twitter-icon text-center mb-3">
                                <i class="fab fa-twitter"></i>
                            </div>

                            <div id="tweets_fetch" class="owl-carousel light-owl-dots owl-theme">
                                <!-- conținutul slider-ului rămâne neschimbat -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =============================
                     ROOM + STATUS ROL
                ============================== -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card card-border h-100">

                        <!-- header cu buton de creare room -->
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">Status Avion + Room-ul tău</h6>
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createRoomModal">
                                + Creează Room
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="row gx-3" id="roomsContainer">
                                <?php foreach ($allAvion as $avion) : ?>
                                    <?php
                                    $roomId    = $avion['id'] ?? null;
                                    $randomId  = $avion['randomn_id'] ?? '';
                                    $title     = $avion['title'] ?? 'Room fără titlu';
                                    $gameType  = $avion['game_type'] ?? 'Metoda Avionului';
                                    $level     = $avion['level'] ?? 'Nedefinit';
                                    $scenario  = $avion['scenario'] ?? 'Scenariu nespecificat în BD.';
                                    ?>
                                    <div class="col-lg-3 mb-3">
                                        <div class="card card-border contact-card">
                                            <div class="card-body text-center">
                                                <div class="avatar avatar-xl avatar-rounded">
                                                    <img src="dist/img/avatar2.jpg" class="avatar-img" alt="">
                                                </div>

                                                <div class="user-name fw-bold mt-2">
                                                    <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>

                                                <!-- Descriere scurtă ROOM -->
                                                <div class="text-muted fs-8 mb-2">
                                                    Room #<?php echo (int)$roomId; ?><br>
                                                    Tip: <?php echo htmlspecialchars($gameType, ENT_QUOTES, 'UTF-8'); ?><br>
                                                    Nivel: <?php echo htmlspecialchars($level, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>

                                                <!-- Buton: Alege Rolul (legat de room + random_id) -->
                                                <button class="btn btn-sm btn-primary mt-2 select-role-btn"
                                                        data-room-id="<?php echo (int)$roomId; ?>"
                                                        data-random-id="<?php echo htmlspecialchars($randomId, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-game-type="<?php echo htmlspecialchars($gameType, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-level="<?php echo htmlspecialchars($level, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-scenario="<?php echo htmlspecialchars($scenario, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#selectRoleModal">
                                                    Alege Rolul
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="separator-full my-4"></div>

                            <!-- DESCRIERE AVION -->
                            <h6 class="mb-2">Despre Metoda Avionului</h6>
                            <p class="fs-8 text-muted mb-0">
                                Este o simulare în care <b>fiecare rol vede aceeași poveste</b>
                                din unghi diferit: Director, Marketolog, Client sau Vânzător.
                                Deciziile tale influențează analiza finală și recomandările EduTask.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- FOOTER -->
        <div class="hk-footer">
            <footer class="container-xxl footer">
                <div class="row">
                    <div class="col-xl-8">
                        <p class="footer-text">
                            Galac EduTask · Metoda Avionului · Sistem de orientare modern.
                        </p>
                    </div>
                </div>
            </footer>
        </div>

    </div>
</div>

<!-- ============================
     MODAL: Selectare Rol (DINAMIC PE ROOM)
============================= -->
<div class="modal fade" id="selectRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bold" id="modalRoomTitle">Simulare „Metoda Avionului”</h4>
                    <div class="small text-muted mt-1" id="modalRoomInfo">
                        <!-- ex: Room #3 · Random: ABC123 · Tip: Metoda Avionului – Standard · Nivel: Mediu -->
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Situație specifică Room-ului (din BD / template) -->
                <div class="mb-4">
                    <h5 class="fw-bold">📌 Situația Room-ului selectat</h5>
                    <p class="text-muted fs-7 mb-0" id="modalRoomScenario">
                        Selectează un Room pentru a vedea situația lui.
                    </p>
                </div>

                <hr>

                <!-- Descriere generală -->
                <div class="mb-4">
                    <h5 class="fw-bold">🎮 Scenariul Jocului (General)</h5>
                    <p class="text-muted mb-0">
                        Te afli într-o simulare reală de business în care o companie aeriană mică
                        se confruntă cu pierderi, reclamații și o scădere rapidă a reputației.
                        Fiecare jucător primește un rol diferit în avion și vede povestea
                        din perspectiva sa.
                        <br><br>
                        Obiectivul tău este să analizezi situația și să iei decizii în 4 faze:
                        <b>Observare → Analiză → Decizie → Concluzie</b>.
                    </p>
                </div>

                <hr>

                <!-- Alegerea rolului -->
                <div class="mb-3">
                    <h5 class="fw-bold mb-3">✈️ Alege Rolul Tău în acest Room</h5>

                    <div class="list-group">
                        <button class="list-group-item list-group-item-action role-option" data-role="Director">
                            Director — vezi totul de sus, iei decizia finală.
                        </button>

                        <button class="list-group-item list-group-item-action role-option" data-role="Marketolog">
                            Marketolog — analizezi reclamațiile și imaginea publică.
                        </button>

                        <button class="list-group-item list-group-item-action role-option" data-role="Client">
                            Client — simți direct experiența și emoțiile zborului.
                        </button>

                        <button class="list-group-item list-group-item-action role-option" data-role="Vinzator">
                            Vânzător — interacționezi cu clienții și vezi tensiunile interne.
                        </button>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
            </div>

        </div>
    </div>
</div>

<!-- ============================
     MODAL: Creare Room Nou
============================= -->
<div class="modal fade" id="createRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title fw-bold">Creează un Room nou</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="createRoomForm">
                <div class="modal-body">

                    <!-- Select scenariu predefinit -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alege scenariul / tipul de joc</label>
                        <select class="form-select" id="roomTemplate" required>
                            <option value="">Selectează un scenariu...</option>
                            <!-- opțiunile sunt generate din JS din GAME_TEMPLATES -->
                        </select>
                    </div>

                    <!-- Detalii scenariu selectat (din array) -->
                    <div id="templateDetails" class="border rounded p-3 bg-light">
                        <div class="mb-2">
                            <span class="fw-semibold">Titlu recomandat Room:</span>
                            <div id="tmplTitle" class="small text-muted">—</div>
                        </div>

                        <div class="mb-2">
                            <span class="fw-semibold">Tip joc / simulare:</span>
                            <div id="tmplGameType" class="small text-muted">—</div>
                        </div>

                        <div class="mb-2">
                            <span class="fw-semibold">Nivel dificultate:</span>
                            <div id="tmplLevel" class="small text-muted">—</div>
                        </div>

                        <div class="mb-2">
                            <span class="fw-semibold">Scenariu / Situație descrisă:</span>
                            <div id="tmplScenario" class="small text-muted">Selectează un scenariu din listă.</div>
                        </div>

                        <div>
                            <span class="fw-semibold">Indicatori cheie:</span>
                            <ul id="tmplIndicators" class="small text-muted mb-0 ps-3">
                                <!-- se populează din JS -->
                            </ul>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulează</button>
                    <button type="submit" class="btn btn-primary">Salvează Room</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    // ==============================
    // TEMPLATE-URI PREDEFINITE
    // ==============================
    const GAME_TEMPLATES = [
        {
            id: 'standard',
            title: 'Avion – Simulare Standard',
            game_type: 'Metoda Avionului – Standard',
            level: 'Mediu – Studenți / Juniori',
            scenario: 'Zbor intern cu 48 de pasageri, întârziere de 25 de minute, 2 clienți cu experiențe negative anterioare, echipă tensionată și raport de scădere a vânzărilor cu 12% în ultima lună.',
            indicators: [
                'Reacții emoționale ale clienților în timpul întârzierii.',
                'Comunicarea echipei (stewardese, vânzător, marketolog).',
                'Felul în care directorul ia decizia finală.',
                'Impactul deciziilor asupra reputației firmei.'
            ]
        },
        {
            id: 'criza_clienti',
            title: 'Criză Clienți Nemulțumiți',
            game_type: 'Criză de reputație & relație cu clienții',
            level: 'Avansat – Manageri / Antreprenori',
            scenario: 'În timpul zborului, un client VIP filmează o situație tensionată și o postează live pe social media. Reclamațiile cresc, iar echipa trebuie să gestioneze criza în timp real.',
            indicators: [
                'Gestionarea unui client VIP / influencer.',
                'Răspunsul echipei la o criză publică.',
                'Coordonarea între director, marketolog și vânzător.',
                'Rezultatul final: escaladare sau calmare a situației.'
            ]
        },
        {
            id: 'scadere_vanzari',
            title: 'Scădere Vânzări & Reputație',
            game_type: 'Analiză cauze + soluții',
            level: 'Mediu – Studenți / Juniori',
            scenario: 'Compania aeriană a pierdut 18% din vânzări în ultimele 3 luni. Zborul curent devine un studiu de caz în timp real, iar fiecare rol trebuie să identifice cauzele ascunse.',
            indicators: [
                'Analiza feedback-ului clienților pe termen lung.',
                'Legătura dintre experiența de zbor și decizia de a recomanda firma.',
                'Propunerea de soluții concrete, nu doar critici.',
                'Gândire pe termen scurt vs termen lung.'
            ]
        },
        {
            id: 'training_echipa',
            title: 'Training Echipă de Vânzări în Avion',
            game_type: 'Exercițiu de training & coaching',
            level: 'Ușor – Începători',
            scenario: 'Zbor scurt, fără crize majore. Focusul este pe cum interacționează echipa cu clienții, cum face upsell și cum colectează feedback util pentru marketing.',
            indicators: [
                'Abilitatea de a pune întrebări bune clienților.',
                'Tehnici simple de vânzare și upsell.',
                'Observarea detaliilor și notarea lor pentru marketing.',
                'Colaborarea între roluri fără stare de criză.'
            ]
        }
    ];

    let currentRoomId   = null;
    let currentRandomId = null;

    const roomsContainer   = document.getElementById('roomsContainer');
    const createRoomForm   = document.getElementById('createRoomForm');
    const templateSelect   = document.getElementById('roomTemplate');
    const tmplTitle        = document.getElementById('tmplTitle');
    const tmplGameType     = document.getElementById('tmplGameType');
    const tmplLevel        = document.getElementById('tmplLevel');
    const tmplScenario     = document.getElementById('tmplScenario');
    const tmplIndicatorsUl = document.getElementById('tmplIndicators');

    const modalRoomTitle    = document.getElementById('modalRoomTitle');
    const modalRoomInfo     = document.getElementById('modalRoomInfo');
    const modalRoomScenario = document.getElementById('modalRoomScenario');

    // ==============================
    // FUNCȚIE: atașează handler pe toate butoanele "Alege Rolul"
    // ==============================
    function attachSelectRoleHandlers(context) {
        const scope = context || document;
        scope.querySelectorAll(".select-role-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                console.log(currentRandomId);
                currentRoomId   = this.dataset.roomId || null;
                currentRandomId = this.dataset.randomId || null;

                const title     = this.dataset.title || 'Simulare „Metoda Avionului”';
                const gameType  = this.dataset.gameType || '';
                const level     = this.dataset.level || '';
                const scenario  = this.dataset.scenario || '';

                modalRoomTitle.textContent = title;

                const parts = [];
                if (currentRoomId)   parts.push('Room #' + currentRoomId);
                if (currentRandomId) parts.push('Random: ' + currentRandomId);
                if (gameType)        parts.push('Tip: ' + gameType);
                if (level)           parts.push('Nivel: ' + level);
                modalRoomInfo.textContent = parts.join(' · ');

                modalRoomScenario.textContent = scenario || 'Scenariu nespecificat pentru acest Room.';
            });
        });
    }

    // inițial pentru toate cardurile generate de PHP
    attachSelectRoleHandlers(document);

    // ==============================
    // SELECTARE ROL – trimite în BD room_id + random_id + role
    // ==============================
    document.querySelectorAll(".role-option").forEach(roleBtn => {
        roleBtn.addEventListener("click", function () {
            const role = this.dataset.role;

            if (!currentRoomId && !currentRandomId) {
                alert("Nu este selectat niciun Room.");
                return;
            }

            fetch("/public/crudavion", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({
                    room_id: currentRoomId,
                    room_random_id: currentRandomId,
                    role: role,
                    type_product:'role'
                })
            })
                .then(res => res.json().catch(() => ({})))
                .then(data => {
                    console.log(data)
                    // poți adapta după răspunsul real din PHP
                    //console.log('set_role response:', data);
                })
                .catch(err => {
                    console.error(err);
                });

            //const modalEl = document.getElementById("selectRoleModal");
            //const modal   = bootstrap.Modal.getInstance(modalEl);
            //modal.hide();

            //alert("Rol selectat: " + role);
        });
    });

    // ==============================
    // POPULARE SELECT CU TEMPLATE-URI
    // ==============================
    function renderTemplateOptions() {
        if (!templateSelect) return;

        GAME_TEMPLATES.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.title;
            templateSelect.appendChild(opt);
        });
    }

    function renderTemplateDetails(templateId) {
        const tmpl = GAME_TEMPLATES.find(t => t.id === templateId);
        tmplIndicatorsUl.innerHTML = '';

        if (!tmpl) {
            tmplTitle.textContent    = '—';
            tmplGameType.textContent = '—';
            tmplLevel.textContent    = '—';
            tmplScenario.textContent = 'Selectează un scenariu din listă.';
            return;
        }

        tmplTitle.textContent    = tmpl.title;
        tmplGameType.textContent = tmpl.game_type;
        tmplLevel.textContent    = tmpl.level;
        tmplScenario.textContent = tmpl.scenario;

        tmpl.indicators.forEach(i => {
            const li = document.createElement('li');
            li.textContent = i;
            tmplIndicatorsUl.appendChild(li);
        });
    }

    if (templateSelect) {
        renderTemplateOptions();

        templateSelect.addEventListener('change', function () {
            renderTemplateDetails(this.value);
        });
    }

    // ==============================
    // CREARE ROOM DIN TEMPLATE (salvăm în BD, apoi adăugăm card nou)
    // ==============================
    if (createRoomForm) {
        createRoomForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const selectedId = templateSelect.value;
            if (!selectedId) {
                alert("Te rog alege un scenariu.");
                return;
            }

            const tmpl = GAME_TEMPLATES.find(t => t.id === selectedId);
            if (!tmpl) {
                alert("Template invalid.");
                return;
            }

            const payload = {
                template_id: tmpl.id,
                title:       tmpl.title,
                game_type:   tmpl.game_type,
                level:       tmpl.level,
                scenario:    tmpl.scenario,
                type_product:'add'
            };

            fetch("/public/crudavion", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    console.log('crudavion response:', data);

                    if (data.success) {
                        const roomId   = data.room_id;        // asigură-te că backend-ul trimite astea
                        const randomId = data.random_id || ''; // random_id generat în PHP

                        const col = document.createElement('div');
                        col.className = 'col-lg-3 mb-3';

                        col.innerHTML = `
                            <div class="card card-border contact-card">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl avatar-rounded">
                                        <img src="dist/img/avatar2.jpg" class="avatar-img" alt="">
                                    </div>
                                    <div class="user-name fw-bold mt-2">${tmpl.title}</div>
                                    <div class="text-muted fs-8 mb-2">
                                        Room #${roomId}<br>
                                        Tip: ${tmpl.game_type}<br>
                                        Nivel: ${tmpl.level}
                                    </div>
                                    <button class="btn btn-sm btn-primary mt-2 select-role-btn"
                                            data-room-id="${roomId}"
                                            data-random-id="${randomId}"
                                            data-title="${tmpl.title}"
                                            data-game-type="${tmpl.game_type}"
                                            data-level="${tmpl.level}"
                                            data-scenario="${tmpl.scenario}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#selectRoleModal">
                                        Alege Rolul
                                    </button>
                                </div>
                            </div>
                        `;

                        roomsContainer.appendChild(col);

                        // atașăm handler pentru noul buton "Alege Rolul"
                        attachSelectRoleHandlers(col);

                        const modalEl = document.getElementById('createRoomModal');
                        const modal   = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        createRoomForm.reset();
                        renderTemplateDetails(''); // reset preview

                    } else {
                        alert("Eroare la creare Room. Verifică backend-ul.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("A apărut o eroare la conexiune.");
                });
        });
    }
</script>
