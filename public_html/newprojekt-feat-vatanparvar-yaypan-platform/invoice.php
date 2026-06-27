<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)vpy_get('id');
$num = vpy_get('num');
$payment = null;
foreach (vpy_read_json('tolovlar', []) as $p) {
    if (($id && (int)$p['id'] === $id) || ($num && ($p['invoice_number'] ?? '') === $num)) {
        $payment = $p;
        break;
    }
}
if (!$payment) {
    http_response_code(404);
    die('<h1 style="font-family:system-ui;text-align:center;margin-top:20vh;color:#0D6B4E">404 — Hisob-faktura topilmadi</h1>');
}

$user = vpy_find('users', 'id', $payment['user_id']);
$tariff = vpy_find('tariflar', 'id', $payment['tariff_id']);
$lang = vpy_lang_code();
$is_cyrl = $lang === 'uz_cyrillic';
$tariff_name = $is_cyrl && !empty($tariff['name_cyrl']) ? $tariff['name_cyrl'] : ($tariff['name'] ?? $payment['tariff_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="<?= $is_cyrl ? 'uz-Cyrl' : 'uz' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0D6B4E">
<meta name="robots" content="noindex">
<title><?= e(t('invoice_title')) ?> #<?= e($payment['invoice_number']) ?></title>
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#FAF7F2;--primary:#0D6B4E;--primary-dark:#094D38;--accent:#E8A838;--dark:#1E1B18;--dark-soft:#3B362F;--muted:#7A6F62;--light:#FFFDF9;--border:rgba(180,160,130,0.25);--shadow:0 30px 60px rgba(30,27,24,0.1);--r:22px;--r-lg:32px;--pill:100px;--t:0.4s cubic-bezier(0.4,0,0.2,1);--serif:"Playfair Display",Georgia,serif;--sans:"Manrope","Inter",sans-serif}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--sans);font-size:14px;line-height:1.55;color:var(--dark);background:var(--bg);min-height:100vh;padding:30px 20px;-webkit-font-smoothing:antialiased}
.actions{max-width:820px;margin:0 auto 20px;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:var(--pill);font-weight:600;font-size:0.88rem;cursor:pointer;transition:var(--t);font-family:inherit;border:none;text-decoration:none}
.btn:hover{transform:translateY(-2px)}
.btn svg{width:16px;height:16px}
.btn-back{background:rgba(255,253,249,0.6);border:1px solid var(--border);color:var(--dark-soft)}
.btn-print{background:linear-gradient(135deg,var(--accent),#D88F1A);color:#fff;box-shadow:0 10px 24px rgba(232,168,56,0.35)}
.invoice{max-width:820px;margin:0 auto;background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:50px;box-shadow:var(--shadow);position:relative;overflow:hidden}
.invoice::before{content:"";position:absolute;top:-50%;right:-30%;width:60%;height:140%;background:radial-gradient(ellipse,rgba(13,107,78,0.05),transparent 60%);pointer-events:none}
.head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:50px;flex-wrap:wrap;gap:20px;position:relative;z-index:2}
.brand-block{display:flex;align-items:center;gap:14px;font-family:var(--serif);font-size:1.4rem;font-weight:700}
.brand-logo{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;display:grid;place-items:center;box-shadow:0 8px 18px rgba(13,107,78,0.3)}
.brand-logo svg{width:24px;height:24px}
.invoice-meta{text-align:right}
.invoice-num{font-family:var(--serif);font-size:1.6rem;font-weight:700;color:var(--primary)}
.invoice-meta-row{font-size:0.85rem;color:var(--muted);margin-top:4px}
.section{margin-bottom:32px}
.section h3{font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);margin-bottom:12px}
.address-grid{display:grid;grid-template-columns:1fr 1fr;gap:30px}
.address-block{padding:22px;background:rgba(13,107,78,0.04);border:1px solid var(--border);border-radius:var(--r)}
.address-block strong{display:block;font-size:1rem;color:var(--dark);margin-bottom:6px}
.address-block span{display:block;color:var(--dark-soft);font-size:0.88rem;line-height:1.5;margin-bottom:2px}
table.line-items{width:100%;border-collapse:collapse;margin-top:14px;font-size:0.92rem}
table.line-items th{text-align:left;padding:14px 16px;background:rgba(13,107,78,0.06);font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--dark-soft);border-bottom:2px solid var(--primary)}
table.line-items th:last-child,table.line-items td:last-child{text-align:right}
table.line-items td{padding:18px 16px;border-bottom:1px solid var(--border)}
.totals{margin-top:30px;display:flex;flex-direction:column;align-items:flex-end;gap:8px}
.totals-row{display:flex;gap:30px;align-items:center;font-size:0.95rem}
.totals-row.total{padding-top:14px;border-top:2px solid var(--primary);font-family:var(--serif);font-size:1.5rem;font-weight:700;color:var(--primary)}
.totals-row.total .amount{font-size:1.8rem}
.status-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:var(--pill);font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em}
.status-chip.success{background:rgba(13,107,78,0.1);color:var(--primary-dark)}
.status-chip.pending{background:rgba(232,168,56,0.15);color:#A87830}
.status-chip.failed{background:rgba(255,96,88,0.1);color:#C73E36}
.foot{margin-top:50px;padding-top:24px;border-top:1px solid var(--border);text-align:center;font-size:0.82rem;color:var(--muted);position:relative;z-index:2}
.qr{width:100px;height:100px;border-radius:14px;background:linear-gradient(135deg,#1E1B18,#3B362F);color:#fff;display:grid;place-items:center;margin:14px auto;font-family:var(--serif);font-size:1.4rem;font-weight:700;letter-spacing:-0.02em}
@media print{
    body{background:#fff;padding:0}
    .actions{display:none}
    .invoice{box-shadow:none;border:none;padding:30px}
}
@media (max-width:640px){
    .invoice{padding:30px 20px}
    .head{flex-direction:column}
    .invoice-meta{text-align:left}
    .address-grid{grid-template-columns:1fr}
    table.line-items{font-size:0.85rem}
    table.line-items th,table.line-items td{padding:12px 10px}
}
</style>
</head>
<body>

<div class="actions">
    <a href="<?= vpy_is_logged() ? '/user/' : '/' ?>" class="btn btn-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <?= e(t('btn_back')) ?>
    </a>
    <button class="btn btn-print" onclick="window.print()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        <?= e(t('invoice_print')) ?>
    </button>
</div>

<div class="invoice">
    <div class="head">
        <div class="brand-block">
            <span class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2 4 4 8-8 4 4"/></svg>
            </span>
            <?= e(t('site_name')) ?>
        </div>
        <div class="invoice-meta">
            <div class="invoice-num">#<?= e($payment['invoice_number']) ?></div>
            <div class="invoice-meta-row"><?= e(t('invoice_date')) ?>: <?= e(vpy_date($payment['created_at'], 'd.m.Y')) ?></div>
            <div class="invoice-meta-row" style="margin-top:8px">
                <span class="status-chip <?= e($payment['status']) ?>">
                    <?php
                    $st = $payment['status'];
                    if ($st === 'success') echo e(t('admin_status_success'));
                    elseif ($st === 'pending') echo e(t('admin_status_pending'));
                    else echo e(t('admin_status_failed'));
                    ?>
                </span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="address-grid">
            <div class="address-block">
                <h3 style="margin-bottom:8px;color:var(--muted);font-size:0.72rem"><?= e(t('invoice_seller')) ?></h3>
                <strong><?= e(vpy_setting('company_name', t('site_name') . ' MChJ')) ?></strong>
                <span><?= e(vpy_setting('contact_address', t('footer_address_value'))) ?></span>
                <span>STIR: <?= e(vpy_setting('company_inn', '300123456')) ?></span>
                <span>H/r: <?= e(vpy_setting('company_account', '20208000900123456789')) ?></span>
                <span>Bank: <?= e(vpy_setting('company_bank', 'Hamkorbank')) ?></span>
                <span>MFO: <?= e(vpy_setting('company_mfo', '00427')) ?></span>
            </div>
            <div class="address-block">
                <h3 style="margin-bottom:8px;color:var(--muted);font-size:0.72rem"><?= e(t('invoice_buyer')) ?></h3>
                <strong><?= e($user['name'] ?? '—') ?></strong>
                <span><?= e($user['phone'] ?? '—') ?></span>
                <span>ID: #<?= e($user['id'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <h3><?= e(t('invoice_item')) ?></h3>
        <table class="line-items">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= e(t('invoice_item')) ?></th>
                    <th><?= e(t('admin_status_active')) ?></th>
                    <th><?= e(t('invoice_amount')) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong><?= e(t('site_name')) ?> · <?= e($tariff_name) ?></strong>
                        <div style="font-size:0.82rem;color:var(--muted);margin-top:2px"><?= (int)($tariff['duration_days'] ?? 30) ?> kun foydalanish huquqi</div>
                    </td>
                    <td><?= (int)($tariff['duration_days'] ?? 30) ?> kun</td>
                    <td><strong><?= number_format((float)$payment['amount'], 0, '.', ' ') ?> <?= e(t('valyuta_sum')) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="totals">
        <div class="totals-row">
            <span style="color:var(--muted)">QQS (0%):</span>
            <span>0 <?= e(t('valyuta_sum')) ?></span>
        </div>
        <div class="totals-row total">
            <span><?= e(t('invoice_total')) ?>:</span>
            <span class="amount"><?= number_format((float)$payment['amount'], 0, '.', ' ') ?> <?= e(t('valyuta_sum')) ?></span>
        </div>
    </div>

    <?php if (($payment['status'] ?? '') !== 'success'): ?>
    <div style="margin-top:40px;padding:20px;background:rgba(232,168,56,0.08);border:1px solid rgba(232,168,56,0.25);border-radius:var(--r);font-size:0.88rem;color:var(--dark-soft)">
        <strong style="color:#A87830">To'lov ko'rsatmasi:</strong> Mazkur summani yuqorida ko'rsatilgan rekvizitlar bo'yicha to'lang. To'lov tasdiqlangach, sizga avtomatik bildirishnoma yuboriladi.
    </div>
    <?php endif; ?>

    <div class="foot">
        <div class="qr">VPY</div>
        <p>Ushbu hisob-faktura elektron tarzda yaratilgan va imzo talab qilmaydi.</p>
        <p style="margin-top:6px">© <?= date('Y') ?> <?= e(vpy_setting('company_name', t('site_name') . ' MChJ')) ?> · <?= e(VPY_DOMAIN) ?></p>
    </div>
</div>

</body>
</html>
