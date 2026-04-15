<div class="hk-pg-wrapper pb-0">
    <div class="hk-pg-body py-0">
        <div class="integrationsapp-wrap integrationsapp-sidebar-toggle">
            <div class="integrationsapp-content">
                <div class="integrationsapp-detail-wrap">
                    <header class="integrations-header">
                        <div class="d-flex align-items-center flex-1">
                            <a class="btn btn-icon btn-flush-dark btn-rounded flush-soft-hover flex-shrink-0"
                               href="#"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Back">
                                <span class="btn-icon-wrap">
                                    <span class="feather-icon"><i data-feather="chevron-left"></i></span>
                                </span>
                            </a>
                            <div class="v-separator d-sm-inline-block d-none"></div>
                            <nav class="ms-1 ms-sm-0" aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="#">Game</a></li>
                                    <li class="breadcrumb-item"><a href="#">Metoda Cubului</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Detalii joc</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="integrations-options-wrap">
                            <a class="btn btn-icon btn-flush-dark btn-rounded flush-soft-hover hk-navbar-togglable d-md-inline-block d-none"
                               href="#"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Collapse">
                                <span class="btn-icon-wrap">
                                    <span class="feather-icon"><i data-feather="chevron-up"></i></span>
                                    <span class="feather-icon d-none"><i data-feather="chevron-down"></i></span>
                                </span>
                            </a>
                        </div>
                    </header>

                    <div class="integrations-body">
                        <div data-simplebar class="nicescroll-bar">
                            <div class="container mt-md-7 mt-3">
                                <div class="row">
                                    <div class="col-xxl-8 col-lg-7">
                                        <div class="media">
                                            <div class="media-head me-3">
                                                <div class="avatar avatar-logo">
                                                    <span class="initial-wrap bg-success-light-5">
                                                        <img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/symbol-avatar-15.png" alt="logo">
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="media-body">
                                                <h3 class="hd-bold mb-0">Metoda Cubului – Game</h3>
                                                <span>Galac EduTask | Cube & Profile Game</span>
                                                <div class="d-flex align-items-center mt-1">
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-flex align-items-center rating rating-yellow my-rating-4 me-2" data-rating="4"></div>
                                                        <span>1,248 jucători</span>
                                                    </div>
                                                    <div class="d-sm-flex align-items-center d-none">
                                                        <span class="opacity-15 mx-2">●</span>
                                                        <span class="d-flex align-items-center fs-8">
                                                            <i class="ri-time-line fs-7 me-1 text-primary"></i> ~10–15 min / sesiune
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-4 col-lg-5 mt-lg-0 mt-3">
                                        <button class="btn btn-primary btn-block"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cube_game_modal">
                                            RUN GAME
                                        </button>
                                        <div class="d-flex mt-3">
                                            <button class="btn btn-sm btn-light btn-block">
                                                <span>
                                                    <span class="icon">
                                                        <span class="feather-icon"><i data-feather="share"></i></span>
                                                    </span>
                                                    <span>Share</span>
                                                </span>
                                            </button>
                                            <button class="btn btn-sm btn-light btn-block ms-2 mt-0">
                                                <span>
                                                    <span class="icon">
                                                        <span class="feather-icon"><i data-feather="bookmark"></i></span>
                                                    </span>
                                                    <span>Bookmark</span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- CONTINUT PRINCIPAL -->
                                    <div class="col-xxl-8 col-lg-7">
                                        <!-- Slider -->
                                        <div class="product-detail-slider">
                                            <div id="owl_demo_1" class="owl-carousel owl-primary mt-6">
                                                <div class="item" data-hash="zero"><img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/slide1.jpg" alt="Cube Game"></div>
                                                <div class="item" data-hash="one"><img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/slide2.jpg" alt="Cube Game"></div>
                                                <div class="item" data-hash="two"><img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/slide3.jpg" alt="Cube Game"></div>
                                                <div class="item" data-hash="three"><img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/slide4.jpg" alt="Cube Game"></div>
                                            </div>
                                            <div class="thumb-wrap">
                                                <a class="active-thumb" href="#zero"></a>
                                                <a href="#one"></a>
                                                <a href="#two"></a>
                                                <a href="#three"></a>
                                            </div>
                                        </div>

                                        <div class="separator"></div>

                                        <!-- TAB-URI -->
                                        <ul class="nav nav-light nav-pills nav-pills-rounded justify-content-center">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="pill" href="#tab_cube_1">
                                                    <span class="nav-link-text">Overview</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="pill" href="#tab_cube_2">
                                                    <span class="nav-link-text">Cum se joacă</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="pill" href="#tab_cube_3">
                                                    <span class="nav-link-text">Feedback elevi</span>
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content py-7">
                                            <!-- OVERVIEW -->
                                            <div class="tab-pane fade show active" id="tab_cube_1">
                                                <h5>Ce este Metoda Cubului – Game?</h5>
                                                <p>
                                                    Metoda Cubului este un joc educațional interactiv, construit în jurul unui cub Rubik digital.
                                                    În loc să fie doar un joc de îndemânare, cubul devine un instrument prin care observăm
                                                    <b>cum gândește și cum lucrează un elev</b>: modul în care testează, revine, experimentează și își corectează greșelile.
                                                </p>
                                                <p>
                                                    Fiecare mișcare făcută pe cub poate fi înregistrată și analizată: de la
                                                    <b>durata sesiunii</b> până la <b>tiparele de rotație</b> sau momentele în care elevul se blochează.
                                                    Aceste date pot fi conectate în următoarea versiune cu un profil EduTask, pentru a sugera
                                                    <b>direcții de carieră, roluri în echipă și tipuri de proiecte potrivite</b>.
                                                </p>
                                                <p>
                                                    Jocul face parte din ecosistemul <b>Galac EduTask</b> și poate fi folosit atât individual,
                                                    cât și în clasă sau la workshop-uri de orientare profesională. Profesorii și mentorii pot
                                                    urmări nu doar „rezultatul final”, ci procesul prin care elevul ajunge acolo.
                                                </p>

                                                <div class="row my-7">
                                                    <div class="col-xxl-6">
                                                        <h6>Ce măsoară jocul (versiunea actuală – MVP)</h6>
                                                        <ul class="list-ul ps-3">
                                                            <li class="mb-1">Numărul și succesiunea mișcărilor făcute pe cub.</li>
                                                            <li class="mb-1">Durata sesiunii de joc (de la start până la stop).</li>
                                                            <li class="mb-1">Starea cubului după fiecare modificare (pentru analiză ulterioară / AI).</li>
                                                            <li class="mb-1">Tiparul general: elevul experimentează liber sau merge foarte disciplinat?</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-xxl-6 mt-xxl-0 mt-3">
                                                        <h6>Ce va urma (nivel următor)</h6>
                                                        <ul class="list-ul ps-3">
                                                            <li class="mb-1">Legare cu întrebări scurte de logică, strategie și storytelling.</li>
                                                            <li class="mb-1">Generare de profil: Programator, Strateg, Designer, Storyteller etc.</li>
                                                            <li class="mb-1">Export automat al rezultatelor și generare de certificat digital.</li>
                                                            <li class="mb-1">Integrare cu jocul „Avionul” pentru analiza rolului în echipă.</li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <h6>De ce este diferit acest joc</h6>
                                                <ul class="list-ul ps-3">
                                                    <li class="mb-1">
                                                        Combină <b>mișcarea fizică și intuiția</b> (cub Rubik) cu <b>analiza digitală</b> a datelor.
                                                    </li>
                                                    <li class="mb-1">
                                                        Poate fi folosit în clase de liceu, la universitate, la cluburi de robotică sau în
                                                        sesiuni 1-la-1 cu mentorul.
                                                    </li>
                                                    <li class="mb-1">
                                                        Datele pot fi conectate la un sistem AI pentru recomandări de carieră și proiecte.
                                                    </li>
                                                    <li class="mb-1">
                                                        Se pot crea scenarii speciale: „cub pentru creativi”, „cub pentru programatori”,
                                                        „cub pentru antreprenori”.
                                                    </li>
                                                    <li>
                                                        Este gândit pentru <b>educație practică</b>, nu doar pentru divertisment.
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- CUM SE JOACĂ – DOAR TEXT -->
                                            <div class="tab-pane fade" id="tab_cube_2">
                                                <h5>Cum se joacă Metoda Cubului</h5>
                                                <p>
                                                    Jocul este simplu de pornit, dar poate fi folosit în scenarii foarte avansate.
                                                    Mai jos ai varianta de bază, pentru elevi care intră prima dată în joc.
                                                </p>

                                                <h6 class="mt-4">Pașii de bază pentru elev</h6>
                                                <ol class="ps-3">
                                                    <li class="mb-1">
                                                        Apasă butonul <b>RUN GAME</b> din partea dreaptă sus. Se va deschide un pop-up (modal)
                                                        cu cubul Rubik digital.
                                                    </li>
                                                    <li class="mb-1">
                                                        Cubul pornește deja <b>amestecat</b>. Nu există „variantă corectă” de început –
                                                        important este modul în care încerci să îl rezolvi.
                                                    </li>
                                                    <li class="mb-1">
                                                        Folosește mouse-ul (sau touch, pe telefon) pentru a roti cubul și pentru a muta fețele.
                                                        Navighează liber, încearcă, greșește, revino.
                                                    </li>
                                                    <li class="mb-1">
                                                        Toate mișcările tale sunt înregistrate în fundal. Dacă deschizi consola browserului
                                                        (F12 → tab-ul <b>Console</b>), poți vedea log-ul tehnic.
                                                    </li>
                                                    <li class="mb-1">
                                                        Când sesiunea se încheie, profesorul sau mentorul poate salva datele și le poate folosi
                                                        pentru discuții, feedback și recomandări.
                                                    </li>
                                                </ol>

                                                <h6 class="mt-4">Recomandări pentru profesori / mentori</h6>
                                                <ul class="list-ul ps-3">
                                                    <li class="mb-1">
                                                        Explică elevilor că <b>nu sunt evaluați după „cât de repede rezolvă cubul”</b>,
                                                        ci după modul în care abordează problema.
                                                    </li>
                                                    <li class="mb-1">
                                                        Folosește jocul ca încălzire pentru discuții despre <b>strategie, perseverență,
                                                            lucru sub presiune</b>.
                                                    </li>
                                                    <li class="mb-1">
                                                        Poți combina jocul cu o mini-fişă: „Cum ai gândit?”, „Ce ai schimbat după ce te-ai blocat?”,
                                                        „Ce ai face diferit data viitoare?”.
                                                    </li>
                                                    <li class="mb-1">
                                                        În sesiuni avansate, le poți da elevilor roluri (ex: „tu ești programatorul echipei”)
                                                        și poți lega modul lor de joc de rolul pe care îl joacă.
                                                    </li>
                                                </ul>

                                                <p class="mt-3 mb-0">
                                                    <b>Important:</b> în această versiune, cubul rulează doar în fereastra de joc (modal)
                                                    pentru a nu încărca pagina principală și pentru a fi mai ușor de folosit în clasă
                                                    sau la prezentări.
                                                </p>
                                            </div>

                                            <!-- FEEDBACK -->
                                            <div class="tab-pane fade" id="tab_cube_3">
                                                <div class="review-block">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="title title-lg mb-0 me-3">
                                                                <span>Feedback elevi</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="separator mt-4"></div>

                                                    <div class="review">
                                                        <div class="media align-items-center">
                                                            <div class="media-head">
                                                                <div class="avatar avatar-xs avatar-rounded">
                                                                    <img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/avatar7.jpg" alt="user" class="avatar-img">
                                                                </div>
                                                            </div>
                                                            <div class="media-body">
                                                                <span class="cr-name">Elev, clasa a XI-a</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-sm-nowrap flex-wrap mt-2 mb-1">
                                                            <div class="d-flex align-items-center rating rating-yellow my-rating-4 me-2 mb-sm-0 mb-2" data-rating="4"></div>
                                                            <div><span class="fs-8">Test pilot</span></div>
                                                        </div>
                                                        <p>
                                                            „Mi se pare tare că un joc cu cubul Rubik poate să zică ceva despre ce meserie
                                                            mi se potrivește. E altfel decât testele clasice de pe hârtie.”
                                                        </p>
                                                    </div>

                                                    <div class="separator separator-light"></div>

                                                    <div class="review">
                                                        <div class="media align-items-center">
                                                            <div class="media-head">
                                                                <div class="avatar avatar-xs">
                                                                    <img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/avatar8.jpg" alt="user" class="avatar-img rounded-circle">
                                                                </div>
                                                            </div>
                                                            <div class="media-body">
                                                                <span class="cr-name">Profesor coordonator</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-sm-nowrap flex-wrap mt-2 mb-1">
                                                            <div class="d-flex align-items-center rating rating-yellow my-rating-4 me-2 mb-sm-0 mb-2" data-rating="5"></div>
                                                            <div><span class="fs-8">Sesiune demo</span></div>
                                                        </div>
                                                        <p>
                                                            „Metoda Cubului ne ajută să vedem elevii în acțiune, nu doar în teorie.
                                                            Văd potențial mare pentru orientare, discuții de carieră și proiecte practice
                                                            pe care le putem construi în jurul jocului.”
                                                        </p>
                                                    </div>

                                                    <div class="separator separator-light"></div>

                                                    <div class="review">
                                                        <div class="media align-items-center">
                                                            <div class="media-head">
                                                                <div class="avatar avatar-xs avatar-rounded">
                                                                    <img src="<?=getCurrentUrl()?>/Templates/admin/dist/img/avatar9.jpg" alt="user" class="avatar-img">
                                                                </div>
                                                            </div>
                                                            <div class="media-body">
                                                                <span class="cr-name">Student IT, anul I</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-sm-nowrap flex-wrap mt-2 mb-1">
                                                            <div class="d-flex align-items-center rating rating-yellow my-rating-4 me-2 mb-sm-0 mb-2" data-rating="5"></div>
                                                            <div><span class="fs-8">Laborator experimental</span></div>
                                                        </div>
                                                        <p>
                                                            „Îmi place că pot vedea cum sunt logate mișcările și cum se poate conecta totul
                                                            cu analiza de date. E un exemplu real de cum poți lega jocul, UX-ul și programarea.”
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SIDEBAR -->
                                    <div class="col-xxl-4 col-lg-5">
                                        <div class="content-aside">
                                            <div class="card card-border mt-6">
                                                <div class="card-body">
                                                    <h6 class="mb-4">Categorie joc</h6>
                                                    <div class="tag-cloud">
                                                        <a href="#" class="badge badge-soft-primary">Educație</a>
                                                        <a href="#" class="badge badge-soft-primary">Profilare</a>
                                                        <a href="#" class="badge badge-soft-primary">Gamification</a>
                                                        <a href="#" class="badge badge-soft-primary">Orientare profesională</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card card-border">
                                                <div class="card-body">
                                                    <div class="media align-items-center">
                                                        <div class="media-head me-3">
                                                            <div class="avatar avatar-sm avatar-icon avatar-soft-success avatar-rounded">
                                                                <span class="initial-wrap">
                                                                    <span class="feather-icon"><i data-feather="external-link"></i></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="media-body">
                                                            <h6 class="mb-0">Documentație</h6>
                                                            <a href="#" class="link-muted">
                                                                Metoda Cubului – Concept & Flow EduTask (PDF)
                                                            </a>
                                                            <div class="fs-8 text-muted mt-1">
                                                                Descrierea modului în care jocul se leagă de profilul EduTask,
                                                                rolurile elevilor și scenariile de utilizare în clasă.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card card-border">
                                                <div class="card-body">
                                                    <h6 class="mb-4">Info tehnic</h6>
                                                    <ul class="list-unstyled">
                                                        <li class="mb-3">
                                                            <div class="fs-7">Versiune</div>
                                                            <div class="text-dark fw-medium">v0.9 – MVP tracking</div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="fs-7">Ultima actualizare</div>
                                                            <div class="text-dark fw-medium">Noiembrie 2025</div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="fs-7">Durata recomandată</div>
                                                            <div class="text-dark fw-medium">10–15 minute / elev</div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="fs-7">Ideal pentru</div>
                                                            <div class="text-dark fw-medium">
                                                                Clase de liceu, studenți, cluburi IT, orientare profesională
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="fs-7">Tehnologie</div>
                                                            <div class="text-dark fw-medium">
                                                                WebGL / JavaScript, integrabil în panoul Galac EduTask
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="fs-7">Integrat în</div>
                                                            <div class="text-dark fw-medium d-flex align-items-center">
                                                                Galac EduTask – modul Games & Profilare
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <a href="#" class="d-flex align-items-center link-danger">
                                                                <span class="d-flex"><i class="ri-information-line fs-7 me-1 lh-1"></i></span>
                                                                Raportează o problemă tehnică sau un bug
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div> <!-- /row -->
                            </div> <!-- /container -->
                        </div>
                    </div>
                </div>

                <!-- MODAL: CUBE GAME -->
                <div id="cube_game_modal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <h5 class="mb-2">Metoda Cubului – RUN GAME</h5>
                                <p class="mb-3" style="font-size:14px;opacity:0.8;">
                                    Cubul este deja amestecat. Rotește, experimentează, joacă-te.
                                    Toate mișcările sunt urmărite în consolă (<b>Console log</b>) pentru analiză.
                                    În versiunile următoare, rezultatele vor putea fi legate direct de profilul tău EduTask.
                                </p>

                                <div id="cubeWrapper">
                                    <div id="cubeTest"></div>
                                </div>

                                <div id="cubeGameStatus" class="mt-3" style="font-size:14px;opacity:0.8;">
                                    Aștept cubul să pornească...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /MODAL -->
            </div>
        </div>
    </div>
</div>
<!-- MODAL: Întrebare în timpul jocului -->
<div class="modal fade" id="gameQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-primary">
            <div class="modal-header">
                <h6 class="modal-title">Întrebare rapidă</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="gameQuestionText" class="mb-2"></p>
                <textarea id="gameQuestionAnswer"
                          class="form-control"
                          rows="3"
                          placeholder="Scrie răspunsul tău..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Anulează</button>
                <button class="btn btn-primary" id="gameQuestionSaveBtn">Trimite</button>
            </div>
        </div>
    </div>
</div>
