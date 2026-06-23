<?php
require_once __DIR__ . '/../includes/panel_layout.php';
require_once __DIR__ . '/../includes/notifications.php';
vpy_require_admin('/login.php');

if (vpy_is_post() && vpy_csrf_check(vpy_post('csrf'))) {
    $action = vpy_post('action');
    $id = (int)vpy_post('id');
    $payment = vpy_find('tolovlar', 'id', $id);
    if ($payment && $action === 'approve' && ($payment['status'] ?? '') !== 'success') {
        $tariff = vpy_find('tariflar', 'id', $payment['tariff_id']);
        $payment['status'] = 'success';
        $payment['paid_at'] = date('Y-m-d H:i:s');
        $payment['expires_at'] = date('Y-m-d H:i:s', strtotime('+' . (int)($tariff['duration_days'] ?? 30) . ' days'));
        if (empty($payment['transaction_id'])) $payment['transaction_id'] = 'MANUAL-' . strtoupper(vpy_random_string(6));
        vpy_upsert('tolovlar', $payment);
        vpy_notify_payment_success($payment['user_id'], $payment['tariff_name'], $payment['amount']);
        vpy_log('payment_approved', 'To\'lov tasdiqlandi', ['id' => $id, 'admin' => vpy_user()['id']]);
        vpy_flash_set('success', t('msg_updated'));
    } elseif ($payment && $action === 'reject') {
        $payment['status'] = 'failed';
        vpy_upsert('tolovlar', $payment);
        vpy_flash_set('success', t('msg_updated'));
    } elseif ($action === 'delete' && $payment) {
        vpy_delete('tolovlar', 'id', $id);
        vpy_flash_set('success', t('msg_deleted'));
    }
    vpy_redirect('/admin/tolovlar.php');
}

$status_filter = vpy_get('status', '');
$payments = vpy_read_json('tolovlar', []);
if ($status_filter) $payments = array_values(array_filter($payments, fn($p) => ($p['status'] ?? '') === $status_filter));
usort($payments, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

$page = max(1, (int)vpy_get('p', 1));
$pag = vpy_paginate($payments, 25, $page);

$total_amount = array_sum(array_column(array_filter($payments, fn($p) => ($p['status'] ?? '') === 'success'), 'amount'));

vpy_panel_head(t('admin_payments'));
vpy_panel_sidebar('tolovlar', true);
?>
<main class="main">
<?php vpy_panel_topbar(t('admin_payments'), 'Jami: ' . number_format($total_amount, 0, '.', ' ') . ' so\'m'); ?>

<div class="card">
    <div style="display:flex;gap:6px;padding:6px;background:var(--glass);border:1px solid var(--border);border-radius:var(--pill);width:fit-content;margin-bottom:18px">
        <a href="?" style="padding:8px 16px;border-radius:var(--pill);font-size:0.85rem;font-weight:600;<?= $status_filter === '' ? 'background:var(--primary);color:#fff' : 'color:var(--dark-soft)' ?>">Hammasi</a>
        <a href="?status=success" style="padding:8px 16px;border-radius:var(--pill);font-size:0.85rem;font-weight:600;<?= $status_filter === 'success' ? 'background:var(--primary);color:#fff' : 'color:var(--dark-soft)' ?>"><?= e(t('admin_status_success')) ?></a>
        <a href="?status=pending" style="padding:8px 16px;border-radius:var(--pill);font-size:0.85rem;font-weight:600;<?= $status_filter === 'pending' ? 'background:var(--accent);color:#fff' : 'color:var(--dark-soft)' ?>"><?= e(t('admin_status_pending')) ?></a>
        <a href="?status=failed" style="padding:8px 16px;border-radius:var(--pill);font-size:0.85rem;font-weight:600;<?= $status_filter === 'failed' ? 'background:#C73E36;color:#fff' : 'color:var(--dark-soft)' ?>"><?= e(t('admin_status_failed')) ?></a>
    </div>

    <?php if (empty($pag['items'])): ?>
        <div class="empty"><h3>To'lovlar yo'q</h3></div>
    <?php else: ?>
    <div style="overflow-x:auto">
        <table class="tbl">
            <thead><tr><th>#</th><th>Foydalanuvchi</th><th>Tarif</th><th>Summa</th><th>Usul</th><th>Status</th><th>Sana</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($pag['items'] as $p):
                    $usr = vpy_find('users', 'id', $p['user_id']);
                    $st = $p['status'];
                    $chip = $st === 'success' ? 'success' : ($st === 'pending' ? 'warning' : 'danger');
                ?>
                <tr>
                    <td><strong>#<?= e($p['invoice_number']) ?></strong></td>
                    <td><?= e($usr['name'] ?? '—') ?><div style="font-size:0.78rem;color:var(--muted)"><?= e($usr['phone'] ?? '') ?></div></td>
                    <td><?= e($p['tariff_name']) ?></td>
                    <td><strong style="font-family:var(--serif);font-size:1.05rem;color:var(--primary)"><?= number_format((float)$p['amount'], 0, '.', ' ') ?></strong></td>
                    <td><span class="chip chip-muted"><?= e(strtoupper($p['method'])) ?></span></td>
                    <td><span class="chip chip-<?= $chip ?>"><?= e($st) ?></span></td>
                    <td><?= e(vpy_date($p['created_at'], 'd.m.Y H:i')) ?></td>
                    <td>
                        <div class="row-actions">
                            <a href="/invoice.php?id=<?= (int)$p['id'] ?>" target="_blank" title="Hisob-faktura"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg></a>
                            <?php if ($st === 'pending'): ?>
                            <form method="post" style="display:inline" onsubmit="return confirm('Tasdiqlansinmi?')">
                                <input type="hidden" name="csrf" value="<?= e(vpy_csrf()) ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" title="Tasdiqlash"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></button>
                            </form>
                            <form method="post" style="display:inline" onsubmit="return confirm('Bekor qilinsinmi?')">
                                <input type="hidden" name="csrf" value="<?= e(vpy_csrf()) ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</main>
<?php vpy_panel_foot(); ?>
