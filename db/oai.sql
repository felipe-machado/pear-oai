-- MySQL dump 10.13  Distrib 5.1.73, for apple-darwin10.3.0 (i386)
--
-- Host: localhost    Database: oai
-- ------------------------------------------------------
-- Server version	5.1.73

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
-- Table structure for table `cmdi`
--

DROP TABLE IF EXISTS `cmdi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cmdi` (
  `cmdi_identifier` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cmdi_record` mediumtext CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`cmdi_identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oai`
--

DROP TABLE IF EXISTS `oai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oai` (
  `oai_datestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `oai_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `oai_sets` varchar(255) NOT NULL DEFAULT '',
  `oai_setname` varchar(255) NOT NULL,
  `dc_identifier` varchar(255) NOT NULL DEFAULT '',
  `dc_title` text NOT NULL,
  `dc_creator` text NOT NULL,
  `dc_subject` varchar(255) NOT NULL DEFAULT '',
  `dc_description` text NOT NULL,
  `dc_publisher` varchar(255) NOT NULL DEFAULT '',
  `dc_contributor` varchar(255) NOT NULL DEFAULT '',
  `dc_type` varchar(255) NOT NULL DEFAULT '',
  `dc_format` varchar(255) NOT NULL DEFAULT '',
  `dc_source` varchar(255) NOT NULL DEFAULT '',
  `dc_language` varchar(255) NOT NULL DEFAULT '',
  `dc_relation` varchar(255) NOT NULL DEFAULT '',
  `dc_coverage` varchar(255) NOT NULL DEFAULT '',
  `dc_rights` varchar(255) NOT NULL DEFAULT '',
  `dc_date` varchar(100) NOT NULL,
  `cmdi_identifier` varchar(255) CHARACTER SET utf8 NOT NULL,
  UNIQUE KEY `dc_identifier` (`dc_identifier`),
  UNIQUE KEY `cmdi_identifier` (`cmdi_identifier`),
  KEY `oai_sets` (`oai_sets`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oai_token`
--

DROP TABLE IF EXISTS `oai_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oai_token` (
  `id` char(32) NOT NULL,
  `expiry` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='This table stores the resumption token sessions.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-11 14:01:46
