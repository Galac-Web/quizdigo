<?php
use Evasystem\Controllers\Firm\FirmService;

$firmsService  = new FirmService();
$allfirm = $firmsService->getAllFirms();

?>

<div class="page-wrapper">
    <div class="page-content">

        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Firmele Mele</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Lista firmelor</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="/public/addfirms" class="btn btn-primary radius-15">
                    <i class="bx bx-plus"></i> Adaugă Firmă
                </a>
            </div>
        </div>

        <hr>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <!-- Card firmă -->
            <?php
            foreach ($allfirm as $firm):
            ?>
                <div class="col">
                    <div class="card radius-15">
                        <div class="card-body text-center">
                            <div class="p-4 border radius-15">
                                <img src="<?=getCurrentUrl();?>/Templates/admin/assets/corporation.png" width="110" height="110" class="rounded-circle shadow" alt="Logo firmă">

                                <h5 class="mb-0 mt-4">
                                    <?=$firm['company_name']?>
                                    <?php
                                    // Afișează badge "Activă" dacă este firma curentă
                                    if (!empty($_SESSION['firm']) && $_SESSION['firm'] === $firm['randomn_id']) {
                                        echo '<span class="badge bg-success ms-2">Activă</span>';
                                    }
                                    ?>
                                </h5>

                                <p class="mb-1"><?=$firm['domain']?></p>
                                <p class="text-muted small">0 membri • 0 proiecte active</p>

                                <div class="d-grid gap-2 mt-3">
                                    <?php
                                    $activat = $firmsService->getIdFirms($firm['randomn_id']);

                                    // Dacă nu există firmă activă în sesiune => arată butonul
                                    if (empty($_SESSION['firm'])) {
                                        echo '<a href="#" data-action="activate" data-id="'.$firm['randomn_id'].'" class="btn btn-outline-primary radius-15">Activează</a>';
                                    }
                                    // Dacă firma e diferită de cea activă => arată butonul
                                    elseif ($activat[0]['randomn_id'] !== $_SESSION['firm']) {
                                        echo '<a href="#" data-action="activate" data-id="'.$firm['randomn_id'].'" class="btn btn-outline-primary radius-15">Activează</a>';
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-6"> <a href="/public/profileroom?id=<?=$firm['randomn_id'];?>" class="btn btn-outline-info px-5 radius-30">Editează</a></div>
                                        <div class="col-lg-6"> <a href="#" class="btn btn-outline-danger px-5 radius-30" data-action="delete"  data-id="<?=$firm['randomn_id'];?>">Delet</a></div>
                                    </div>



                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>



            <!-- Adaugă firmă (card final) -->
            <div class="col">
                <div class="card radius-15 border-dashed">
                    <div class="card-body text-center d-flex align-items-center justify-content-center" style="height: 100%;">
                        <a href="/public/addfirms" class="text-muted">
                            <i class="bx bx-plus-circle fs-1"></i>
                            <div>Adaugă firmă nouă</div>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-action="activate"]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault(); // prevenim navigarea

                const id = btn.dataset.id;
                const action = btn.dataset.action || 'activate';

                const data = {
                    type_product: action,
                    id: id
                };

                fetch('/public/addusers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Eroare la server');
                        return response.json();
                    })
                    .then(result => {
                        console.log(result.data);
                        //alert(result.message || 'Acțiune executată!');
                        // Redirect dacă e nevoie:
                         window.location.href = "/public/firms";
                    })
                    .catch(error => {
                        alert('Eroare la trimiterea datelor.');
                        console.error('[ActivateBtn] Eroare:', error);
                    });
            });
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();

                if (!confirm("Ești sigur că vrei să ștergi această firmă?")) return;

                const id = btn.dataset.id;
                const action = btn.dataset.action || 'delete';

                const data = {
                    type_product: action,
                    id: id
                };

                fetch('/public/addusers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Eroare la server');
                        return response.json();
                    })
                    .then(result => {
                        window.location.href = "/public/firms";
                    })
                    .catch(error => {
                        alert('Eroare la trimiterea datelor.');
                        console.error('[DeleteFirmBtn] Eroare:', error);
                    });
            });
        });
    });
</script>