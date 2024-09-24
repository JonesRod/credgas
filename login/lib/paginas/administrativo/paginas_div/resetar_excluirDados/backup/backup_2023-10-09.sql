-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: associacao_40ribas
-- ------------------------------------------------------
-- Server version	8.0.31

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
-- Table structure for table `config_admin`
--

DROP TABLE IF EXISTS `config_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_admin` (
  `id` int NOT NULL,
  `id_admin` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `data_alteracao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `razao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uf` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rua` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bairro` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `presidente` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vice_presidente` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_tesoureiro` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_suporte` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idade_minima` int NOT NULL,
  `termos_insc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estatuto_int` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `validade_insc` int NOT NULL,
  `reg_int` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia_fecha_mes` int NOT NULL,
  `valor_mensalidades` int NOT NULL,
  `desconto_mensalidades` int NOT NULL,
  `multa` int NOT NULL,
  `joia` int NOT NULL,
  `parcela_joia` int NOT NULL,
  `meses_vence3` int NOT NULL,
  `meses_vence5` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_admin`
--

LOCK TABLES `config_admin` WRITE;
/*!40000 ALTER TABLE `config_admin` DISABLE KEYS */;
INSERT INTO `config_admin` VALUES (1,'1','2023-10-09 08:23:06','','','','','','','','','','','','apelido','','',0,'','',0,'',0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `config_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_votacao`
--

DROP TABLE IF EXISTS `em_votacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_votacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_socio` int NOT NULL,
  `id_inscrito` int NOT NULL,
  `admin` varchar(20) NOT NULL,
  `voto` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_votacao`
--

LOCK TABLES `em_votacao` WRITE;
/*!40000 ALTER TABLE `em_votacao` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_votacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_config_admin`
--

DROP TABLE IF EXISTS `historico_config_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_config_admin` (
  `id` int NOT NULL,
  `id_admin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `data_alteracao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `razao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(20) NOT NULL,
  `uf` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(20) NOT NULL,
  `cid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rua` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bairro` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `presidente` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vice_presidente` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_tesoureiro` varchar(150) NOT NULL,
  `email_suporte` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idade_minima` int NOT NULL,
  `termos_insc` text NOT NULL,
  `estatuto_int` text NOT NULL,
  `validade_insc` int NOT NULL,
  `reg_int` text NOT NULL,
  `dia_fecha_mes` int NOT NULL,
  `valor_mensalidades` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `desconto_mensalidades` int NOT NULL,
  `multa` int NOT NULL,
  `joia` int NOT NULL,
  `parcela_joia` int NOT NULL,
  `meses_vence3` int NOT NULL,
  `meses_vence5` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_config_admin`
--

LOCK TABLES `historico_config_admin` WRITE;
/*!40000 ALTER TABLE `historico_config_admin` DISABLE KEYS */;
INSERT INTO `historico_config_admin` VALUES (1,'1','2023-10-09 08:23:06','','','','','','','','','','','','apelido','','',0,'','',0,'',0,'',0,0,0,0,0,0);
/*!40000 ALTER TABLE `historico_config_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_joias_receber`
--

DROP TABLE IF EXISTS `historico_joias_receber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_joias_receber` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `admin` varchar(100) NOT NULL,
  `id_socio` int NOT NULL,
  `apelido` varchar(100) NOT NULL,
  `nome_completo` varchar(150) NOT NULL,
  `celular1` varchar(50) NOT NULL,
  `celular2` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `valor` varchar(15) NOT NULL,
  `entrada` int NOT NULL,
  `restante` int NOT NULL,
  `num_parcela` int NOT NULL,
  `qt_parcelas` int NOT NULL,
  `valor_parcelas` int NOT NULL,
  `vencimento` date NOT NULL,
  `desconto_parcela` int NOT NULL,
  `recebido` int NOT NULL,
  `data_recebeu` date NOT NULL,
  `a_receber` varchar(15) NOT NULL,
  `status_pagamento` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_joias_receber`
--

LOCK TABLES `historico_joias_receber` WRITE;
/*!40000 ALTER TABLE `historico_joias_receber` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_joias_receber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_mensalidades`
--

DROP TABLE IF EXISTS `historico_mensalidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_mensalidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_socio` int NOT NULL,
  `data` date NOT NULL,
  `admin` varchar(50) NOT NULL,
  `apelido` varchar(20) NOT NULL,
  `nome_completo` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL,
  `mensalidade_dia` int NOT NULL,
  `mensalidade_mes` int NOT NULL,
  `mensalidade_ano` int NOT NULL,
  `valor_mensalidade` int NOT NULL,
  `data_vencimento` date NOT NULL,
  `desconto_mensalidade` int NOT NULL,
  `multa_mensalidade` int NOT NULL,
  `valor_receber` int NOT NULL,
  `valor_recebido` int NOT NULL,
  `data_recebida` date NOT NULL,
  `restante` int NOT NULL,
  `status_pagamento` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_mensalidades`
--

LOCK TABLES `historico_mensalidades` WRITE;
/*!40000 ALTER TABLE `historico_mensalidades` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_mensalidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `int_associar`
--

DROP TABLE IF EXISTS `int_associar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `int_associar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `foto` varchar(150) NOT NULL,
  `apelido` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nome_completo` varchar(50) NOT NULL,
  `cpf` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rg` varchar(15) NOT NULL,
  `nascimento` date NOT NULL,
  `uf` varchar(100) NOT NULL,
  `cid_natal` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `mae` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pai` varchar(100) NOT NULL,
  `sexo` varchar(15) NOT NULL,
  `uf_atual` varchar(100) NOT NULL,
  `cep` varchar(15) NOT NULL,
  `cid_atual` varchar(100) NOT NULL,
  `endereco` varchar(100) NOT NULL,
  `nu` varchar(15) NOT NULL,
  `bairro` varchar(50) NOT NULL,
  `celular1` varchar(15) NOT NULL,
  `celular2` varchar(15) NOT NULL,
  `email` varchar(140) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `motivo` varchar(1000) NOT NULL,
  `termos` varchar(10000) NOT NULL,
  `validade` date NOT NULL,
  `status` varchar(10) NOT NULL,
  `observacao` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `em_votacao` varchar(5) NOT NULL,
  `admin` varchar(150) NOT NULL,
  `inicio_votacao` date NOT NULL,
  `inicio_hora` time NOT NULL,
  `fim_votacao` date NOT NULL,
  `fim_hora` time NOT NULL,
  `voto_sim` int NOT NULL,
  `voto_nao` int NOT NULL,
  `resultado` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `int_associar`
--

LOCK TABLES `int_associar` WRITE;
/*!40000 ALTER TABLE `int_associar` DISABLE KEYS */;
/*!40000 ALTER TABLE `int_associar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `joias_receber`
--

DROP TABLE IF EXISTS `joias_receber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `joias_receber` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `admin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_socio` int NOT NULL,
  `apelido` varchar(100) NOT NULL,
  `nome_completo` varchar(150) NOT NULL,
  `celular1` varchar(50) NOT NULL,
  `celular2` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `valor` varchar(15) NOT NULL,
  `entrada` int NOT NULL,
  `restante` int NOT NULL,
  `num_parcela` int NOT NULL,
  `qt_parcelas` int NOT NULL,
  `valor_parcelas` int NOT NULL,
  `vencimento` date NOT NULL,
  `desconto_parcela` int NOT NULL,
  `recebido` int NOT NULL,
  `data_recebeu` date NOT NULL,
  `a_receber` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `joias_receber`
--

LOCK TABLES `joias_receber` WRITE;
/*!40000 ALTER TABLE `joias_receber` DISABLE KEYS */;
/*!40000 ALTER TABLE `joias_receber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensalidades`
--

DROP TABLE IF EXISTS `mensalidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensalidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_socio` int NOT NULL,
  `data` date NOT NULL,
  `admin` varchar(50) NOT NULL,
  `apelido` varchar(50) NOT NULL,
  `nome_completo` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL,
  `mensalidade_dia` int NOT NULL,
  `mensalidade_mes` int NOT NULL,
  `mensalidade_ano` int NOT NULL,
  `valor_mensalidade` int NOT NULL,
  `data_vencimento` date NOT NULL,
  `desconto_mensalidade` int NOT NULL,
  `multa_mensalidade` int NOT NULL,
  `valor_receber` int NOT NULL,
  `valor_recebido` int NOT NULL,
  `data_recebida` date NOT NULL,
  `restante` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensalidades`
--

LOCK TABLES `mensalidades` WRITE;
/*!40000 ALTER TABLE `mensalidades` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensalidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensalidades_geradas`
--

DROP TABLE IF EXISTS `mensalidades_geradas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensalidades_geradas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `admin` varchar(50) NOT NULL,
  `mensalidade_dia` int NOT NULL,
  `mensalidade_mes` int NOT NULL,
  `mensalidade_ano` int NOT NULL,
  `data_vencimento` date NOT NULL,
  `valor` int NOT NULL,
  `desconto` int NOT NULL,
  `multa` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensalidades_geradas`
--

LOCK TABLES `mensalidades_geradas` WRITE;
/*!40000 ALTER TABLE `mensalidades_geradas` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensalidades_geradas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socios`
--

DROP TABLE IF EXISTS `socios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `socios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `foto` varchar(150) NOT NULL,
  `apelido` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nome_completo` varchar(50) NOT NULL,
  `cpf` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rg` varchar(15) NOT NULL,
  `nascimento` date NOT NULL,
  `uf` varchar(100) NOT NULL,
  `cid_natal` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `mae` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pai` varchar(100) NOT NULL,
  `sexo` varchar(15) NOT NULL,
  `uf_atual` varchar(100) NOT NULL,
  `cep` varchar(20) NOT NULL,
  `cid_atual` varchar(100) NOT NULL,
  `endereco` varchar(100) NOT NULL,
  `numero` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `bairro` varchar(50) NOT NULL,
  `celular1` varchar(15) NOT NULL,
  `celular2` varchar(15) NOT NULL,
  `email` varchar(140) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `senha` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `motivo` varchar(1000) NOT NULL,
  `termos` varchar(10000) NOT NULL,
  `observacao` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `joia` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socios`
--

LOCK TABLES `socios` WRITE;
/*!40000 ALTER TABLE `socios` DISABLE KEYS */;
INSERT INTO `socios` VALUES (1,'2023-10-09',1,'','jones','','','','0000-00-00','','','','','','','','','','','','','','batata_jonesrodrigues@hotmail.com','$2y$10$BWBxLoZuppPsNl6xnWqFsefnMjP4lG909RRhOKKTel5Tpseu89RI2','ATIVO','','','',0);
/*!40000 ALTER TABLE `socios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-10-09  8:33:07
