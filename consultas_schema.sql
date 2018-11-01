-- MySQL dump 10.16  Distrib 10.1.21-MariaDB, for Win32 (AMD64)
--
-- Host: 10.91.0.163    Database: 10.91.0.163
-- ------------------------------------------------------
-- Server version	10.1.19-MariaDB

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
-- Table structure for table `arquivos`
--

DROP TABLE IF EXISTS `arquivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arquivos` (
  `nome` varchar(255) COLLATE latin1_bin DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_etapa` int(11) DEFAULT NULL,
  `url` mediumtext COLLATE latin1_bin,
  `atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `autor` mediumtext COLLATE latin1_bin NOT NULL,
  `descricao` mediumtext COLLATE latin1_bin,
  `posicao` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_arquivos_etapas_idx` (`id_etapa`),
  CONSTRAINT `fk_arquivos_etapas` FOREIGN KEY (`id_etapa`) REFERENCES `etapas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultas`
--

DROP TABLE IF EXISTS `consultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultas` (
  `id_consulta` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `data_cadastro` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL,
  `nome_publico` varchar(200) NOT NULL,
  `data_final` date DEFAULT NULL,
  `texto_intro` text CHARACTER SET latin1 NOT NULL,
  `url_consulta` text CHARACTER SET latin1 NOT NULL,
  `url_capa` text CHARACTER SET latin1 NOT NULL,
  `url_devolutiva` text CHARACTER SET latin1,
  PRIMARY KEY (`id_consulta`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etapas`
--

DROP TABLE IF EXISTS `etapas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `etapas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE latin1_bin DEFAULT NULL,
  `fk_projeto` int(11) DEFAULT NULL,
  `slug` varchar(255) COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_etapas_projetos_idx` (`fk_projeto`),
  CONSTRAINT `fk_etapas_projetos` FOREIGN KEY (`fk_projeto`) REFERENCES `projetos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `members` (
  `memid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `content` text NOT NULL,
  `commentdate` datetime NOT NULL,
  `public` tinyint(1) NOT NULL,
  `postid` int(11) NOT NULL,
  `trash` tinyint(1) NOT NULL,
  `commentid` int(11) NOT NULL,
  `commentcontext` text NOT NULL,
  `id_consulta` int(11) NOT NULL,
  PRIMARY KEY (`memid`),
  KEY `fk_consulta_id` (`id_consulta`),
  CONSTRAINT `fk_consulta_id` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id_consulta`)
) ENGINE=InnoDB AUTO_INCREMENT=3138 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projetos`
--

DROP TABLE IF EXISTS `projetos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projetos` (
  `nome` varchar(500) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativo` tinyint(4) DEFAULT '1',
  `atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projetos_arquivos`
--

DROP TABLE IF EXISTS `projetos_arquivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projetos_arquivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_projeto` int(11) DEFAULT NULL,
  `fk_arquivo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_projetos_arquivos_arquivos_idx` (`fk_arquivo`),
  KEY `fk_projetos_arquivos_projetos_idx` (`fk_projeto`),
  CONSTRAINT `projetos_arquivos_arquivos` FOREIGN KEY (`fk_arquivo`) REFERENCES `arquivos` (`id`),
  CONSTRAINT `projetos_arquivos_projetos` FOREIGN KEY (`fk_projeto`) REFERENCES `projetos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projetos_consultas`
--

DROP TABLE IF EXISTS `projetos_consultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projetos_consultas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_projeto` int(11) DEFAULT NULL,
  `fk_consulta` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_projetos_consultas_consulta_idx` (`fk_consulta`),
  KEY `fk_projetos_consultas_projetos_idx` (`fk_projeto`),
  CONSTRAINT `fk_projetos_consultas_consulta` FOREIGN KEY (`fk_consulta`) REFERENCES `consultas` (`id_consulta`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_projetos_consultas_projetos` FOREIGN KEY (`fk_projeto`) REFERENCES `projetos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projetos_urls`
--

DROP TABLE IF EXISTS `projetos_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projetos_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_projeto` int(11) DEFAULT NULL,
  `fk_url` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_projetos_urls_projeto` (`fk_projeto`),
  KEY `fk_projetos_urls_url` (`fk_url`),
  CONSTRAINT `fk_projetos_urls_projeto` FOREIGN KEY (`fk_projeto`) REFERENCES `projetos` (`id`),
  CONSTRAINT `fk_projetos_urls_url` FOREIGN KEY (`fk_url`) REFERENCES `urls` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projetos_usuarios`
--

DROP TABLE IF EXISTS `projetos_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projetos_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_projeto` int(11) DEFAULT NULL,
  `fk_usuarios` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_projetos_usuarios_projetos_idx` (`fk_projeto`),
  KEY `fk_projetos_usuarios_usuarios_idx` (`fk_usuarios`),
  CONSTRAINT `fk_projetos_usuarios_projetos` FOREIGN KEY (`fk_projeto`) REFERENCES `projetos` (`id`),
  CONSTRAINT `fk_projetos_usuarios_usuarios` FOREIGN KEY (`fk_usuarios`) REFERENCES `usuarios` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `urls`
--

DROP TABLE IF EXISTS `urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(2083) COLLATE latin1_bin DEFAULT NULL,
  `extensao` varchar(20) COLLATE latin1_bin DEFAULT NULL,
  `id_arquivo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arquivos` (`id_arquivo`),
  CONSTRAINT `arquivos` FOREIGN KEY (`id_arquivo`) REFERENCES `arquivos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(50) NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Organizacao` varchar(255) DEFAULT NULL,
  `CEP` varchar(50) DEFAULT NULL,
  `RegioesDeInteresse` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ProjetosDeInteresse` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='Usuários cadastrados no Gestão Participativa - cadastro para envio de novidades acerca dos processos participativos.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-11-01 16:59:02
