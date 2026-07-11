-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: carxmeet
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.3

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
-- Table structure for table `adresses`
--

DROP TABLE IF EXISTS `adresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `photo` varchar(255) NOT NULL,
  `website_url` varchar(255) NOT NULL,
  `region_id_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EF192552C7209D4F` (`region_id_id`),
  CONSTRAINT `FK_EF192552C7209D4F` FOREIGN KEY (`region_id_id`) REFERENCES `region` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adresses`
--

LOCK TABLES `adresses` WRITE;
/*!40000 ALTER TABLE `adresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `adresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `cover_photo` varchar(255) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BFDD31689D86650F` (`user_id_id`),
  CONSTRAINT `FK_BFDD31689D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `convoy`
--

DROP TABLE IF EXISTS `convoy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `convoy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `departure_location` varchar(255) NOT NULL,
  `departure_time` varchar(255) NOT NULL,
  `participants` varchar(255) DEFAULT NULL,
  `event_id_id` int NOT NULL,
  `departure_date` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_64F916E93E5F2F7B` (`event_id_id`),
  CONSTRAINT `FK_64F916E93E5F2F7B` FOREIGN KEY (`event_id_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `convoy`
--

LOCK TABLES `convoy` WRITE;
/*!40000 ALTER TABLE `convoy` DISABLE KEYS */;
/*!40000 ALTER TABLE `convoy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `convoy_participation`
--

DROP TABLE IF EXISTS `convoy_participation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `convoy_participation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `joined_at` datetime NOT NULL,
  `convoy_id_id` int NOT NULL,
  `user_id_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_convoy_user` (`convoy_id_id`,`user_id_id`),
  KEY `IDX_883675D29F276072` (`convoy_id_id`),
  KEY `IDX_883675D29D86650F` (`user_id_id`),
  CONSTRAINT `FK_883675D29D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_883675D29F276072` FOREIGN KEY (`convoy_id_id`) REFERENCES `convoy` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `convoy_participation`
--

LOCK TABLES `convoy_participation` WRITE;
/*!40000 ALTER TABLE `convoy_participation` DISABLE KEYS */;
/*!40000 ALTER TABLE `convoy_participation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crew`
--

DROP TABLE IF EXISTS `crew`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crew` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_894940B2B03A8386` (`created_by_id`),
  CONSTRAINT `FK_894940B2B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crew`
--

LOCK TABLES `crew` WRITE;
/*!40000 ALTER TABLE `crew` DISABLE KEYS */;
/*!40000 ALTER TABLE `crew` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crew_user`
--

DROP TABLE IF EXISTS `crew_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crew_user` (
  `crew_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`crew_id`,`user_id`),
  KEY `IDX_B4AF30565FE259F6` (`crew_id`),
  KEY `IDX_B4AF3056A76ED395` (`user_id`),
  CONSTRAINT `FK_B4AF30565FE259F6` FOREIGN KEY (`crew_id`) REFERENCES `crew` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B4AF3056A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crew_user`
--

LOCK TABLES `crew_user` WRITE;
/*!40000 ALTER TABLE `crew_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `crew_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20260127133610','2026-07-09 11:38:46',2465),('DoctrineMigrations\\Version20260127135540','2026-07-09 11:38:49',335),('DoctrineMigrations\\Version20260127140442','2026-07-09 11:38:49',323),('DoctrineMigrations\\Version20260127140955','2026-07-09 11:38:49',156),('DoctrineMigrations\\Version20260127141257','2026-07-09 11:38:50',92),('DoctrineMigrations\\Version20260128154053','2026-07-09 11:38:50',79),('DoctrineMigrations\\Version20260318103644','2026-07-09 11:38:50',57),('DoctrineMigrations\\Version20260318111557','2026-07-09 11:38:50',88),('DoctrineMigrations\\Version20260416100000','2026-07-09 11:38:50',608),('DoctrineMigrations\\Version20260416124214','2026-07-09 11:38:51',436),('DoctrineMigrations\\Version20260416133956','2026-07-09 11:38:51',311),('DoctrineMigrations\\Version20260701095900',NULL,NULL);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_photo`
--

DROP TABLE IF EXISTS `event_photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_photo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id_id` int NOT NULL,
  `photo` longblob NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_55AC35343E5F2F7B` (`event_id_id`),
  CONSTRAINT `FK_55AC35343E5F2F7B` FOREIGN KEY (`event_id_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_photo`
--

LOCK TABLES `event_photo` WRITE;
/*!40000 ALTER TABLE `event_photo` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_photo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_rating`
--

DROP TABLE IF EXISTS `event_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_rating` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id_id` int NOT NULL,
  `user_id_id` int NOT NULL,
  `rating` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EA1051703E5F2F7B` (`event_id_id`),
  KEY `IDX_EA1051709D86650F` (`user_id_id`),
  CONSTRAINT `FK_EA1051703E5F2F7B` FOREIGN KEY (`event_id_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_EA1051709D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_rating`
--

LOCK TABLES `event_rating` WRITE;
/*!40000 ALTER TABLE `event_rating` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_rating` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `cover_photo` longblob,
  `gallery` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `rating_average` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `region_id_id` int DEFAULT NULL,
  `organisateur` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5387574AC7209D4F` (`region_id_id`),
  CONSTRAINT `FK_5387574AC7209D4F` FOREIGN KEY (`region_id_id`) REFERENCES `region` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friendship`
--

DROP TABLE IF EXISTS `friendship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `friendship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL,
  `requester_id_id` int NOT NULL,
  `receiver_id_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7234A45F9C0CF0F6` (`requester_id_id`),
  KEY `IDX_7234A45FBE20CAB0` (`receiver_id_id`),
  CONSTRAINT `FK_7234A45F9C0CF0F6` FOREIGN KEY (`requester_id_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_7234A45FBE20CAB0` FOREIGN KEY (`receiver_id_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friendship`
--

LOCK TABLES `friendship` WRITE;
/*!40000 ALTER TABLE `friendship` DISABLE KEYS */;
/*!40000 ALTER TABLE `friendship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participation`
--

DROP TABLE IF EXISTS `participation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `joined_at` datetime NOT NULL,
  `user_id_id` int DEFAULT NULL,
  `event_id_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AB55E24F9D86650F` (`user_id_id`),
  KEY `IDX_AB55E24F3E5F2F7B` (`event_id_id`),
  CONSTRAINT `FK_AB55E24F3E5F2F7B` FOREIGN KEY (`event_id_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_AB55E24F9D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participation`
--

LOCK TABLES `participation` WRITE;
/*!40000 ALTER TABLE `participation` DISABLE KEYS */;
/*!40000 ALTER TABLE `participation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postphoto`
--

DROP TABLE IF EXISTS `postphoto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postphoto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_80C793089D86650F` (`user_id_id`),
  CONSTRAINT `FK_80C793089D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postphoto`
--

LOCK TABLES `postphoto` WRITE;
/*!40000 ALTER TABLE `postphoto` DISABLE KEYS */;
/*!40000 ALTER TABLE `postphoto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `region` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
/*!40000 ALTER TABLE `region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `snapchat` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle`
--

DROP TABLE IF EXISTS `vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `year` varchar(4) NOT NULL,
  `engine` varchar(255) NOT NULL,
  `preparation` varchar(255) NOT NULL,
  `photos` longblob,
  `user_id_id` int DEFAULT NULL,
  `description` longtext,
  PRIMARY KEY (`id`),
  KEY `IDX_1B80E4869D86650F` (`user_id_id`),
  CONSTRAINT `FK_1B80E4869D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle`
--

LOCK TABLES `vehicle` WRITE;
/*!40000 ALTER TABLE `vehicle` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_photo`
--

DROP TABLE IF EXISTS `vehicle_photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_photo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id_id` int NOT NULL,
  `photo` longblob NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_761804F41DEB1EBB` (`vehicle_id_id`),
  CONSTRAINT `FK_761804F41DEB1EBB` FOREIGN KEY (`vehicle_id_id`) REFERENCES `vehicle` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_photo`
--

LOCK TABLES `vehicle_photo` WRITE;
/*!40000 ALTER TABLE `vehicle_photo` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_photo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-09 14:34:22
