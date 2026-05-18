-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: tramway.proxy.rlwy.net    Database: railway
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
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int DEFAULT NULL,
  `paid_by` bigint DEFAULT NULL,
  `recorded_by` bigint DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,1,5403000194,394619546,260000.00,'dparagon','2026-05-03 06:56:49'),(2,1,5403000194,394619546,325000.00,'sogogi','2026-05-03 06:57:22'),(3,1,394619546,5403000194,105000.00,'coffee and thyme','2026-05-03 07:04:30'),(4,4,5403000194,394619546,80900.00,'mie aceh','2026-05-04 15:40:31'),(5,4,394619546,394619546,54000.00,'angkringan','2026-05-08 16:15:24'),(6,4,394619546,394619546,350000.00,'pijat ewe','2026-05-08 16:15:48'),(7,4,5403000194,5403000194,285000.00,'paragon','2026-05-09 08:39:19'),(8,4,5403000194,5403000194,77500.00,'ssb','2026-05-09 08:40:15'),(9,4,394619546,394619546,89000.00,'sambel setan','2026-05-09 12:42:50'),(10,4,5403000194,394619546,52000.00,'loui coffee','2026-05-09 12:43:20'),(11,4,394619546,394619546,88000.00,'bar burger','2026-05-09 14:06:20'),(12,4,5403000194,5403000194,90000.00,'kopikina','2026-05-10 03:38:01'),(13,4,5403000194,5403000194,89500.00,'kejarsetoran','2026-05-10 09:37:37'),(14,4,5403000194,394619546,223098.00,'paragon lagi','2026-05-12 11:46:38'),(15,4,394619546,394619546,99000.00,'panci bahagia','2026-05-13 00:28:53'),(16,4,394619546,394619546,243000.00,'paragon lagiy','2026-05-13 14:26:06'),(17,4,394619546,394619546,67000.00,'sayogi','2026-05-13 17:12:35'),(18,4,5403000194,394619546,35000.00,'warung solo','2026-05-14 05:33:15'),(19,4,5403000194,394619546,211080.00,'paragon lagi','2026-05-14 08:56:10'),(20,4,394619546,394619546,100000.00,'coto','2026-05-15 13:22:12');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `chat_id` bigint NOT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (-5219332157,'Test','2026-05-03 06:54:55');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `user_id` bigint NOT NULL,
  `chat_id` bigint NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`chat_id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `groups` (`chat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (394619546,-5219332157,'Tommy Anggoro','tommianggoro'),(5403000194,-5219332157,'princess','princess_minalminul');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `from_user_id` bigint NOT NULL,
  `to_user_id` bigint NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` bigint DEFAULT NULL,
  `label` varchar(50) DEFAULT 'umum',
  `status` enum('Active','Closed') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `groups` (`chat_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (1,-5219332157,'mei','Closed','2026-05-03 06:56:49'),(2,-5219332157,'mei','Closed','2026-05-03 06:57:22'),(3,-5219332157,'mei','Closed','2026-05-03 07:04:30'),(4,-5219332157,'mei','Active','2026-05-04 15:40:31'),(5,-5219332157,'mei','Active','2026-05-08 16:15:24'),(6,-5219332157,'mei','Active','2026-05-08 16:15:48'),(7,-5219332157,'mei','Active','2026-05-09 08:39:19'),(8,-5219332157,'mei','Active','2026-05-09 08:40:15'),(9,-5219332157,'mei','Active','2026-05-09 12:42:50'),(10,-5219332157,'mei','Active','2026-05-09 12:43:20'),(11,-5219332157,'mei','Active','2026-05-09 14:06:20'),(12,-5219332157,'mei','Active','2026-05-10 03:38:01'),(13,-5219332157,'mei','Active','2026-05-10 09:37:37'),(14,-5219332157,'mei','Active','2026-05-12 11:46:38'),(15,-5219332157,'mei','Active','2026-05-13 00:28:53'),(16,-5219332157,'mei','Active','2026-05-13 14:26:06'),(17,-5219332157,'mei','Active','2026-05-13 17:12:35'),(18,-5219332157,'mei','Active','2026-05-14 05:33:15'),(19,-5219332157,'mei','Active','2026-05-14 08:56:10'),(20,-5219332157,'mei','Active','2026-05-15 13:22:12');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'railway'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-18 17:24:34
