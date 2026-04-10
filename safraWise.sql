-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: farmcontrol_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cotacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto` varchar(100) DEFAULT NULL,
  `praca` varchar(100) DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `variacao_mensal` decimal(10,4) DEFAULT NULL,
  `moeda` varchar(15) DEFAULT NULL,
  `unidade` varchar(55) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotacoes`
--

LOCK TABLES `cotacoes` WRITE;
/*!40000 ALTER TABLE `cotacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `cotacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cultura`
--

DROP TABLE IF EXISTS `cultura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cultura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `variedade` varchar(155) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estoque_insumos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `propriedade_id` int(11) DEFAULT NULL,
  `insumo_id` int(11) DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insumo_id` (`insumo_id`),
  KEY `propriedade_id` (`propriedade_id`),
  CONSTRAINT `estoque_insumos_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  CONSTRAINT `estoque_insumos_ibfk_2` FOREIGN KEY (`propriedade_id`) REFERENCES `propriedade` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insumo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor_por_dose` decimal(10,2) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `insumo_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operacoes_agricolas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_operacao` varchar(155) DEFAULT NULL,
  `data_operacao` datetime DEFAULT current_timestamp(),
  `descricao` varchar(255) DEFAULT NULL,
  `insumo_id` int(11) DEFAULT NULL,
  `safra_id` int(11) DEFAULT NULL,
  `talhao_id` int(11) DEFAULT NULL,
  `peao_id` int(11) DEFAULT NULL,
  `quantidade_insumo` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insumo_id` (`insumo_id`),
  KEY `cultura_id` (`safra_id`),
  KEY `talhao_id` (`talhao_id`),
  KEY `peao_id` (`peao_id`),
  CONSTRAINT `operacoes_agricolas_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_2` FOREIGN KEY (`safra_id`) REFERENCES `cultura` (`id`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operacoes_financeiras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_operacao` enum('COMPRA','VENDA') DEFAULT NULL,
  `valor_operacao` decimal(10,2) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  `safra_id` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `cpf_cnpj` varchar(14) DEFAULT NULL,
  `telefone` varchar(18) DEFAULT NULL,
  `email` varchar(155) DEFAULT NULL,
  `senha` varchar(155) DEFAULT NULL,
  `proprietario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietario_id` (`proprietario_id`),
  CONSTRAINT `peao_ibfk_1` FOREIGN KEY (`proprietario_id`) REFERENCES `proprietario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peao`
--

LOCK TABLES `peao` WRITE;
/*!40000 ALTER TABLE `peao` DISABLE KEYS */;
/*!40000 ALTER TABLE `peao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produto`
--

DROP TABLE IF EXISTS `produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `marca` varchar(155) DEFAULT NULL,
  `unidade_medida` varchar(55) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produto`
--

LOCK TABLES `produto` WRITE;
/*!40000 ALTER TABLE `produto` DISABLE KEYS */;
/*!40000 ALTER TABLE `produto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `propriedade`
--

DROP TABLE IF EXISTS `propriedade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `propriedade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `localizacao` varchar(155) DEFAULT NULL,
  `municipio` varchar(155) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `area_total` decimal(10,2) DEFAULT NULL,
  `area_produtiva` decimal(10,2) DEFAULT NULL,
  `proprietario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietario_id` (`proprietario_id`),
  CONSTRAINT `propriedade_ibfk_1` FOREIGN KEY (`proprietario_id`) REFERENCES `proprietario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `propriedade`
--

LOCK TABLES `propriedade` WRITE;
/*!40000 ALTER TABLE `propriedade` DISABLE KEYS */;
/*!40000 ALTER TABLE `propriedade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proprietario`
--

DROP TABLE IF EXISTS `proprietario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proprietario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `cpf_cnpj` varchar(14) DEFAULT NULL,
  `telefone` varchar(18) DEFAULT NULL,
  `email` varchar(155) DEFAULT NULL,
  `senha` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proprietario`
--

LOCK TABLES `proprietario` WRITE;
/*!40000 ALTER TABLE `proprietario` DISABLE KEYS */;
/*!40000 ALTER TABLE `proprietario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safra`
--

DROP TABLE IF EXISTS `safra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_inicio` date DEFAULT curdate(),
  `data_fim` date DEFAULT NULL,
  `talhao_id` int(11) DEFAULT NULL,
  `cultura_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `talhao_id` (`talhao_id`),
  KEY `cultura_id` (`cultura_id`),
  CONSTRAINT `safra_ibfk_1` FOREIGN KEY (`talhao_id`) REFERENCES `talhoes` (`id`),
  CONSTRAINT `safra_ibfk_2` FOREIGN KEY (`cultura_id`) REFERENCES `cultura` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safra`
--

LOCK TABLES `safra` WRITE;
/*!40000 ALTER TABLE `safra` DISABLE KEYS */;
/*!40000 ALTER TABLE `safra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `talhoes`
--

DROP TABLE IF EXISTS `talhoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `talhoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `area_hectare` decimal(10,2) DEFAULT NULL,
  `coordenadas_json` varchar(255) DEFAULT NULL,
  `propriedade_id` int(11) DEFAULT NULL,
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
-- Dumping routines for database 'farmcontrol_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-09 20:57:15
