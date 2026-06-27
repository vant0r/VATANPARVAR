<?php
require_once __DIR__ . '/includes/public_layout.php';
require_once __DIR__ . '/includes/notifications.php';

$success = false;
$error = '';
$vals = ['name' => '', 'phone' => '', 'subject' => '', 'message' => ''];

if (vpy_is_post()) {
    if (!vpy_csrf_check(vpy_post('csrf'))) { $error = t('xato_csrf'); }
    else {
        $vals['name'] = vpy_post('name');
        $vals['phone'] = vpy_post('phone');
        $vals['subject'] = vpy_post('subject');
        $vals['message'] = vpy_post('message');
        if (mb_strlen($vals['name'], 'UTF-8') < 2 || mb_strlen($vals['message'], 'UTF-8') < 5) { $error = t('xato_format'); }
        else {
            vpy_log('contact', 'Aloqa xabari', $vals);
            vpy_notify_admin('Yangi aloqa xabari', $vals['name'] . ': ' . $vals['subject']);
            $success = true;
            $vals = ['name' => '', 'phone' => '', 'subject' => '', 'message' => ''];
        }
    }
}
$developer_active = vpy_setting('developer_active', '0') === '1';

vpy_public_head(t('contact_title'), t('contact_subtitle'), <<<CSS
.contact-grid{display:grid;grid-template-columns:1fr 1.3fr;gap:40px;align-items:start;margin-top:24px}
.contact-info{display:flex;flex-direction:column;gap:14px}
.contact-info-card{padding:20px;background:var(--glass-strong);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:var(--r);display:flex;align-items:flex-start;gap:16px;transition:var(--t)}
.contact-info-card:hover{transform:translateX(3px);border-color:var(--primary)}
.contact-info-ico{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;display:grid;place-items:center;flex-shrink:0;box-shadow:0 4px 12px var(--primary-glow)}
.contact-info-ico svg{width:20px;height:20px}
.contact-info-card h3{font-size:0.95rem;font-weight:700;margin-bottom:3px}
.contact-info-card p,.contact-info-card a{font-size:0.85rem;color:var(--muted);line-height:1.5}
.contact-info-card a:hover{color:var(--primary)}
.contact-form{padding:36px 32px;background:var(--glass-strong);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--shadow)}
.contact-form h2{font-family:var(--serif);font-size:1.4rem;font-weight:600;margin-bottom:6px}
.contact-form .sub{color:var(--muted);font-size:0.85rem;margin-bottom:22px}
.field{margin-bottom:14px}
.field label{display:block;font-size:0.75rem;font-weight:600;color:var(--dark-soft);margin-bottom:5px;text-transform:uppercase;letter-spacing:0.04em}
.field input,.field textarea{width:100%;padding:12px 16px;border-radius:12px;border:1.5px solid var(--border-strong);background:var(--surface);color:var(--dark);font-size:0.92rem;transition:var(--t)}
.field textarea{min-height:120px;resize:vertical}
.field input:focus,.field textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-glow)}
.fld-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.flash{padding:12px 16px;border-radius:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;font-size:0.88rem}
.flash.success{background:rgba(26,95,180,0.08);color:var(--primary-dark);border:1px solid rgba(26,95,180,0.15)}
.flash.error{background:rgba(220,53,69,0.08);color:#A81D2B;border:1px solid rgba(220,53,69,0.2)}
.flash svg{width:16px;height:16px;flex-shrink:0}
.contact-form .btn{width:100%;padding:15px}
.dev-section{margin-top:60px;padding:40px;background:var(--glass-strong);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:var(--r-xl);display:flex;align-items:center;gap:28px}
.dev-section .dev-ico{width:60px;height:60px;border-radius:50%;background:var(--blue-soft);color:var(--primary);display:grid;place-items:center;flex-shrink:0;border:2px solid var(--border)}
.dev-section .dev-ico svg{width:28px;height:28px}
.dev-section h3{font-family:var(--serif);font-size:1.2rem;font-weight:700;margin-bottom:3px}
.dev-section .dev-title{font-size:0.82rem;color:var(--primary);font-weight:600;margin-bottom:8px}
.dev-section p{font-size:0.88rem;color:var(--muted);line-height:1.6}
.dev-contacts{display:flex;gap:10px;margin-top:10px}
.dev-contacts a{padding:6px 14px;background:var(--blue-soft);border:1px solid var(--border);border-radius:var(--pill);font-size:0.8rem;font-weight:600;color:var(--primary);transition:var(--t)}
.dev-contacts a:hover{background:var(--primary);color:#fff}
@media (max-width:1024px){.contact-grid{grid-template-columns:1fr}.dev-section{flex-direction:column;text-align:center}}
@media (max-width:640px){.contact-form{padding:26px 20px}.fld-row{grid-template-columns:1fr}}
CSS);
vpy_public_navbar('aloqa');
?>

<main>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow"><?= e(t('nav_contact')) ?></span>
        <h1 class="h-display" style="margin-top:14px"><?= e(t('contact_title')) ?></h1>
        <p class="lead"><?= e(t('contact_subtitle')) ?></p>
    </div>
</section>

<section style="padding-top:10px">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <div class="contact-info-card reveal r1">
                    <span class="contact-info-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg></span>
                    <div><h3><?= e(t('footer_phone')) ?></h3><a href="tel:<?= e(preg_replace('/\D/', '', vpy_setting('contact_phone'))) ?>"><?= e(vpy_setting('contact_phone', t('footer_phone_value'))) ?></a></div>
                </div>
                <div class="contact-info-card reveal r2">
                    <span class="contact-info-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                    <div><h3><?= e(t('footer_email')) ?></h3><a href="mailto:<?= e(vpy_setting('contact_email')) ?>"><?= e(vpy_setting('contact_email', t('footer_email_value'))) ?></a></div>
                </div>
                <div class="contact-info-card reveal r3">
                    <span class="contact-info-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                    <div><h3><?= e(t('footer_address')) ?></h3><p><?= e(vpy_setting('contact_address', t('footer_address_value'))) ?></p></div>
                </div>
                <div class="contact-info-card reveal r4">
                    <span class="contact-info-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                    <div><h3><?= e(t('footer_hours')) ?></h3><p><?= e(t('footer_hours_value')) ?></p></div>
                </div>
            </div>
            <div class="contact-form reveal r2">
                <h2><?= e(t('contact_title')) ?></h2>
                <p class="sub"><?= e(t('contact_subtitle')) ?></p>
                <?php if ($success): ?>
                <div class="flash success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg><span><?= e(t('contact_sent')) ?></span></div>
                <?php elseif ($error): ?>
                <div class="flash error"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg><span><?= e($error) ?></span></div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <input type="hidden" name="csrf" value="<?= e(vpy_csrf()) ?>">
                    <div class="fld-row">
                        <div class="field"><label><?= e(t('contact_name')) ?></label><input name="name" type="text" required value="<?= e($vals['name']) ?>"></div>
                        <div class="field"><label><?= e(t('contact_phone')) ?></label><input name="phone" type="tel" required value="<?= e($vals['phone']) ?>"></div>
                    </div>
                    <div class="field"><label><?= e(t('contact_subject')) ?></label><input name="subject" type="text" required value="<?= e($vals['subject']) ?>"></div>
                    <div class="field"><label><?= e(t('contact_message')) ?></label><textarea name="message" required><?= e($vals['message']) ?></textarea></div>
                    <button type="submit" class="btn btn-primary"><?= e(t('contact_send')) ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>
                </form>
            </div>
        </div>

        <!-- DEVELOPER SECTION -->
        <?php if ($developer_active && vpy_setting('developer_name')): ?>
        <div class="dev-section reveal">
            <div class="dev-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
            <div>
                <h3><?= e(vpy_setting('developer_name')) ?></h3>
                <div class="dev-title"><?= e(vpy_setting('developer_title', 'Dasturchi')) ?></div>
                <p><?= e(vpy_setting('developer_description')) ?></p>
                <div class="dev-contacts">
                    <?php if (vpy_setting('developer_phone')): ?><a href="tel:<?= e(preg_replace('/\D/', '', vpy_setting('developer_phone'))) ?>">Qo'ng'iroq</a><?php endif; ?>
                    <?php if (vpy_setting('developer_telegram')): ?><a href="https://t.me/<?= e(ltrim(vpy_setting('developer_telegram'), '@')) ?>">Telegram</a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
</main>

<?php vpy_public_footer(); ?>
