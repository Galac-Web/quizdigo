<?php
declare(strict_types=1);

use Evasystem\Controllers\Mybank\Mybank;
use Evasystem\Controllers\Mybank\MybankService;
use Evasystem\Controllers\Users\UsersService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die('Utilizatorul nu este autentificat.');
}

$usersService = new UsersService();
$currentUserData = $usersService->getIdUserss((int)$_SESSION['user_id']);
$currentUser = (is_array($currentUserData) && isset($currentUserData[0])) ? $currentUserData[0] : $currentUserData;

$userRandomnId = (string)($currentUser['randomn_id'] ?? '');
if ($userRandomnId === '') {
    die('randomn_id utilizator lipsă.');
}

$service = new MybankService();
$controller = new Mybank($service);
$dashboard = $controller->index($userRandomnId);

$account = $dashboard['account'] ?? [];
$card = $dashboard['card'] ?? [];
$billing = $dashboard['billing'] ?? [];
$transactions = $dashboard['transactions'] ?? [];
$invoices = $dashboard['invoices'] ?? [];

$currentPlan = (string)($account['plan_name'] ?? 'Premium');
$currentPrice = (string)($account['plan_price'] ?? '19.00');
$currentCurrency = (string)($account['currency'] ?? 'EUR');
$paymentStatus = (string)($account['payment_status'] ?? 'paid');
$nextBillingDate = !empty($account['next_billing_date']) ? date('d M Y', strtotime((string)$account['next_billing_date'])) : '-';

$lastSuccessful = '-';
foreach ($transactions as $trx) {
    if (($trx['status'] ?? '') === 'paid') {
        $lastSuccessful = rtrim(rtrim((string)$trx['amount'], '0'), '.') . ($trx['currency'] ?? 'EUR');
        break;
    }
}

