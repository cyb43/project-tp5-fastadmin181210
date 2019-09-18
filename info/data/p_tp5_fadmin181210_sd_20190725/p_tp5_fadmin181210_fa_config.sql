CREATE DATABASE  IF NOT EXISTS `p_tp5_fadmin181210` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `p_tp5_fadmin181210`;
-- MySQL dump 10.13  Distrib 5.7.17, for macos10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: p_tp5_fadmin181210
-- ------------------------------------------------------
-- Server version	5.6.35

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
-- Table structure for table `fa_config`
--

DROP TABLE IF EXISTS `fa_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fa_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text NOT NULL COMMENT '变量值',
  `content` text NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='系统配置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_config`
--

LOCK TABLES `fa_config` WRITE;
/*!40000 ALTER TABLE `fa_config` DISABLE KEYS */;
INSERT INTO `fa_config` VALUES (1,'name','basic','Site name','请填写站点名称','string','2workroom3工作室','','required',''),(2,'beian','basic','Beian','粤ICP备15054802号-4','string','粤ICP备15054802号-4','','',''),(3,'cdnurl','basic','Cdn url','如果静态资源使用第三方云储存请配置该值','string','','','',''),(4,'version','basic','Version','如果静态资源有变动请重新配置该值','string','1.0.1','','required',''),(5,'timezone','basic','Timezone','','string','Asia/Shanghai','','required',''),(6,'forbiddenip','basic','Forbidden ip','一行一条记录','text','','','',''),(7,'languages','basic','Languages','','array','{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}','','required',''),(8,'fixedpage','basic','Fixed page','请尽量输入左侧菜单栏存在的链接','string','dashboard','','required',''),(9,'categorytype','dictionary','Category type','','array','{\"default\":\"Default\",\"page\":\"Page\",\"article\":\"Article\",\"test\":\"Test\"}','','',''),(10,'configgroup','dictionary','Config group','','array','{\"basic\":\"Basic\",\"email\":\"Email\",\"dictionary\":\"Dictionary\",\"user\":\"User\",\"example\":\"Example\"}','','',''),(11,'mail_type','email','Mail type','选择邮件发送方式','select','1','[\"Please select\",\"SMTP\",\"Mail\"]','',''),(12,'mail_smtp_host','email','Mail smtp host','错误的配置发送邮件会导致服务器超时','string','smtp.qq.com','','',''),(13,'mail_smtp_port','email','Mail smtp port','(不加密默认25,SSL默认465,TLS默认587)','string','465','','',''),(14,'mail_smtp_user','email','Mail smtp user','（填写完整用户名）','string','1142733640','','',''),(15,'mail_smtp_pass','email','Mail smtp password','（填写您的密码）','string','egumwxrhoamyjehd','','',''),(16,'mail_verify_type','email','Mail vertify type','（SMTP验证方式[推荐SSL]）','select','2','[\"None\",\"TLS\",\"SSL\"]','',''),(17,'mail_from','email','Mail from','','string','1142733640@qq.com','','',''),(18,'e_string','example','e_string','tip','string','e_string_value','','',''),(19,'e_text','example','e_text','tip','text','e_text_value','','',''),(20,'e_editor','example','e_editor','tip','editor','e_editor_value','','',''),(21,'e_number','example','e_number','tip','number','3','','',''),(22,'e_date','example','e_date','tip','date','2019-07-05','','',''),(23,'e_time','example','e_time','tip','time','03:04:23','','',''),(24,'e_datetime','example','e_datetime','tip','datetime','2019-07-05 03:04:24','','',''),(25,'e_select','example','e_select','tip','select','value2','{\"value1\":\"title1\",\"value2\":\"title2\"}','',''),(26,'e_selects','example','e_selects','tip','selects','value1,value2','{\"value1\":\"title1\",\"value2\":\"title2\"}','',''),(27,'e_image','example','e_image','tip','image','/assets/img/qrcode.png','','',''),(28,'e_images','example','e_images','tip','images','/assets/img/qrcode.png','','',''),(29,'e_file','example','e_file','tip','file','/assets/img/qrcode.png','','',''),(30,'e_files','example','e_files','tip','files','/assets/img/qrcode.png','','',''),(31,'e_switch','example','e_switch','tip','switch','1','','',''),(32,'e_checkbox','example','e_checkbox','tip','checkbox','value1,value2','{\"value1\":\"title1\",\"value2\":\"title2\"}','',''),(33,'e_radio','example','e_radio','tip','radio','value1','{\"value1\":\"title1\",\"value2\":\"title2\"}','',''),(34,'e_array','example','e_array','tip','array','{\"a_key_1\":\"a_value_1\",\"a_key_2\":\"a_value_2\"}','{\"value1\":\"title1\",\"value2\":\"title2\"}','',''),(35,'e_custom','example','e_custom','tip','custom','e_custom_value','','','');
/*!40000 ALTER TABLE `fa_config` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-25  1:11:44
