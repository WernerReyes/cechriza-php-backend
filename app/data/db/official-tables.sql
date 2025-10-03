drop database cechriza_web_v2;
create database cechriza_web_v2;
use cechriza_web_v2;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id_user` int NOT NULL AUTO_INCREMENT,
    `name` varchar(45) NOT NULL,
    `lastname` varchar(45) NOT NULL,
    `email` varchar(45) NOT NULL,
    `password` varchar(150) NOT NULL,
    `role` enum('USER') NOT NULL DEFAULT 'USER',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- TODO ADD THIS PROPERTY
    PRIMARY KEY (`id_user`)
) ENGINE = InnoDB AUTO_INCREMENT = 18 DEFAULT CHARSET = utf8mb3;
INSERT INTO users (name, lastname, email, password, role)
VALUES (
        'Werner',
        'Reyes',
        'werner@example.com',
        '123456',
        'USER'
    ),
    (
        'Ana',
        'López',
        'ana@example.com',
        '123456',
        'USER'
    );
-- ==========================================
-- Tabla de Páginas (contenido interno)
-- ==========================================
DROP TABLE IF EXISTS pages;
CREATE TABLE pages (
    id_page INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    active TINYINT DEFAULT 1,
    link_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO pages (title, slug, description)
VALUES (
        'Procesamiento de Billetes',
        'procesamiento_billete',
        'Soluciones para clasificación, conteo y depósito de billetes.'
    ),
    (
        'Procesamiento de Monedas',
        'procesamiento_moneda',
        'Soluciones para conteo y clasificación de monedas.'
    ),
    (
        'Kisan NEWTON 30',
        'kisan-newton-30',
        'Clasificadora de billetes de alta velocidad y tamaño compacto.'
    ),
    (
        'Kisan K5-A',
        'kisan-k5a',
        'Clasificadora de billetes de 5 bolsillos de alta velocidad.'
    ),
    (
        'Kisan K6',
        'kisan-k6',
        'Clasificadora modular de alto volumen.'
    ),
    (
        'Contacto',
        'contacto',
        'Página de contacto para consultas.'
    );
-- ==========================================
-- Tabla de Links (intermedio entre menú y destino)
-- ==========================================
CREATE TABLE links (
    id_link INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('PAGE', 'EXTERNAL') NOT NULL,
    url VARCHAR(255) DEFAULT NULL,
    -- solo se usa si es EXTERNAL
    page_id INT DEFAULT NULL,
    -- solo se usa si es PAGE
    --- TODO: Add these fields later
    title VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_links_pages FOREIGN KEY (page_id) REFERENCES pages(id_page)
    -- TODO
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO links (type, url, page_id)
VALUES ('PAGE', NULL, 1),
    -- Procesamiento Billetes
    ('PAGE', NULL, 2),
    -- Procesamiento Monedas
    ('PAGE', NULL, 3),
    -- Newton 30
    ('PAGE', NULL, 4),
    -- K5-A
    ('PAGE', NULL, 5),
    -- K6
    ('PAGE', NULL, 6),
    -- Contacto
    ('EXTERNAL', 'https://www.kisan.com', NULL);
-- link externo
-- ==========================================
-- Tabla de Menú (estructura jerárquica)
-- ==========================================
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    order_num INT NOT NULL,
    active TINYINT DEFAULT 1,
    parent_id INT DEFAULT NULL,
    link_id INT NULL,
    CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu(id_menu),
    CONSTRAINT fk_menu_links FOREIGN KEY (link_id) REFERENCES links(id_link),
    UNIQUE KEY unique_order_per_parent (parent_id, order_num)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Menú principal
INSERT INTO menu (title, order_num, active, parent_id, link_id)
VALUES ('Inicio', 1, 1, NULL, 1),
    ('Monedas', 2, 1, NULL, 2),
    ('Productos', 3, 1, NULL, NULL),
    ('Contacto', 4, 1, NULL, 6);
-- Submenús bajo "Productos"
INSERT INTO menu (title, order_num, active, parent_id, link_id)
VALUES ('Kisan NEWTON 30', 1, 1, 3, 3),
    ('Kisan K5-A', 2, 1, 3, 4),
    ('Kisan K6', 3, 1, 3, 5);
CREATE TABLE sections (
    id_section INT AUTO_INCREMENT PRIMARY KEY,
    order_num INT NOT NULL,
    type ENUM(
        'HERO',
        'BENEFIT',
        'MACHINE_TYPE',
        'BILL_MACHINE',
        'VALUE_PROPOSITION',
        'COIN_MACHINE',
        'CLIENT',
        'CONTACT',
        'FOOTER'
    ) NOT NULL,
    title VARCHAR(100),
    subtitle VARCHAR(200),
    description TEXT,
    text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
    -- botón que apunta a link
    active TINYINT DEFAULT 1,
    page_id INT NOT NULL,
    CONSTRAINT fk_sections_pages FOREIGN KEY (page_id) REFERENCES pages(id_page),
    CONSTRAINT fk_sections_link FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- HERO en Procesamiento de Billetes
INSERT INTO sections (
        order_num,
        type,
        title,
        subtitle,
        description,
        text_button,
        page_id,
        link_id
    )
VALUES (
        1,
        'HERO',
        'Procesamiento de Billetes',
        'Soluciones Profesionales',
        'Ofrecemos máquinas de clasificación, conteo y reciclaje de billetes.',
        'Ver productos',
        1,
        NULL
    );
-- Sección de categorías
INSERT INTO sections (order_num, type, title, subtitle, page_id)
VALUES (
        2,
        'MACHINE_TYPE',
        'Categorías de Productos',
        'Filtra por categoría',
        1
    );
-- Sección de productos
INSERT INTO sections (order_num, type, title, subtitle, page_id)
VALUES (
        3,
        'BILL_MACHINE',
        'Nuestros Productos',
        'Clasificadoras y contadoras disponibles',
        1
    );
-- Contacto
INSERT INTO sections (
        order_num,
        type,
        title,
        subtitle,
        description,
        page_id
    )
VALUES (
        1,
        'CONTACT',
        'Contáctanos',
        'Estamos aquí para ayudarte',
        'Rellena el formulario y nos pondremos en contacto contigo.',
        6
    );
CREATE TABLE categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    -- TODO: Add these fields later
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO categories (title, slug, description)
VALUES (
        'Clasificadoras de Billetes',
        'clasificadoras',
        'Máquinas para clasificar billetes por denominación y estado.'
    ),
    (
        'Contadoras de Billetes',
        'contadoras',
        'Dispositivos para conteo rápido de billetes.'
    ),
    (
        'Sistemas de Depósito de Billetes',
        'deposito',
        'Soluciones para depositar billetes de forma segura.'
    ),
    (
        'Recicladoras de Billetes',
        'recicladoras',
        'Equipos que permiten reutilizar billetes en circulación.'
    );
--
-- Table structure for table `section_items`
--
CREATE TABLE section_items (
    id_section_item INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    subtitle VARCHAR(200),
    description TEXT,
    image VARCHAR(245),
    background_image VARCHAR(100),
    icon VARCHAR(100),
    text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
    -- cada item puede apuntar a un link
    order_num INT,
    section_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    CONSTRAINT fk_section_items_sections FOREIGN KEY (section_id) REFERENCES sections(id_section),
    CONSTRAINT fk_section_items_link FOREIGN KEY (link_id) REFERENCES links(id_link),
    CONSTRAINT fk_section_items_category FOREIGN KEY (category_id) REFERENCES categories(id_category)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Items de categorías en la sección 2
INSERT INTO section_items (
        title,
        description,
        icon,
        order_num,
        section_id,
        category_id
    )
VALUES (
        'Clasificadoras de Billetes',
        'Máquinas para clasificar billetes de forma precisa.',
        'icon-clasificadora.png',
        1,
        2,
        1
    ),
    (
        'Contadoras de Billetes',
        'Equipos para conteo rápido y seguro.',
        'icon-contadora.png',
        2,
        2,
        2
    ),
    (
        'Sistemas de Depósito',
        'Soluciones seguras de depósito.',
        'icon-deposito.png',
        3,
        2,
        3
    ),
    (
        'Recicladoras de Billetes',
        'Tecnología para reutilizar billetes.',
        'icon-recicladora.png',
        4,
        2,
        4
    );
-- Items de productos en la sección 3
INSERT INTO section_items (
        title,
        subtitle,
        description,
        image,
        text_button,
        link_id,
        order_num,
        section_id,
        category_id
    )
VALUES (
        'Kisan NEWTON 30',
        'Clasificadora compacta',
        'Clasificadora de billetes de alta velocidad y tamaño compacto.',
        'newton30.jpg',
        'Ver detalle',
        3,
        1,
        3,
        1
    ),
    (
        'Kisan K5-A',
        '5 bolsillos',
        'Clasificadora de billetes de alta velocidad con 5 bolsillos.',
        'k5a.jpg',
        'Ver detalle',
        4,
        2,
        3,
        1
    ),
    (
        'Kisan K6',
        'Modular',
        'Clasificadora modular de alto volumen.',
        'k6.jpg',
        'Ver detalle',
        5,
        3,
        3,
        1
    );
SELECT p.title AS page,
    s.title AS section,
    si.title AS item,
    si.subtitle,
    si.description,
    l.page_id
FROM pages p
    JOIN sections s ON s.page_id = p.id_page
    LEFT JOIN section_items si ON si.section_id = s.id_section
    LEFT JOIN links l ON si.link_id = l.id_link
WHERE p.slug = 'procesamiento_billete'
ORDER BY s.order_num,
    si.order_num;