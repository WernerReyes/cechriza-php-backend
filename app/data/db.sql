-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: cechriza_web
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `function_machine`
--

DROP TABLE IF EXISTS `function_machine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `function_machine`
--

LOCK TABLES `function_machine` WRITE;
/*!40000 ALTER TABLE `function_machine` DISABLE KEYS */;
/*!40000 ALTER TABLE `function_machine` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `url` text,
  `slug` varchar(80) NOT NULL,
  `order` int NOT NULL,
  `active` tinyint DEFAULT '1',
  `parent_id` int DEFAULT NULL,
  `users_id` int NOT NULL,
  PRIMARY KEY (`id_menu`),
  UNIQUE KEY `unique_order_per_parent` (`parent_id`,`order`),
  KEY `fk_menu_menu_idx` (`parent_id`),
  KEY `fk_menu_users1_idx` (`users_id`),
  CONSTRAINT `fk_menu_menu` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id_menu`),
  CONSTRAINT `fk_menu_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'Inicio editado',NULL,'inicio-editado',0,1,NULL,17),(24,'Inicio',NULL,'inicio',2,1,NULL,17),(26,'Inicio',NULL,'inicio',4,1,NULL,17),(27,'Inicio',NULL,'inicio',3,1,NULL,17),(32,'Inicio',NULL,'inicio',1,1,1,17);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id_pages` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `active` tinyint DEFAULT '1',
  `menu_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pages`),
  UNIQUE KEY `unique_menu_id` (`menu_id`),
  KEY `fk_pages_menu1_idx` (`menu_id`),
  CONSTRAINT `fk_pages_menu1` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'Pagina de inicio',NULL,1,24,'2025-09-26 17:07:49','2025-09-26 17:07:49'),(17,'Pagina de inicio',NULL,1,1,'2025-09-26 17:07:49','2025-09-26 17:07:49'),(19,'Pagina de inicio',NULL,1,32,'2025-09-26 17:09:21','2025-09-26 17:09:21'),(20,'Pagina de inicio',NULL,1,27,'2025-09-26 17:11:06','2025-09-26 17:11:06');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `section_items`
--

DROP TABLE IF EXISTS `section_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `section_items` (
  `id_section_items` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `description` text,
  `image` varchar(245) DEFAULT NULL,
  `background_image` varchar(100) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `text_button` varchar(100) DEFAULT NULL,
  `link_button` varchar(100) DEFAULT NULL,
  `order` varchar(45) DEFAULT NULL,
  `sections_id` int NOT NULL,
  `function_machine_id` int DEFAULT NULL,
  PRIMARY KEY (`id_section_items`),
  UNIQUE KEY `order_UNIQUE` (`order`),
  UNIQUE KEY `order_section_UNIQUE` (`order`,`sections_id`),
  KEY `fk_section_items_sections1_idx` (`sections_id`),
  KEY `fk_section_items_function_machine1_idx` (`function_machine_id`),
  CONSTRAINT `fk_section_items_function_machine1` FOREIGN KEY (`function_machine_id`) REFERENCES `function_machine` (`id_function_machine`),
  CONSTRAINT `fk_section_items_sections1` FOREIGN KEY (`sections_id`) REFERENCES `sections` (`id_section`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `section_items`
--

LOCK TABLES `section_items` WRITE;
/*!40000 ALTER TABLE `section_items` DISABLE KEYS */;
INSERT INTO `section_items` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2',1,NULL),(9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1',1,NULL),(11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'5',1,NULL),(12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3',1,NULL),(14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6',1,NULL),(15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'8',1,NULL),(17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'9',1,NULL);
/*!40000 ALTER TABLE `section_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id_section` int NOT NULL AUTO_INCREMENT,
  `order` tinyint NOT NULL,
  `type` enum('HERO','BENEFIT','MACHINE_TYPE','BILL_MACHINE','VALUE_PROPOSITION','COIN_MACHINE','CLIENT','CONTACT','FOOTER') NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `description` text,
  `text_button` varchar(100) DEFAULT NULL,
  `url_button` text,
  `active` tinyint DEFAULT '1',
  `pages_id` int NOT NULL,
  PRIMARY KEY (`id_section`),
  UNIQUE KEY `order_pages_UNIQUE` (`order`,`pages_id`),
  UNIQUE KEY `order_UNIQUE` (`order`),
  UNIQUE KEY `id_section_UNIQUE` (`id_section`),
  KEY `fk_sections_pages1_idx` (`pages_id`),
  CONSTRAINT `fk_sections_pages1` FOREIGN KEY (`pages_id`) REFERENCES `pages` (`id_pages`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (1,2,'HERO',NULL,NULL,NULL,NULL,NULL,1,17),(4,1,'HERO',NULL,NULL,NULL,NULL,NULL,1,17),(6,5,'HERO',NULL,NULL,NULL,NULL,NULL,1,17),(7,53,'HERO',NULL,NULL,NULL,NULL,NULL,1,17);
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `lastname` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(150) NOT NULL,
  `role` enum('USER','EDITOR') NOT NULL DEFAULT 'USER',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (7,'Juan','Pérez','juan@example.com','$2y$10$22MM7gbSks7syT13vmUdhOQdoHROcPwoqy7xqBM0L1VctJQSG6Wku','USER','2025-09-24 15:30:41'),(8,'María','González','maria@example.com','$2y$10$ss/DqYmTd2P9aWueoyXw.OPb6TNmfiNAeLMR.zXVwlO6i5yLDpidu','EDITOR','2025-09-24 15:30:41'),(9,'Carlos','Rodríguez','carlos@example.com','$2y$10$8sInJ8XBeM1T5qC8giaXkuagweCKdHiOEPmYg1fl3VMABztUbE2pa','EDITOR','2025-09-24 15:30:41'),(10,'Juan','Pérez','juan@example.com','$2y$10$5id9gi0Je4ZNHKm4k5xK.e9TJd9MOt1nStEJF98T6.uruJ/Y/Dt3e','USER','2025-09-24 16:00:50'),(11,'María','González','maria@example.com','$2y$10$Km2L.VaQteP/7NF.I5amUuZ1ibCvPwPxzRK3jyi.XFhXYsSx/03XS','EDITOR','2025-09-24 16:00:50'),(12,'Carlos','Rodríguez','carlos@example.com','$2y$10$FL3pXZa7URbz87TyEwBZ2u9Bud7eiOadl/dyF6K1MAPCWxVkCes96','EDITOR','2025-09-24 16:00:50'),(13,'Werner','Reyes','werner@gmail.com','$2y$10$KanuPl72xbdaxJOh8dLRBuo/EJjozRioFRvXWLgQH8pSzyWeoFqtW','USER','2025-09-25 14:10:01'),(14,'Werner','Reyes','werner2@gmail.com','$2y$10$SmlXjorFpbqI6hTZN0qiTe0rq7aD3Qu8Nbj24/8tr5.A3mF75EPV.','USER','2025-09-25 14:12:56'),(15,'Werner','Reyes','werner3@gmail.com','$2y$10$MHd3doNpvfYrnOrFveBtouUAK6ujm5B5v7lF4jIWPEEzF3Jkp9fHm','USER','2025-09-25 14:14:11'),(16,'Werner','Reyes','werner6@gmail.com','$2y$10$JqBaWuFt1kHHFfKFay65uukykAZf3SETBXxQElpP8bnRl5Mdkahqq','USER','2025-09-25 14:23:17'),(17,'Werner','Reyes','werner7@gmail.com','$2y$10$FRmW22OxA7v1KcS3naxGjeex/mLzLfHzgovQxx/lofT44p.Vv9XdC','USER','2025-09-25 14:57:21');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'cechriza_web'
--
/*!50003 DROP PROCEDURE IF EXISTS `DeleteMenu` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `DeleteMenu`(IN m_menu_id INT)
BEGIN
    UPDATE menu SET active = 0 WHERE  id_menu = m_menu_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetAllMenusOrdered` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `GetAllMenusOrdered`()
