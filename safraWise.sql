-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: safrawise
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `cotacoes`
--

DROP TABLE IF EXISTS `cotacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produto` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `praca` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `variacao_mensal` decimal(10,4) DEFAULT NULL,
  `moeda` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unidade` varchar(55) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotacoes`
--

LOCK TABLES `cotacoes` WRITE;
/*!40000 ALTER TABLE `cotacoes` DISABLE KEYS */;
INSERT INTO `cotacoes` VALUES (1,'Arroz','Campanha','2026-05-04 15:25:02',62.25,0.0168,'R$','SACA 50 KG','RS'),(2,'Arroz','Depressao central','2026-05-04 15:25:02',59.83,-0.0065,'R$','SACA 50 KG','RS'),(3,'Arroz','Fronteira oeste','2026-05-04 15:25:02',62.41,0.0081,'R$','SACA 50 KG','RS'),(4,'Arroz','Zona sul','2026-05-04 15:25:02',63.30,0.0040,'R$','SACA 50 KG','RS'),(5,'Trigo - tipo pão','Norte do parana','2026-05-04 15:25:02',1361.43,0.0483,'R$','TON','PR'),(6,'Trigo - tipo pão','Oeste do parana','2026-05-04 15:25:02',1305.53,0.0124,'R$','TON','PR'),(7,'Trigo - tipo pão','Parana','2026-05-04 15:25:02',1342.67,0.0449,'R$','TON','PR'),(8,'Trigo - tipo pão','Ponta grossa','2026-05-04 15:25:02',1362.53,0.0543,'R$','TON','PR'),(9,'Trigo - tipo pão','Sudoeste do parana','2026-05-04 15:25:02',1331.60,0.0638,'R$','TON','PR'),(10,'Soja','Barreiras','2026-05-04 15:25:02',110.85,0.0000,'R$','SACA 60 KG','BA'),(11,'Soja','Rio verde','2026-05-04 15:25:02',106.81,-0.0335,'R$','SACA 60 KG','GO'),(12,'Soja','Triangulo mineiro','2026-05-04 15:25:02',108.18,-0.0233,'R$','SACA 60 KG','MG'),(13,'Soja','Campo grande','2026-05-04 15:25:02',104.17,-0.0280,'R$','SACA 60 KG','MS'),(14,'Soja','Dourados','2026-05-04 15:25:02',104.43,-0.0366,'R$','SACA 60 KG','MS'),(15,'Soja','Maracaju','2026-05-04 15:25:02',104.34,-0.0279,'R$','SACA 60 KG','MS'),(16,'Soja','Primavera do leste','2026-05-04 15:25:02',103.59,0.0118,'R$','SACA 60 KG','MT'),(17,'Soja','Sorriso','2026-05-04 15:25:02',98.17,0.0068,'R$','SACA 60 KG','MT'),(18,'Soja','Norte do parana','2026-05-04 15:25:02',110.80,-0.0331,'R$','SACA 60 KG','PR'),(19,'Soja','Oeste do parana','2026-05-04 15:25:02',109.95,-0.0341,'R$','SACA 60 KG','PR'),(20,'Soja','Ponta grossa','2026-05-04 15:25:02',113.34,-0.0244,'R$','SACA 60 KG','PR'),(21,'Soja','Sudoeste do parana','2026-05-04 15:25:02',112.12,-0.0362,'R$','SACA 60 KG','PR'),(22,'Soja','Ijui','2026-05-04 15:25:02',114.42,-0.0430,'R$','SACA 60 KG','RS'),(23,'Soja','Passo fundo','2026-05-04 15:25:02',114.60,-0.0365,'R$','SACA 60 KG','RS'),(24,'Soja','Santa rosa','2026-05-04 15:25:02',113.70,-0.0435,'R$','SACA 60 KG','RS'),(25,'Soja','Campos novos','2026-05-04 15:25:02',116.80,-0.0199,'R$','SACA 60 KG','SC'),(26,'Soja','Mogiana','2026-05-04 15:25:02',109.40,-0.0208,'R$','SACA 60 KG','SP'),(27,'Soja','Sorocaba','2026-05-04 15:25:02',111.87,-0.0212,'R$','SACA 60 KG','SP'),(28,'Milho','Barreiras','2026-05-04 15:25:02',60.00,0.0000,'R$','SACA 60 KG','BA'),(29,'Milho','Rio verde','2026-05-04 15:25:02',54.71,-0.0710,'R$','SACA 60 KG','GO'),(30,'Milho','Triangulo mineiro','2026-05-04 15:25:02',53.78,0.0000,'R$','SACA 60 KG','MG'),(31,'Milho','Dourados','2026-05-04 15:25:02',44.82,-0.0817,'R$','SACA 60 KG','MS'),(32,'Milho','Sorriso','2026-05-04 15:25:02',41.08,0.0000,'R$','SACA 60 KG','MT'),(33,'Milho','Cascavel','2026-05-04 15:25:02',51.98,-0.0537,'R$','SACA 60 KG','PR'),(34,'Milho','Norte do parana','2026-05-04 15:25:02',52.06,-0.0662,'R$','SACA 60 KG','PR'),(35,'Milho','Sudoeste do parana','2026-05-04 15:25:02',56.10,-0.0395,'R$','SACA 60 KG','PR'),(36,'Milho','Ijui','2026-05-04 15:25:02',56.81,0.0074,'R$','SACA 60 KG','RS'),(37,'Milho','Passo fundo','2026-05-04 15:25:02',56.95,0.0021,'R$','SACA 60 KG','RS'),(38,'Milho','Chapeco','2026-05-04 15:25:02',56.77,-0.0321,'R$','SACA 60 KG','SC'),(39,'Milho','Mogiana','2026-05-04 15:25:02',57.54,-0.0033,'R$','SACA 60 KG','SP'),(40,'Milho','Sorocaba','2026-05-04 15:25:02',55.63,-0.0442,'R$','SACA 60 KG','SP'),(41,'Suíno','Ponte nova','2026-05-04 15:25:02',5.58,-0.1226,'R$','KG VIVO','MG'),(42,'Suíno','Sul de minas','2026-05-04 15:25:02',5.70,-0.1618,'R$','KG VIVO','MG'),(43,'Suíno','Arapoti','2026-05-04 15:25:02',5.25,-0.2164,'R$','KG VIVO','PR'),(44,'Suíno','Cascavel','2026-05-04 15:25:02',5.09,-0.2193,'R$','KG VIVO','PR'),(45,'Suíno','Erechim','2026-05-04 15:25:02',5.45,-0.2032,'R$','KG VIVO','RS'),(46,'Suíno','Vale do taquari','2026-05-04 15:25:02',5.25,-0.2369,'R$','KG VIVO','RS'),(47,'Suíno','Braco do norte','2026-05-04 15:25:02',4.94,-0.1669,'R$','KG VIVO','SC'),(48,'Suíno','Chapeco','2026-05-04 15:25:02',5.20,-0.1900,'R$','KG VIVO','SC'),(49,'Suíno','Avare','2026-05-04 15:25:02',6.17,-0.0464,'R$','KG VIVO','SP'),(50,'Suíno','Sao paulo','2026-05-04 15:25:02',5.40,-0.1831,'R$','KG VIVO','SP'),(51,'Laranja indústria','Bebedouro','2026-05-04 15:25:02',27.17,-0.2237,'R$','CX DE 40,8 KG','SP'),(52,'Laranja indústria','Limeira','2026-05-04 15:25:02',24.17,-0.1468,'R$','CX DE 40,8 KG','SP'),(53,'Laranja indústria','Sao paulo','2026-05-04 15:25:02',25.82,-0.1185,'R$','CX DE 40,8 KG','SP'),(54,'Laranja pera','Araraquara','2026-05-04 15:25:02',44.63,0.1158,'R$','CX DE 40,8 KG','SP'),(55,'Laranja pera','Bebedouro','2026-05-04 15:25:02',31.25,-0.3243,'R$','CX DE 40,8 KG','SP'),(56,'Laranja pera','Limeira','2026-05-04 15:25:02',45.00,-0.1000,'R$','CX DE 40,8 KG','SP'),(57,'Algodão','Barreiras','2026-05-04 15:25:02',3.91,0.0483,'R$','CENTS-LIBRA','BA'),(58,'Algodão','Triangulo mineiro','2026-05-04 15:25:02',4.23,0.0000,'R$','CENTS-LIBRA','MG'),(59,'Algodão','Chapadao do sul','2026-05-04 15:25:02',3.31,0.0000,'R$','CENTS-LIBRA','MS'),(60,'Algodão','Navirai','2026-05-04 15:25:02',3.93,0.0000,'R$','CENTS-LIBRA','MS'),(61,'Algodão','Campo novo do parecis','2026-05-04 15:25:02',3.92,0.0595,'R$','CENTS-LIBRA','MT'),(62,'Algodão','Primavera do leste','2026-05-04 15:25:02',3.97,0.0672,'R$','CENTS-LIBRA','MT'),(63,'Algodão','Rondonopolis','2026-05-04 15:25:02',3.80,0.0243,'R$','CENTS-LIBRA','MT'),(64,'Algodão','Sao paulo','2026-05-04 15:25:02',4.33,0.2887,'R$','CENTS-LIBRA','SP'),(65,'Açúcar','Alagoas','2026-05-04 15:25:02',149.11,0.0000,'R$','SACA 50 KG','AL'),(66,'Açúcar','Paraiba','2026-05-04 15:25:02',144.11,0.0000,'R$','SACA 50 KG','PB'),(67,'Açúcar','Pernambuco','2026-05-04 15:25:02',147.55,0.0000,'R$','SACA 50 KG','PE'),(68,'Açúcar','Assis','2026-05-04 15:25:02',100.00,0.0760,'R$','SACA 50 KG','SP'),(69,'Açúcar','Jau','2026-05-04 15:25:02',97.91,-0.0675,'R$','SACA 50 KG','SP'),(70,'Açúcar','Piracicaba','2026-05-04 15:25:02',100.50,-0.0032,'R$','SACA 50 KG','SP'),(71,'Açúcar','Ribeirao preto','2026-05-04 15:25:02',102.24,-0.0333,'R$','SACA 50 KG','SP'),(72,'Açúcar','Santos','2026-05-04 15:25:02',134.05,0.0000,'R$','SACA 50 KG','SP'),(73,'Boi gordo','Bahia','2026-05-04 15:25:02',327.08,0.0217,'R$','@','BA'),(74,'Boi gordo','Goiania','2026-05-04 15:25:02',327.25,-0.0084,'R$','@','GO'),(75,'Boi gordo','Rio verde','2026-05-04 15:25:02',331.64,0.0039,'R$','@','GO'),(76,'Boi gordo','Triangulo mineiro','2026-05-04 15:25:02',330.49,-0.0199,'R$','@','MG'),(77,'Boi gordo','Campo grande','2026-05-04 15:25:02',340.06,0.0039,'R$','@','MS'),(78,'Boi gordo','Dourados','2026-05-04 15:25:02',340.00,-0.0002,'R$','@','MS'),(79,'Boi gordo','Tres lagoas','2026-05-04 15:25:02',340.18,-0.0018,'R$','@','MS'),(80,'Boi gordo','Colider','2026-05-04 15:25:02',345.64,0.0202,'R$','@','MT'),(81,'Boi gordo','Cuiaba','2026-05-04 15:25:02',344.70,0.0101,'R$','@','MT'),(82,'Boi gordo','Para','2026-05-04 15:25:02',338.94,0.0257,'R$','@','PA'),(83,'Boi gordo','Noroeste do parana','2026-05-04 15:25:02',344.45,0.0047,'R$','@','PR'),(84,'Boi gordo','Rondonia','2026-05-04 15:25:02',323.57,0.0242,'R$','@','RO'),(85,'Boi gordo','Aracatuba','2026-05-04 15:25:02',350.00,-0.0106,'R$','@','SP'),(86,'Boi gordo','Sao jose do rio preto','2026-05-04 15:25:02',351.94,-0.0060,'R$','@','SP'),(87,'Boi gordo','Tocantins','2026-05-04 15:25:02',335.73,0.0184,'R$','@','TO'),(88,'Etanol anidro','Goias','2026-05-04 15:25:02',2.71,-0.1763,'R$','L','GO'),(89,'Etanol anidro','Sao paulo','2026-05-04 15:25:02',2.70,-0.1892,'R$','L','SP'),(90,'Etanol hidratado','Goias','2026-05-04 15:25:02',2.26,-0.2180,'R$','L','GO'),(91,'Etanol hidratado','Sao paulo','2026-05-04 15:25:02',2.32,-0.2136,'R$','L','SP'),(92,'Trigo','Ijui','2026-05-04 15:25:02',1268.31,0.0901,'R$','TON','RS'),(93,'Trigo','Passo fundo','2026-05-04 15:25:02',1241.95,0.0966,'R$','TON','RS'),(94,'Trigo','Rio grande do sul','2026-05-04 15:25:02',1260.96,0.0939,'R$','TON','RS'),(95,'Café arábica','Vitoria','2026-05-04 15:25:02',1676.00,-0.0515,'TIPO 6 (R$','SACA 60 KG)','ES'),(96,'Café arábica','Cerrado de minas','2026-05-04 15:25:02',1768.75,-0.0620,'TIPO 6 (R$','SACA 60 KG)','MG'),(97,'Café arábica','Sul de minas','2026-05-04 15:25:02',1751.08,-0.0742,'TIPO 6 (R$','SACA 60 KG)','MG'),(98,'Café arábica','Zona da mata','2026-05-04 15:25:02',1697.00,-0.0762,'TIPO 6 (R$','SACA 60 KG)','MG'),(99,'Café arábica','Noroeste do parana','2026-05-04 15:25:02',1642.33,-0.0875,'TIPO 6 (R$','SACA 60 KG)','PR'),(100,'Café arábica','Mogiana','2026-05-04 15:25:02',1769.14,-0.0529,'TIPO 6 (R$','SACA 60 KG)','SP'),(101,'Café robusta','Espirito santo','2026-05-04 15:25:02',925.63,-0.0419,'TIPO 6 (R$','SACA 60 KG)','ES'),(102,'Arroz','Campanha','2026-05-04 19:55:45',62.25,0.0168,'R$','SACA 50 KG','RS'),(103,'Arroz','Depressao central','2026-05-04 19:55:45',59.83,-0.0065,'R$','SACA 50 KG','RS'),(104,'Arroz','Fronteira oeste','2026-05-04 19:55:45',62.41,0.0081,'R$','SACA 50 KG','RS'),(105,'Arroz','Zona sul','2026-05-04 19:55:45',63.30,0.0040,'R$','SACA 50 KG','RS'),(106,'Trigo - tipo pão','Norte do parana','2026-05-04 19:55:45',1361.43,0.0483,'R$','TON','PR'),(107,'Trigo - tipo pão','Oeste do parana','2026-05-04 19:55:45',1305.53,0.0124,'R$','TON','PR'),(108,'Trigo - tipo pão','Parana','2026-05-04 19:55:45',1342.67,0.0449,'R$','TON','PR'),(109,'Trigo - tipo pão','Ponta grossa','2026-05-04 19:55:45',1362.53,0.0543,'R$','TON','PR'),(110,'Trigo - tipo pão','Sudoeste do parana','2026-05-04 19:55:45',1331.60,0.0638,'R$','TON','PR'),(111,'Soja','Barreiras','2026-05-04 19:55:45',110.85,0.0000,'R$','SACA 60 KG','BA'),(112,'Soja','Rio verde','2026-05-04 19:55:45',106.81,-0.0335,'R$','SACA 60 KG','GO'),(113,'Soja','Triangulo mineiro','2026-05-04 19:55:45',108.18,-0.0233,'R$','SACA 60 KG','MG'),(114,'Soja','Campo grande','2026-05-04 19:55:45',104.17,-0.0280,'R$','SACA 60 KG','MS'),(115,'Soja','Dourados','2026-05-04 19:55:45',104.43,-0.0366,'R$','SACA 60 KG','MS'),(116,'Soja','Maracaju','2026-05-04 19:55:45',104.34,-0.0279,'R$','SACA 60 KG','MS'),(117,'Soja','Primavera do leste','2026-05-04 19:55:45',103.59,0.0118,'R$','SACA 60 KG','MT'),(118,'Soja','Sorriso','2026-05-04 19:55:45',98.17,0.0068,'R$','SACA 60 KG','MT'),(119,'Soja','Norte do parana','2026-05-04 19:55:45',110.80,-0.0331,'R$','SACA 60 KG','PR'),(120,'Soja','Oeste do parana','2026-05-04 19:55:45',109.95,-0.0341,'R$','SACA 60 KG','PR'),(121,'Soja','Ponta grossa','2026-05-04 19:55:45',113.34,-0.0244,'R$','SACA 60 KG','PR'),(122,'Soja','Sudoeste do parana','2026-05-04 19:55:45',112.12,-0.0362,'R$','SACA 60 KG','PR'),(123,'Soja','Ijui','2026-05-04 19:55:45',114.42,-0.0430,'R$','SACA 60 KG','RS'),(124,'Soja','Passo fundo','2026-05-04 19:55:45',114.60,-0.0365,'R$','SACA 60 KG','RS'),(125,'Soja','Santa rosa','2026-05-04 19:55:45',113.70,-0.0435,'R$','SACA 60 KG','RS'),(126,'Soja','Campos novos','2026-05-04 19:55:45',116.80,-0.0199,'R$','SACA 60 KG','SC'),(127,'Soja','Mogiana','2026-05-04 19:55:45',109.40,-0.0208,'R$','SACA 60 KG','SP'),(128,'Soja','Sorocaba','2026-05-04 19:55:45',111.87,-0.0212,'R$','SACA 60 KG','SP'),(129,'Milho','Barreiras','2026-05-04 19:55:45',60.00,0.0000,'R$','SACA 60 KG','BA'),(130,'Milho','Rio verde','2026-05-04 19:55:45',54.71,-0.0710,'R$','SACA 60 KG','GO'),(131,'Milho','Triangulo mineiro','2026-05-04 19:55:45',53.78,0.0000,'R$','SACA 60 KG','MG'),(132,'Milho','Dourados','2026-05-04 19:55:45',44.82,-0.0817,'R$','SACA 60 KG','MS'),(133,'Milho','Sorriso','2026-05-04 19:55:45',41.08,0.0000,'R$','SACA 60 KG','MT'),(134,'Milho','Cascavel','2026-05-04 19:55:45',51.98,-0.0537,'R$','SACA 60 KG','PR'),(135,'Milho','Norte do parana','2026-05-04 19:55:45',52.06,-0.0662,'R$','SACA 60 KG','PR'),(136,'Milho','Sudoeste do parana','2026-05-04 19:55:45',56.10,-0.0395,'R$','SACA 60 KG','PR'),(137,'Milho','Ijui','2026-05-04 19:55:45',56.81,0.0074,'R$','SACA 60 KG','RS'),(138,'Milho','Passo fundo','2026-05-04 19:55:45',56.95,0.0021,'R$','SACA 60 KG','RS'),(139,'Milho','Chapeco','2026-05-04 19:55:45',56.77,-0.0321,'R$','SACA 60 KG','SC'),(140,'Milho','Mogiana','2026-05-04 19:55:45',57.54,-0.0033,'R$','SACA 60 KG','SP'),(141,'Milho','Sorocaba','2026-05-04 19:55:45',55.63,-0.0442,'R$','SACA 60 KG','SP'),(142,'Suíno','Ponte nova','2026-05-04 19:55:45',5.58,-0.1226,'R$','KG VIVO','MG'),(143,'Suíno','Sul de minas','2026-05-04 19:55:45',5.70,-0.1618,'R$','KG VIVO','MG'),(144,'Suíno','Arapoti','2026-05-04 19:55:45',5.25,-0.2164,'R$','KG VIVO','PR'),(145,'Suíno','Cascavel','2026-05-04 19:55:45',5.09,-0.2193,'R$','KG VIVO','PR'),(146,'Suíno','Erechim','2026-05-04 19:55:45',5.45,-0.2032,'R$','KG VIVO','RS'),(147,'Suíno','Vale do taquari','2026-05-04 19:55:45',5.25,-0.2369,'R$','KG VIVO','RS'),(148,'Suíno','Braco do norte','2026-05-04 19:55:45',4.94,-0.1669,'R$','KG VIVO','SC'),(149,'Suíno','Chapeco','2026-05-04 19:55:45',5.20,-0.1900,'R$','KG VIVO','SC'),(150,'Suíno','Avare','2026-05-04 19:55:45',6.17,-0.0464,'R$','KG VIVO','SP'),(151,'Suíno','Sao paulo','2026-05-04 19:55:45',5.40,-0.1831,'R$','KG VIVO','SP'),(152,'Laranja indústria','Bebedouro','2026-05-04 19:55:45',27.17,-0.2237,'R$','CX DE 40,8 KG','SP'),(153,'Laranja indústria','Limeira','2026-05-04 19:55:45',24.17,-0.1468,'R$','CX DE 40,8 KG','SP'),(154,'Laranja indústria','Sao paulo','2026-05-04 19:55:45',25.82,-0.1185,'R$','CX DE 40,8 KG','SP'),(155,'Laranja pera','Araraquara','2026-05-04 19:55:45',44.63,0.1158,'R$','CX DE 40,8 KG','SP'),(156,'Laranja pera','Bebedouro','2026-05-04 19:55:45',31.25,-0.3243,'R$','CX DE 40,8 KG','SP'),(157,'Laranja pera','Limeira','2026-05-04 19:55:45',45.00,-0.1000,'R$','CX DE 40,8 KG','SP'),(158,'Algodão','Barreiras','2026-05-04 19:55:45',3.91,0.0483,'R$','CENTS-LIBRA','BA'),(159,'Algodão','Triangulo mineiro','2026-05-04 19:55:45',4.23,0.0000,'R$','CENTS-LIBRA','MG'),(160,'Algodão','Chapadao do sul','2026-05-04 19:55:45',3.31,0.0000,'R$','CENTS-LIBRA','MS'),(161,'Algodão','Navirai','2026-05-04 19:55:45',3.93,0.0000,'R$','CENTS-LIBRA','MS'),(162,'Algodão','Campo novo do parecis','2026-05-04 19:55:45',3.92,0.0595,'R$','CENTS-LIBRA','MT'),(163,'Algodão','Primavera do leste','2026-05-04 19:55:45',3.97,0.0672,'R$','CENTS-LIBRA','MT'),(164,'Algodão','Rondonopolis','2026-05-04 19:55:45',3.80,0.0243,'R$','CENTS-LIBRA','MT'),(165,'Algodão','Sao paulo','2026-05-04 19:55:45',4.33,0.2887,'R$','CENTS-LIBRA','SP'),(166,'Açúcar','Alagoas','2026-05-04 19:55:45',149.11,0.0000,'R$','SACA 50 KG','AL'),(167,'Açúcar','Paraiba','2026-05-04 19:55:45',144.11,0.0000,'R$','SACA 50 KG','PB'),(168,'Açúcar','Pernambuco','2026-05-04 19:55:45',147.55,0.0000,'R$','SACA 50 KG','PE'),(169,'Açúcar','Assis','2026-05-04 19:55:45',100.00,0.0760,'R$','SACA 50 KG','SP'),(170,'Açúcar','Jau','2026-05-04 19:55:45',97.91,-0.0675,'R$','SACA 50 KG','SP'),(171,'Açúcar','Piracicaba','2026-05-04 19:55:45',100.50,-0.0032,'R$','SACA 50 KG','SP'),(172,'Açúcar','Ribeirao preto','2026-05-04 19:55:45',102.24,-0.0333,'R$','SACA 50 KG','SP'),(173,'Açúcar','Santos','2026-05-04 19:55:45',134.05,0.0000,'R$','SACA 50 KG','SP'),(174,'Boi gordo','Bahia','2026-05-04 19:55:45',327.08,0.0217,'R$','@','BA'),(175,'Boi gordo','Goiania','2026-05-04 19:55:45',327.25,-0.0084,'R$','@','GO'),(176,'Boi gordo','Rio verde','2026-05-04 19:55:45',331.64,0.0039,'R$','@','GO'),(177,'Boi gordo','Triangulo mineiro','2026-05-04 19:55:45',330.49,-0.0199,'R$','@','MG'),(178,'Boi gordo','Campo grande','2026-05-04 19:55:45',340.06,0.0039,'R$','@','MS'),(179,'Boi gordo','Dourados','2026-05-04 19:55:45',340.00,-0.0002,'R$','@','MS'),(180,'Boi gordo','Tres lagoas','2026-05-04 19:55:45',340.18,-0.0018,'R$','@','MS'),(181,'Boi gordo','Colider','2026-05-04 19:55:45',345.64,0.0202,'R$','@','MT'),(182,'Boi gordo','Cuiaba','2026-05-04 19:55:45',344.70,0.0101,'R$','@','MT'),(183,'Boi gordo','Para','2026-05-04 19:55:45',338.94,0.0257,'R$','@','PA'),(184,'Boi gordo','Noroeste do parana','2026-05-04 19:55:45',344.45,0.0047,'R$','@','PR'),(185,'Boi gordo','Rondonia','2026-05-04 19:55:45',323.57,0.0242,'R$','@','RO'),(186,'Boi gordo','Aracatuba','2026-05-04 19:55:45',350.00,-0.0106,'R$','@','SP'),(187,'Boi gordo','Sao jose do rio preto','2026-05-04 19:55:45',351.94,-0.0060,'R$','@','SP'),(188,'Boi gordo','Tocantins','2026-05-04 19:55:45',335.73,0.0184,'R$','@','TO'),(189,'Etanol anidro','Goias','2026-05-04 19:55:45',2.71,-0.1763,'R$','L','GO'),(190,'Etanol anidro','Sao paulo','2026-05-04 19:55:45',2.70,-0.1892,'R$','L','SP'),(191,'Etanol hidratado','Goias','2026-05-04 19:55:45',2.26,-0.2180,'R$','L','GO'),(192,'Etanol hidratado','Sao paulo','2026-05-04 19:55:45',2.32,-0.2136,'R$','L','SP'),(193,'Trigo','Ijui','2026-05-04 19:55:45',1268.31,0.0901,'R$','TON','RS'),(194,'Trigo','Passo fundo','2026-05-04 19:55:45',1241.95,0.0966,'R$','TON','RS'),(195,'Trigo','Rio grande do sul','2026-05-04 19:55:45',1260.96,0.0939,'R$','TON','RS'),(196,'Café arábica','Vitoria','2026-05-04 19:55:45',1676.00,-0.0515,'TIPO 6 (R$','SACA 60 KG)','ES'),(197,'Café arábica','Cerrado de minas','2026-05-04 19:55:45',1768.75,-0.0620,'TIPO 6 (R$','SACA 60 KG)','MG'),(198,'Café arábica','Sul de minas','2026-05-04 19:55:45',1751.08,-0.0742,'TIPO 6 (R$','SACA 60 KG)','MG'),(199,'Café arábica','Zona da mata','2026-05-04 19:55:45',1697.00,-0.0762,'TIPO 6 (R$','SACA 60 KG)','MG'),(200,'Café arábica','Noroeste do parana','2026-05-04 19:55:45',1642.33,-0.0875,'TIPO 6 (R$','SACA 60 KG)','PR'),(201,'Café arábica','Mogiana','2026-05-04 19:55:45',1769.14,-0.0529,'TIPO 6 (R$','SACA 60 KG)','SP'),(202,'Café robusta','Espirito santo','2026-05-04 19:55:45',925.63,-0.0419,'TIPO 6 (R$','SACA 60 KG)','ES');
/*!40000 ALTER TABLE `cotacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cultura`
--

DROP TABLE IF EXISTS `cultura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cultura` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `variedade` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cultura`
--

LOCK TABLES `cultura` WRITE;
/*!40000 ALTER TABLE `cultura` DISABLE KEYS */;
/*!40000 ALTER TABLE `cultura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque_insumos`
--

DROP TABLE IF EXISTS `estoque_insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_insumos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `propriedade_id` int DEFAULT NULL,
  `insumo_id` int DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insumo_id` (`insumo_id`),
  KEY `propriedade_id` (`propriedade_id`),
  CONSTRAINT `estoque_insumos_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  CONSTRAINT `estoque_insumos_ibfk_2` FOREIGN KEY (`propriedade_id`) REFERENCES `propriedade` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque_insumos`
--

LOCK TABLES `estoque_insumos` WRITE;
/*!40000 ALTER TABLE `estoque_insumos` DISABLE KEYS */;
/*!40000 ALTER TABLE `estoque_insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insumo`
--

DROP TABLE IF EXISTS `insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `insumo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `valor_por_dose` decimal(10,2) DEFAULT NULL,
  `produto_id` int DEFAULT NULL,
  `data_referencia` date DEFAULT (curdate()),
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `insumo_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumo`
--

LOCK TABLES `insumo` WRITE;
/*!40000 ALTER TABLE `insumo` DISABLE KEYS */;
/*!40000 ALTER TABLE `insumo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operacoes_agricolas`
--

DROP TABLE IF EXISTS `operacoes_agricolas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operacoes_agricolas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_operacao` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_operacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `insumo_id` int DEFAULT NULL,
  `safra_id` int DEFAULT NULL,
  `talhao_id` int DEFAULT NULL,
  `peao_id` int DEFAULT NULL,
  `quantidade_insumo` decimal(10,2) DEFAULT NULL,
  `custos_operacao` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insumo_id` (`insumo_id`),
  KEY `cultura_id` (`safra_id`),
  KEY `talhao_id` (`talhao_id`),
  KEY `peao_id` (`peao_id`),
  CONSTRAINT `FK_operacoes_agricolas_safra` FOREIGN KEY (`safra_id`) REFERENCES `safra` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_3` FOREIGN KEY (`talhao_id`) REFERENCES `talhoes` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_4` FOREIGN KEY (`peao_id`) REFERENCES `peao` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operacoes_agricolas`
--

LOCK TABLES `operacoes_agricolas` WRITE;
/*!40000 ALTER TABLE `operacoes_agricolas` DISABLE KEYS */;
/*!40000 ALTER TABLE `operacoes_agricolas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operacoes_financeiras`
--

DROP TABLE IF EXISTS `operacoes_financeiras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operacoes_financeiras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_operacao` enum('COMPRA','VENDA') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor_operacao` decimal(10,2) DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `produto_id` int DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  `safra_id` int DEFAULT NULL,
  `data` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `safra_id` (`safra_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `operacoes_financeiras_ibfk_1` FOREIGN KEY (`safra_id`) REFERENCES `safra` (`id`),
  CONSTRAINT `operacoes_financeiras_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operacoes_financeiras`
--

LOCK TABLES `operacoes_financeiras` WRITE;
/*!40000 ALTER TABLE `operacoes_financeiras` DISABLE KEYS */;
/*!40000 ALTER TABLE `operacoes_financeiras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peao`
--

DROP TABLE IF EXISTS `peao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cpf_cnpj` varchar(14) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(18) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `senha` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `proprietario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietario_id` (`proprietario_id`),
  CONSTRAINT `peao_ibfk_1` FOREIGN KEY (`proprietario_id`) REFERENCES `proprietario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peao`
--

LOCK TABLES `peao` WRITE;
/*!40000 ALTER TABLE `peao` DISABLE KEYS */;
INSERT INTO `peao` VALUES (1,'teste','12312312312','32323232323','peao@email.com','$2y$10$K7/8nonNL3Yr1SYzuZ2QbOHT8umAy3.RHBpgpSt5L793wYxV.2Bey',2);
/*!40000 ALTER TABLE `peao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produto`
--

DROP TABLE IF EXISTS `produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `marca` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unidade_medida` varchar(55) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produto`
--

LOCK TABLES `produto` WRITE;
/*!40000 ALTER TABLE `produto` DISABLE KEYS */;
INSERT INTO `produto` VALUES (1,'RANDAPE','ROUNDUP','L','TESTANDO PRA VER SE MATA BUVA','Outro');
/*!40000 ALTER TABLE `produto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `propriedade`
--

DROP TABLE IF EXISTS `propriedade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `propriedade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `localizacao` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `municipio` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_total` decimal(10,2) DEFAULT NULL,
  `area_produtiva` decimal(10,2) DEFAULT NULL,
  `proprietario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietario_id` (`proprietario_id`),
  CONSTRAINT `propriedade_ibfk_1` FOREIGN KEY (`proprietario_id`) REFERENCES `proprietario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `propriedade`
--

LOCK TABLES `propriedade` WRITE;
/*!40000 ALTER TABLE `propriedade` DISABLE KEYS */;
INSERT INTO `propriedade` VALUES (1,'Terras Largas','','Capinzal','RS',54.16,48.78,2);
/*!40000 ALTER TABLE `propriedade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proprietario`
--

DROP TABLE IF EXISTS `proprietario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proprietario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cpf_cnpj` varchar(14) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(18) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `senha` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proprietario`
--

LOCK TABLES `proprietario` WRITE;
/*!40000 ALTER TABLE `proprietario` DISABLE KEYS */;
INSERT INTO `proprietario` VALUES (2,'Roger','12312312312','5454545454','roger@gmail.com','$2y$10$xcNBGCdHpRiWqFDYQyw81u2z4cAzKGafv.6otKiAKnn5Cy3shxMiG');
/*!40000 ALTER TABLE `proprietario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safra`
--

DROP TABLE IF EXISTS `safra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `safra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_inicio` date DEFAULT (curdate()),
  `data_fim` date DEFAULT (curdate()),
  `talhao_id` int DEFAULT NULL,
  `cultura_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `talhao_id` (`talhao_id`),
  KEY `cultura_id` (`cultura_id`),
  CONSTRAINT `safra_ibfk_1` FOREIGN KEY (`talhao_id`) REFERENCES `talhoes` (`id`),
  CONSTRAINT `safra_ibfk_2` FOREIGN KEY (`cultura_id`) REFERENCES `cultura` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safra`
--

LOCK TABLES `safra` WRITE;
/*!40000 ALTER TABLE `safra` DISABLE KEYS */;
/*!40000 ALTER TABLE `safra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `silo`
--

DROP TABLE IF EXISTS `silo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `silo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `propriedade_id` int NOT NULL,
  `cultura_id` int NOT NULL,
  `quantidade_kg` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `fk_silo_propriedade` (`propriedade_id`),
  KEY `fk_silo_cultura` (`cultura_id`),
  CONSTRAINT `fk_silo_cultura` FOREIGN KEY (`cultura_id`) REFERENCES `cultura` (`id`),
  CONSTRAINT `fk_silo_propriedade` FOREIGN KEY (`propriedade_id`) REFERENCES `propriedade` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `silo`
--

LOCK TABLES `silo` WRITE;
/*!40000 ALTER TABLE `silo` DISABLE KEYS */;
/*!40000 ALTER TABLE `silo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `talhoes`
--

DROP TABLE IF EXISTS `talhoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `talhoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_hectare` decimal(10,2) DEFAULT NULL,
  `coordenadas_json` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `propriedade_id` int DEFAULT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `propriedade_id` (`propriedade_id`),
  CONSTRAINT `talhoes_ibfk_1` FOREIGN KEY (`propriedade_id`) REFERENCES `propriedade` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `talhoes`
--

LOCK TABLES `talhoes` WRITE;
/*!40000 ALTER TABLE `talhoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `talhoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'safrawise'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-06 20:23:21
