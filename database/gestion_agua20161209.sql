-- MySQL dump 10.13  Distrib 5.6.13, for Win64 (x86_64)
--
-- Host: localhost    Database: gestion_agua
-- ------------------------------------------------------
-- Server version	5.6.13

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
-- Table structure for table `app_config`
--

DROP TABLE IF EXISTS `app_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_config` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_config`
--

LOCK TABLES `app_config` WRITE;
/*!40000 ALTER TABLE `app_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumo`
--

DROP TABLE IF EXISTS `consumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(10) NOT NULL,
  `id_cuota` int(10) NOT NULL,
  `registro_medidor` int(10) DEFAULT NULL,
  `consumo_medidor` int(10) DEFAULT NULL,
  `valor_a_pagar` double DEFAULT NULL,
  `fecha_consumo` datetime NOT NULL,
  `fecha_hasta` datetime NOT NULL,
  `fecha_creación` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('generado','pagado') DEFAULT NULL,
  `cargo` double DEFAULT NULL,
  `detalle_cargo` varchar(200) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `id_cuota` (`id_cuota`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `consumo_ibfk_1` FOREIGN KEY (`id_cuota`) REFERENCES `cuotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consumo_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `customers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumo`
--

LOCK TABLES `consumo` WRITE;
/*!40000 ALTER TABLE `consumo` DISABLE KEYS */;
INSERT INTO `consumo` VALUES (1,1256,13,1012,2,1.5,'2017-01-26 00:00:00','2017-02-26 00:00:00','2016-12-09 22:53:31','2016-12-09 22:53:31','generado',NULL,NULL,0),(2,1256,13,1014,2,1.5,'2017-02-26 00:00:00','2017-03-26 00:00:00','2016-12-09 22:53:41','2016-12-09 22:53:41','generado',NULL,NULL,0),(3,1256,13,1019,5,6,'2017-03-26 00:00:00','2017-04-26 00:00:00','2016-12-09 22:53:49','2016-12-09 22:53:49','generado',NULL,NULL,0),(4,1257,18,2015,996,5.75,'2017-04-26 00:00:00','2017-05-26 00:00:00','2016-12-09 22:55:27','2016-12-09 22:55:27','generado',NULL,NULL,0),(5,1257,14,2027,12,6.75,'2017-05-26 00:00:00','2017-06-26 00:00:00','2016-12-09 22:56:26','2016-12-09 22:56:26','generado',4.5,'Cambio de Medidor',0);
/*!40000 ALTER TABLE `consumo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuotas`
--

DROP TABLE IF EXISTS `cuotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuotas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rango` varchar(255) DEFAULT NULL,
  `valor` double DEFAULT NULL,
  `fecha_creación` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_tipo_consumo` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `id_tipo_consumo` (`id_tipo_consumo`),
  CONSTRAINT `cuotas_ibfk_1` FOREIGN KEY (`id_tipo_consumo`) REFERENCES `tipo_consumo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuotas`
--

LOCK TABLES `cuotas` WRITE;
/*!40000 ALTER TABLE `cuotas` DISABLE KEYS */;
INSERT INTO `cuotas` VALUES (1,'10',4,'2016-11-30 20:15:50',1,0),(2,'15',5,'2016-11-30 20:15:50',1,0),(3,'20',7,'2016-11-30 20:15:50',1,0),(4,'30',10,'2016-11-30 20:15:50',1,0),(5,'50',20,'2016-11-30 20:15:50',1,0),(6,'9999999',30,'2016-11-30 20:15:50',1,0),(7,'10',10,'2016-11-30 20:15:50',2,0),(8,'15',12,'2016-11-30 20:15:50',2,0),(9,'20',15,'2016-11-30 20:15:50',2,0),(10,'30',20,'2016-11-30 20:15:50',2,0),(11,'50',50,'2016-11-30 20:15:50',2,0),(12,'9999999',100,'2016-11-30 20:15:50',2,0),(13,'10',1.5,'2016-11-30 20:15:50',3,0),(14,'15',2.25,'2016-11-30 20:15:50',3,0),(15,'20',3,'2016-11-30 20:15:50',3,0),(16,'30',3.75,'2016-11-30 20:15:50',3,0),(17,'50',4.5,'2016-11-30 20:15:50',3,0),(18,'9999999',5.75,'2016-11-30 20:15:50',3,0),(19,'medidor',20,'2016-11-30 20:19:29',1,0),(20,'medidor',100,'2016-11-30 20:19:29',2,0),(21,'medidor',4.5,'2016-11-30 20:19:29',3,0);
/*!40000 ALTER TABLE `cuotas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `person_id` int(10) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `taxable` int(1) NOT NULL DEFAULT '1',
  `id_tipo_consumo` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `fecha_ingreso` date DEFAULT NULL,
  `registro_inicial` int(10) DEFAULT NULL,
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`),
  KEY `fk_tipo_consumo_idx` (`id_tipo_consumo`),
  CONSTRAINT `fk_people_en_customers` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_tipo_consumo` FOREIGN KEY (`id_tipo_consumo`) REFERENCES `tipo_consumo` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1256,'1',0,3,0,'2016-12-26',1010),(1257,'2',0,3,0,'2016-11-01',988);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `person_id` int(10) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `username` (`username`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `fk_person_on_employee` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES ('cobrador','1c0ac62df25b8615de51fc423f15cbdd',1255,0),('mariofertc','cebdd715d4ecaafee8f147c2e85e0754',3,0);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `name_lang_key` varchar(255) NOT NULL,
  `desc_lang_key` varchar(255) NOT NULL,
  `sort` int(10) NOT NULL,
  `module_id` varchar(255) NOT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `desc_lang_key` (`desc_lang_key`),
  UNIQUE KEY `name_lang_key` (`name_lang_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES ('module_config','module_config_desc',12,'config'),('module_consumos','module_consumos_desc',2,'consumos'),('module_customers','module_customers_desc',1,'customers'),('module_employees','module_employees_desc',7,'employees'),('module_pagar','module_pagar_desc',6,'pagar'),('module_payments','module_payments_desc',9,'payments'),('module_porpagar','module_porpagar_desc',11,'porpagar'),('module_receivings','module_receivings_desc',5,'receivings'),('module_reports','module_reports_desc',3,'reports'),('module_sales','module_sales_desc',5,'sales'),('module_suppliers','module_suppliers_desc',4,'suppliers');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multa`
--

DROP TABLE IF EXISTS `multa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multa` (
  `multa_id` int(10) NOT NULL DEFAULT '0',
  `detalle` varchar(100) DEFAULT NULL,
  `valor` double(15,2) DEFAULT NULL,
  `fecha_aprobacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multa`
--

LOCK TABLES `multa` WRITE;
/*!40000 ALTER TABLE `multa` DISABLE KEYS */;
/*!40000 ALTER TABLE `multa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multa_cliente`
--

DROP TABLE IF EXISTS `multa_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multa_cliente` (
  `multa_id` int(10) NOT NULL DEFAULT '0',
  `detalle` varchar(100) DEFAULT NULL,
  `valor` double(15,2) DEFAULT NULL,
  `fecha_aprobacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multa_cliente`
--

LOCK TABLES `multa_cliente` WRITE;
/*!40000 ALTER TABLE `multa_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `multa_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(40) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `por_cobrar` int(1) DEFAULT '0',
  `sort` int(11) DEFAULT NULL,
  `have_plazo` int(1) DEFAULT '0',
  `payment_days` int(11) DEFAULT '0',
  `payment_months` int(11) DEFAULT '0',
  `share` int(11) DEFAULT '0',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (2,'Efectivo',0,0,1,0,0,0,0),(3,'Cheque',0,1,3,0,0,0,0);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `person_id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1258 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
INSERT INTO `people` VALUES ('Mario','Torres','','','','','','','','','',3),('Cobrador','T','','c@c.com','','','','','1818181818','','',1255),('Ernesto Ramiro','Abril Espin','','','','','','','1802532943','','',1256),('Felicidad Ubaldina','Abril Martinez','','','','','','','1803089778','','',1257);
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `module_id` varchar(255) NOT NULL,
  `person_id` int(10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `fk_customers_en_permi` FOREIGN KEY (`person_id`) REFERENCES `employees` (`person_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_modulos_en_permisos` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES ('config',3),('consumos',3),('customers',3),('employees',3),('pagar',3),('sales',3),('customers',1255),('employees',1255);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(100) DEFAULT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('b5e892d7761df4378fd6b17676f252c2','0.0.0.0','Mozilla/5.0 (Windows NT 10.0; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',1480555276,'a:2:{s:9:\"user_data\";s:0:\"\";s:9:\"person_id\";s:1:\"3\";}');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_consumo`
--

DROP TABLE IF EXISTS `tipo_consumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_consumo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `fecha_creación` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_consumo`
--

LOCK TABLES `tipo_consumo` WRITE;
/*!40000 ALTER TABLE `tipo_consumo` DISABLE KEYS */;
INSERT INTO `tipo_consumo` VALUES (1,'Residencial','2016-11-30 20:06:55',0),(2,'Comercial','2016-11-30 20:06:55',0),(3,'Pública','2016-11-30 20:06:55',0);
/*!40000 ALTER TABLE `tipo_consumo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-12-09 23:07:25
