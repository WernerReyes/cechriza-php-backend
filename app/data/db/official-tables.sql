use cechriza_web_v2;


-- ==========================================
-- LIMPIEZA DE TABLAS EXISTENTES (orden inverso)
-- ==========================================
DROP TABLE IF EXISTS section_machines;
DROP TABLE IF EXISTS section_pages;
DROP TABLE IF EXISTS section_menus;
DROP TABLE IF EXISTS section_items;
DROP TABLE IF EXISTS sections;
DROP TABLE IF EXISTS machines;
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
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- ==========================================
-- Tabla de Páginas
-- ==========================================
CREATE TABLE pages (
    id_page INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    active TINYINT DEFAULT 1,
    is_main TINYINT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- ==========================================
-- Tabla de Links
-- ==========================================
CREATE TABLE links (
    id_link INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('PAGE', 'EXTERNAL', 'FILE') NOT NULL,
    url VARCHAR(255) DEFAULT NULL,
    page_id INT DEFAULT NULL,
    title VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    new_tab TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_links_pages FOREIGN KEY (page_id) REFERENCES pages(id_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




-- ==========================================
-- Tabla de Menú (estructura jerárquica)
-- ==========================================
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL,
    link_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu(id_menu),
    CONSTRAINT fk_menu_links FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- Tabla de Categorías
-- ==========================================
CREATE TABLE categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    type enum('BILL', 'COIN') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT uk_categories_type_title unique (type, title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




-- ==========================================
-- Tabla de Secciones
-- ==========================================
CREATE TABLE sections (
    id_section INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM(
        'HERO',
        'WHY_US',
        'CASH_PROCESSING_EQUIPMENT',
        'OUR_COMPANY',
        'CONTACT_TOP_BAR',
        'MAIN_NAVIGATION_MENU',
        'CTA_BANNER',
        'MACHINE',
        'ADVANTAGES',
        'SOLUTIONS_OVERVIEW',
        'SUPPORT_MAINTENANCE',
        'MISSION_VISION',
        'CONTACT_US',
        'CLIENT',
        'FOOTER',
        'VALUE_PROPOSITION',
        'OPERATIONAL_BENEFITS',
        'MACHINE_DETAILS',
        'MACHINES_CATALOG',
        'FULL_MAINTENANCE_PLAN',
        'PREVENTIVE_CORRECTIVE_MAINTENANCE',
        'SUPPORT_WIDGET'
    ) NOT NULL,
    image VARCHAR(245),
    video VARCHAR(245),
    title VARCHAR(200),
    subtitle VARCHAR(200),
    description TEXT,
    icon_url VARCHAR(200),
    icon_type  ENUM('IMAGE', 'LIBRARY') DEFAULT NULL,
    icon JSON DEFAULT NULL,
    text_button VARCHAR(100),
    extra_text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
    extra_link_id INT DEFAULT NULL,
    additional_info_list JSON,
    CONSTRAINT fk_sections_link FOREIGN KEY (link_id) REFERENCES links(id_link),
    CONSTRAINT kk_sections_extra_link FOREIGN KEY (extra_link_id) REFERENCES links(id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    icon_url VARCHAR(200),
    icon_type  ENUM('IMAGE', 'LIBRARY') DEFAULT NULL,
    icon JSON DEFAULT NULL,
    text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
	input_type ENUM('TEXT', 'EMAIL', 'TEXTAREA') DEFAULT NULL,
	additional_info_list JSON,
    section_id INT NOT NULL,
    CONSTRAINT fk_section_items_sections FOREIGN KEY (section_id) REFERENCES sections(id_section),
    CONSTRAINT fk_section_items_link FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- Tabla de machines
-- ==========================================
CREATE TABLE machines (
    id_machine INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255),
    long_description TEXT,
    images JSON,
    technical_specifications JSON, -- Para especificaciones técnicas flexibles
    category_id INT NOT NULL, -- Relación con categoría
    manual TEXT NULL,
    link_id INT DEFAULT NULL,
    text_button VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_machines_category FOREIGN KEY (category_id)
        REFERENCES categories(id_category),
	CONSTRAINT fk_machines_link FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- ==========================================
-- Tabla de section_menus
-- ==========================================
CREATE TABLE section_menus (
    id_section INT NOT NULL,
    id_menu INT NOT NULL,
        order_num INT NULL default 1,
    PRIMARY KEY (id_section, id_menu),
    CONSTRAINT fk_section_menus_section FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE,
    CONSTRAINT fk_section_menus_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);


-- ==========================================
-- Tabla de page_sections
-- ==========================================
CREATE TABLE section_pages (
    id_page INT NOT NULL,
    id_section INT NOT NULL,
    order_num INT DEFAULT 1,
    active TINYINT DEFAULT 1,
    type ENUM('LAYOUT', 'CUSTOM') DEFAULT 'CUSTOM',
    PRIMARY KEY (id_page, id_section),
    CONSTRAINT fk_section_pages_page FOREIGN KEY (id_page) REFERENCES pages(id_page),
    CONSTRAINT fk_section_pages_section FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE
);


-- ==========================================
-- Tabla de section_machines
-- ==========================================
CREATE TABLE section_machines (
     id_section INT NOT NULL,
    id_machine INT NOT NULL,
    order_num INT DEFAULT 1,
    PRIMARY KEY (id_section, id_machine),
    CONSTRAINT fk_section_machines_section FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE,
    CONSTRAINT fk_section_machines_machine FOREIGN KEY (id_machine) REFERENCES machines(id_machine)
);
