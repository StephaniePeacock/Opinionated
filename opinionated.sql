-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: opinionated
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `entity_answer`
--

DROP TABLE IF EXISTS `entity_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entity_answer` (
  `id_answer` int(10) NOT NULL AUTO_INCREMENT,
  `id_question` int(10) DEFAULT NULL,
  `answer_text` varchar(255) DEFAULT NULL,
  `is_custom` tinyint(1) DEFAULT NULL,
  `response_count` int(10) DEFAULT NULL,
  PRIMARY KEY (`id_answer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entity_answer`
--

LOCK TABLES `entity_answer` WRITE;
/*!40000 ALTER TABLE `entity_answer` DISABLE KEYS */;
/*!40000 ALTER TABLE `entity_answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entity_question`
--

DROP TABLE IF EXISTS `entity_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entity_question` (
  `id_question` int(10) NOT NULL AUTO_INCREMENT,
  `id_survey` int(10) DEFAULT NULL,
  `question_text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_question`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entity_question`
--

LOCK TABLES `entity_question` WRITE;
/*!40000 ALTER TABLE `entity_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `entity_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entity_response`
--

DROP TABLE IF EXISTS `entity_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entity_response` (
  `id_response` int(10) NOT NULL AUTO_INCREMENT,
  `id_question` int(10) NOT NULL,
  `id_answer` int(10) NOT NULL,
  PRIMARY KEY (`id_response`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entity_response`
--

LOCK TABLES `entity_response` WRITE;
/*!40000 ALTER TABLE `entity_response` DISABLE KEYS */;
/*!40000 ALTER TABLE `entity_response` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entity_survey`
--

DROP TABLE IF EXISTS `entity_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entity_survey` (
  `id_survey` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_survey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entity_survey`
--

LOCK TABLES `entity_survey` WRITE;
/*!40000 ALTER TABLE `entity_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `entity_survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entity_user`
--

DROP TABLE IF EXISTS `entity_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entity_user` (
  `id_user` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admin_status` tinyint(1) NOT NULL,
  `rank` int(10) DEFAULT NULL,
  `surveys` int(10) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entity_user`
--

LOCK TABLES `entity_user` WRITE;
/*!40000 ALTER TABLE `entity_user` DISABLE KEYS */;
INSERT INTO `entity_user` VALUES (1,'admin@no.com','$2y$10$VCcw.jjp7vDcalj0t1FWnu9HCgGAJtqB0ue7agg0dJXmelxOdRKkG',1,0,0),(2,'user1@no.com','$2y$10$VCcw.jjp7vDcalj0t1FWnu9HCgGAJtqB0ue7agg0dJXmelxOdRKkG',0,0,0),(3,'user2@no.com','$2y$10$VCcw.jjp7vDcalj0t1FWnu9HCgGAJtqB0ue7agg0dJXmelxOdRKkG',0,0,0);
/*!40000 ALTER TABLE `entity_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-05-27 20:08:14
