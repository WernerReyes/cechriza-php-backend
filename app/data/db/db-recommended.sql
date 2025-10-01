DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  id_menu INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  `order` INT NOT NULL,
  active TINYINT DEFAULT '1',
  parent_id INT DEFAULT NULL,
  users_id INT NOT NULL,
  PRIMARY KEY (id_menu),
  UNIQUE KEY unique_order_per_parent (parent_id, `order`),
  KEY fk_menu_menu_idx (parent_id),
  KEY fk_menu_users1_idx (users_id),
  KEY fk_menu_links_idx (link_id),
  CONSTRAINT fk_menu_menu FOREIGN KEY (parent_id) REFERENCES menu (id_menu),
  CONSTRAINT fk_menu_users1 FOREIGN KEY (users_id) REFERENCES users (id_user),
  CONSTRAINT fk_menu_links FOREIGN KEY (link_id) REFERENCES links (id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE links (
  id_link INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('EXTERNAL', 'PAGE') NOT NULL,
  external_url TEXT DEFAULT NULL,
  page_id INT DEFAULT NULL,
  CONSTRAINT fk_links_menu FOREIGN KEY (menu_id) REFERENCES menu(id_menu),
  CONSTRAINT fk_links_page FOREIGN KEY (page_id) REFERENCES pages(id_pages)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ==========================================
-- Tabla de Páginas (contenido interno)
-- ==========================================
CREATE TABLE pages (
  id_page INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  active TINYINT DEFAULT 1,
  slug VARCHAR(80) NOT NULL, 
  link_id INT DEFAULT NULL, 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- Tabla de Links (intermedio entre menú y destino)
-- ==========================================
CREATE TABLE links (
  id_link INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('PAGE','EXTERNAL') NOT NULL,
  url VARCHAR(255) DEFAULT NULL,         -- solo se usa si es EXTERNAL
  page_id INT DEFAULT NULL,              -- solo se usa si es PAGE
  CONSTRAINT fk_links_pages FOREIGN KEY (page_id) REFERENCES pages(id_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- Tabla de Menú (estructura jerárquica)
-- ==========================================
CREATE TABLE menu (
  id_menu INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  order_num INT NOT NULL,
  active TINYINT DEFAULT 1,
  parent_id INT DEFAULT NULL,
  link_id INT NOT NULL,
  CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu(id_menu),
  CONSTRAINT fk_menu_links FOREIGN KEY (link_id) REFERENCES links(id_link),
  UNIQUE KEY unique_order_per_parent (parent_id, order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE sections (
  id_section INT AUTO_INCREMENT PRIMARY KEY,
  order_num INT NOT NULL,
  type ENUM('HERO','BENEFIT','MACHINE_TYPE','BILL_MACHINE','VALUE_PROPOSITION','COIN_MACHINE','CLIENT','CONTACT','FOOTER') NOT NULL,
  title VARCHAR(100),
  subtitle VARCHAR(200),
  description TEXT,
  text_button VARCHAR(100),
  link_id INT DEFAULT NULL,  -- botón que apunta a link
  active TINYINT DEFAULT 1,
  page_id INT NOT NULL,
  CONSTRAINT fk_sections_pages FOREIGN KEY (page_id) REFERENCES pages(id_page),
  CONSTRAINT fk_sections_link FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `function_machine`;
CREATE TABLE `function_machine` (
  `id_function_machine` int NOT NULL AUTO_INCREMENT,
  `type` enum('BILL','COIN"') NOT NULL,
  `title` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sections_id` int NOT NULL,
  PRIMARY KEY (`id_function_machine`),
  KEY `fk_function_machine_sections1_idx` (`sections_id`),
  CONSTRAINT `fk_function_machine_sections1` FOREIGN KEY (`sections_id`) REFERENCES `sections` (`id_section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
--
-- Dumping data for table `function_machine`
--


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
  link_id INT DEFAULT NULL,  -- cada item puede apuntar a un link
  order_num INT,
  section_id INT NOT NULL,
  CONSTRAINT fk_section_items_sections FOREIGN KEY (section_id) REFERENCES sections(id_section),
  CONSTRAINT fk_section_items_link FOREIGN KEY (link_id) REFERENCES links(id_link)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE categories (
  id_category INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT
);



CREATE TABLE machine_types (
  id_machine_type INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,         -- Ej: Clasificadoras de Billetes
  description TEXT DEFAULT NULL,       -- Opcional
  icon VARCHAR(50) DEFAULT NULL,
  type ENUM('BILL','COIN') NOT NULL    -- Si necesitas clasificar
);


CREATE TABLE products (
  id_product INT AUTO_INCREMENT PRIMARY KEY,
  machine_type_id INT NOT NULL,      -- relación con tipo
  title VARCHAR(100) NOT NULL,       -- Ej: Kisan Newton 30
  subtitle VARCHAR(200) DEFAULT NULL,
  short_desc TEXT,                   -- Descripción corta (para listado)
  image VARCHAR(245) DEFAULT NULL,
  section_id INT NULL,              -- Página detalle
  `order` INT DEFAULT 1,
  FOREIGN KEY (machine_type_id) REFERENCES machine_types(id_machine_type),
  FOREIGN KEY (section_id) REFERENCES pages(id_page)
);
