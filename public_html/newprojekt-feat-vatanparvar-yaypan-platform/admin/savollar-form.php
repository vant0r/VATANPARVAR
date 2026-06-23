<?php
require_once __DIR__ . '/../includes/panel_layout.php';
vpy_require_admin('/login.php');

$pdo = vpy_pdo();
if (!$pdo) {
    vpy_flash_set('error', 'Bazaga ulanib bo\'lmadi');
    vpy_redirect('/admin/savollar.php');
}

$id = (int)vpy_get('id');
$q = null;
if ($id) {
    $st = $pdo->prepare("SELECT * FROM test_savollar WHERE id = :id");
    $st->execute([':id' => $id]);
    $q = $st->fetch();
}
$is_edit = !empty($q);

if (vpy_is_post() && vpy_csrf_check(vpy_post('csrf'))) {
    $data = [
        'bilet_id' => (int)vpy_post('bilet_id', 1),
        'tartib' => (int)vpy_post('tartib', 1),
        'mavzu' => vpy_post('mavzu', 'umumiy'),
        'qiyinlik' => vpy_post('qiyinlik', 'orta'),
        'savol' => vpy_post('savol'),
        'savol_cyrl' => vpy_post('savol_cyrl'),
        'variant_a' => vpy_post('variant_a'),
        'variant_b' => vpy_post('variant_b'),
        'variant_c' => vpy_post('variant_c'),
        'variant_d' => vpy_post('variant_d'),
        'variant_a_cyrl' => vpy_post('variant_a_cyrl'),
        'variant_b_cyrl' => vpy_post('variant_b_cyrl'),
        'variant_c_cyrl' => vpy_post('variant_c_cyrl'),
        'variant_d_cyrl' => vpy_post('variant_d_cyrl'),
        'togri' => vpy_post('togri', 'A'),
        'izoh' => vpy_post('izoh'),
        'izoh_cyrl' => vpy_post('izoh_cyrl'),
        'holat' => vpy_post('holat', 'faol'),
    ];
    if ($is_edit) {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $st = $pdo->prepare("UPDATE test_savollar SET $set WHERE id = :id");
        $data[':id'] = $id;
        $params = [];
        foreach ($data as $k => $v) $params[(strpos($k, ':') === 0 ? $k : ":$k")] = $v;
        $st->execute($params);
        vpy_flash_set('success', t('msg_updated'));
    } else {
        $cols = implode(',', array_keys($data));
        $vals = ':' . implode(',:', array_keys($data));
        $st = $pdo->prepare("INSERT INTO test_savollar ($cols) VALUES ($vals)");
        foreach ($data as $k => $v) $st->bindValue(":$k", $v);
        $st->execute();
        vpy_flash_set('success', t('msg_added'));
    }
    vpy_redirect('/admin/savollar.php');
}

vpy_panel_head($is_edit ? t('admin_edit') . ' #' . $id : t('admin_add'));
vpy_panel_sidebar('savollar', true);
?>
<main class="main">
<?php vpy_panel_topbar(
    $is_edit ? t('admin_edit') . ' · #' . $id : t('admin_add'),
    sprintf('Bilet %02d · Savol %02d', (int)($q['bilet_id'] ?? 1), (int)($q['tartib'] ?? 1)),
    '<a href="/admin/savollar.php" class="btn btn-ghost">' . e(t('btn_back')) . '</a>'
); ?>

