<?php
function getCurrentUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

// Dacă ai datele studentului din sesiune:
$studentName  = $_SESSION['name'] ?? "Student";
$studentEmail = $_SESSION['email'] ?? "student@example.com";
?>
<!doctype html>
<html lang="ro">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="<?=getCurrentUrl();?>/Templates/admin/assets/images/favicon-32x32.png" type="image/png" />

    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />

    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/pace.min.css" rel="stylesheet" />
    <script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/pace.min.js"></script>

    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/app.css" rel="stylesheet">
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/icons.css" rel="stylesheet">

    <title>Avion & Cub – Panou Student</title>
</head>

<body>
<div class="wrapper">
    <div class="section-authentication-cover">
        <div class="container-fluid py-5">
            <div class="row g-4 justify-content-center align-items-stretch">

                <!-- ============================
                     COL STÂNGA — HERO COLORAT
                ================================== -->
                <div class="col-12 col-lg-7">
                    <div class="card radius-10 shadow-sm border-0 overflow-hidden"
                         style="background: linear-gradient(135deg, #4e54c8, #8f94fb); color: #fff;">

                        <div class="card-body p-5">
                            <span class="badge bg-light text-dark rounded-pill px-3 py-2 mb-3">
                                👋 Bine ai venit în platformă
                            </span>

                            <h1 class="fw-bold mb-3">
                                Începe aventura ta de învățare!
                                <div class="fw-normal">Teste interactive Avion & Cub</div>
                            </h1>

                            <p class="lead mb-4">
                                Te-ai înregistrat cu succes. Următorul pas este să intri într-un <strong>Room</strong>
                                creat de profesor și să participi la teste interactive în timp real.
                            </p>

                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-3"
                                             style="width: 45px; height: 45px;">
                                            <i class="bx bx-rocket fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Simulare „Avion”</h6>
                                            <p class="small mb-0">Roluri, decizii, timp real. Experiență unică.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-3"
                                             style="width: 45px; height: 45px;">
                                            <i class="bx bx-cube-alt fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Test „Cub”</h6>
                                            <p class="small mb-0">Logică, viteză, analiză și rezultate instant.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 small">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white rounded-circle text-primary d-flex align-items-center justify-content-center me-2"
                                         style="width: 32px; height: 32px;">
                                        <i class="bx bx-shield-quarter"></i>
                                    </div>
                                    Datele tale sunt protejate și folosite doar pentru evaluare.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================
                     COL DREAPTA — CARD STUDENT + JOIN ROOM
                ========================================= -->
                <div class="col-12 col-lg-4">
                    <!-- JOIN ROOM CARD -->
                    <div class="card radius-10 shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Intră într-un Room</h6>
                        </div>
                        <div class="card-body">
                            <form action="/public/joinroom" method="get">
                                <div class="mb-3">
                                    <label class="form-label">Cod Room (UID)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bx bx-key"></i>
                                        </span>
                                        <input type="text" name="uid" class="form-control"
                                               placeholder="Ex: ABC123XY" required>
                                    </div>
                                </div>

                                <button class="btn btn-primary w-100">
                                    <i class="bx bx-log-in-circle me-1"></i> Intră în Room
                                </button>
                            </form>

                            <p class="small text-muted mt-2 mb-0">
                                Codul îți este trimis de profesor.
                            </p>
                        </div>
                    </div>

                    <!-- STUDENT INFO CARD -->
                    <div class="card radius-10 shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Datele tale</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Nume:</strong> <?= htmlspecialchars($studentName); ?></li>
                                <li><strong>Email:</strong> <?= htmlspecialchars($studentEmail); ?></li>
                                <li><strong>Rol:</strong> Student</li>
                            </ul>

                            <p class="small text-muted mt-2">
                                Dacă datele nu sunt corecte, contactează profesorul sau administratorul.
                            </p>
                        </div>
                    </div>
                </div>

            </div> <!-- row -->
        </div> <!-- container -->
    </div>
</div>

<!-- JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/jquery.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/app.js"></script>

</body>
</html>