$cardNumberMask = !empty($card['last4']) ? '**** **** **** ' . $card['last4'] : 'No card added';
$cardHolder = (string)($card['card_holder'] ?? '-');
$cardExp = (!empty($card['exp_month']) && !empty($card['exp_year'])) ? ($card['exp_month'] . ' / ' . $card['exp_year']) : '-';
$cardBrand = (string)($card['brand'] ?? '-');
$autoRenewText = !empty($account['auto_renew']) ? 'Auto renewal enabled' : 'Auto renewal disabled';
?>
<style>
    :root{
        --bg: #f4f7fb;
        --card: #ffffff;
        --text: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --primary: #0A5084;
        --primary-2: #2E85C7;
        --success: #16a34a;
        --warning: #f59e0b;
        --danger: #dc2626;
        --shadow: 0 12px 30px rgba(15, 23, 42, .08);
        --radius-xl: 26px;
        --radius-lg: 20px;
        --radius-md: 14px;
    }

    .page{max-width:1320px;margin:0 auto;}
    .topbar{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:26px;flex-wrap:wrap;}
    .title-wrap h1{margin:0;font-size:34px;line-height:1.1;font-weight:900;color:var(--primary);letter-spacing:-.7px;}
    .title-wrap p{margin:8px 0 0;color:var(--muted);font-size:15px;}
    .top-actions{display:flex;gap:12px;flex-wrap:wrap;}

    .btn{
        border:none;outline:none;cursor:pointer;border-radius:14px;padding:12px 18px;
        font-size:14px;font-weight:700;transition:all .25s ease;display:inline-flex;
        align-items:center;justify-content:center;gap:8px;
    }
    .btn:hover{transform:translateY(-2px);}
    .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-2));color:#fff;box-shadow:0 12px 22px rgba(46,133,199,.22);}
    .btn-light{background:#fff;color:var(--primary);border:1px solid var(--line);}
    .btn-danger{background:#fff0f0;color:var(--danger);border:1px solid #ffd7d7;}

    .stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;margin-bottom:26px;}
    .stat-card{
        background:var(--card);border-radius:var(--radius-xl);box-shadow:var(--shadow);border:1px solid #edf2f7;
        padding:22px;position:relative;overflow:hidden;min-height:142px;opacity:0;transform:translateY(18px);
    }
    .stat-card::after{
        content:"";position:absolute;top:-40px;right:-40px;width:120px;height:120px;border-radius:50%;
        background:linear-gradient(135deg,rgba(46,133,199,.11),rgba(10,80,132,.04));
    }
    .stat-label{color:var(--muted);font-size:14px;margin-bottom:12px;position:relative;z-index:2;}
    .stat-value{font-size:34px;font-weight:900;color:var(--text);line-height:1;margin-bottom:10px;position:relative;z-index:2;}
    .stat-meta{font-size:13px;color:var(--muted);position:relative;z-index:2;}

    .main-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:24px;margin-bottom:24px;}
    .card{
        background:var(--card);border-radius:var(--radius-xl);box-shadow:var(--shadow);border:1px solid #edf2f7;
        padding:24px;opacity:0;transform:translateY(18px);
    }
    .card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px;flex-wrap:wrap;}
    .card-head h2{margin:0;font-size:22px;font-weight:900;color:var(--primary);}
    .card-head p{margin:6px 0 0;color:var(--muted);font-size:14px;}

    .bank-card{
        position:relative;border-radius:24px;padding:26px;min-height:220px;overflow:hidden;color:#fff;
        background:
                radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 24%),
                radial-gradient(circle at bottom left, rgba(255,255,255,.10), transparent 28%),
                linear-gradient(135deg, #0A5084 0%, #2E85C7 55%, #3bb3ff 100%);
        box-shadow:0 16px 36px rgba(46,133,199,.28);
    }
    .bank-card::before{
        content:"";position:absolute;inset:auto -70px -70px auto;width:220px;height:220px;border-radius:50%;
        background:rgba(255,255,255,.08);
    }
    .bank-chip{
        width:58px;height:42px;border-radius:12px;background:linear-gradient(135deg,#ffe18f,#ffbe0b);
        margin-bottom:26px;box-shadow:inset 0 1px 1px rgba(255,255,255,.35);
    }
    .bank-number{font-size:28px;font-weight:800;letter-spacing:3px;margin-bottom:22px;position:relative;z-index:2;}
    .bank-footer{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;flex-wrap:wrap;position:relative;z-index:2;}
    .bank-footer .small{font-size:12px;opacity:.88;text-transform:uppercase;margin-bottom:6px;letter-spacing:.8px;}
    .bank-footer .strong{font-weight:800;font-size:15px;}

    .payment-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;}
    .info-list{display:grid;gap:14px;margin-top:6px;}
    .info-item{
        padding:16px 18px;border:1px solid var(--line);border-radius:16px;background:#f8fbff;
        display:flex;justify-content:space-between;gap:14px;align-items:center;flex-wrap:wrap;
    }
    .info-item strong{display:block;margin-bottom:4px;font-size:15px;}
    .info-item span{color:var(--muted);font-size:13px;}

    .billing-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
    .field{display:flex;flex-direction:column;gap:8px;}
    .field label{font-size:13px;font-weight:700;color:#334155;}
    .field input,.field select{
        width:100%;border:1px solid var(--line);border-radius:14px;padding:13px 14px;
        font-size:14px;outline:none;transition:.2s ease;background:#fff;
    }
    .field input:focus,.field select:focus{border-color:var(--primary-2);box-shadow:0 0 0 4px rgba(46,133,199,.10);}
    .field.full{grid-column:1 / -1;}
    .save-row{margin-top:18px;display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;}

    .table-card{margin-bottom:24px;}
    .table-wrap{overflow:auto;border-radius:18px;border:1px solid var(--line);}
    table{width:100%;border-collapse:collapse;min-width:760px;background:#fff;}
    thead{background:#f8fbff;}
    th,td{text-align:left;padding:16px 18px;border-bottom:1px solid var(--line);font-size:14px;}
    th{font-size:13px;text-transform:uppercase;letter-spacing:.6px;color:#64748b;}
    tbody tr:hover{background:#f9fcff;}

    .badge{
        display:inline-flex;align-items:center;justify-content:center;min-width:86px;padding:8px 10px;
        border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.2px;
    }
    .badge-success{background:rgba(22,163,74,.10);color:var(--success);}
    .badge-warning{background:rgba(245,158,11,.14);color:var(--warning);}
    .badge-danger{background:rgba(220,38,38,.10);color:var(--danger);}

    .download-btn{
        border:none;background:#eef6fd;color:var(--primary);border-radius:10px;padding:10px 12px;
        font-size:13px;font-weight:800;cursor:pointer;transition:.2s ease;
    }
    .download-btn:hover{background:#dceefd;transform:translateY(-1px);}

    .section-title{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px;flex-wrap:wrap;}
    .section-title h3{margin:0;font-size:24px;color:var(--primary);}
    .section-title p{margin:4px 0 0;font-size:14px;color:var(--muted);}

    .fade-in.show{opacity:1;transform:translateY(0);transition:all .6s ease;}
    .lift{transition:transform .25s ease, box-shadow .25s ease;}
    .lift:hover{transform:translateY(-4px);box-shadow:0 16px 34px rgba(15,23,42,.10);}
    .success-flash{animation:successFlash .9s ease;}
    @keyframes successFlash{
        0%{ box-shadow: 0 0 0 rgba(22,163,74,0); }
        40%{ box-shadow: 0 0 0 6px rgba(22,163,74,.12); }
        100%{ box-shadow: 0 0 0 rgba(22,163,74,0); }
    }

    .modal-backdrop{
        position:fixed;inset:0;background:rgba(15,23,42,.52);display:none;align-items:center;justify-content:center;
        z-index:9999;padding:18px;
    }
    .modal-backdrop.show{display:flex;}
    .modal-card{
        width:100%;max-width:560px;background:#fff;border-radius:22px;box-shadow:0 22px 60px rgba(15,23,42,.20);
        overflow:hidden;
    }
    .modal-head{padding:20px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px;}
    .modal-head h3{margin:0;font-size:22px;color:var(--primary);}
    .modal-close{border:none;background:#f1f5f9;width:40px;height:40px;border-radius:12px;cursor:pointer;font-size:20px;}
    .modal-body{padding:22px;}
    .modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
    .modal-foot{padding:0 22px 22px;display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;}
    .msg-box{margin-top:14px;padding:12px 14px;border-radius:12px;font-size:14px;display:none;}
    .msg-box.show{display:block;}
    .msg-success{background:rgba(22,163,74,.10);color:var(--success);}
    .msg-error{background:rgba(220,38,38,.10);color:var(--danger);}

    @media (max-width:1100px){
        .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
        .main-grid{grid-template-columns:1fr;}
    }

    @media (max-width:720px){
        .page{padding:18px;}
        .stats-grid{grid-template-columns:1fr;}
        .billing-grid,.modal-grid{grid-template-columns:1fr;}
        .bank-number{font-size:22px;letter-spacing:2px;}
        .title-wrap h1{font-size:28px;}
        .top-actions{width:100%;}
        .top-actions .btn{flex:1;}
    }
</style>

<div class="page" style="max-width: 100%;width: 100%;">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>

    <div class="topbar">
        <div class="title-wrap">
            <h1>My Bank</h1>
            <p>Gestionează metodele de plată, facturile, tranzacțiile și datele de facturare.</p>
        </div>

        <div class="top-actions">
            <button class="btn btn-light" id="exportTransactionsBtn">Export Transactions</button>
            <button class="btn btn-primary" id="addCardBtn">Add New Card</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card fade-in lift">
            <div class="stat-label">Current Plan</div>
            <div class="stat-value"><?= htmlspecialchars($currentPlan) ?></div>
            <div class="stat-meta">Active subscription · renews on <?= htmlspecialchars($nextBillingDate) ?></div>
        </div>

        <div class="stat-card fade-in lift">
            <div class="stat-label">This Month</div>
            <div class="stat-value"><?= htmlspecialchars($lastSuccessful) ?></div>
            <div class="stat-meta">Last successful payment</div>
        </div>

        <div class="stat-card fade-in lift">
            <div class="stat-label">Invoices</div>
            <div class="stat-value"><?= count($invoices) ?></div>
            <div class="stat-meta">Available for download</div>
        </div>

        <div class="stat-card fade-in lift">
            <div class="stat-label">Payment Status</div>
            <div class="stat-value"><?= htmlspecialchars(ucfirst($paymentStatus)) ?></div>
            <div class="stat-meta">All billing records are up to date</div>
        </div>
    </div>

    <div class="main-grid">
        <div class="card fade-in lift">
            <div class="card-head">
                <div>
                    <h2>Payment Method</h2>
                    <p>Cardul principal folosit pentru abonament și plăți automate.</p>
                </div>
            </div>

            <div class="bank-card" id="bankCardVisual">
                <div class="bank-chip"></div>
                <div class="bank-number" id="jsCardNumber"><?= htmlspecialchars($cardNumberMask) ?></div>

                <div class="bank-footer">
                    <div>
                        <div class="small">Card Holder</div>
                        <div class="strong" id="jsCardHolder"><?= htmlspecialchars($cardHolder) ?></div>
                    </div>

                    <div>
                        <div class="small">Expires</div>
                        <div class="strong" id="jsCardExpires"><?= htmlspecialchars($cardExp) ?></div>
                    </div>

                    <div>
                        <div class="small">Brand</div>
                        <div class="strong" id="jsCardBrand"><?= htmlspecialchars($cardBrand) ?></div>
                    </div>
                </div>
            </div>

            <div class="payment-actions">
                <button class="btn btn-primary" id="changeCardBtn">Change Card</button>
                <button class="btn btn-danger" id="removeCardBtn">Remove Card</button>
            </div>

            <div class="info-list" style="margin-top:20px;">
                <div class="info-item">
                    <div>
                        <strong>Primary Payment Method</strong>
                        <span id="jsPrimaryMethodText"><?= htmlspecialchars($cardBrand) ?> ending in <?= htmlspecialchars((string)($card['last4'] ?? '----')) ?> · <?= htmlspecialchars($autoRenewText) ?></span>
                    </div>
                    <span class="badge badge-success">Active</span>
                </div>

                <div class="info-item">
                    <div>
                        <strong>Next Billing Date</strong>
                        <span><?= htmlspecialchars($nextBillingDate) ?> · <?= htmlspecialchars((string)($account['billing_cycle'] ?? 'monthly')) ?> recurring invoice</span>
                    </div>
                    <span class="badge badge-warning">Upcoming</span>
                </div>
            </div>
        </div>

        <div class="card fade-in lift" id="billingCard">
            <div class="card-head">
                <div>
                    <h2>Billing Address</h2>
                    <p>Informațiile folosite pentru facturi și documente financiare.</p>
                </div>
            </div>

            <form id="billingForm">
                <input type="hidden" name="type_product" value="save_billing">
                <input type="hidden" name="account_randomn_id" value="<?= htmlspecialchars((string)$account['randomn_id']) ?>">

                <div class="billing-grid">
                    <div class="field">
                        <label>Company Name</label>
                        <input type="text" name="company_name" value="<?= htmlspecialchars((string)($billing['company_name'] ?? '')) ?>">
                    </div>

                    <div class="field">
                        <label>VAT / IDNO</label>
                        <input type="text" name="vat_idno" value="<?= htmlspecialchars((string)($billing['vat_idno'] ?? '')) ?>">
                    </div>

                    <div class="field">
                        <label>Country</label>
                        <select name="country">
                            <?php
                            $selectedCountry = (string)($billing['country'] ?? 'Moldova');
                            $countries = ['Moldova', 'Romania', 'Other'];
                            foreach ($countries as $c):
                                ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $selectedCountry === $c ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label>City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars((string)($billing['city'] ?? '')) ?>">
                    </div>

                    <div class="field full">
                        <label>Address</label>
                        <input type="text" name="address_line" value="<?= htmlspecialchars((string)($billing['address_line'] ?? '')) ?>">
                    </div>

                    <div class="field">
                        <label>Email for invoices</label>
                        <input type="email" name="invoice_email" value="<?= htmlspecialchars((string)($billing['invoice_email'] ?? '')) ?>">
                    </div>

                    <div class="field">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" value="<?= htmlspecialchars((string)($billing['postal_code'] ?? '')) ?>">
                    </div>
                </div>

                <div class="save-row">
                    <button type="button" class="btn btn-light" id="billingCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Billing Info</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card fade-in lift">
        <div class="section-title">
            <div>
                <h3>Transactions</h3>
                <p>Istoricul plăților și al încercărilor de procesare.</p>
            </div>
            <button class="btn btn-light" id="transactionsFilterBtn">Filter</button>
        </div>

        <div class="table-wrap">
            <table id="transactionsTable">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                </thead>

                <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $trx): ?>
                        <?php
                        $trxStatus = strtolower((string)($trx['status'] ?? 'paid'));
                        $badgeClass = 'badge-success';
                        if ($trxStatus === 'pending') $badgeClass = 'badge-warning';
                        if (in_array($trxStatus, ['failed', 'error', 'declined'], true)) $badgeClass = 'badge-danger';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d M Y', strtotime((string)$trx['transaction_date']))) ?></td>
                            <td><?= htmlspecialchars((string)$trx['description']) ?></td>
                            <td><?= htmlspecialchars((string)$trx['payment_method']) ?></td>
                            <td><?= htmlspecialchars((string)$trx['amount'] . ($trx['currency'] ?? 'EUR')) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($trxStatus)) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Nu există tranzacții.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card table-card fade-in lift">
        <div class="section-title">
            <div>
                <h3>Invoices</h3>
                <p>Facturi disponibile pentru descărcare și arhivare.</p>
            </div>
            <button class="btn btn-light" id="viewAllInvoicesBtn">View All</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>File</th>
                </tr>
                </thead>

                <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$inv['invoice_no']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime((string)$inv['invoice_date']))) ?></td>
                            <td><?= htmlspecialchars((string)$inv['description']) ?></td>
                            <td><?= htmlspecialchars((string)$inv['amount'] . ($inv['currency'] ?? 'EUR')) ?></td>
                            <td>
                                <button
                                        class="download-btn"
                                        type="button"
                                        data-invoice="<?= htmlspecialchars((string)$inv['invoice_no']) ?>"
                                >
                                    Download PDF
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Nu există facturi.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="cardModal">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Add / Change Card</h3>
            <button type="button" class="modal-close" id="closeCardModal">&times;</button>
        </div>

        <div class="modal-body">
            <form id="cardForm">
                <input type="hidden" name="type_product" value="add_card">
                <input type="hidden" name="account_randomn_id" value="<?= htmlspecialchars((string)$account['randomn_id']) ?>">

                <div class="modal-grid">
                    <div class="field full">
                        <label>Card Holder</label>
                        <input type="text" name="card_holder" id="modalCardHolder" value="<?= htmlspecialchars($cardHolder !== '-' ? $cardHolder : '') ?>">
                    </div>

                    <div class="field full">
                        <label>Card Number</label>
                        <input type="text" name="card_number" id="modalCardNumber" placeholder="4242 4242 4242 4242">
                    </div>

                    <div class="field">
                        <label>Brand</label>
                        <select name="brand" id="modalCardBrand">
                            <option value="VISA" selected>VISA</option>
                            <option value="MASTERCARD">MASTERCARD</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Exp Month</label>
                        <input type="text" name="exp_month" id="modalExpMonth" placeholder="05">
                    </div>

                    <div class="field">
                        <label>Exp Year</label>
                        <input type="text" name="exp_year" id="modalExpYear" placeholder="2028">
                    </div>

                    <div class="field">
                        <label>CVV</label>
                        <input type="password" name="cvv_fake" placeholder="***">
                    </div>
                </div>

                <div class="msg-box" id="cardMessageBox"></div>
            </form>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn btn-light" id="cancelCardModal">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveCardBtn">Save Card</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const animatedItems = document.querySelectorAll('.fade-in');

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('show');
                    }, index * 80);
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        animatedItems.forEach(item => revealObserver.observe(item));

        const billingForm = document.getElementById('billingForm');
        const billingCard = document.getElementById('billingCard');
        const billingCancelBtn = document.getElementById('billingCancelBtn');

        billingCancelBtn.addEventListener('click', function () {
            window.location.reload();
        });

        billingForm.addEventListener('submit', async function(e){
            e.preventDefault();

            const formData = new FormData(billingForm);
            const saveBtn = billingForm.querySelector('button[type="submit"]');
            const originalText = saveBtn.textContent;

            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;

            try {
                const response = await fetch('/public/crudmybank', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Eroare la salvare.');
                    return;
                }

                billingCard.classList.remove('success-flash');
                void billingCard.offsetWidth;
                billingCard.classList.add('success-flash');

                saveBtn.textContent = 'Saved Successfully';

                setTimeout(() => {
                    saveBtn.textContent = originalText;
                    saveBtn.disabled = false;
                }, 1400);
            } catch (error) {
                console.error(error);
                alert('Eroare la request.');
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            }
        });

        const bankCardVisual = document.getElementById('bankCardVisual');
        const changeCardBtn = document.getElementById('changeCardBtn');
        const removeCardBtn = document.getElementById('removeCardBtn');
        const addCardBtn = document.getElementById('addCardBtn');

        const cardModal = document.getElementById('cardModal');
        const closeCardModal = document.getElementById('closeCardModal');
        const cancelCardModal = document.getElementById('cancelCardModal');
        const saveCardBtn = document.getElementById('saveCardBtn');
        const cardForm = document.getElementById('cardForm');
        const cardMessageBox = document.getElementById('cardMessageBox');

        function pulseCard() {
            bankCardVisual.animate([
                { transform: 'scale(1)', boxShadow: '0 16px 36px rgba(46,133,199,.28)' },
                { transform: 'scale(1.02)', boxShadow: '0 20px 40px rgba(46,133,199,.38)' },
                { transform: 'scale(1)', boxShadow: '0 16px 36px rgba(46,133,199,.28)' }
            ], {
                duration: 700,
                easing: 'ease'
            });
        }

        function openCardModal() {
            cardModal.classList.add('show');
            cardMessageBox.className = 'msg-box';
            cardMessageBox.textContent = '';
        }

        function closeCardModalFn() {
            cardModal.classList.remove('show');
            cardMessageBox.className = 'msg-box';
            cardMessageBox.textContent = '';
        }

        function setCardMessage(type, text) {
            cardMessageBox.className = 'msg-box show ' + (type === 'success' ? 'msg-success' : 'msg-error');
            cardMessageBox.textContent = text;
        }

        addCardBtn.addEventListener('click', function(){
            pulseCard();
            openCardModal();
        });

        changeCardBtn.addEventListener('click', function(){
            pulseCard();
            openCardModal();
        });

        closeCardModal.addEventListener('click', closeCardModalFn);
        cancelCardModal.addEventListener('click', closeCardModalFn);

        cardModal.addEventListener('click', function(e){
            if (e.target === cardModal) {
                closeCardModalFn();
            }
        });

        saveCardBtn.addEventListener('click', async function () {
            const formData = new FormData(cardForm);
            const originalText = saveCardBtn.textContent;

            saveCardBtn.disabled = true;
            saveCardBtn.textContent = 'Saving...';

            try {
                const response = await fetch('/public/crudmybank', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!result.success) {
                    setCardMessage('error', result.message || 'Eroare la salvare card.');
                    saveCardBtn.disabled = false;
                    saveCardBtn.textContent = originalText;
                    return;
                }

                const holder = document.getElementById('modalCardHolder').value.trim() || '-';
                const cardNumber = document.getElementById('modalCardNumber').value.replace(/\D+/g, '');
                const brand = document.getElementById('modalCardBrand').value.trim() || '-';
                const expMonth = document.getElementById('modalExpMonth').value.trim() || '--';
                const expYear = document.getElementById('modalExpYear').value.trim() || '----';
                const last4 = cardNumber.length >= 4 ? cardNumber.slice(-4) : '----';

                document.getElementById('jsCardNumber').textContent = '**** **** **** ' + last4;
                document.getElementById('jsCardHolder').textContent = holder;
                document.getElementById('jsCardExpires').textContent = expMonth + ' / ' + expYear;
                document.getElementById('jsCardBrand').textContent = brand;
                document.getElementById('jsPrimaryMethodText').textContent = brand + ' ending in ' + last4 + ' · Auto renewal enabled';

                pulseCard();
                setCardMessage('success', result.message || 'Card salvat.');

                setTimeout(() => {
                    closeCardModalFn();
                    saveCardBtn.disabled = false;
                    saveCardBtn.textContent = originalText;
                }, 900);
            } catch (error) {
                console.error(error);
                setCardMessage('error', 'Eroare la request card.');
                saveCardBtn.disabled = false;
                saveCardBtn.textContent = originalText;
            }
        });

        removeCardBtn.addEventListener('click', async function(){
            const ok = confirm('Sigur vrei să ștergi cardul principal?');
            if (!ok) return;

            const formData = new FormData();
            formData.append('type_product', 'remove_card');
            formData.append('account_randomn_id', '<?= htmlspecialchars((string)$account['randomn_id']) ?>');

            const originalText = removeCardBtn.textContent;
            removeCardBtn.disabled = true;
            removeCardBtn.textContent = 'Removing...';

            try {
                const response = await fetch('/public/crudmybank', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Eroare la ștergere card.');
                    removeCardBtn.disabled = false;
                    removeCardBtn.textContent = originalText;
                    return;
                }

                document.getElementById('jsCardNumber').textContent = 'No card added';
                document.getElementById('jsCardHolder').textContent = '-';
                document.getElementById('jsCardExpires').textContent = '-';
                document.getElementById('jsCardBrand').textContent = '-';
                document.getElementById('jsPrimaryMethodText').textContent = 'No primary card · Auto renewal enabled';

                removeCardBtn.textContent = 'Removed';
                setTimeout(() => {
                    removeCardBtn.disabled = false;
                    removeCardBtn.textContent = originalText;
                }, 1000);
            } catch (error) {
                console.error(error);
                alert('Eroare la request remove card.');
                removeCardBtn.disabled = false;
                removeCardBtn.textContent = originalText;
            }
        });

        document.querySelectorAll('.download-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                const original = this.textContent;
                this.textContent = 'Preparing...';
                this.disabled = true;

                setTimeout(() => {
                    this.textContent = 'Downloaded';
                    setTimeout(() => {
                        this.textContent = original;
                        this.disabled = false;
                    }, 1200);
                }, 900);
            });
        });

        document.getElementById('exportTransactionsBtn').addEventListener('click', function () {
            const rows = [...document.querySelectorAll('#transactionsTable tbody tr')];
            let csv = 'Date,Description,Method,Amount,Status\n';

            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                if (cols.length === 5) {
                    const values = [...cols].map(td => '"' + td.textContent.trim().replace(/"/g, '""') + '"');
                    csv += values.join(',') + '\n';
                }
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.href = url;
            link.setAttribute('download', 'transactions.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });

        document.getElementById('transactionsFilterBtn').addEventListener('click', function () {
            alert('Aici poți adăuga filtrare după paid / pending / failed.');
        });

        document.getElementById('viewAllInvoicesBtn').addEventListener('click', function () {
            alert('Aici poți deschide o pagină separată cu toate facturile.');
        });
    });
</script>