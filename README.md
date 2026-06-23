# VatanParvar Yaypan

Yaypan shahridagi avtomaktab uchun yo'l harakati qoidalari nazariy imtihoniga onlayn tayyorlanish premium platformasi.

**Domen:** [vatanparvaryaypan.uz](https://vatanparvaryaypan.uz)

## Asosiy imkoniyatlar

- 4000+ rasmiy YHQ savollari (40 ta bilet)
- Aqlli takrorlash tizimi
- Reyting va liderlar jadvali
- Telegram bot bildirishnomalari
- Click va Payme to'lov tizimlari
- Hisob-faktura generatsiyasi
- O'zbek lotin / kirill tillari
- PWA (oflayn rejim)
- Premium 2026 dizayn standarti

## Texnik stack

- **Backend:** PHP 7.4+
- **Frontend:** HTML / CSS / JavaScript (har bir sahifa o'z fayli, tashqi fayllarsiz)
- **Ma'lumotlar:** JSON (foydalanuvchilar, tariflar, blog, sharhlar, to'lovlar, loglar, sozlamalar)
- **Test bazasi:** MySQL (`test_savollar` jadvali)
- **PWA:** Service Worker + Manifest

## Fayl tuzilmasi

```
vatanparvar-yaypan/
├── index.php                 # Premium landing
├── login.php / register.php  # Autentifikatsiya
├── tariflar.php              # Ommaviy tariflar
├── blog.php / aloqa.php      # Blog va aloqa
├── invoice.php               # Hisob-faktura
├── install.php               # O'rnatish sehrgari
├── sw.js / manifest.json     # PWA
├── assets/                   # Statik resurslar
├── includes/                 # PHP backend (config, auth, layouts, payments)
├── lang/                     # uz_latin.php, uz_cyrillic.php (360+ kalit)
├── data/                     # JSON ma'lumotlar bazasi
├── sql/                      # test_savollar.sql
├── user/                     # Foydalanuvchi paneli (9 sahifa)
├── admin/                    # Admin panel (16 sahifa)
├── api/                      # Click, Payme, invoice API
├── telegram/                 # Telegram bot
└── cron/                     # Kunlik vazifalar
```

## O'rnatish

1. Fayllarni serverga yuklang
2. `data/` papkasiga yozish ruxsatini bering: `chmod 775 data`
3. `includes/config.php` da MySQL sozlamalarini to'g'rilang:
   ```php
   define('VPY_DB_HOST', 'localhost');
   define('VPY_DB_NAME', 'host8873_avto');
   define('VPY_DB_USER', 'host8873_avto');
   define('VPY_DB_PASS', 'YOUR_PASSWORD');
   ```
4. `https://vatanparvaryaypan.uz/install.php` ga kiring
5. SQL'ni import qiling, administrator hisobini yarating
6. Xavfsizlik uchun `install.php` ni o'chiring
7. Cron sozlang: `0 3 * * * /usr/bin/php /path/to/cron/daily.php`

## Telegram webhook

Botni sozlash uchun:
```
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://vatanparvaryaypan.uz/telegram/bot.php
```

## Standart kirish ma'lumotlari

JSON dan default test foydalanuvchilari:

| Rol | Telefon | Parol |
|-----|---------|-------|
| Admin | +998 90 123 45 67 | admin123 |
| Foydalanuvchi | +998 90 111 22 33 | user1234 |

> ⚠️ Production muhitida `install.php` orqali yangi parollar yarating.

## Dizayn xususiyatlari

15 ta dizayn elementi:
1. Glassmorphism — shaffof panellar, 30px blur
2. Bento Grid — assimetrik kartochkalar
3. Mesh Gradient — animatsion organic shakllar
4. Floating Layers — sekin suzuvchi elementlar
5. 3D Depth — perspective transform
6. Parallax — scroll va mouse bilan
7. Advanced Typography — Playfair Display + Manrope
8. Micro Interactions — hover, click, focus
9. Motion Design — keyframe animatsiyalar
10. Visual Storytelling — eyebrow → title → CTA
11. Dynamic Counters — scroll trigger raqamlar
12. Dashboard Mockup — interaktiv UI
13. Animated Components — ripple, icon spin
14. Grain Texture — SVG fractal noise
15. Organic Shapes — yumshoq egri chiziqlar

## Rang palitrasi

```
Asosiy fon:    #FAF7F2 (warm cream)
Primary:       #0D6B4E (deep emerald)
Accent:        #E8A838 (warm amber)
Secondary:     #B7C9B3 (soft sage)
Dark:          #1E1B18 (espresso)
```

## Litsenziya

© 2026 VatanParvar Yaypan. Barcha huquqlar himoyalangan.
