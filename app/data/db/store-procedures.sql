
--
-- Dumping routines for database 'cechriza_web'
--

--
-- MENU
--
-- TODO: New field page_id in InsertMenu procedure, it's necessary to update this procedure
DELIMITER ;;
CREATE PROCEDURE `InsertMenu`(IN m_title VARCHAR(100), IN m_slug VARCHAR(80), IN m_order INT, IN m_id_user INT, IN m_url TEXT, IN m_parent_id INT, IN m_page_id INT)
BEGIN
	DECLARE menu_id INT;
    
    INSERT INTO menu (title, slug, `order`, users_id, url, parent_id) VALUES (m_title, m_slug, m_order, m_id_user, m_url, m_parent_id);
    SET menu_id = LAST_INSERT_ID();
    
    IF m_page_id IS NOT NULL THEN
		UPDATE pages set pages.menu_id = menu_id where id_page = m_page_id;
    END IF;
    
    SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active
    FROM menu
    WHERE id_menu = menu_id;
END;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `DeleteMenu`(IN m_menu_id INT)
BEGIN
    UPDATE menu SET active = 0 WHERE  id_menu = m_menu_id;
END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `GetAllMenusOrdered`()
BEGIN
	SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active FROM menu ORDER BY `order` ASC;
END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `GetMenuByField`( IN search_field VARCHAR(50), IN m_value varchar(60))
BEGIN
   SET @query = CONCAT(
        'SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active FROM menu WHERE ',
        search_field,
        ' = ?'
    );

    PREPARE stmt FROM @query;
    SET @val = m_value;
    EXECUTE stmt USING @val;
    DEALLOCATE PREPARE stmt;

END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `UpdateMenu`(IN m_menu_id INT, IN m_title VARCHAR(100), IN m_slug VARCHAR(80), IN m_order INT, IN m_url TEXT, IN m_parent_id INT)
BEGIN
    UPDATE menu SET title = m_title, slug = m_slug, `order` = m_order, url = m_url, parent_id = m_parent_id WHERE  id_menu = m_menu_id;
    
    SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active
    FROM menu
    WHERE id_menu = m_menu_id;
END ;;
DELIMITER ;

-- --------------------

--
-- PAGE
--
DELIMITER ;;
CREATE  PROCEDURE `GetAllPages`()
BEGIN
	SELECT
    p.id_pages,
    p.title,
    p.description,
    p.active,
    p.menu_id,
    p.created_at,
    p.updated_at,
    COUNT(s.id_section) AS section_count
FROM pages p
LEFT JOIN sections s ON s.pages_id = p.id_pages
GROUP BY p.id_pages;
END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `InsertPage`(p_title VARCHAR(100), p_description TEXT, p_menu_id INT)
BEGIN
        DECLARE page_id INT;
        INSERT INTO pages (title, description, menu_id) 
        VALUES (p_title , p_description, p_menu_id);
        
        SET page_id = LAST_INSERT_ID();
    
    -- ✅ IMPORTANTE: SELECT para retornar datos
    SELECT id_pages, title, description, active, menu_id, created_at, updated_at
    FROM pages
    WHERE id_pages = page_id;
END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `GetPageByField`( IN search_field VARCHAR(50), IN p_value varchar(60))
BEGIN
   SET @query = CONCAT(
        'SELECT id_pages, title, description, active, menu_id FROM pages WHERE ',
        search_field,
        ' = ?'
    );

    PREPARE stmt FROM @query;
    SET @val = p_value;
    EXECUTE stmt USING @val;
    DEALLOCATE PREPARE stmt;

END ;;
DELIMITER ;
-- --------------------

--
-- SECTION
--
DELIMITER ;;
CREATE  PROCEDURE `GetSectionByField`( IN search_field VARCHAR(50), IN u_value varchar(60))
BEGIN
IF search_field IN ('id_section') THEN
    SET @query = CONCAT(
        'SELECT id_section, `order`, `type`, `title`, `subtitle`, `description`, `text_button`, `url_button`, `active`, `pages_id` ',
        'FROM sections WHERE ', search_field, ' = ?'
    );

    PREPARE stmt FROM @query;
    SET @val = u_value;
    EXECUTE stmt USING @val;
    DEALLOCATE PREPARE stmt;
ELSE
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Campo de búsqueda no permitido';
END IF;
END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `InsertSection`(s_order TINYINT, s_type VARCHAR(30), s_title VARCHAR(100), s_subtitle VARCHAR(200), s_description TEXT, 
								s_text_button VARCHAR(100), s_url_button TEXT, s_pages_id INT)
BEGIN
	declare section_id INT;
    
        INSERT INTO sections (`order`, `type`, title, subtitle, description, text_button, url_button, pages_id) 
        VALUES (s_order, s_type, s_title, s_subtitle, s_description, s_text_button, s_url_button, s_pages_id);
        
        SET section_id = LAST_INSERT_ID();
    
    -- ✅ IMPORTANTE: SELECT para retornar datos
    SELECT id_section, `order`, type, title, subtitle, description, text_button, url_button, active, pages_id 
    FROM sections
    WHERE id_section = section_id;

END ;;
DELIMITER ;
-- --------------------

--
-- USERS
--
DELIMITER ;;
CREATE  PROCEDURE `GetUserByField`( IN search_field VARCHAR(50), IN u_value varchar(60))
BEGIN
   SET @query = CONCAT(
        'SELECT id_user, name, lastname, email, role, password FROM users WHERE ',
        search_field,
        ' = ?'
    );

    PREPARE stmt FROM @query;
    SET @val = u_value;
    EXECUTE stmt USING @val;
    DEALLOCATE PREPARE stmt;

END ;;
DELIMITER ;

DELIMITER ;;
CREATE  PROCEDURE `InsertUser`(IN u_name VARCHAR(100), IN u_lastname VARCHAR(45), IN u_email varchar(45), IN u_password VARCHAR(150), IN u_role ENUM('USER', 'EDITOR'))
BEGIN
	DECLARE user_id INT;
    
    INSERT INTO users (name, lastname, email, password, role) VALUES (u_name, u_lastname, u_email, u_password, u_role);
    SET user_id = LAST_INSERT_ID();
    
    -- ✅ IMPORTANTE: SELECT para retornar datos
    SELECT id_user, name, lastname, email, role 
    FROM users 
    WHERE id_user = user_id;
END ;;
DELIMITER ;
-- --------------------

--
-- SECTION ITEM
--
DELIMITER ;;
CREATE PROCEDURE `InsertSectionItem`(IN mi_sections_id INT, IN mi_order INT, IN mi_title VARCHAR(100), IN mi_subtitle VARCHAR(200), 
								  IN mi_description TEXT, IN mi_image VARCHAR(245), IN mi_background_image VARCHAR(100), IN mi_icon VARCHAR(100), 
                                  IN mi_text_button VARCHAR(100), IN mi_link_button VARCHAR(100), IN mi_function_machine_id INT)
BEGIN
	DECLARE section_item_id INT;
    INSERT INTO section_items (sections_id, `order`,title, subtitle, description, image, background_image, icon, text_button, link_button, function_machine_id) 
    VALUES (mi_sections_id, mi_order, mi_title, mi_subtitle, mi_description, mi_image, mi_background_image, mi_icon, mi_text_button, mi_link_button, mi_function_machine_id);
	
    SET section_item_id = LAST_INSERT_ID();
    
    select id_section_items, sections_id, `order`,title, subtitle, description, image, background_image, icon, text_button, link_button, function_machine_id
    from section_items where id_section_items = section_item_id;

END ;;
DELIMITER ;

