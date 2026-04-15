<div class="page-wrapper" data-page="addmember">
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Adaugă un membru în echipă</h5>
                <hr>

                <form id="addMemberForm"
                      data-autosave
                      data-endpoint="/public/crudteam"
                      data-method="POST"
                      data-success="Membru adăugat cu succes!">

                    <div class="row g-3">

                        <div class="col-lg-6">
                            <label for="nume" class="form-label">Nume complet</label>
                            <input type="text" class="form-control" id="nume" name="nume" required>
                        </div>

                        <div class="col-lg-6">
                            <label for="rol" class="form-label">Rol în firmă</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">-- Selectează un rol --</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Manager Proiect">Manager Proiect</option>
                                <option value="Dezvoltator">Dezvoltator</option>
                                <option value="Designer">Designer</option>
                                <option value="Marketer">Marketer</option>
                                <option value="Consultant">Consultant</option>
                                <option value="Suport Clienți">Suport Clienți</option>
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>

                        <div class="col-lg-6">
                            <label for="telefon" class="form-label">Telefon</label>
                            <input type="text" class="form-control" id="telefon" name="telefon">
                        </div>

                        <div class="col-lg-6">
                            <label for="avatar" class="form-label">URL Avatar</label>
                            <input type="url" class="form-control" id="avatar" name="avatar">
                        </div>

                        <div class="col-lg-6">
                            <label for="alaturat" class="form-label">Anul Alăturării</label>
                            <input type="number" class="form-control" id="workfirm" name="workfirm" min="2000" max="2099">
                        </div>

                        <div class="col-lg-6 d-none">
                            <label for="firma_id" class="form-label">ID Firmă (firma_id)</label>
                            <input type="number" class="form-control" id="firma_id" name="firma_id" required value="<?=$_SESSION['firm']?>">
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" data-action="add-team">Adaugă Membru</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('#addMemberForm');
        const saveBtn = form.querySelector('[data-action="add-team"]');

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
                    window.location.href = "https://worldwinner.online/public/team";

                })
                .catch(error => {
                    alert('A apărut o eroare la trimiterea datelor.');
                    console.error('[AddFirm] Eroare:', error);
                });
        });
    });

</script>