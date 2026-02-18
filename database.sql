-- MySQL dump 10.13  Distrib 9.4.0, for macos15.4 (arm64)
--
-- Host: localhost    Database: vite_gourmand
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `allergenes`
--

DROP TABLE IF EXISTS `allergenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `allergenes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allergenes`
--

LOCK TABLES `allergenes` WRITE;
/*!40000 ALTER TABLE `allergenes` DISABLE KEYS */;
INSERT INTO `allergenes` VALUES (3,'fruits_a_coque'),(1,'gluten'),(2,'lactose');
/*!40000 ALTER TABLE `allergenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `note` tinyint NOT NULL,
  `commentaire` text COLLATE utf8mb4_general_ci,
  `is_validated` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_avis_user` (`user_id`),
  KEY `fk_avis_menu` (`menu_id`),
  CONSTRAINT `fk_avis_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  CONSTRAINT `fk_avis_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_note` CHECK ((`note` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (2,3,2,5,'good service',0,'2026-02-14 01:11:03'),(3,3,2,5,'test avis',1,'2026-02-14 01:21:03'),(4,3,2,5,'avis test good',0,'2026-02-14 01:27:35'),(5,3,2,4,'tres bon service',0,'2026-02-14 17:26:05'),(6,3,2,5,'good',0,'2026-02-14 17:26:16');
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commandes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `nb_personnes` int NOT NULL,
  `date_prestation` date NOT NULL,
  `heure_livraison` time NOT NULL,
  `adresse_livraison` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ville_livraison` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `km_hors_bordeaux` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prix_menu` decimal(10,2) NOT NULL DEFAULT '0.00',
  `remise` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prix_livraison` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prix_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_commandes_user` (`user_id`),
  KEY `fk_commandes_menu` (`menu_id`),
  CONSTRAINT `fk_commandes_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  CONSTRAINT `fk_commandes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commandes`
--

LOCK TABLES `commandes` WRITE;
/*!40000 ALTER TABLE `commandes` DISABLE KEYS */;
INSERT INTO `commandes` VALUES (8,3,2,10,'2026-02-15','12:30:00','12 rue de test','Bordeaux',0.00,80.00,0.00,0.00,80.00,'2026-02-13 01:56:04');
/*!40000 ALTER TABLE `commandes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commandes_statuts`
--

DROP TABLE IF EXISTS `commandes_statuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commandes_statuts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `commande_id` int NOT NULL,
  `statut` enum('accepte','en_preparation','en_cours_de_livraison','livre','en_attente_retour_materiel','terminee') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_statuts_commande` (`commande_id`),
  CONSTRAINT `fk_statuts_commande` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commandes_statuts`
--

LOCK TABLES `commandes_statuts` WRITE;
/*!40000 ALTER TABLE `commandes_statuts` DISABLE KEYS */;
INSERT INTO `commandes_statuts` VALUES (11,8,'accepte','2026-02-13 02:01:35'),(12,8,'en_preparation','2026-02-13 02:01:36'),(13,8,'en_cours_de_livraison','2026-02-13 02:01:37'),(14,8,'livre','2026-02-13 02:01:38'),(15,8,'terminee','2026-02-13 02:01:39'),(16,8,'accepte','2026-02-15 12:32:01'),(17,8,'accepte','2026-02-15 12:32:02'),(18,8,'accepte','2026-02-15 12:34:20'),(19,8,'accepte','2026-02-15 12:34:21'),(20,8,'accepte','2026-02-15 12:34:21'),(21,8,'en_preparation','2026-02-15 12:34:24'),(22,8,'livre','2026-02-15 12:34:28'),(23,8,'livre','2026-02-15 12:34:42'),(24,8,'accepte','2026-02-15 14:13:05'),(25,8,'terminee','2026-02-16 18:31:32'),(26,8,'terminee','2026-02-16 18:31:35'),(27,8,'terminee','2026-02-16 18:31:36'),(28,8,'terminee','2026-02-16 18:31:40'),(29,8,'terminee','2026-02-16 18:31:41'),(30,8,'terminee','2026-02-16 18:31:41'),(31,8,'terminee','2026-02-16 18:31:42'),(32,8,'terminee','2026-02-16 18:31:42'),(33,8,'terminee','2026-02-16 18:31:42'),(34,8,'terminee','2026-02-16 18:31:42'),(35,8,'terminee','2026-02-16 18:31:42'),(36,8,'terminee','2026-02-16 18:31:43'),(37,8,'terminee','2026-02-16 18:31:43'),(38,8,'terminee','2026-02-16 18:31:44'),(39,8,'terminee','2026-02-16 18:33:44'),(40,8,'terminee','2026-02-16 18:33:47'),(41,8,'terminee','2026-02-16 18:33:48'),(42,8,'terminee','2026-02-16 19:12:03'),(43,8,'terminee','2026-02-16 19:43:33'),(44,8,'terminee','2026-02-17 03:32:40'),(45,8,'accepte','2026-02-17 03:39:45'),(46,8,'terminee','2026-02-17 03:44:32'),(47,8,'terminee','2026-02-17 04:14:18');
/*!40000 ALTER TABLE `commandes_statuts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `titre` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,'yasser@test.com','Demande info','Bonjour, je veux des infos sur un menu.','2026-01-29 00:10:54');
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horaires`
--

DROP TABLE IF EXISTS `horaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jour` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') COLLATE utf8mb4_general_ci NOT NULL,
  `heure_ouverture` time NOT NULL,
  `heure_fermeture` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jour` (`jour`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horaires`
--

LOCK TABLES `horaires` WRITE;
/*!40000 ALTER TABLE `horaires` DISABLE KEYS */;
INSERT INTO `horaires` VALUES (1,'lundi','09:00:00','18:00:00'),(2,'mardi','09:00:00','18:00:00'),(3,'mercredi','09:00:00','18:00:00'),(4,'jeudi','09:00:00','18:00:00'),(5,'vendredi','09:00:00','18:00:00'),(6,'samedi','10:00:00','16:00:00'),(7,'dimanche','10:00:00','14:00:00');
/*!40000 ALTER TABLE `horaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_images`
--

DROP TABLE IF EXISTS `menu_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_menu_images_menu` (`menu_id`),
  CONSTRAINT `fk_menu_images_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_images`
--

LOCK TABLES `menu_images` WRITE;
/*!40000 ALTER TABLE `menu_images` DISABLE KEYS */;
INSERT INTO `menu_images` VALUES (1,2,'/images/menus/classique.png'),(2,3,'/images/menus/vegetarien.png'),(3,4,'/images/menus/premium.png');
/*!40000 ALTER TABLE `menu_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_plat`
--

DROP TABLE IF EXISTS `menu_plat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_plat` (
  `menu_id` int NOT NULL,
  `plat_id` int NOT NULL,
  PRIMARY KEY (`menu_id`,`plat_id`),
  KEY `fk_menu_plat_plat` (`plat_id`),
  CONSTRAINT `fk_menu_plat_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_menu_plat_plat` FOREIGN KEY (`plat_id`) REFERENCES `plats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_plat`
--

LOCK TABLES `menu_plat` WRITE;
/*!40000 ALTER TABLE `menu_plat` DISABLE KEYS */;
INSERT INTO `menu_plat` VALUES (2,4),(3,4),(4,4),(2,5),(3,5),(4,5),(2,6),(3,6),(4,6),(2,7),(3,7),(4,7),(2,8),(3,8),(4,8),(2,9),(3,9),(4,9),(2,10),(3,10),(4,10),(2,11),(3,11),(4,11),(2,12),(3,12),(4,12);
/*!40000 ALTER TABLE `menu_plat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `theme` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `regime` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nb_personnes_min` int NOT NULL,
  `prix_min` decimal(10,2) NOT NULL,
  `conditions` text COLLATE utf8mb4_general_ci,
  `stock` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (2,'Menu Classique','Entrée + plat + dessert. Idéal pour tous types d’événements.','Classique','Standard',10,80.00,'Livraison : 0€ à Bordeaux, sinon 5€ + 0,59€/km. Réduction -10% si min +5 personnes.',20,'2026-02-06 20:05:56',NULL),(3,'Menu Végétarien','Menu sans viande, produits frais et de saison.','Végétarien','Végétarien',8,90.00,'Livraison : 0€ à Bordeaux, sinon 5€ + 0,59€/km. Réduction -10% si min +5 personnes.',15,'2026-02-06 20:05:56',NULL),(4,'Menu Premium','Produits haut de gamme, pour un événement exceptionnel.','Premium','Standard',12,140.00,'Livraison : 0€ à Bordeaux, sinon 5€ + 0,59€/km. Réduction -10% si min +5 personnes.',10,'2026-02-06 20:05:56',NULL);
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,3,'$2y$12$t9EBJWcBm4OLR8o6I3ScnO7VZUIo9XPquYpNSFenUVz/.brO5rm4O','2026-02-15 19:24:00','2026-02-15 20:21:28','2026-02-15 19:54:00');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plat_allergene`
--

DROP TABLE IF EXISTS `plat_allergene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plat_allergene` (
  `plat_id` int NOT NULL,
  `allergene_id` int NOT NULL,
  PRIMARY KEY (`plat_id`,`allergene_id`),
  KEY `fk_plat_allergene_allergene` (`allergene_id`),
  CONSTRAINT `fk_plat_allergene_allergene` FOREIGN KEY (`allergene_id`) REFERENCES `allergenes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_plat_allergene_plat` FOREIGN KEY (`plat_id`) REFERENCES `plats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plat_allergene`
--

LOCK TABLES `plat_allergene` WRITE;
/*!40000 ALTER TABLE `plat_allergene` DISABLE KEYS */;
INSERT INTO `plat_allergene` VALUES (3,1),(7,1),(9,1),(11,2),(11,3);
/*!40000 ALTER TABLE `plat_allergene` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plats`
--

DROP TABLE IF EXISTS `plats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('entree','plat','dessert') COLLATE utf8mb4_general_ci NOT NULL,
  `nom` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plats`
--

LOCK TABLES `plats` WRITE;
/*!40000 ALTER TABLE `plats` DISABLE KEYS */;
INSERT INTO `plats` VALUES (1,'entree','Salade de saison',NULL),(2,'plat','Poulet rôti et pommes de terre',NULL),(3,'dessert','Tarte aux pommes',NULL),(4,'entree','Salade composée','Salade fraîche de saison.'),(5,'entree','Œufs mimosa','Œufs farcis, mayonnaise maison.'),(6,'entree','Velouté de légumes','Velouté doux et crémeux.'),(7,'plat','Poulet rôti','Poulet rôti, jus réduit.'),(8,'plat','Bœuf bourguignon','Mijoté traditionnel.'),(9,'plat','Poisson grillé','Poisson du jour, citron.'),(10,'dessert','Tarte aux pommes','Tarte fine, pommes caramélisées.'),(11,'dessert','Mousse au chocolat','Mousse légère.'),(12,'dessert','Crème brûlée','Crème vanillée, caramel croquant.');
/*!40000 ALTER TABLE `plats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (3,'admin'),(2,'employe'),(1,'utilisateur');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `nom` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `gsm` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_roles` (`role_id`),
  CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,3,'Admin','Jose','admin@vite-gourmand.test','0600000000','Bordeaux','$2y$10$CHANGE_ME_HASH',1,'2026-01-28 22:55:01'),(2,2,'Test','User','test@vite.com','0600000000','Bordeaux','$2y$12$8c5tJ9X9R56PcK.9LyoJv.43Mqc3tDk9lfN8FMXvKnmFvCT92ViU2',1,'2026-01-29 00:16:41'),(3,1,'kossoko','yasser','kossokoyasser@gmail.com','0758444439','12 fgh paris 75020','$2y$12$fsPUiwC3ce9JnJNPwTmbbOwFJxrHd5PqYNn6wrvIrhFASILqc9UWW',1,'2026-01-30 01:34:43'),(4,1,'jean','francois','jean2026@gmail.com','0600000001','15 boulevard paris, 75012','$2y$12$qgGHo8uzvgXEqShfUtHuFeqnVse64BAFE1zU0cM4lYY64XjODD0Y.',1,'2026-02-15 16:14:18'),(5,1,'jean','jonas','eboutique68@gmail.com','0600000001','15 boulevard paris, 75012','$2y$12$yBK9Js1HQ/5szIGklGuKNe9UmV6ggs7ThYl3b6OKmDQwVEyHfznyS',1,'2026-02-15 19:24:14'),(6,1,'jean','yasser','kossokoyasserr@gmail.com','0600000001','12 fgh paris 75020','$2y$12$.yhQQWbMFE0kKYAXCatPFuxW2xOkhaFZr9diKlvjLVBD7ENrQ839u',1,'2026-02-15 21:25:09'),(7,1,'jean','yasser','jean@gmail.com','0600000001','12 fgh paris 75020','$2y$12$eykgGX.5qonC5p89HYzv1OZ0XARsDfoQYTYsa7Z1U2XMj/TitaHTm',1,'2026-02-15 21:26:24'),(8,1,'jean','jonas','jonas@gmail.com','0600000001','12 fgh paris 75020','$2y$12$7hxISXJj1waSlo5KvbBGWesEIo0T55n91VqdtVwzTSLwEECn7iL2O',1,'2026-02-17 05:19:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-17  5:38:37
