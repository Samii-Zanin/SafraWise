-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para safrawise
-- CREATE DATABASE IF NOT EXISTS `safrawise` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `teste`;

-- Copiando estrutura para tabela safrawise.cotacoes
CREATE TABLE IF NOT EXISTS `cotacoes` (
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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.cultura
CREATE TABLE IF NOT EXISTS `cultura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `variedade` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.estoque_insumos
CREATE TABLE IF NOT EXISTS `estoque_insumos` (
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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.insumo
CREATE TABLE IF NOT EXISTS `insumo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor_por_dose` decimal(10,2) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `insumo_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.operacoes_agricolas
CREATE TABLE IF NOT EXISTS `operacoes_agricolas` (
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
  CONSTRAINT `FK_operacoes_agricolas_safra` FOREIGN KEY (`safra_id`) REFERENCES `safra` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_3` FOREIGN KEY (`talhao_id`) REFERENCES `talhoes` (`id`),
  CONSTRAINT `operacoes_agricolas_ibfk_4` FOREIGN KEY (`peao_id`) REFERENCES `peao` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.operacoes_financeiras
CREATE TABLE IF NOT EXISTS `operacoes_financeiras` (
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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.peao
CREATE TABLE IF NOT EXISTS `peao` (
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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.produto
CREATE TABLE IF NOT EXISTS `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `marca` varchar(155) DEFAULT NULL,
  `unidade_medida` varchar(55) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.propriedade
CREATE TABLE IF NOT EXISTS `propriedade` (
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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.proprietario
CREATE TABLE IF NOT EXISTS `proprietario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `cpf_cnpj` varchar(14) DEFAULT NULL,
  `telefone` varchar(18) DEFAULT NULL,
  `email` varchar(155) DEFAULT NULL,
  `senha` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.safra
CREATE TABLE IF NOT EXISTS `safra` (
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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela safrawise.talhoes
CREATE TABLE IF NOT EXISTS `talhoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) DEFAULT NULL,
  `area_hectare` decimal(10,2) DEFAULT NULL,
  `coordenadas_json` varchar(255) DEFAULT NULL,
  `propriedade_id` int(11) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `propriedade_id` (`propriedade_id`),
  CONSTRAINT `talhoes_ibfk_1` FOREIGN KEY (`propriedade_id`) REFERENCES `propriedade` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
