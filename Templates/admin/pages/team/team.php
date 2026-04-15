<?php
use Evasystem\Controllers\Team\TeamService;
$firmsService  = new TeamService();
$allfirm = $firmsService->getAllFirms();
$firmId = $_SESSION['firm'] ?? null;
?>


<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Firma activă</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Echipă și Roluri</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="/public/addtems" class="btn btn-primary radius-15">
                    <i class="bx bx-plus"></i> Adaugă Firmă
                </a>
            </div>
        </div>
        <!-- End breadcrumb -->

        <h6 class="mb-0 text-uppercase">Echipa firmei <strong>SoftInnov SRL</strong></h6>
        <hr>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
            <!-- Card membru -->
            <?php foreach ($allfirm as $allfirm):?>
            <?php if($allfirm['firma_id'] != $firmId) continue ; ?>
            <div class="col">
                <div class="card border-primary border-bottom border-3 border-0 shadow-sm">
                    <img src="<?=getCurrentUrl();?>/Templates/admin/assets/images/avatars/avatar-1.png" class="card-img-top" alt="Foto membru">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary mb-1"><?=$allfirm['nume']?></h5>
                        <p class="card-text text-muted small"><?=$allfirm['rol']?></p>
                        <hr>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <div class="d-grid"> <a href="/public/profile?id=<?=$allfirm['randomn_id']?>" class="btn btn-outline-primary radius-15">Aceseaza <i class="bx bx-share"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
            <div class="col">
                <div class="card radius-15 border-dashed">
                    <div class="card-body text-center d-flex align-items-center justify-content-center" style="height: 100%;">
                        <a href="/public/addtems" class="text-muted">
                            <i class="bx bx-plus-circle fs-1"></i>
                            <div>Adaugă</div>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Repetă acest card pentru fiecare membru -->

        </div>
    </div>
</div>