<form method="post">
    <input type="hidden" name="csrf" value="<?= e(vpy_csrf()) ?>">

    <div class="card">
        <div class="card-head"><h2>Asosiy ma'lumotlar</h2></div>
        <div class="field-row">
            <div class="field">
                <label>Bilet raqami</label>
                <input type="number" name="bilet_id" min="1" max="40" value="<?= (int)($q['bilet_id'] ?? 1) ?>" required>
            </div>
            <div class="field">
                <label>Tartib</label>
                <input type="number" name="tartib" min="1" max="20" value="<?= (int)($q['tartib'] ?? 1) ?>" required>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <label>Mavzu</label>
                <select name="mavzu" style="width:100%;padding:13px 18px;border-radius:14px;border:1px solid var(--border-strong);background:rgba(255,253,249,0.85)">
                    <?php foreach (['belgilar','chiziqlar','signallar','tezlik','parking','kesishma','piyoda','hujjatlar','favqulodda','umumiy'] as $m): ?>
                        <option value="<?= e($m) ?>" <?= ($q['mavzu'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Qiyinlik</label>
                <select name="qiyinlik" style="width:100%;padding:13px 18px;border-radius:14px;border:1px solid var(--border-strong);background:rgba(255,253,249,0.85)">
                    <option value="oson" <?= ($q['qiyinlik'] ?? '') === 'oson' ? 'selected' : '' ?>>Oson</option>
                    <option value="orta" <?= ($q['qiyinlik'] ?? 'orta') === 'orta' ? 'selected' : '' ?>>O'rta</option>
                    <option value="qiyin" <?= ($q['qiyinlik'] ?? '') === 'qiyin' ? 'selected' : '' ?>>Qiyin</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-head"><h2>Savol matni</h2></div>
        <div class="field"><label>Savol (lotin)</label><textarea name="savol" required><?= e($q['savol'] ?? '') ?></textarea></div>
        <div class="field"><label>Savol (kirill)</label><textarea name="savol_cyrl"><?= e($q['savol_cyrl'] ?? '') ?></textarea></div>
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-head"><h2>Variantlar</h2></div>
        <?php foreach (['a','b','c','d'] as $L): ?>
            <div style="display:grid;grid-template-columns:auto 1fr 1fr;gap:14px;align-items:start;margin-bottom:14px">
                <div style="padding-top:36px"><label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:700"><input type="radio" name="togri" value="<?= strtoupper($L) ?>" <?= strtoupper($q['togri'] ?? 'A') === strtoupper($L) ? 'checked' : '' ?>> <span style="width:32px;height:32px;border-radius:10px;background:rgba(13,107,78,0.08);color:var(--primary);display:grid;place-items:center;font-weight:700"><?= strtoupper($L) ?></span></label></div>
                <div class="field" style="margin:0"><label>Variant <?= strtoupper($L) ?> (lotin)</label><input type="text" name="variant_<?= $L ?>" value="<?= e($q["variant_$L"] ?? '') ?>" <?= in_array($L, ['a','b']) ? 'required' : '' ?>></div>
                <div class="field" style="margin:0"><label>Variant <?= strtoupper($L) ?> (kirill)</label><input type="text" name="variant_<?= $L ?>_cyrl" value="<?= e($q["variant_{$L}_cyrl"] ?? '') ?>"></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-head"><h2>Izoh</h2></div>
        <div class="field-row">
            <div class="field"><label>Izoh (lotin)</label><textarea name="izoh"><?= e($q['izoh'] ?? '') ?></textarea></div>
            <div class="field"><label>Izoh (kirill)</label><textarea name="izoh_cyrl"><?= e($q['izoh_cyrl'] ?? '') ?></textarea></div>
        </div>
        <div class="field-row">
            <div class="field">
                <label>Holat</label>
                <select name="holat" style="width:100%;padding:13px 18px;border-radius:14px;border:1px solid var(--border-strong);background:rgba(255,253,249,0.85)">
                    <option value="faol" <?= ($q['holat'] ?? 'faol') === 'faol' ? 'selected' : '' ?>>Faol</option>
                    <option value="noaktiv" <?= ($q['holat'] ?? '') === 'noaktiv' ? 'selected' : '' ?>>Noaktiv</option>
                </select>
            </div>
            <div></div>
        </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:18px">
        <button type="submit" class="btn btn-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg><?= e(t('btn_save')) ?></button>
        <a href="/admin/savollar.php" class="btn btn-ghost"><?= e(t('btn_cancel')) ?></a>
    </div>
</form>
</main>
<?php vpy_panel_foot(); ?>
