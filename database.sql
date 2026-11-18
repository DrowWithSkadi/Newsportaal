-- =====================================================================
--  Lilia ilusalong — andmebaas
--  Ilusalongi veebisaidi andmebaas: teenused, kategooriad, meistrid,
--  broneeringud, arvustused ja kasutajad. Sisu on kahes keeles (ET/RU).
-- =====================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS ilusalong;
CREATE DATABASE IF NOT EXISTS ilusalong CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ilusalong;

-- ---------------------------------------------------------------------
--  Kasutajad (administraatorid ja registreeritud kliendid)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    username          VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    status            ENUM('admin','user') NOT NULL DEFAULT 'user',
    registration_date DATE NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Sisselogimise katsed (kaitse parooli ära arvamise vastu):
--  5 ebaõnnestunud katset 15 minuti jooksul -> konto lukustub 15 minutiks
-- ---------------------------------------------------------------------
CREATE TABLE login_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(150) NOT NULL,
    ip           VARCHAR(45)  NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempts_email (email, attempted_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Teenuste kategooriad
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name_et    VARCHAR(100) NOT NULL,
    name_ru    VARCHAR(100) NOT NULL,
    icon       VARCHAR(30)  NOT NULL DEFAULT 'sparkle',
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Teenused (hind eurodes, kestus minutites)
-- ---------------------------------------------------------------------
CREATE TABLE services (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    category_id    INT NOT NULL,
    title_et       VARCHAR(150) NOT NULL,
    title_ru       VARCHAR(150) NOT NULL,
    description_et TEXT NOT NULL,
    description_ru TEXT NOT NULL,
    price          DECIMAL(6,2) NOT NULL,
    duration_min   INT NOT NULL,
    picture        VARCHAR(255) NULL,
    is_popular     TINYINT(1) NOT NULL DEFAULT 0,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_services_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Meistrid (salongi töötajad)
-- ---------------------------------------------------------------------
CREATE TABLE masters (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    bio_et      TEXT NOT NULL,
    bio_ru      TEXT NOT NULL,
    experience  INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_masters_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Broneeringud (online-registreerimine teenusele)
-- ---------------------------------------------------------------------
CREATE TABLE bookings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NULL,
    service_id   INT NOT NULL,
    master_id    INT NOT NULL,
    client_name  VARCHAR(100) NOT NULL,
    phone        VARCHAR(30)  NOT NULL,
    email        VARCHAR(150) NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    note         VARCHAR(500) NULL,
    status       ENUM('new','confirmed','cancelled') NOT NULL DEFAULT 'new',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- 1 kehtiva broneeringu puhul, NULL tühistatu puhul: tühistatud aega saab uuesti broneerida
    slot_active  TINYINT AS (IF(status = 'cancelled', NULL, 1)) STORED,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_master FOREIGN KEY (master_id)
        REFERENCES masters(id) ON DELETE CASCADE,
    UNIQUE KEY uq_master_slot (master_id, booking_date, booking_time, slot_active)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Arvustused teenuste kohta (hinne 1–5)
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    user_id    INT NULL,
    author     VARCHAR(100) NOT NULL,
    rating     TINYINT NOT NULL,
    text       VARCHAR(1000) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL,
    -- üks kasutaja saab teenusele jätta ühe arvustuse
    UNIQUE KEY uq_review_user (service_id, user_id),
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- =====================================================================
--  Testandmed
-- =====================================================================

-- Administraator: admin@lilia.ee / admin123
-- Testklient:      mari@example.com / mari123
INSERT INTO users (username, email, password, status, registration_date) VALUES
('Administraator', 'admin@lilia.ee', '$2y$10$Ed0C/TqKM9cDWYOBHmU2D.OnZUiH4.O7bmd4JoAkoOEgxF6zwxoXa', 'admin', '2026-11-16'),
('Mari Kask', 'mari@example.com', '$2y$10$h5okAd.hDkEaL5u/Cq3TsOvyzEpcK2LYXDBgAnhIOXTmPuXk2dTwi', 'user', '2026-11-18');

INSERT INTO categories (id, name_et, name_ru, icon, sort_order) VALUES
(1, 'Juuksur',               'Парикмахерские услуги', 'scissors', 1),
(2, 'Maniküür ja pediküür',  'Маникюр и педикюр',     'hand',     2),
(3, 'Näohooldus',            'Уход за лицом',         'leaf',     3),
(4, 'Meik ja kulmud',        'Макияж и брови',        'brush',    4),
(5, 'Massaaž',               'Массаж',                'lotus',    5);

INSERT INTO services (category_id, title_et, title_ru, description_et, description_ru, price, duration_min, is_popular) VALUES
(1, 'Naiste juukselõikus', 'Женская стрижка',
 'Konsultatsioon, juuste pesu, lõikus ja kuivatus. Meister aitab valida soengu, mis sobib teie näokuju ja juuste tüübiga.',
 'Консультация, мытьё головы, стрижка и укладка феном. Мастер поможет подобрать форму, которая подойдёт к типу лица и волос.',
 35.00, 60, 1),
(1, 'Meeste juukselõikus', 'Мужская стрижка',
 'Klassikaline või moodne meeste lõikus masina ja kääridega, sh kontuuride viimistlus.',
 'Классическая или современная мужская стрижка машинкой и ножницами, включая окантовку.',
 20.00, 30, 0),
(1, 'Juuste värvimine', 'Окрашивание волос',
 'Ühtlane värvimine professionaalsete värvidega. Hinnas on hooldav maskiga pesu ja soeng.',
 'Однотонное окрашивание профессиональными красителями. В стоимость входит мытьё с ухаживающей маской и укладка.',
 70.00, 120, 1),
(1, 'Õhtusoeng', 'Вечерняя причёска',
 'Pidulik soeng lõpupeoks, pulmaks või muuks eriliseks sündmuseks.',
 'Праздничная причёска на выпускной, свадьбу или другое особенное событие.',
 45.00, 60, 0),
(2, 'Klassikaline maniküür', 'Классический маникюр',
 'Küünte kujundamine, küünenahkade hooldus ja kätekreem. Sobib neile, kes eelistavad naturaalset välimust.',
 'Придание формы ногтям, обработка кутикулы и крем для рук. Подходит тем, кто любит натуральный вид.',
 25.00, 45, 0),
(2, 'Geellakkimine', 'Покрытие гель-лаком',
 'Maniküür ja püsiv geellakk, mis püsib kuni kolm nädalat. Suur värvivalik.',
 'Маникюр и стойкое покрытие гель-лаком, которое держится до трёх недель. Большой выбор цветов.',
 35.00, 75, 1),
(2, 'Pediküür', 'Педикюр',
 'Jalavann, küünte ja kanna hooldus ning soovi korral lakkimine.',
 'Ванночка для ног, обработка ногтей и пяток, по желанию покрытие лаком.',
 40.00, 60, 0),
(3, 'Näopuhastus', 'Чистка лица',
 'Sügavpuhastus, koorimine ja rahustav mask. Sobib kõikidele nahatüüpidele.',
 'Глубокое очищение, пилинг и успокаивающая маска. Подходит для всех типов кожи.',
 50.00, 75, 1),
(3, 'Niisutav näohooldus', 'Увлажняющий уход за лицом',
 'Hooldus hüaluroonhappega kuivale ja väsinud nahale, sh näomassaaž.',
 'Уход с гиалуроновой кислотой для сухой и уставшей кожи, включая массаж лица.',
 45.00, 60, 0),
(4, 'Päevameik', 'Дневной макияж',
 'Kerge ja loomulik meik igapäevaseks või ärikohtumiseks.',
 'Лёгкий естественный макияж на каждый день или для деловой встречи.',
 30.00, 45, 0),
(4, 'Kulmude korrigeerimine ja värvimine', 'Коррекция и окрашивание бровей',
 'Kulmude kuju modelleerimine ja värvimine püsiva värviga.',
 'Моделирование формы бровей и окрашивание стойкой краской.',
 18.00, 30, 1),
(4, 'Pruutmeik', 'Свадебный макияж',
 'Vastupidav pidulik meik pulmapäevaks. Hinnas on proovimeik.',
 'Стойкий праздничный макияж на день свадьбы. В стоимость входит пробный макияж.',
 70.00, 90, 0),
(5, 'Klassikaline seljamassaaž', 'Классический массаж спины',
 'Leevendab pingeid seljas ja kaelas, sobib istuva töö tegijatele.',
 'Снимает напряжение в спине и шее, подходит тем, у кого сидячая работа.',
 35.00, 45, 0),
(5, 'Lõõgastav aroomimassaaž', 'Расслабляющий аромамассаж',
 'Kogu keha massaaž eeterlike õlidega sügavaks lõõgastumiseks.',
 'Массаж всего тела с эфирными маслами для глубокого расслабления.',
 55.00, 60, 1);

INSERT INTO masters (name, category_id, bio_et, bio_ru, experience) VALUES
('Kristiina Tamm', 1,
 'Juuksur, kes armastab naturaalseid toone ja lihtsalt hooldatavaid soenguid.',
 'Парикмахер, который любит натуральные оттенки и стрижки, не требующие сложной укладки.', 8),
('Olga Ivanova', 2,
 'Maniküüri- ja pediküürimeister, kelle tööd on alati täpsed ja korralikud.',
 'Мастер маникюра и педикюра, известный аккуратной и точной работой.', 6),
('Maria Kask', 3,
 'Kosmeetik, kes valib hoolduse iga kliendi nahatüübi järgi.',
 'Косметолог, подбирающий уход под тип кожи каждого клиента.', 10),
('Anastassia Petrova', 4,
 'Meigikunstnik ja kulmumeister, teeb nii päeva- kui ka pruutmeiki.',
 'Визажист и мастер бровей, делает как дневной, так и свадебный макияж.', 5),
('Mart Saar', 5,
 'Sertifitseeritud massöör, spetsialiseerunud klassikalisele ja lõõgastavale massaažile.',
 'Сертифицированный массажист, специализируется на классическом и расслабляющем массаже.', 7);

-- Kuupäevad arvutatakse andmebaasi loomise päeva järgi, et broneeringud oleksid alati
-- tulevikus; pühapäev (DAYOFWEEK = 1) nihutatakse esmaspäevale.
INSERT INTO bookings (user_id, service_id, master_id, client_name, phone, email, booking_date, booking_time, note, status) VALUES
(NULL, 1, 1, 'Liis Mets',       '+372 5123 4567', 'liis@example.com',
    CURDATE() + INTERVAL 2 DAY + INTERVAL (DAYOFWEEK(CURDATE() + INTERVAL 2 DAY) = 1) DAY, '10:00:00', NULL, 'confirmed'),
(NULL, 6, 2, 'Jelena Smirnova', '+372 5234 5678', NULL,
    CURDATE() + INTERVAL 2 DAY + INTERVAL (DAYOFWEEK(CURDATE() + INTERVAL 2 DAY) = 1) DAY, '12:30:00', 'Pastelsed toonid', 'new'),
(NULL, 8, 3, 'Kadri Lepp',      '+372 5345 6789', 'kadri@example.com',
    CURDATE() + INTERVAL 3 DAY + INTERVAL (DAYOFWEEK(CURDATE() + INTERVAL 3 DAY) = 1) DAY, '14:00:00', NULL, 'new'),
(2, 6, 2, 'Mari Kask',          '+372 5456 7890', 'mari@example.com',
    CURDATE() + INTERVAL 4 DAY + INTERVAL (DAYOFWEEK(CURDATE() + INTERVAL 4 DAY) = 1) DAY, '11:00:00', NULL, 'confirmed'),
(2, 1, 1, 'Mari Kask',          '+372 5456 7890', 'mari@example.com',
    CURDATE() - INTERVAL 10 DAY, '13:00:00', NULL, 'confirmed');

INSERT INTO reviews (service_id, user_id, author, rating, text) VALUES
(1, 2,    'Mari Kask', 5, 'Kristiina tegi täpselt sellise lõikuse, nagu soovisin. Tulen kindlasti tagasi!'),
(1, NULL, 'Liis',    5, 'Väga meeldiv meister ja täpselt selline lõikus, nagu soovisin!'),
(1, NULL, 'Анна',    5, 'Отличная стрижка, мастер всё объяснила и подсказала уход.'),
(6, NULL, 'Jelena',  4, 'Покрытие держится уже две недели, всё аккуратно.'),
(8, NULL, 'Kadri',   5, 'Nahk on pärast hooldust palju puhtam ja rahulikum.'),
(14, NULL, 'Marina',  5, 'Лучший массаж в городе, очень расслабляющая атмосфера.'),
(11, NULL, 'Triin',   4, 'Kulmud said ilusa kuju, tulen kindlasti tagasi.');
