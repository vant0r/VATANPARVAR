-- =============================================================
-- VatanParvar Yaypan — Test savollar bazasi
-- O'zbekiston Respublikasi yo'l harakati qoidalari nazariy imtihoni
-- Faqat test_savollar SQL da, qolgan barcha ma'lumotlar JSON da
-- =============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- -------------------------------------------------------------
-- Jadval: test_savollar
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `test_savollar`;
CREATE TABLE `test_savollar` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bilet_id` SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Bilet raqami 1-40',
    `tartib` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Bilet ichidagi tartib 1-20',
    `mavzu` VARCHAR(80) NOT NULL DEFAULT 'umumiy' COMMENT 'belgilar, chiziqlar, signallar, tezlik, parking, kesishma, piyoda, hujjatlar, favqulodda, umumiy',
    `qiyinlik` ENUM('oson','orta','qiyin') NOT NULL DEFAULT 'orta',
    `savol` TEXT NOT NULL,
    `savol_cyrl` TEXT NULL,
    `rasm` VARCHAR(255) NULL DEFAULT NULL,
    `variant_a` VARCHAR(500) NOT NULL,
    `variant_b` VARCHAR(500) NOT NULL,
    `variant_c` VARCHAR(500) NULL,
    `variant_d` VARCHAR(500) NULL,
    `variant_a_cyrl` VARCHAR(500) NULL,
    `variant_b_cyrl` VARCHAR(500) NULL,
    `variant_c_cyrl` VARCHAR(500) NULL,
    `variant_d_cyrl` VARCHAR(500) NULL,
    `togri` ENUM('A','B','C','D') NOT NULL DEFAULT 'A',
    `izoh` TEXT NULL,
    `izoh_cyrl` TEXT NULL,
    `holat` ENUM('faol','noaktiv') NOT NULL DEFAULT 'faol',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bilet` (`bilet_id`, `tartib`),
    KEY `idx_mavzu` (`mavzu`),
    KEY `idx_holat` (`holat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Namuna savollar (40 ta bilet uchun boshlang'ich majmua)
-- Tayyor importdan keyin admin paneli orqali to'ldirib boriladi
-- -------------------------------------------------------------

INSERT INTO `test_savollar` (`bilet_id`,`tartib`,`mavzu`,`qiyinlik`,`savol`,`savol_cyrl`,`variant_a`,`variant_b`,`variant_c`,`variant_d`,`variant_a_cyrl`,`variant_b_cyrl`,`variant_c_cyrl`,`variant_d_cyrl`,`togri`,`izoh`,`izoh_cyrl`) VALUES

-- BILET 1
(1,1,'belgilar','oson','Tartibga soluvchi yo''l belgilari qaysi guruhga kiradi?','Тартибга солувчи йўл белгилари қайси гуруҳга киради?','Ogohlantiruvchi belgilar','Ustunlik belgilar','Taqiqlovchi belgilar','Buyuruvchi belgilar','Огоҳлантирувчи белгилар','Устунлик белгилар','Тақиқловчи белгилар','Буюрувчи белгилар','D','Buyuruvchi belgilar yumaloq, ko''k fonli bo''lib, ma''lum bir harakatni majburlaydi.','Буюрувчи белгилар юмалоқ, кўк фонли бўлиб, маълум бир ҳаракатни мажбурлайди.'),

(1,2,'signallar','orta','Yo''lda bir vaqtda svetofor va regulyator bo''lsa, qaysi biri ustun hisoblanadi?','Йўлда бир вақтда светофор ва регулятор бўлса, қайси бири устун ҳисобланади?','Svetofor signali','Regulyator signali','Ikkalasi ham teng','Yo''l belgilar ustun','Светофор сигнали','Регулятор сигнали','Иккаласи ҳам тенг','Йўл белгилар устун','B','Regulyator signali har doim svetofor va belgilarga nisbatan ustun hisoblanadi.','Регулятор сигнали ҳар доим светофор ва белгиларга нисбатан устун ҳисобланади.'),

(1,3,'tezlik','orta','Aholi punktida umumiy maksimal tezlik chegarasi qanday?','Аҳоли пунктида умумий максимал тезлик чегараси қандай?','50 km/soat','60 km/soat','70 km/soat','80 km/soat','50 км/соат','60 км/соат','70 км/соат','80 км/соат','B','Aholi punktida umumiy yo''l qoidasi bo''yicha maksimal tezlik 60 km/soat.','Аҳоли пунктида умумий йўл қоидаси бўйича максимал тезлик 60 км/соат.'),

(1,4,'hujjatlar','oson','Haydovchi avtomobilda doimiy ravishda olib yurishi shart bo''lgan hujjat?','Ҳайдовчи автомобилда доимий равишда олиб юриши шарт бўлган ҳужжат?','Faqat haydovchilik guvohnomasi','Pasport','Haydovchilik guvohnomasi va texnik pasport','Faqat texnik pasport','Фақат ҳайдовчилик гувоҳномаси','Паспорт','Ҳайдовчилик гувоҳномаси ва техник паспорт','Фақат техник паспорт','C','Haydovchi yo''nalishda haydovchilik guvohnomasi va transport vositasi texnik pasportini olib yurishi shart.','Ҳайдовчи йўналишда ҳайдовчилик гувоҳномаси ва транспорт воситаси техник паспортини олиб юриши шарт.'),

(1,5,'kesishma','qiyin','Boshqarilmaydigan kesishmada qaysi haydovchi ustunlikka ega?','Бошқарилмайдиган кесишмада қайси ҳайдовчи устунликка эга?','Chap tomondan kelayotgan','O''ng tomondan kelayotgan','Tezroq harakatlanayotgan','Yirik transportdagi','Чап томондан келаётган','Ўнг томондан келаётган','Тезроқ ҳаракатланаётган','Йирик транспортдаги','B','Teng huquqli yo''l kesishmasida o''ng tomondan kelayotgan transportga ustunlik beriladi.','Тенг ҳуқуқли йўл кесишмасида ўнг томондан келаётган транспортга устунлик берилади.'),

-- BILET 2
(2,1,'belgilar','oson','Qizil chegarali uchburchak shaklidagi belgilar nimani anglatadi?','Қизил чегарали учбурчак шаклидаги белгилар нимани англатади?','Taqiqlovchi','Ogohlantiruvchi','Buyuruvchi','Axborot','Тақиқловчи','Огоҳлантирувчи','Буюрувчи','Ахборот','B','Uchburchak qizil chegarali — ogohlantiruvchi belgilar guruhi.','Учбурчак қизил чегарали — огоҳлантирувчи белгилар гуруҳи.'),

(2,2,'chiziqlar','orta','Uzluksiz chiziqning qanday vazifasi bor?','Узлуксиз чизиқнинг қандай вазифаси бор?','Kesib o''tish ruxsat etiladi','Kesib o''tish taqiqlanadi','Faqat chap tomonga o''tish mumkin','Faqat to''xtash uchun','Кесиб ўтиш рухсат этилади','Кесиб ўтиш тақиқланади','Фақат чап томонга ўтиш мумкин','Фақат тўхташ учун','B','Uzluksiz yo''l chizig''ini kesib o''tish qat''iyan taqiqlanadi.','Узлуксиз йўл чизиғини кесиб ўтиш қатъиян тақиқланади.'),

(2,3,'piyoda','oson','Piyodalar o''tish joyida haydovchi qanday harakatlanishi kerak?','Пиёдалар ўтиш жойида ҳайдовчи қандай ҳаракатланиши керак?','Tez o''tib ketish','Piyodaga yo''l berish','Signal berib o''tish','Sekinlatib o''tish kifoya','Тез ўтиб кетиш','Пиёдага йўл бериш','Сигнал бериб ўтиш','Секинлатиб ўтиш кифоя','B','Belgilangan piyodalar o''tish joyida har doim piyodaga ustunlik beriladi.','Белгиланган пиёдалар ўтиш жойида ҳар доим пиёдага устунлик берилади.'),

(2,4,'signallar','orta','Sariq signal yongan paytda haydovchi nima qiladi?','Сариқ сигнал ёнган пайтда ҳайдовчи нима қилади?','To''xtash chizig''i oldida to''xtaydi','Tez o''tib ketadi','Yo''lni o''zgartiradi','Bibilatadi','Тўхташ чизиғи олдида тўхтайди','Тез ўтиб кетади','Йўлни ўзгартиради','Сигнал беради','A','Sariq signalda haydovchi to''xtash chizig''i oldida to''xtashi shart, faqat to''xtatish xavfli bo''lsa o''tib ketishga ruxsat.','Сариқ сигналда ҳайдовчи тўхташ чизиғи олдида тўхташи шарт.'),

(2,5,'tezlik','orta','Aholi punkti tashqarisida engil avtomobil uchun tezlik chegarasi?','Аҳоли пункти ташқарисида енгил автомобиль учун тезлик чегараси?','70 km/soat','90 km/soat','100 km/soat','110 km/soat','70 км/соат','90 км/соат','100 км/соат','110 км/соат','C','Aholi punkti tashqarisida engil avtomobil uchun maksimal tezlik 100 km/soat.','Аҳоли пункти ташқарисида енгил автомобиль учун максимал тезлик 100 км/соат.'),

-- BILET 3
(3,1,'parking','orta','To''xtash va qo''yish o''rtasidagi farq qancha vaqt?','Тўхташ ва қўйиш ўртасидаги фарқ қанча вақт?','3 daqiqa','5 daqiqa','10 daqiqa','15 daqiqa','3 дақиқа','5 дақиқа','10 дақиқа','15 дақиқа','B','Transport vositasini 5 daqiqadan ortiq turg''un holatda saqlash — qo''yish hisoblanadi.','Транспорт воситасини 5 дақиқадан ортиқ турғун ҳолатда сақлаш — қўйиш ҳисобланади.'),

(3,2,'belgilar','qiyin','Qaysi belgi maxsus huquqli yo''lni bildiradi?','Қайси белги махсус ҳуқуқли йўлни билдиради?','Asosiy yo''l','Aylanma yo''l','Bir tomonlama yo''l','Avtomagistral','Асосий йўл','Айланма йўл','Бир томонлама йўл','Автомагистраль','D','Avtomagistral belgisi maxsus, yuqori tezlikdagi yo''llarni belgilaydi.','Автомагистраль белгиси махсус, юқори тезликдаги йўлларни белгилайди.'),

(3,3,'kesishma','orta','Aylanma harakatda kim ustunlikka ega?','Айланма ҳаракатда ким устунликка эга?','Aylanmaga kirayotgan','Aylanmada harakatlanayotgan','Tezroq harakatlanayotgan','Birinchi kelgan','Айланмага кираётган','Айланмада ҳаракатланаётган','Тезроқ ҳаракатланаётган','Биринчи келган','B','Hozirgi qoidalar bo''yicha aylanmadagi haydovchi har doim ustunlikka ega.','Ҳозирги қоидалар бўйича айланмадаги ҳайдовчи ҳар доим устунликка эга.'),

(3,4,'piyoda','oson','Piyoda yo''lda qaysi tomondan yurishi kerak?','Пиёда йўлда қайси томондан юриши керак?','Yo''l harakati yo''nalishi bo''yicha','Yo''l harakati yo''nalishiga qarama-qarshi','Yo''lning chap tomonidan','Yo''lning o''rtasidan','Йўл ҳаракати йўналиши бўйича','Йўл ҳаракати йўналишига қарама-қарши','Йўлнинг чап томонидан','Йўлнинг ўртасидан','B','Piyoda harakat yo''nalishiga qarama-qarshi yurishi kerak — kelayotgan transportni ko''rib turish uchun.','Пиёда ҳаракат йўналишига қарама-қарши юриши керак.'),

(3,5,'hujjatlar','oson','Haydovchilik guvohnomasi yo''qligi uchun jarima miqdori qanday belgilanadi?','Ҳайдовчилик гувоҳномаси йўқлиги учун жарима миқдори қандай белгиланади?','Eng kam ish haqi miqdorida','BHM bo''yicha','Doim 100 ming so''m','Faqat ogohlantirish','Энг кам иш ҳақи миқдорида','БҲМ бўйича','Доим 100 минг сўм','Фақат огоҳлантириш','B','Jarimalar BHM (bazaviy hisoblash miqdori) asosida belgilanadi.','Жарималар БҲМ (базавий ҳисоблаш миқдори) асосида белгиланади.'),

-- BILET 4
(4,1,'favqulodda','qiyin','Yo''l-transport hodisasidan keyin haydovchi birinchi navbatda nima qilishi kerak?','Йўл-транспорт ҳодисасидан кейин ҳайдовчи биринчи навбатда нима қилиши керак?','Tarqashga harakat qilish','Transportni to''xtatish va avariya signali yoqish','Politsiyaga qo''ng''iroq qilish va boshqa ish qilmaslik','Transportni surib qo''yish','Тарқашга ҳаракат қилиш','Транспортни тўхтатиш ва авария сигнали ёқиш','Полицияга қўнғироқ қилиш ва бошқа иш қилмаслик','Транспортни суриб қўйиш','B','YTH dan keyin avariya signali yoqilib, transport to''xtatiladi va xavfsizlik chorasi ko''riladi.','ЙТҲ дан кейин авария сигнали ёқилиб, транспорт тўхтатилади ва хавфсизлик чораси кўрилади.'),

(4,2,'belgilar','orta','To''xtash taqiqlangan belgisi qanday ko''rinishga ega?','Тўхташ тақиқланган белгиси қандай кўринишга эга?','Yumaloq, qizil chegara, bir chiziqli','Yumaloq, qizil chegara, ikki chiziqli','Uchburchak shakli','To''rtburchak shakli','Юмалоқ, қизил чегара, бир чизиқли','Юмалоқ, қизил чегара, икки чизиқли','Учбурчак шакли','Тўртбурчак шакли','B','To''xtash taqiqlovchi belgi ikki diagonal chiziqli yumaloq shaklda bo''ladi.','Тўхташ тақиқловчи белги икки диагонал чизиқли юмалоқ шаклда бўлади.'),

(4,3,'tezlik','orta','Hovli hududida maksimal tezlik chegarasi?','Ҳовли ҳудудида максимал тезлик чегараси?','10 km/soat','15 km/soat','20 km/soat','30 km/soat','10 км/соат','15 км/соат','20 км/соат','30 км/соат','C','Hovli hududlarida tezlik 20 km/soatdan oshmasligi kerak.','Ҳовли ҳудудларида тезлик 20 км/соатдан ошмаслиги керак.'),

(4,4,'umumiy','orta','Quvib o''tish qaysi tomondan amalga oshiriladi?','Қувиб ўтиш қайси томондан амалга оширилади?','O''ng tomondan','Chap tomondan','Har ikki tomondan','Faqat aholi punktida o''ng tomondan','Ўнг томондан','Чап томондан','Ҳар икки томондан','Фақат аҳоли пунктида ўнг томондан','B','Quvib o''tish faqat chap tomondan amalga oshiriladi (chap qatorga chiqib).','Қувиб ўтиш фақат чап томондан амалга оширилади.'),

(4,5,'signallar','oson','Yashil signal yongan paytda nima qilish kerak?','Яшил сигнал ёнган пайтда нима қилиш керак?','Faqat o''tish mumkin','O''tish, ammo piyodalarni o''tkazib yuborish','Tezroq o''tish','To''xtash','Фақат ўтиш мумкин','Ўтиш, аммо пиёдаларни ўтказиб юбориш','Тезроқ ўтиш','Тўхташ','B','Yashil signalda harakat boshlanadi, ammo piyoda va boshqa harakatlanuvchi transportlarga ustunlik beriladi.','Яшил сигналда ҳаракат бошланади, аммо пиёда ва бошқа ҳаракатланувчи транспортларга устунлик берилади.'),

-- BILET 5
(5,1,'belgilar','orta','Bolalar belgisi qaysi guruhga kiradi?','Болалар белгиси қайси гуруҳга киради?','Taqiqlovchi','Ogohlantiruvchi','Buyuruvchi','Axborot','Тақиқловчи','Огоҳлантирувчи','Буюрувчи','Ахборот','B','Bolalar belgisi maktab, bog''cha yaqinida o''rnatiladi va ogohlantiruvchi guruhga kiradi.','Болалар белгиси мактаб, боғча яқинида ўрнатилади ва огоҳлантирувчи гуруҳга киради.'),

(5,2,'kesishma','qiyin','Asosiy yo''lda harakatlanayotgan haydovchi yon yo''ldagi haydovchiga nisbatan?','Асосий йўлда ҳаракатланаётган ҳайдовчи ён йўлдаги ҳайдовчига нисбатан?','Yo''l beradi','Ustunlikka ega','Teng huquqli','Sekinlashishi kerak','Йўл беради','Устунликка эга','Тенг ҳуқуқли','Секинлашиши керак','B','Asosiy yo''lda harakatlanayotgan haydovchi yon yo''ldagiga nisbatan ustunlikka ega.','Асосий йўлда ҳаракатланаётган ҳайдовчи ён йўлдагига нисбатан устунликка эга.'),

(5,3,'piyoda','orta','Piyodalar o''tish joyida transport to''xtagan bo''lsa, orqadagi haydovchi nima qiladi?','Пиёдалар ўтиш жойида транспорт тўхтаган бўлса, орқадаги ҳайдовчи нима қилади?','Quvib o''tib ketadi','U ham to''xtaydi va piyodaning o''tib ketishini kutadi','Sekinlatib o''tib ketadi','Signal berib o''tadi','Қувиб ўтиб кетади','У ҳам тўхтайди ва пиёдани ўтиб кетишини кутади','Секинлатиб ўтиб кетади','Сигнал бериб ўтади','B','Piyodalar o''tish joyida boshqa transport to''xtagan bo''lsa, ham to''xtab piyodaga yo''l berish shart.','Пиёдалар ўтиш жойида бошқа транспорт тўхтаган бўлса, ҳам тўхтаб пиёдага йўл бериш шарт.'),

(5,4,'umumiy','orta','Avtomagistralda minimal tezlik chegarasi?','Автомагистралда минимал тезлик чегараси?','30 km/soat','40 km/soat','50 km/soat','Cheklov yo''q','30 км/соат','40 км/соат','50 км/соат','Чеклов йўқ','B','Avtomagistralda 40 km/soatdan past tezlikda harakat taqiqlanadi.','Автомагистралда 40 км/соатдан паст тезликда ҳаракат тақиқланади.'),

(5,5,'hujjatlar','oson','Haydovchilik guvohnomasining amal qilish muddati qancha?','Ҳайдовчилик гувоҳномасининг амал қилиш муддати қанча?','5 yil','10 yil','15 yil','20 yil','5 йил','10 йил','15 йил','20 йил','B','O''zbekistonda haydovchilik guvohnomasi 10 yilga beriladi.','Ўзбекистонда ҳайдовчилик гувоҳномаси 10 йилга берилади.'),

-- BILET 6
(6,1,'belgilar','oson','Bir tomonlama harakat belgisi qanday rangda?','Бир томонлама ҳаракат белгиси қандай рангда?','Qizil','Sariq','Ko''k','Yashil','Қизил','Сариқ','Кўк','Яшил','C','Bir tomonlama harakat belgisi to''rtburchak ko''k fonli, oq strelka bilan.','Бир томонлама ҳаракат белгиси тўртбурчак кўк фонли, оқ стрелка билан.'),

(6,2,'tezlik','orta','Yomg''irli ob-havoda tezlik qanday tanlanishi kerak?','Ёмғирли об-ҳавода тезлик қандай танланиши керак?','Belgilangan tezlikda','Belgilangan tezlikdan past','Belgilangan tezlikdan yuqori','Farqi yo''q','Белгиланган тезликда','Белгиланган тезликдан паст','Белгиланган тезликдан юқори','Фарқи йўқ','B','Yomon ob-havo sharoitida haydovchi xavfsiz tezlik tanlashi shart, bu belgilangan tezlikdan past bo''lishi mumkin.','Ёмон об-ҳаво шароитида ҳайдовчи хавфсиз тезлик танлаши шарт.'),

(6,3,'parking','orta','Trotuarda transport vositasini qo''yish ruxsat etiladimi?','Тротуарда транспорт воситасини қўйиш рухсат этиладими?','Ha, har doim','Faqat maxsus belgi mavjud bo''lsa','Yo''q, hech qachon','Faqat tunda','Ҳа, ҳар доим','Фақат махсус белги мавжуд бўлса','Йўқ, ҳеч қачон','Фақат тунда','B','Trotuarda transport vositasi faqat maxsus belgi yoki belgilangan zonalarda qo''yiladi.','Тротуарда транспорт воситаси фақат махсус белги ёки белгиланган зоналарда қўйилади.'),

(6,4,'signallar','qiyin','Regulyator qo''lini yuqoriga ko''targan paytda harakatlanish ruxsat etiladimi?','Регулятор қўлини юқорига кўтарган пайтда ҳаракатланиш рухсат этиладими?','Faqat to''g''riga','Faqat o''ngga','Faqat chapga','Hech tomonga','Фақат тўғрига','Фақат ўнгга','Фақат чапга','Ҳеч томонга','D','Regulyator qo''lini yuqoriga ko''targanda hech tomondan harakatga ruxsat berilmaydi.','Регулятор қўлини юқорига кўтарганда ҳеч томондан ҳаракатга рухсат берилмайди.'),

(6,5,'piyoda','orta','Piyoda yo''l chegarasidan o''tayotgan paytda ortidan kelayotgan transport?','Пиёда йўл чегарасидан ўтаётган пайтда ортидан келаётган транспорт?','Tez o''tib ketadi','To''xtaydi','Sekinlatadi','Signal beradi','Тез ўтиб кетади','Тўхтайди','Секинлатади','Сигнал беради','B','Piyoda yo''lni kesib o''tayotgan bo''lsa, transport vositasi to''xtashi shart.','Пиёда йўлни кесиб ўтаётган бўлса, транспорт воситаси тўхташи шарт.'),

-- BILET 7
(7,1,'kesishma','qiyin','To-shaped (T-simon) kesishmada asosiy yo''l burilgan bo''lsa, kim ustun?','T-симон кесишмада асосий йўл бурилган бўлса, ким устун?','Asosiy yo''lda harakatlanayotgan','To''g''ri ketayotgan','O''ng tomondan kelgan','Yo''l belgisi orqali aniqlanadi','Асосий йўлда ҳаракатланаётган','Тўғри кетаётган','Ўнг томондан келган','Йўл белгиси орқали аниқланади','A','Asosiy yo''l qaerga burilgan bo''lsa, o''sha tomonda harakatlanayotganlar ustun bo''ladi.','Асосий йўл қаерга бурилган бўлса, ўша томонда ҳаракатланаётганлар устун бўлади.'),

(7,2,'umumiy','orta','Tunda yorug''lik fararlari qachon yoqiladi?','Тунда ёруғлик фараллари қачон ёқилади?','Quyosh botganidan boshlab','Tushunarsiz havoda','Quyosh botgandan keyingi yarim soat','Faqat aholi punktida','Қуёш ботганидан бошлаб','Тушунарсиз ҳавода','Қуёш ботгандан кейинги ярим соат','Фақат аҳоли пунктида','A','Quyosh botishi bilan farar yoqilishi shart, kunduzi ham yaqin yorug''lik tavsiya etiladi.','Қуёш ботиши билан фарар ёқилиши шарт.'),

(7,3,'tezlik','oson','Yo''lovchi avtobusi aholi punktida maksimal tezligi?','Йўловчи автобуси аҳоли пунктида максимал тезлиги?','40 km/soat','60 km/soat','70 km/soat','90 km/soat','40 км/соат','60 км/соат','70 км/соат','90 км/соат','B','Yo''lovchi avtobusi ham aholi punktida 60 km/soatdan oshmasligi kerak.','Йўловчи автобуси ҳам аҳоли пунктида 60 км/соатдан ошмаслиги керак.'),

(7,4,'belgilar','orta','Asosiy yo''l belgisi qaysi shaklda?','Асосий йўл белгиси қайси шаклда?','Uchburchak','Yumaloq','Romb (qiya kvadrat)','Olti burchak','Учбурчак','Юмалоқ','Ромб (қия квадрат)','Олти бурчак','C','Asosiy yo''l belgisi sariq romb shaklida, oq chegarali bo''ladi.','Асосий йўл белгиси сариқ ромб шаклида, оқ чегарали бўлади.'),

(7,5,'favqulodda','qiyin','Tormoz tizimi ishlamayotgan transportni harakatlantirish?','Тормоз тизими ишламаётган транспортни ҳаракатлантириш?','Mumkin, ehtiyotlik bilan','Qat''iyan taqiqlanadi','Faqat aholi punkti tashqarisida','Faqat sekin tezlikda','Мумкин, эҳтиётлик билан','Қатъиян тақиқланади','Фақат аҳоли пункти ташқарисида','Фақат секин тезликда','B','Asosiy tormoz tizimi ishlamayotgan transport vositasini harakatlantirish qat''iyan taqiqlanadi.','Асосий тормоз тизими ишламаётган транспорт воситасини ҳаракатлантириш қатъиян тақиқланади.'),

-- BILET 8
(8,1,'umumiy','orta','Avtomagistralda quvib o''tish ruxsat etiladimi?','Автомагистралда қувиб ўтиш рухсат этиладими?','Yo''q, taqiqlanadi','Ha, ruxsat etiladi','Faqat o''ng tomondan','Faqat tunda','Йўқ, тақиқланади','Ҳа, рухсат этилади','Фақат ўнг томондан','Фақат тунда','B','Avtomagistralda chap tomondan quvib o''tish ruxsat etiladi.','Автомагистралда чап томондан қувиб ўтиш рухсат этилади.'),

(8,2,'belgilar','oson','Taqiqlovchi belgilarning asosiy rangi?','Тақиқловчи белгиларнинг асосий ранги?','Yashil','Sariq','Qizil','Ko''k','Яшил','Сариқ','Қизил','Кўк','C','Taqiqlovchi belgilar yumaloq, qizil chegarali, oq fonli bo''ladi.','Тақиқловчи белгилар юмалоқ, қизил чегарали, оқ фонли бўлади.'),

(8,3,'piyoda','orta','Bolalar guruhini olib o''tayotgan paytda haydovchi nima qiladi?','Болалар гуруҳини олиб ўтаётган пайтда ҳайдовчи нима қилади?','Sekinlik bilan o''tib ketadi','To''xtaydi','Bibilatadi','Signal beradi','Секинлик билан ўтиб кетади','Тўхтайди','Сигнал беради','Шамол беради','B','Bolalar guruhi yo''lni kesib o''tayotgan paytda haydovchi to''liq to''xtashi shart.','Болалар гуруҳи йўлни кесиб ўтаётган пайтда ҳайдовчи тўлиқ тўхташи шарт.'),

(8,4,'parking','qiyin','To''xtash chizig''idan keyin to''xtash kerak bo''ladimi, agar svetofor qizil yongan bo''lsa?','Тўхташ чизиғидан кейин тўхташ керак бўладими, агар светофор қизил ёнган бўлса?','Ha, har doim','Yo''q, hech qachon','Faqat svetofor old qismida','Faqat avariya signali yoqib','Ҳа, ҳар доим','Йўқ, ҳеч қачон','Фақат светофор олд қисмида','Фақат авария сигнали ёқиб','C','Qizil signal yonganda to''xtash chizig''i oldida to''xtash shart, oldinga o''tish taqiqlanadi.','Қизил сигнал ёнганда тўхташ чизиғи олдида тўхташ шарт.'),

(8,5,'kesishma','orta','Aylanma harakatga kirish belgisi qanday?','Айланма ҳаракатга кириш белгиси қандай?','Yumaloq, ko''k fonli, uchta strelka','Uchburchak','To''rtburchak','Romb','Юмалоқ, кўк фонли, учта стрелка','Учбурчак','Тўртбурчак','Ромб','A','Aylanma harakat belgisi ko''k fonli, oq strelkalar yumaloq holatda joylashgan.','Айланма ҳаракат белгиси кўк фонли, оқ стрелкалар юмалоқ ҳолатда жойлашган.'),

-- BILET 9
(9,1,'tezlik','qiyin','Bolalarni tashuvchi avtobus aholi punkti tashqarisida maksimal tezlik?','Болаларни ташувчи автобус аҳоли пункти ташқарисида максимал тезлик?','60 km/soat','70 km/soat','80 km/soat','90 km/soat','60 км/соат','70 км/соат','80 км/соат','90 км/соат','B','Bolalarni tashuvchi maxsus avtobuslar uchun 70 km/soat chegarasi qo''yiladi.','Болаларни ташувчи махсус автобуслар учун 70 км/соат чегараси қўйилади.'),

(9,2,'umumiy','orta','Spirtli ichimlik iste''mol qilgan haydovchi uchun jarima qanday?','Спиртли ичимлик истеъмол қилган ҳайдовчи учун жарима қандай?','Ogohlantirish','Maishiy jarima','Haydovchilik huquqidan mahrum etish','Hech narsa qilinmaydi','Огоҳлантириш','Маиший жарима','Ҳайдовчилик ҳуқуқидан маҳрум этиш','Ҳеч нарса қилинмайди','C','Spirtli ichimlik iste''mol qilgan holda haydash haydovchilik huquqidan mahrum etishga olib keladi.','Спиртли ичимлик истеъмол қилган ҳолда ҳайдаш ҳайдовчилик ҳуқуқидан маҳрум этишга олиб келади.'),

(9,3,'belgilar','orta','To''xtash joyi belgisi qanday rangda?','Тўхташ жойи белгиси қандай рангда?','Yashil','Ko''k','Sariq','Qizil','Яшил','Кўк','Сариқ','Қизил','B','To''xtash joyi belgisi to''rtburchak, ko''k fonli, oq P harfi bilan.','Тўхташ жойи белгиси тўртбурчак, кўк фонли, оқ P ҳарфи билан.'),

(9,4,'piyoda','oson','Mayda farzandni avtomobilda qanday tashish kerak?','Майда фарзандни автомобилда қандай ташиш керак?','Quchog''ida','Maxsus bolalar o''rindig''ida','Orqa o''rindiqda yotqizib','Old o''rindiqda','Қучоғида','Махсус болалар ўриндиғида','Орқа ўриндиқда ётқизиб','Олд ўриндиқда','B','12 yoshgacha bo''lgan bolalar maxsus bolalar o''rindig''ida tashilishi shart.','12 ёшгача бўлган болалар махсус болалар ўриндиғида ташилиши шарт.'),

(9,5,'signallar','orta','Yashil chiqib turgan strelka qanday signalga teng?','Яшил чиқиб турган стрелка қандай сигналга тенг?','Qo''shimcha ruxsat strelka yo''nalishida','Taqiq','Ogohlantirish','Avariya signali','Қўшимча рухсат стрелка йўналишида','Тақиқ','Огоҳлантириш','Авария сигнали','A','Qo''shimcha yashil strelka — strelka ko''rsatgan yo''nalishda harakatga ruxsat beriladi.','Қўшимча яшил стрелка — стрелка кўрсатган йўналишда ҳаракатга рухсат берилади.'),

-- BILET 10
(10,1,'kesishma','qiyin','Temir yo''l kesishmasida shlagbaum yopilayotgan paytda harakat?','Темир йўл кесишмасида шлагбаум ёпилаётган пайтда ҳаракат?','Tez o''tib ketish','To''xtash, kutish','Sekin o''tish','Avariya signali yoqish','Тез ўтиб кетиш','Тўхташ, кутиш','Секин ўтиш','Авария сигнали ёқиш','B','Shlagbaum yopilayotgan yoki yopiq bo''lganda kesishmaga kirish qat''iyan taqiqlanadi.','Шлагбаум ёпилаётган ёки ёпиқ бўлганда кесишмага кириш қатъиян тақиқланади.'),

(10,2,'belgilar','orta','Avtomobil o''tishi taqiqlangan belgi qanday ko''rinadi?','Автомобиль ўтиши тақиқланган белги қандай кўринади?','Yumaloq, qizil chegarali, avtomobil rasmi bilan','Uchburchak shaklida','Romb shaklida','To''rtburchak','Юмалоқ, қизил чегарали, автомобиль расми билан','Учбурчак шаклида','Ромб шаклида','Тўртбурчак','A','Taqiqlovchi belgi yumaloq, qizil chegarali bo''lib, ichida ko''rsatilgan transport turi taqiqlanadi.','Тақиқловчи белги юмалоқ, қизил чегарали бўлиб, ичида кўрсатилган транспорт тури тақиқланади.'),

(10,3,'tezlik','orta','Tirakador transport vositasi minimal tezlik?','Тиракадор транспорт воситаси минимал тезлик?','Cheklov yo''q','5 km/soat','10 km/soat','15 km/soat','Чеклов йўқ','5 км/соат','10 км/соат','15 км/соат','A','Yuk tirakador uchun maxsus minimal tezlik chegarasi yo''q, ammo umumiy harakat qoidalariga rioya qilinadi.','Юк тиракадор учун махсус минимал тезлик чегараси йўқ.'),

(10,4,'umumiy','oson','Yelkamasli ichkari qatorga o''tishda nimani ishlatish kerak?','Ёкамасли ичкари қаторга ўтишда нимани ишлатиш керак?','Bibilash','Avariya signali','Burilish ko''rsatkichi','Hech nima','Сигнал бериш','Авария сигнали','Бурилиш кўрсаткичи','Ҳеч нима','C','Har qanday yo''l o''zgarishida burilish ko''rsatkichi (povorotnik) yoqilishi shart.','Ҳар қандай йўл ўзгаришида бурилиш кўрсаткичи (поворотник) ёқилиши шарт.'),

(10,5,'parking','oson','Yo''l haydovchining tomonida qaysi tomonida transport qo''yiladi?','Йўл ҳайдовчининг томонида қайси томонида транспорт қўйилади?','Chap tomonda','O''ng tomonda','Har ikki tomonda','Yo''l o''rtasida','Чап томонда','Ўнг томонда','Ҳар икки томонда','Йўл ўртасида','B','O''ng tomonlama harakat mamlakatida transport o''ng tomonda qo''yiladi.','Ўнг томонлама ҳаракат мамлакатида транспорт ўнг томонда қўйилади.');

-- =============================================================
-- Eslatma: bu dastlabki 50 ta savol — 10 bilet uchun
-- Admin paneldan import yoki form orqali 4000+ savol qo''shiladi
-- =============================================================

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
-- Statistika
-- -------------------------------------------------------------
-- SELECT bilet_id, COUNT(*) AS savollar FROM test_savollar GROUP BY bilet_id;
-- SELECT mavzu, COUNT(*) AS soni FROM test_savollar GROUP BY mavzu ORDER BY soni DESC;
