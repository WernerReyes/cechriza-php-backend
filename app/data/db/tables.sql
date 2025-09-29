DROP DATABASE cechriza_web;
CREATE DATABASE cechriza_web;
use cechriza_web;

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
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

--
-- Dumping data for table `users`
--
INSERT INTO `users` VALUES (7,'Juan','Pérez','juan@example.com','$2y$10$22MM7gbSks7syT13vmUdhOQdoHROcPwoqy7xqBM0L1VctJQSG6Wku','USER','2025-09-24 15:30:41'),(8,'María','González','maria@example.com','$2y$10$ss/DqYmTd2P9aWueoyXw.OPb6TNmfiNAeLMR.zXVwlO6i5yLDpidu','EDITOR','2025-09-24 15:30:41'),(9,'Carlos','Rodríguez','carlos@example.com','$2y$10$8sInJ8XBeM1T5qC8giaXkuagweCKdHiOEPmYg1fl3VMABztUbE2pa','EDITOR','2025-09-24 15:30:41'),(10,'Juan','Pérez','juan@example.com','$2y$10$5id9gi0Je4ZNHKm4k5xK.e9TJd9MOt1nStEJF98T6.uruJ/Y/Dt3e','USER','2025-09-24 16:00:50'),(11,'María','González','maria@example.com','$2y$10$Km2L.VaQteP/7NF.I5amUuZ1ibCvPwPxzRK3jyi.XFhXYsSx/03XS','EDITOR','2025-09-24 16:00:50'),(12,'Carlos','Rodríguez','carlos@example.com','$2y$10$FL3pXZa7URbz87TyEwBZ2u9Bud7eiOadl/dyF6K1MAPCWxVkCes96','EDITOR','2025-09-24 16:00:50'),(13,'Werner','Reyes','werner@gmail.com','$2y$10$KanuPl72xbdaxJOh8dLRBuo/EJjozRioFRvXWLgQH8pSzyWeoFqtW','USER','2025-09-25 14:10:01'),(14,'Werner','Reyes','werner2@gmail.com','$2y$10$SmlXjorFpbqI6hTZN0qiTe0rq7aD3Qu8Nbj24/8tr5.A3mF75EPV.','USER','2025-09-25 14:12:56'),(15,'Werner','Reyes','werner3@gmail.com','$2y$10$MHd3doNpvfYrnOrFveBtouUAK6ujm5B5v7lF4jIWPEEzF3Jkp9fHm','USER','2025-09-25 14:14:11'),(16,'Werner','Reyes','werner6@gmail.com','$2y$10$JqBaWuFt1kHHFfKFay65uukykAZf3SETBXxQElpP8bnRl5Mdkahqq','USER','2025-09-25 14:23:17'),(17,'Werner','Reyes','werner7@gmail.com','$2y$10$FRmW22OxA7v1KcS3naxGjeex/mLzLfHzgovQxx/lofT44p.Vv9XdC','USER','2025-09-25 14:57:21');


--
-- Table structure for table `menu`
--
DROP TABLE IF EXISTS `menu`;
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

--
-- Dumping data for table `menu`
--
INSERT INTO `menu` VALUES (1,'Inicio editado',NULL,'inicio-editado',0,1,NULL,17),(24,'Inicio',NULL,'inicio',2,1,NULL,17),(26,'Inicio',NULL,'inicio',4,1,NULL,17),(27,'Inicio',NULL,'inicio',3,1,NULL,17),(32,'Inicio',NULL,'inicio',1,1,1,17);


--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
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

--
-- Dumping data for table `pages`
--
INSERT INTO `pages` VALUES (1,'Pagina de inicio',NULL,1,24,'2025-09-26 17:07:49','2025-09-26 17:07:49'),(17,'Pagina de inicio',NULL,1,1,'2025-09-26 17:07:49','2025-09-26 17:07:49'),(19,'Pagina de inicio',NULL,1,32,'2025-09-26 17:09:21','2025-09-26 17:09:21'),(20,'Pagina de inicio',NULL,1,27,'2025-09-26 17:11:06','2025-09-26 17:11:06');


--
-- Table structure for table `sections`
--
DROP TABLE IF EXISTS `sections`;
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

--
-- Dumping data for table `sections`
--
INSERT INTO `sections` VALUES (1,2,'HERO',NULL,NULL,NULL,NULL,NULL,1,17),(4,1,'HERO',NULL,NULL,NULL,NULL,NULL,1,17),(6,5,'HERO',NULL,NULL,NULL,NULL,NULL,1,17),(7,53,'HERO',NULL,NULL,NULL,NULL,NULL,1,17);


--
-- Table structure for table `function_machine`
--
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
DROP TABLE IF EXISTS `section_items`;
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
--
-- Dumping data for table `section_items`
--
INSERT INTO `section_items` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2',1,NULL),(9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1',1,NULL),(11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'5',1,NULL),(12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3',1,NULL),(14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6',1,NULL),(15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'8',1,NULL),(17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'9',1,NULL);



