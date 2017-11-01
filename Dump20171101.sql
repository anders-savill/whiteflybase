-- MySQL dump 10.13  Distrib 5.7.9, for Win64 (x86_64)
--
-- Host: localhost    Database: whiteflydb
-- ------------------------------------------------------
-- Server version	5.5.47-MariaDB

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
-- Table structure for table `HISEQ`
--

DROP TABLE IF EXISTS `HISEQ`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `HISEQ` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GBID` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=626 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `INSTITUTION`
--

DROP TABLE IF EXISTS `INSTITUTION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `INSTITUTION` (
  `ID` int(11) NOT NULL,
  `NAME` varchar(10000) CHARACTER SET latin1 NOT NULL,
  `REGION` varchar(10000) CHARACTER SET latin1 NOT NULL,
  `COUNTRY` varchar(10000) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MT_GLOBAL_QA`
--

DROP TABLE IF EXISTS `MT_GLOBAL_QA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MT_GLOBAL_QA` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SUB_NAME` varchar(255) DEFAULT NULL,
  `GENBANK_ID` varchar(255) DEFAULT NULL,
  `MT_SEQUENCE` varchar(7000) DEFAULT NULL,
  `SOURCEDB` varchar(255) DEFAULT NULL,
  `SEQ_MATCH` int(11) DEFAULT NULL,
  `SEQ_MATCH_PC` decimal(5,4) DEFAULT NULL,
  `SEQ_MATCH_START` int(11) DEFAULT NULL,
  `SEQ_MATCH_END` int(11) DEFAULT NULL,
  `QUE_START` int(11) DEFAULT NULL,
  `QUE_END` int(11) DEFAULT NULL,
  `GLOBAL_POSITION` int(11) DEFAULT NULL,
  `OUTGROUP` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=545 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MT_LIB_SEQ_LINK`
--

DROP TABLE IF EXISTS `MT_LIB_SEQ_LINK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MT_LIB_SEQ_LINK` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MT_SEQ_LIB_ID` int(11) NOT NULL,
  `MT_SEQ_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MT_SEQUENCE`
--

DROP TABLE IF EXISTS `MT_SEQUENCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MT_SEQUENCE` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SUB_NAME` varchar(100) CHARACTER SET latin1 NOT NULL,
  `GENBANK_ID` varchar(700) CHARACTER SET latin1 DEFAULT NULL,
  `LOC1` varchar(7000) CHARACTER SET latin1 NOT NULL,
  `LOC2` varchar(7000) CHARACTER SET latin1 NOT NULL,
  `LOC3` varchar(7000) CHARACTER SET latin1 NOT NULL,
  `GPS` varchar(7000) CHARACTER SET latin1 DEFAULT NULL,
  `MT_SEQUENCE` varchar(20000) CHARACTER SET latin1 NOT NULL,
  `OWNER` int(11) NOT NULL DEFAULT '0',
  `PUBLIC` int(11) DEFAULT '0',
  `SPECIES_ID` int(11) DEFAULT NULL,
  `BLAST` int(11) NOT NULL DEFAULT '0',
  `BAYES` int(11) NOT NULL DEFAULT '0',
  `APPROVER_ID` int(11) DEFAULT NULL,
  `SUBMITTED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SEQ_MATCH` int(11) DEFAULT NULL,
  `SEQ_MATCH_PC` decimal(5,4) DEFAULT NULL,
  `SEQ_MATCH_START` int(11) DEFAULT NULL,
  `SEQ_MATCH_END` int(11) DEFAULT NULL,
  `QUE_START` int(11) DEFAULT NULL,
  `QUE_END` int(11) DEFAULT NULL,
  `HOST` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `GLOBAL_POSITION` int(11) DEFAULT NULL,
  `ROSENBERG` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `DISCARD` int(11) NOT NULL DEFAULT '0',
  `DISCARD_REASON` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `NEW` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `GENBANK_ID_UNIQUE` (`GENBANK_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6304 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MT_SEQUENCE_PRIVATE`
--

DROP TABLE IF EXISTS `MT_SEQUENCE_PRIVATE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MT_SEQUENCE_PRIVATE` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SUB_NAME` varchar(255) NOT NULL,
  `MT_SEQUENCE` varchar(7000) NOT NULL,
  `OWNER` int(11) NOT NULL,
  `SPECIES_ID` int(11) DEFAULT NULL,
  `SEQ_MATCH` int(11) DEFAULT NULL,
  `SEQ_MATCH_PC` decimal(5,4) DEFAULT NULL,
  `SEQ_MATCH_START` int(11) DEFAULT NULL,
  `SEQ_MATCH_END` int(11) DEFAULT NULL,
  `QUE_START` int(11) DEFAULT NULL,
  `QUE_END` int(11) DEFAULT NULL,
  `GLOBAL_POSITION` int(11) DEFAULT NULL,
  `ROSENBERG` varchar(255) DEFAULT NULL,
  `SUBMITTED` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `BLAST` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=69420 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MT_SEQUENCE_QA`
--

DROP TABLE IF EXISTS `MT_SEQUENCE_QA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MT_SEQUENCE_QA` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SUB_NAME` varchar(255) DEFAULT NULL,
  `GENBANK_ID` varchar(255) DEFAULT NULL,
  `LOC1` varchar(255) DEFAULT NULL,
  `MT_SEQUENCE` varchar(7000) DEFAULT NULL,
  `OWNER` int(11) DEFAULT NULL,
  `PUBLIC` int(11) DEFAULT NULL,
  `HOST` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6200 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MT_SEQ_DISCARD_REASON`
--

DROP TABLE IF EXISTS `MT_SEQ_DISCARD_REASON`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MT_SEQ_DISCARD_REASON` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REASON` varchar(1000) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MYRIAM_IMPORT`
--

DROP TABLE IF EXISTS `MYRIAM_IMPORT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MYRIAM_IMPORT` (
  `ID` int(11) DEFAULT NULL,
  `SUB_NAME` text,
  `GENBANK_ID` text,
  `LOC1` text,
  `LOC2` text,
  `LOC3` text,
  `GPS` text,
  `MT_SEQUENCE` text,
  `OWNER` int(11) DEFAULT NULL,
  `PUBLIC` int(11) DEFAULT NULL,
  `SPECIES_ID` int(11) DEFAULT NULL,
  `BLAST` int(11) DEFAULT NULL,
  `BAYES` int(11) DEFAULT NULL,
  `APPROVER_ID` int(11) DEFAULT NULL,
  `SUBMITTED_ON` text,
  `SEQ_MATCH` int(11) DEFAULT NULL,
  `SEQ_MATCH_PC` double DEFAULT NULL,
  `SEQ_MATCH_START` int(11) DEFAULT NULL,
  `SEQ_MATCH_END` int(11) DEFAULT NULL,
  `QUE_START` int(11) DEFAULT NULL,
  `QUE_END` int(11) DEFAULT NULL,
  `HOST` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `SEL_USR_SEQ`
--

DROP TABLE IF EXISTS `SEL_USR_SEQ`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SEL_USR_SEQ` (
  `USER_ID` int(11) NOT NULL,
  `MT_SEQUENCE_ID` int(11) NOT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `SPECIES`
--

DROP TABLE IF EXISTS `SPECIES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SPECIES` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SPECIES` varchar(100) NOT NULL,
  `Outgroup` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SPECIES_UNIQUE` (`SPECIES`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `TR_METADATA`
--

DROP TABLE IF EXISTS `TR_METADATA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TR_METADATA` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILE` mediumblob NOT NULL,
  `DESC` varchar(2000) DEFAULT NULL,
  `CREATED` datetime DEFAULT NULL,
  `CREATED_BY_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `USERS`
--

DROP TABLE IF EXISTS `USERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `USERS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FIRST_NAME` varchar(1000) NOT NULL,
  `SURNAME` varchar(1000) NOT NULL,
  `EMAIL` varchar(700) NOT NULL,
  `PASSWD` varchar(1000) NOT NULL,
  `INSTITUTION_ID` int(11) NOT NULL,
  `ACTIVE_LIST_ID` int(11) DEFAULT NULL,
  `ADMIN` int(11) DEFAULT '0',
  `ACTIVATED` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EMAIL_UNIQUE` (`EMAIL`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `USERS_LIST`
--

DROP TABLE IF EXISTS `USERS_LIST`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `USERS_LIST` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERS_ID` int(11) NOT NULL,
  `LIST_NAME` varchar(255) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `USERS_LIST_LIB`
--

DROP TABLE IF EXISTS `USERS_LIST_LIB`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `USERS_LIST_LIB` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERS_LIST_ID` int(11) NOT NULL,
  `MT_SEQUENCE_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `WFGPS`
--

DROP TABLE IF EXISTS `WFGPS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WFGPS` (
  `Lab` int(11) NOT NULL,
  `District` text,
  `Subcounty` text,
  `Village` text,
  `Latitude` text,
  `Longitude` text,
  `Altitude` int(11) DEFAULT NULL,
  `Variety` text,
  `Age` int(11) DEFAULT NULL,
  PRIMARY KEY (`Lab`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `WFGPS2`
--

DROP TABLE IF EXISTS `WFGPS2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WFGPS2` (
  `County` text,
  `Area` text,
  `Field` text,
  `Geographic location` text,
  `Altitude` int(11) DEFAULT NULL,
  `Genetic group` text,
  `Accession No.` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `WFGPS3`
--

DROP TABLE IF EXISTS `WFGPS3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WFGPS3` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BoxNumber` int(11) DEFAULT NULL,
  `UWACODE` int(11) DEFAULT NULL,
  `MTWFBASE` text CHARACTER SET latin1,
  `MTBAYES` text CHARACTER SET latin1,
  `Date Received` text CHARACTER SET latin1,
  `Sender Ref#` text COLLATE utf8_bin,
  `Historic Lab Ref#` int(11) DEFAULT NULL,
  `Country` text CHARACTER SET latin1,
  `Village/City/Province` text CHARACTER SET latin1,
  `District` text CHARACTER SET latin1,
  `Sub-county` text CHARACTER SET latin1,
  `Village` text CHARACTER SET latin1,
  `Latitude` varchar(75) COLLATE utf8_bin DEFAULT NULL,
  `Longitude` varchar(75) COLLATE utf8_bin DEFAULT NULL,
  `Altitude` int(11) DEFAULT NULL,
  `Host` text CHARACTER SET latin1,
  `Variety` text CHARACTER SET latin1,
  `Date Collected` text CHARACTER SET latin1,
  `Collector` text CHARACTER SET latin1,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=467 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `WFGPS4`
--

DROP TABLE IF EXISTS `WFGPS4`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WFGPS4` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SequenceNo` text,
  `Region` text,
  `Location` text,
  `Latitude` text,
  `Longitude` text,
  `Altitude` int(11) DEFAULT NULL,
  `SampleID` text,
  `FieldNo` int(11) DEFAULT NULL,
  `Species` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mt_Sequence_backup`
--

DROP TABLE IF EXISTS `mt_Sequence_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mt_Sequence_backup` (
  `ID` int(11) DEFAULT NULL,
  `SUB_NAME` text,
  `GENBANK_ID` text,
  `LOC1` text,
  `LOC2` text,
  `LOC3` text,
  `GPS` text,
  `MT_SEQUENCE` text,
  `OWNER` int(11) DEFAULT NULL,
  `PUBLIC` int(11) DEFAULT NULL,
  `SPECIES_ID` int(11) DEFAULT NULL,
  `BLAST` int(11) DEFAULT NULL,
  `BAYES` int(11) DEFAULT NULL,
  `APPROVER_ID` int(11) DEFAULT NULL,
  `SUBMITTED_ON` text,
  `SEQ_MATCH` int(11) DEFAULT NULL,
  `SEQ_MATCH_PC` double DEFAULT NULL,
  `SEQ_MATCH_START` int(11) DEFAULT NULL,
  `SEQ_MATCH_END` int(11) DEFAULT NULL,
  `QUE_START` int(11) DEFAULT NULL,
  `QUE_END` int(11) DEFAULT NULL,
  `HOST` text,
  `GLOBAL_POSITION` int(11) DEFAULT NULL,
  `ROSENBERG` text,
  `DISCARD` int(11) DEFAULT NULL,
  `DISCARD_REASON` text,
  `NEW` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'whiteflydb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-11-01 11:28:26
