-- drop database cechriza_web_v3;
-- create database cechriza_web_v3;
use cechriza_web_v2;
-- ==========================================
-- LIMPIEZA DE TABLAS EXISTENTES (orden inverso)
-- ==========================================
DROP TABLE IF EXISTS section_pages;
DROP TABLE IF EXISTS section_menus;
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
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
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
-- ==========================================
-- Tabla de Categorías
-- ==========================================
CREATE TABLE categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('COIN', 'BILL') NOT NULL, -- TODO: Add type
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uk_categories_type_title unique (type, title) -- TODO: Add this constraint
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
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
        'SOLUTIONS_OVERVIEW',
        'ADVANTAGES',
        'MACHINE',
        'SUPPORT_MAINTENANCE',
        'MISSION_VISION',
        'CONTACT_US',
        'CLIENT',
        'FOOTER',
        'VALUE_PROPOSITION'
    ) NOT NULL,
    image VARCHAR(245),
    title VARCHAR(200),
    subtitle VARCHAR(200),
    description TEXT,
    text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
    CONSTRAINT fk_sections_link FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
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
    icon_url VARCHAR(100), -- TODO: Update the name to icon_url
    -- TODO: Add icon_type and icon fields
    icon_type  ENUM('IMAGE', 'LIBRARY') DEFAULT NULL,
    icon JSON DEFAULT NULL,
    -- TODO: End
    text_button VARCHAR(100),
    link_id INT DEFAULT NULL,
    input_type ENUM('TEXT', 'EMAIL', 'TEXTAREA') DEFAULT NULL,
    order_num INT,
    section_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    CONSTRAINT fk_section_items_sections FOREIGN KEY (section_id) REFERENCES sections(id_section),
    CONSTRAINT fk_section_items_link FOREIGN KEY (link_id) REFERENCES links(id_link),
    CONSTRAINT fk_section_items_category FOREIGN KEY (category_id) REFERENCES categories(id_category)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ==========================================
-- Tabla de section_menus
-- ==========================================
CREATE TABLE section_menus (
    id_section INT NOT NULL,
    id_menu INT NOT NULL,
    PRIMARY KEY (id_section, id_menu),
    FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE
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
    FOREIGN KEY (id_page) REFERENCES pages(id_page) ON DELETE CASCADE,
    FOREIGN KEY (id_section) REFERENCES sections(id_section) ON DELETE CASCADE
)

 -- TODO: ADD THESE TABLES FOR MACHINES AND IMAGESENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Tabla de máquinas
CREATE TABLE machines (
    id_machine INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255),
    long_description TEXT,
    images JSON,
    -- Max 5 imágenes por máquina
    technical_specifications JSON,
    -- Para especificaciones técnicas flexibles
    category_id INT NOT NULL,
    manual TEXT NULL,
    -- Relación con categoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_machines_category FOREIGN KEY (category_id) REFERENCES categories(id_category) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;