BEGIN
  
        SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active FROM menu ORDER BY `order` ASC;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetAllPages` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `GetAllPages`()
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetMenuByField` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `GetMenuByField`( IN search_field VARCHAR(50), IN m_value varchar(60))
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetPageByField` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `GetPageByField`( IN search_field VARCHAR(50), IN p_value varchar(60))
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetSectionByField` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `GetSectionByField`( IN search_field VARCHAR(50), IN u_value varchar(60))
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetUserByField` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `GetUserByField`( IN search_field VARCHAR(50), IN u_value varchar(60))
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `InsertMenu` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `InsertMenu`(IN m_title VARCHAR(100), IN m_slug VARCHAR(80), IN m_order INT, IN m_id_user INT, IN m_url TEXT, IN m_parent_id INT)
BEGIN
	DECLARE menu_id INT;
    
    INSERT INTO menu (title, slug, `order`, users_id, url, parent_id) VALUES (m_title, m_slug, m_order, m_id_user, m_url, m_parent_id);
    SET menu_id = LAST_INSERT_ID();
    
    SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active
    FROM menu
    WHERE id_menu = menu_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `InsertPage` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `InsertPage`(p_title VARCHAR(100), p_description TEXT, p_menu_id INT)
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `InsertSection` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `InsertSection`(s_order TINYINT, s_type VARCHAR(30), s_title VARCHAR(100), s_subtitle VARCHAR(200), s_description TEXT, 
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `InsertSectionItem` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `InsertSectionItem`(IN mi_sections_id INT, IN mi_order INT, IN mi_title VARCHAR(100), IN mi_subtitle VARCHAR(200), 
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `InsertUser` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `InsertUser`(IN u_name VARCHAR(100), IN u_lastname VARCHAR(45), IN u_email varchar(45), IN u_password VARCHAR(150), IN u_role ENUM('USER', 'EDITOR'))
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
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `UpdateMenu` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`werner`@`%` PROCEDURE `UpdateMenu`(IN m_menu_id INT, IN m_title VARCHAR(100), IN m_slug VARCHAR(80), IN m_order INT, IN m_url TEXT, IN m_parent_id INT)
BEGIN
    UPDATE menu SET title = m_title, slug = m_slug, `order` = m_order, url = m_url, parent_id = m_parent_id WHERE  id_menu = m_menu_id;
    
    SELECT id_menu, title, slug, `order`, users_id, url, parent_id, active
    FROM menu
    WHERE id_menu = m_menu_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-26 17:42:54
