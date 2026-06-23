<?php
require_once __DIR__ . '/../includes/panel_layout.php';
vpy_require_login('/login.php');

$u = vpy_user();
$lang_code = vpy_lang_code();
$is_cyrl = $lang_code === 'uz_cyrillic';

$tariffs = vpy_filter('tariflar', fn($t) => !empty($t['active']));
usort($tariffs, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

$active_payment = vpy_active_tariff_for_user($u['id']);

if (vpy_is_post() && vpy_csrf_check(vpy_post('csrf'))) {
    $tariff_id = (int)vpy_post('tariff_id');
    $method = vpy_post('method');
    $tariff = vpy_find('tariflar', 'id', $tariff_id);
    if ($tariff && in_array($method, ['click', 'payme', 'invoice'], true)) {
        $payment = [
            'id' => vpy_id_next('tolovlar'),
            'user_id' => (int)$u['id'],
            'tariff_id' => (int)$tariff['id'],
            'tariff_name' => $tariff['name'],
            'amount' => (float)$tariff['price'],
            'method' => $method,
            'status' => $method === 'invoice' ? 'pending' : 'pending',
            'transaction_id' => '',
            'invoice_number' => 'INV-' . date('Y') . '-' . sprintf('%04d', vpy_id_next('tolovlar')),
            'expires_at' => null,
            'paid_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        vpy_upsert('tolovlar', $payment);
        vpy_log('payment_init', sprintf('Tarif sotib olish: %s — %s', $tariff['name'], $method), ['user_id' => $u['id']]);
        if ($method === 'invoice') {
            vpy_redirect('/invoice.php?id=' . $payment['id']);
        }
        if ($method === 'click') {
            vpy_redirect('/includes/payments/click.php?id=' . $payment['id']);
        }
        if ($method === 'payme') {
            vpy_redirect('/includes/payments/payme.php?id=' . $payment['id']);
        }
    }
}

$selected = (int)vpy_get('tarif', 0);

vpy_panel_head(t('tariffs_title'), <<<CSS
.tariffs-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:30px}
.tariff-card{position:relative;padding:36px 30px;border-radius:var(--r-lg);background:var(--glass-strong);backdrop-filter:blur(30px);border:1px solid var(--border);transition:var(--t);display:flex;flex-direction:column;overflow:hidden}
.tariff-card:hover{transform:translateY(-6px);box-shadow:var(--shadow);border-color:var(--border-strong)}
.tariff-card.featured{background:linear-gradient(135deg,#FFFDF9,#FAF7F2);border:2px solid var(--accent);box-shadow:0 24px 48px rgba(232,168,56,0.18);transform:scale(1.02)}
.tariff-card.featured:hover{transform:translateY(-6px) scale(1.04)}
.tariff-card.current{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border-color:transparent}
.tariff-card.current::before{content:"";position:absolute;top:-50%;right:-30%;width:80%;height:160%;background:radial-gradient(ellipse,rgba(232,168,56,0.18),transparent 60%);pointer-events:none}
.tariff-badge{position:absolute;top:-1px;right:30px;padding:6px 16px;background:linear-gradient(135deg,var(--accent),#D88F1A);color:#fff;font-size:0.7rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;border-radius:0 0 12px 12px}
.tariff-card.current .tariff-badge{background:#fff;color:var(--primary)}
.tariff-name{font-family:var(--serif);font-size:1.4rem;font-weight:700;margin-bottom:4px}
.tariff-desc{font-size:0.85rem;opacity:0.85;margin-bottom:24px;min-height:38px}
.tariff-price{font-family:var(--serif);font-size:2.4rem;font-weight:700;color:var(--primary);line-height:1}
.tariff-card.current .tariff-price{color:#fff}
.tariff-period{font-size:0.85rem;opacity:0.75;margin-bottom:24px}
.tariff-features{flex:1;display:flex;flex-direction:column;gap:10px;margin-bottom:24px;padding-top:18px;border-top:1px dashed var(--border-strong);font-size:0.85rem}
.tariff-card.current .tariff-features{border-color:rgba(255,255,255,0.18)}
.tariff-features li{display:flex;align-items:flex-start;gap:10px;line-height:1.4}
.tariff-features svg{width:16px;height:16px;color:var(--primary);flex-shrink:0;margin-top:2px}
.tariff-card.current .tariff-features svg{color:var(--accent)}
.tariff-card .btn{width:100%}
.pay-modal{position:fixed;inset:0;background:rgba(30,27,24,0.5);backdrop-filter:blur(8px);display:none;align-items:center;justify-content:center;z-index:100;padding:20px}
.pay-modal.show{display:flex}
.pay-modal-card{background:#fff;border-radius:var(--r-lg);padding:36px;max-width:520px;width:100%;box-shadow:0 30px 60px rgba(0,0,0,0.3)}
.pay-modal-card h3{font-family:var(--serif);font-size:1.4rem;font-weight:600;margin-bottom:6px}
.pay-modal-card .sub{color:var(--muted);font-size:0.9rem;margin-bottom:24px}
.pay-method-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.pay-method{padding:18px;border-radius:14px;border:1.5px solid var(--border-strong);background:rgba(255,253,249,0.6);cursor:pointer;text-align:center;transition:var(--t)}
.pay-method:hover{border-color:var(--primary);background:var(--light)}
.pay-method.selected{background:linear-gradient(135deg,rgba(13,107,78,0.08),rgba(232,168,56,0.06));border-color:var(--primary);box-shadow:0 4px 14px rgba(13,107,78,0.12)}
.pay-method-ico{width:36px;height:36px;border-radius:10px;background:rgba(13,107,78,0.08);color:var(--primary);display:grid;place-items:center;margin:0 auto 8px}
.pay-method.selected .pay-method-ico{background:var(--primary);color:#fff}
.pay-method-name{font-weight:600;font-size:0.88rem;color:var(--dark)}
.pay-method-sub{font-size:0.75rem;color:var(--muted);margin-top:2px}
.pay-modal-actions{display:flex;gap:10px;justify-content:flex-end}
@media (max-width:1024px){.tariffs-grid{grid-template-columns:1fr;max-width:480px;margin-left:auto;margin-right:auto}.tariff-card.featured{transform:none}}
CSS);
vpy_panel_sidebar('tariflar', false);
?>

<main class="main">
<?php vpy_panel_topbar(t('tariffs_title'), t('tariffs_subtitle')); ?>

<?php if ($active_payment): ?>
<div class="card" style="margin-bottom:20px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border-color:transparent">
    <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
        <div style="width:56px;height:56px;border-radius:18px;background:rgba(232,168,56,0.25);color:var(--accent);display:grid;place-items:center;flex-shrink:0">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div style="flex:1">
            <div style="font-family:var(--serif);font-size:1.2rem;font-weight:600;color:#fff"><?= e(t('tariffs_active')) ?>: <?= e($active_payment['tariff_name']) ?></div>
            <div style="font-size:0.85rem;color:rgba(255,253,249,0.75);margin-top:2px">
                <?= e(vpy_date($active_payment['expires_at'], 'd.m.Y')) ?> gacha amal qiladi
                · <?= max(0, ceil((strtotime($active_payment['expires_at']) - time()) / 86400)) ?> kun qoldi
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="tariffs-grid">
    <?php foreach ($tariffs as $tf):
        $features = $is_cyrl ? ($tf['features_cyrl'] ?? $tf['features']) : $tf['features'];
        $name = $is_cyrl ? ($tf['name_cyrl'] ?? $tf['name']) : $tf['name'];
        $desc = $is_cyrl ? ($tf['description_cyrl'] ?? $tf['description']) : $tf['description'];
        $period = $is_cyrl ? ($tf['period_label_cyrl'] ?? $tf['period_label']) : $tf['period_label'];
        $is_current = $active_payment && (int)$active_payment['tariff_id'] === (int)$tf['id'];
        $is_featured = !empty($tf['popular']) || !empty($tf['highlight']);
    ?>
    <div class="tariff-card <?= $is_current ? 'current' : ($is_featured ? 'featured' : '') ?>">
        <?php if ($is_current): ?><div class="tariff-badge"><?= e(t('tariffs_active')) ?></div>
        <?php elseif (!empty($tf['popular'])): ?><div class="tariff-badge"><?= e(t('tariffs_badge_popular')) ?></div>
        <?php endif; ?>
        <h3 class="tariff-name"><?= e($name) ?></h3>
        <p class="tariff-desc"><?= e($desc) ?></p>
        <div class="tariff-price"><?= number_format((float)$tf['price'], 0, '.', ' ') ?> <span style="font-size:0.5em;font-weight:500"><?= e(t('valyuta_sum')) ?></span></div>
        <div class="tariff-period"><?= e($period) ?></div>
        <ul class="tariff-features">
            <?php foreach ((array)$features as $f): ?>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><?= e($f) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($is_current): ?>
            <button class="btn" style="background:rgba(255,255,255,0.95);color:var(--dark)" disabled><?= e(t('tariffs_active')) ?></button>
        <?php else: ?>
            <button type="button" class="btn <?= $is_featured ? 'btn-primary' : 'btn-dark' ?>" data-tariff="<?= (int)$tf['id'] ?>" data-tariff-name="<?= e($name) ?>" data-tariff-price="<?= (float)$tf['price'] ?>" onclick="openPay(this)"><?= e(t('tariffs_buy')) ?></button>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<div class="pay-modal" id="payModal">
    <div class="pay-modal-card">
        <h3><?= e(t('pay_select_method')) ?></h3>
        <p class="sub" id="paySub">Tariff tanlang</p>
        <form method="post" id="payForm">
            <input type="hidden" name="csrf" value="<?= e(vpy_csrf()) ?>">
            <input type="hidden" name="tariff_id" id="payTariffId">
            <input type="hidden" name="method" id="payMethod" value="click">
            <div class="pay-method-grid">
                <div class="pay-method selected" data-method="click" onclick="selectMethod(this)">
                    <div class="pay-method-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                    <div class="pay-method-name">Click</div>
                    <div class="pay-method-sub">Karta orqali</div>
                </div>
                <div class="pay-method" data-method="payme" onclick="selectMethod(this)">
                    <div class="pay-method-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg></div>
                    <div class="pay-method-name">Payme</div>
                    <div class="pay-method-sub">Mobil to'lov</div>
                </div>
                <div class="pay-method" data-method="invoice" onclick="selectMethod(this)">
                    <div class="pay-method-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg></div>
                    <div class="pay-method-name"><?= e(t('pay_method_invoice')) ?></div>
                    <div class="pay-method-sub">Bank o'tkazma</div>
                </div>
                <div class="pay-method" data-method="cash" onclick="alert('Naqd to\'lov uchun avtomaktabga murojaat qiling');" style="cursor:not-allowed;opacity:0.7">
                    <div class="pay-method-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/></svg></div>
                    <div class="pay-method-name"><?= e(t('pay_method_cash')) ?></div>
                    <div class="pay-method-sub">Avtomaktabda</div>
                </div>
            </div>
            <div class="pay-modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closePay()"><?= e(t('btn_cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><?= e(t('tariffs_buy')) ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openPay(btn){
    var id = btn.getAttribute('data-tariff');
    var name = btn.getAttribute('data-tariff-name');
    var price = parseFloat(btn.getAttribute('data-tariff-price'));
    document.getElementById('payTariffId').value = id;
    document.getElementById('paySub').textContent = name + ' — ' + price.toLocaleString('uz-UZ').replace(/,/g, ' ') + ' so\'m';
    document.getElementById('payModal').classList.add('show');
}
function closePay(){ document.getElementById('payModal').classList.remove('show'); }
function selectMethod(el){
    document.querySelectorAll('.pay-method').forEach(function(m){ m.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('payMethod').value = el.getAttribute('data-method');
}
document.getElementById('payModal').addEventListener('click', function(e){
    if (e.target === this) closePay();
});
<?php if ($selected): ?>
window.addEventListener('load', function(){
    var btn = document.querySelector('[data-tariff="<?= (int)$selected ?>"]');
    if (btn) openPay(btn);
});
<?php endif; ?>
</script>

</main>
<?php vpy_panel_foot(); ?>
