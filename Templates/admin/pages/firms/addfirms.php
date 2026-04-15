<div class="page-wrapper" data-page="addfirm">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Firme</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="https://worldwinner.online/public/firms"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Adaugă firmă</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Adaugă o firmă nouă în sistem</h5>
                <hr>
                <form id="addFirmForm" data-autosave data-endpoint="/public/addusers" data-method="POST" data-success="Firmă adăugată cu succes!">
                    <div class="form-body mt-4">
                        <div class="row">
                            <!-- Col stânga -->
                            <div class="col-lg-8">
                                <div class="border border-3 p-4 rounded">
                                    <div class="mb-3">
                                        <label for="companyName" class="form-label">Denumire Firmă</label>
                                        <input type="text" class="form-control" id="companyName" name="company_name" required>

                                    </div>
                                    <div class="mb-3">
                                        <label for="companyDescription" class="form-label">Descriere</label>
                                        <textarea class="form-control" id="companyDescription" name="description" rows="4" placeholder="Scurtă descriere a activității"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="logoUrl" class="form-label">Logo (URL imagine externă)</label>
                                        <input type="url" class="form-control" id="logoUrl" name="logo_url" placeholder="https://imgbb.com/logo.png" oninput="previewLogo()">
                                        <div class="form-text">Încarcă imagine pe <a href="https://imgbb.com" target="_blank">imgbb.com</a> sau <a href="https://postimages.org" target="_blank">postimages.org</a>.</div>
                                    </div>
                                    <div class="mb-3 text-center">
                                        <img id="logoPreview" src="" alt="Previzualizare Logo" class="rounded shadow" style="max-width: 150px; display: none;">
                                    </div>
                                </div>
                            </div>

                            <!-- Col dreapta -->
                            <div class="col-lg-4">
                                <div class="border border-3 p-4 rounded">
                                    <div class="row g-3">

                                        <div class="col-12">
                                            <label for="domain" class="form-label">Domeniu de Activitate</label>
                                            <select class="form-select" id="domain" name="domain">
                                                <option value="">Alege...</option>
                                                <option value="IT">IT & Software</option>
                                                <option value="Marketing">Marketing</option>
                                                <option value="Educație">Educație</option>
                                                <option value="Finanțe">Finanțe</option>
                                                <option value="Altele">Altele</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" class="form-control" id="website" name="website" placeholder="https://www.firma.md">
                                        </div>

                                        <div class="col-12">
                                            <label for="email" class="form-label">Email Firmă</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="contact@firma.md">
                                        </div>

                                        <div class="col-12">
                                            <label for="phone" class="form-label">Telefon</label>
                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="+373 123 456 78">
                                        </div>

                                        <div class="col-12">
                                            <label for="cui" class="form-label">Cod Fiscal / IDNO</label>
                                            <input type="text" class="form-control" id="cui" name="cui" placeholder="123456789">
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="d-grid">
                                                <button type="button" class="btn btn-primary" data-action="add-firm">Salvează Firma</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> <!-- end row -->
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('#addFirmForm');
        const saveBtn = form.querySelector('[data-action="add-firm"]');

        saveBtn.addEventListener('click', () => {
            const endpoint = form.dataset.endpoint;
            const method = (form.dataset.method || 'POST').toUpperCase();
            const successMsg = form.dataset.success || 'Trimis cu succes!';

            const formData = new FormData(form);
            const data = { type_product: 'add' }; // ← adăugăm aici valoarea

            formData.forEach((value, key) => {
                data[key] = value;
            });

            fetch(endpoint, {
                method: method,
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
                    alert(successMsg);
                    form.reset();
                    window.location.href = "https://worldwinner.online/public/firms";
                })
                .catch(error => {
                    alert('A apărut o eroare la trimiterea datelor.');
                    console.error('[AddFirm] Eroare:', error);
                });
        });
    });

</script>