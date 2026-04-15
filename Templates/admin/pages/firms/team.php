


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
            <div class="col">
                <div class="card border-primary border-bottom border-3 border-0 shadow-sm">
                    <img src="<?=getCurrentUrl();?>/Templates/admin/assets/images/avatars/avatar-1.png" class="card-img-top" alt="Foto membru">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary mb-1">Ion Munteanu</h5>
                        <p class="card-text text-muted small">Manager Proiecte • Alăturat în 2022</p>
                        <hr>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="/firm/team/view/1" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-user-circle"></i> Profil
                            </a>
                            <a href="mailto:ion@example.com" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-envelope"></i> Email
                            </a>
                            <a href="tel:+37360000001" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-phone"></i> Sună
                            </a>
                            <a href="/firm/team/tasks/1" class="btn btn-sm btn-outline-success">
                                <i class="bx bx-task"></i> Taskuri
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repetă acest card pentru fiecare membru -->

        </div>
    </div>
</div>
