
<?php
// $users = ...  // ia din DB (users_connect) câmpuri: id, fullname, login, role, status, id_firm, datareg
// $firms = ...  // ia din DB firmele: id, name
use Evasystem\Controllers\Users\UsersService;
use Evasystem\Core\Auth\RolesRepository;
$rolesusrs = RolesRepository::roles();
$service    = new UsersService();
//$res        = $controller->editStatus($data); // tu deja ai metoda
$idusers = $_SESSION['user_id'];
$users = $service->getAllUserss();
echo json_encode(['success'=>true,'message'=>'Actualizat.']);


$endpoint = '/public/addusersadd'; // sau /public/crudusers – cum îl ai în rute
$rolesMap = [
    'super_ambassador'   => 'Ambasador Suprem',
    'regional_ambassador'=> 'Ambasador Regional',
    'manager'            => 'Manager',
    'executive'          => 'Executiv',
    'guest'              => 'Vizitator',
];
?>
<div class="page-wrapper" data-endpoint="<?= htmlspecialchars($endpoint, ENT_QUOTES) ?>">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Utilizatori</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="/public/homepages"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Lista utilizatori</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" id="bulkApprove" class="btn btn-success">Aprobă selectații</button>
                    <button type="button" id="bulkBlock"   class="btn btn-warning">Blochează selectații</button>
                    <button type="button" id="bulkDelete"  class="btn btn-danger">Șterge selectații</button>
                </div>
            </div>
        </div>

        <div id="formAlert" class="alert hidden" role="alert" style="display:none;"></div>

        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center mb-4 gap-3">
                    <div class="position-relative">
                        <input type="text" id="searchInput" class="form-control ps-5 radius-30" placeholder="Caută utilizator...">
                        <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <select id="filterRole" class="form-select">
                            <option value="">— Toate rolurile —</option>
                            <?php foreach ($rolesMap as $slug=>$label): ?>
                                <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filterStatus" class="form-select">
                            <option value="">— Toate statusurile —</option>
                            <option value="1">Aprobat</option>
                            <option value="0">Blocat / În așteptare</option>
                        </select>
                    </div>
                </div>
                <?
                foreach ($rolesMap as $slug=>$label){

                }

                ?>
                <div class="table-responsive">
                    <table class="table mb-0" id="usersTable">
                        <thead class="table-light">
                        <tr>
                            <th style="width:32px;"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th>Utilizator</th>
                            <th>Login / Email</th>
                            <th>Rol</th>
                            <th>Firmă</th>
                            <th>Status</th>
                            <th>Înregistrat</th>
                            <th>Acțiuni</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u):
                            $id       = (int)($u['randomn_id'] ?? 0);
                            $name     = trim($u['fullname'] ?? $u['nikname'] ?? '');
                            $login    = $u['login'] ?? '';
                            $role     = $u['role'] ?? 'guest';
                            $roleText = $rolesMap[$role] ?? ucfirst($role);
                            $status   = (string)($u['status'] ?? '0');
                            $date     = $u['datareg'] ?? '';
                            $firmId   = (int)($u['id_firm'] ?? 0);

                            $badgeClass = $status === '1' ? 'text-success bg-light-success' : 'text-warning bg-light-warning';
                            $badgeText  = $status === '1' ? 'Aprobat' : 'În așteptare / Blocat';
                            $rolesMap = ['super_ambassador'=>'Ambasador Suprem']
                            ?>
                            <tr data-user-id="<?= $id ?>" data-role="<?= htmlspecialchars($role) ?>" data-status="<?= htmlspecialchars($status) ?>">
                                <td><input class="form-check-input row-check" type="checkbox" value="<?= $id ?>"></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2"><h6 class="mb-0 font-14">#U-<?= str_pad((string)$id, 6, '0', STR_PAD_LEFT) ?></h6>
                                            <div class="small text-muted"><?= htmlspecialchars($name) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($login) ?></td>
                                <td>

                                    <select name="role" class="form-select form-select-sm js-user-role" data-user-id="<?= $id ?>">
                                        <?php foreach ($rolesusrs as $row):
                                            $slug  = (string)($row['slug'] ?? '');
                                            $label = (string)($row['label'] ?? $slug);
                                            ?>
                                            <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= ($slug === ($role ?? '')) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                </td>
                                <td>

                                </td>
                                <td>
                                    <div class="badge rounded-pill <?= $badgeClass ?> p-2 text-uppercase px-3">
                                        <i class="bx bxs-circle me-1"></i><?= $badgeText ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($date) ?></td>
                                <td>
                                    <div class="d-flex order-actions">
                                        <?php if ($status !== '1'): ?>
                                            <a href="javascript:;" class="text-success approve-user" title="Aprobă"><i class="bx bxs-check-circle"></i></a>
                                        <?php else: ?>
                                            <a href="javascript:;" class="text-warning block-user"  title="Blochează"><i class="bx bxs-minus-circle"></i></a>
                                        <?php endif; ?>
                                        <a href="javascript:;" class="ms-3 text-danger delete-user" title="Șterge"><i class="bx bxs-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Nu există utilizatori.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root      = document.querySelector('.page-wrapper');
        const endpoint  = root?.dataset.endpoint || '/public/addusersadd';
        const table     = document.getElementById('usersTable');
        const alertBox  = document.getElementById('formAlert');
        const searchInp = document.getElementById('searchInput');
        const filterRole= document.getElementById('filterRole');
        const filterSt  = document.getElementById('filterStatus');
        const checkAll  = document.getElementById('checkAll');

        const showAlert = (type, msg) => {
            if (!alertBox) return;
            alertBox.style.display = '';
            alertBox.className = 'alert ' + (type === 'error' ? 'alert-danger' : 'alert-success');
            alertBox.textContent = msg || (type === 'error' ? 'A apărut o eroare.' : 'Succes.');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => { alertBox.style.display = 'none'; }, 2500);
        };

        const extractJson = (raw) => {
            const s = raw.indexOf('{'); const e = raw.lastIndexOf('}');
            if (s === -1 || e === -1) return null;
            try { return JSON.parse(raw.slice(s, e+1)); } catch { return null; }
        };

        const postJson = async (payload) => {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {'Content-Type':'application/json','Accept':'application/json'},
                body: JSON.stringify(payload)
            });
            const raw = await res.text();
            const json = extractJson(raw);
            if (!json) throw new Error('Răspuns server invalid.');
            if (!res.ok || json.success === false) {
                throw new Error(json.message || 'Operațiune eșuată.');
            }
            return json;
        };

        // ——— Filtrare locală (căutare/rol/status)
        const applyFilters = () => {
            const q   = (searchInp?.value || '').toLowerCase();
            const r   = (filterRole?.value || '');
            const st  = (filterSt?.value || '');
            table.querySelectorAll('tbody tr').forEach(tr => {
                const txt = tr.innerText.toLowerCase();
                const role = tr.dataset.role || '';
                const s    = tr.dataset.status || '';
                const matchQ = !q || txt.includes(q);
                const matchR = !r || role === r;
                const matchS = !st || s === st;
                tr.style.display = (matchQ && matchR && matchS) ? '' : 'none';
            });
        };
        searchInp?.addEventListener('input', applyFilters);
        filterRole?.addEventListener('change', applyFilters);
        filterSt?.addEventListener('change', applyFilters);

        // ——— Check all
        checkAll?.addEventListener('change', (e) => {
            const ck = e.target.checked;
            table.querySelectorAll('.row-check').forEach(ch => ch.checked = ck);
        });

        document.addEventListener('change', async (e) => {
            const sel = e.target.closest('.js-user-role'); // <select class="js-user-role" data-user-id="123">
            if (!sel) return;

            const userId = Number(sel.dataset.userId);
            const newRole = sel.value;
            const prev = sel.dataset.prev ?? sel.value;
            sel.dataset.prev = prev;

            sel.disabled = true;
            try {
                const resp = await postJson({ type_product: 'edit', id: userId, role: newRole });
                // opțional: afișează feedback
                console.log('Rol salvat:', resp);
            } catch (err) {
                // revino la rolul anterior dacă a eșuat
                sel.value = sel.dataset.prev;
                console.error('Eroare setrole:', err);
                alert(err.message || 'Nu s-a putut salva rolul');
            } finally {
                sel.disabled = false;
            }
        });



        table?.addEventListener('click', async (e) => {
            const a = e.target.closest('a');
            if (!a) return;
            const tr = e.target.closest('tr[data-user-id]');
            if (!tr) return;
            const id = parseInt(tr.dataset.userId || tr.getAttribute('data-user-id'), 10);
            if (!id) return;

            try {
                if (a.classList.contains('approve-user')) {

                    await postJson({ type_product:'edit', id, status: 1 });
                    tr.dataset.status = '1';
                    tr.querySelector('.badge').className = 'badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3';
                    tr.querySelector('.badge').innerHTML = "<i class='bx bxs-circle me-1'></i>Aprobat";
                    // înlocuiește butonul cu “Blochează”
                    a.outerHTML = "<a href='javascript:;' class='ms-3 text-warning block-user' title='Blochează'><i class='bx bxs-minus-circle'></i></a>";
                    showAlert('success','Utilizator aprobat.');
                }

                if (a.classList.contains('block-user')) {
                    await postJson({ type_product:'edit', id, status: 0 });
                    tr.dataset.status = '0';
                    tr.querySelector('.badge').className = 'badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3';
                    tr.querySelector('.badge').innerHTML = "<i class='bx bxs-circle me-1'></i>În așteptare / Blocat";
                    // înlocuiește cu “Aprobă”
                    a.outerHTML = "<a href='javascript:;' class='text-success approve-user' title='Aprobă'><i class='bx bxs-check-circle'></i></a>";
                    showAlert('success','Utilizator blocat.');
                }

                if (a.classList.contains('delete-user')) {
                    if (!confirm('Ștergi acest utilizator?')) return;
                    await postJson({ type_product:'delete', id });
                    tr.remove();
                    showAlert('success','Utilizator șters.');
                }
            } catch (err) {
                showAlert('error', err.message);
                console.error(err);
            }
        });

        // ——— Legare firmă (on change)
        table?.addEventListener('change', async (e) => {
            const sel = e.target.closest('select.link-firm');
            if (!sel) return;
            const tr = e.target.closest('tr[data-user-id]');
            const id = parseInt(tr.dataset.userId, 10);
            const firmId = sel.value || '';
            try {
                await postJson({ type_product:'edit', id, id_firm: firmId });
                showAlert('success','Firmă actualizată.');
            } catch (err) {
                // revine la valoarea anterioară dacă a eșuat
                sel.value = sel.dataset.current || '';
                showAlert('error', err.message);
                console.error(err);
            }
        });

        // ——— Bulk actions
        const collectSelected = () => {
            return Array.from(table.querySelectorAll('.row-check:checked')).map(ch => parseInt(ch.value, 10)).filter(Boolean);
        };

        document.getElementById('bulkApprove')?.addEventListener('click', async () => {
            const ids = collectSelected();
            if (!ids.length) return showAlert('error','Nimic selectat.');
            try {
                await Promise.all(ids.map(id => postJson({ type_product:'edit', id, status: 1 })));
                ids.forEach(id => {
                    const tr = table.querySelector(`tr[data-user-id="${id}"]`);
                    if (!tr) return;
                    tr.dataset.status = '1';
                    const b = tr.querySelector('.badge');
                    if (b) {
                        b.className = 'badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3';
                        b.innerHTML  = "<i class='bx bxs-circle me-1'></i>Aprobat";
                    }
                });
                showAlert('success','Aprobat.');
            } catch (err) {
                showAlert('error', err.message);
            }
        });

        document.getElementById('bulkBlock')?.addEventListener('click', async () => {
            const ids = collectSelected();
            if (!ids.length) return showAlert('error','Nimic selectat.');
            try {
                await Promise.all(ids.map(id => postJson({ type_product:'edit', id, status: 0 })));
                ids.forEach(id => {
                    const tr = table.querySelector(`tr[data-user-id="${id}"]`);
                    if (!tr) return;
                    tr.dataset.status = '0';
                    const b = tr.querySelector('.badge');
                    if (b) {
                        b.className = 'badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3';
                        b.innerHTML  = "<i class='bx bxs-circle me-1'></i>În așteptare / Blocat";
                    }
                });
                showAlert('success','Blocare efectuată.');
            } catch (err) {
                showAlert('error', err.message);
            }
        });

        document.getElementById('bulkDelete')?.addEventListener('click', async () => {
            const ids = collectSelected();
            if (!ids.length) return showAlert('error','Nimic selectat.');
            if (!confirm('Ștergi utilizatorii selectați?')) return;
            try {
                await Promise.all(ids.map(id => postJson({ type_product:'delete', id })));
                ids.forEach(id => {
                    const tr = table.querySelector(`tr[data-user-id="${id}"]`);
                    tr?.remove();
                });
                showAlert('success','Ștergere efectuată.');
            } catch (err) {
                showAlert('error', err.message);
            }
        });
    });
</script>
