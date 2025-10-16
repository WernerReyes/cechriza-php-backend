use cechriza_web;
-- ==========================================
-- LIMPIEZA DE TABLAS EXISTENTES (orden inverso)
-- ==========================================
DROP TABLE IF EXISTS section_items;
DROP TABLE IF EXISTS sections;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS links;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS users;
-- ==========================================
-- Tabla de Usuarios
-- ==========================================
CREATE TABLE users (
    id_user INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL,
    lastname VARCHAR(45) NOT NULL,
    email VARCHAR(45) NOT NULL,
    password VARCHAR(150) NOT NULL,
    role ENUM('USER') NOT NULL DEFAULT 'USER',
    profile VARCHAR(255) DEFAULT NULL,
    -- TODO: Add profile picture URL
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
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
-- Tabla de Páginas
-- ==========================================
CREATE TABLE pages (
    id_page INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    active TINYINT DEFAULT 1,
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
-- Tabla de Links
-- ==========================================
CREATE TABLE links (
    id_link INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('PAGE', 'EXTERNAL') NOT NULL,
    url VARCHAR(255) DEFAULT NULL,
    page_id INT DEFAULT NULL,
    title VARCHAR(100) NOT NULL,
    new_tab TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_links_pages FOREIGN KEY (page_id) REFERENCES pages(id_page)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO links (title, type, url, page_id)
VALUES ('nueva', 'PAGE', NULL, 1),
    ('nueva1', 'PAGE', NULL, 2),
    ('nueva2', 'PAGE', NULL, 3),
    ('nueva3', 'PAGE', NULL, 4),
    ('nueva4', 'PAGE', NULL, 5),
    ('nueva5', 'PAGE', NULL, 6),
    (
        'nueva6',
        'EXTERNAL',
        'https://www.kisan.com',
        NULL
    );
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
    CONSTRAINT fk_menu_links FOREIGN KEY (link_id) REFERENCES links(id_link)
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
-- ==========================================
-- Tabla de Categorías
-- ==========================================
CREATE TABLE categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO categories (title)
VALUES (
        'Clasificadoras de Billetes'
    ),
    (
        'Contadoras de Billetes'
    ),
    (
        'Sistemas de Depósito de Billetes'
    ),
    (
        'Recicladoras de Billetes'
    );

-- ==========================================
-- Tabla de Secciones
-- ==========================================
CREATE TABLE sections (
    id_section INT AUTO_INCREMENT PRIMARY KEY,
    order_num INT NOT NULL,
    type ENUM(
        'HERO',
        'WHY_US',
        'CASH_PROCESSING_EQUIPMENT',
        'OUR_COMPANY',
        'CONTACT_TOP_BAR',
        'MAIN_NAVIGATION_MENU',

        -- TODO: Add these fields later
        'CTA_BANNER',
        'SOLUTIONS_OVERVIEW',
        'MISSION_VISION',
        'CONTACT_US',
        -- TODO
        
        'BENEFIT',
        'MACHINE_TYPE',
        'BILL_MACHINE',
        'VALUE_PROPOSITION',
        'COIN_MACHINE',
        'CLIENT',
        'CONTACT',
        'FOOTER'
    ) NOT NULL,
    image VARCHAR(245),
    title VARCHAR(200),
    subtitle VARCHAR(200),
    description TEXT,
    text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
    active TINYINT DEFAULT 1,
    page_id INT NOT NULL, -- TODO: Remove this field later
    CONSTRAINT fk_sections_pages FOREIGN KEY (page_id) REFERENCES pages(id_page), -- TODO: Remove this constraint later
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
        -- page_id,
        link_id
    )
VALUES (
        1,
        'HERO',
        'Procesamiento de Billetes',
        'Soluciones Profesionales',
        'Ofrecemos máquinas de clasificación, conteo y reciclaje de billetes.',
        'Ver productos',
        -- 1,
        NULL
    );
-- Sección de categorías
INSERT INTO sections (order_num, type, title, subtitle, page_id)
VALUES (
        2,
        'MACHINE_TYPE',
        'Categorías de Productos',
        'Filtra por categoría',
        -- 1
    );
-- Sección de productos
INSERT INTO sections (order_num, type, title, subtitle, page_id)
VALUES (
        3,
        'BILL_MACHINE',
        'Nuestros Productos',
        'Clasificadoras y contadoras disponibles',
        -- 1
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
        -- 6
    );
-- ==========================================
-- Tabla de Section Items
-- ==========================================
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
    order_num INT,

    -- TODO: Add these fields later
    input_type ENUM('TEXT', 'EMAIL', 'TEXTAREA') DEFAULT NULL,
    -- TODO --

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
-- ==========================================
-- Tabla de section_menus
-- ==========================================
CREATE TABLE section_menus (
    id_section INT NOT NULL,
    id_menu INT NOT NULL,
    PRIMARY KEY (id_section, id_menu),
    FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;



-- TODO: Create this table later if needed
-- ==========================================
-- Tabla de page_sections
-- ==========================================
CREATE TABLE page_sections (
    id_page INT NOT NULL,
    id_section INT NOT NULL,
    order_num INT DEFAULT 1,
    active TINYINT DEFAULT 1,
    PRIMARY KEY (id_page, id_section),
    FOREIGN KEY (id_page) REFERENCES pages(id_page) ON DELETE CASCADE,
    FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- CONSULTA FINAL
-- ==========================================
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