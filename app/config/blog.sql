-- MySQL dump 10.13  Distrib 5.5.34, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: blog
-- ------------------------------------------------------
-- Server version	5.5.34-0ubuntu0.12.04.1

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
-- Table structure for table `blog_plugins`
--

DROP TABLE IF EXISTS `blog_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL DEFAULT '0',
  `plugin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` text NOT NULL,
  `list` text NOT NULL,
  `contents` text NOT NULL,
  `attribute` text NOT NULL,
  `device_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `category` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `plugin_order` int(11) unsigned NOT NULL DEFAULT '0',
  `display` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`,`blog_id`),
  KEY `plugin_id_idx` (`plugin_id`),
  KEY `id_idx` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blog_settings`
--

DROP TABLE IF EXISTS `blog_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_settings` (
  `blog_id` varchar(50) NOT NULL,
  `comment_confirm` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT ' /* comment truncated */ /*0 = 承認なしで表示 1 = 要承認*/',
  `comment_display_approval` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT ' /* comment truncated */ /*0 = 承認中コメントの表示 1 = 非表示*/',
  `comment_display_private` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT ' /* comment truncated */ /*0 = 非公開コメントの表示 1 = 非表示*/',
  `comment_cookie_save` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `comment_captcha` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `comment_order` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `comment_display_count` int(10) unsigned NOT NULL DEFAULT '10',
  `comment_quote` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `entry_order` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `entry_display_count` int(10) unsigned NOT NULL DEFAULT '5',
  `entry_recent_display_count` tinyint(3) unsigned NOT NULL DEFAULT '5',
  `entry_password` varchar(50) DEFAULT NULL,
  `start_page` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `template_pc_reply_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `template_mb_reply_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `template_sp_reply_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `template_tb_reply_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blog_templates`
--

DROP TABLE IF EXISTS `blog_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL,
  `template_id` int(10) unsigned NOT NULL COMMENT 'コピー元のtemplate_id',
  `title` varchar(45) NOT NULL,
  `html` mediumtext NOT NULL,
  `css` mediumtext NOT NULL,
  `device_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`,`blog_id`),
  KEY `blog_id_idx` (`blog_id`),
  KEY `template_id_idx` (`template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blogs` (
  `id` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `introduction` varchar(200) DEFAULT NULL,
  `template_pc_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT ' /* comment truncated */ /*PC用のテンプレートID*/',
  `template_mb_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT ' /* comment truncated */ /*携帯用のテンプレートID*/',
  `template_sp_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT ' /* comment truncated */ /*スマフォ用のテンプレートID*/',
  `template_tb_id` int(11) unsigned NOT NULL DEFAULT '0',
  `timezone` varchar(50) NOT NULL DEFAULT 'Asia/Tokyo',
  `open_status` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `blog_password` varchar(50) DEFAULT NULL,
  `last_posted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 = 親無し',
  `lft` int(10) unsigned NOT NULL,
  `rgt` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `category_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`blog_id`),
  KEY `blog_id_idx` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL,
  `entry_id` int(10) unsigned NOT NULL,
  `name` text,
  `title` text,
  `body` text NOT NULL,
  `mail` text,
  `url` text,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `open_status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `reply_body` text,
  `reply_status` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `reply_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`,`blog_id`),
  KEY `entry_id_idx` (`blog_id`,`entry_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entries`
--

DROP TABLE IF EXISTS `entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL,
  `title` text,
  `body` mediumtext,
  `extend` mediumtext,
  `first_image` text,
  `open_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '公開、パスワード保護、期間限定、予約投稿、下書き、非公開',
  `password` varchar(50) DEFAULT NULL,
  `auto_linefeed` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `comment_accepted` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '0 = 受け付けない 1 = 受け付ける',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `posted_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`,`blog_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entry_categories`
--

DROP TABLE IF EXISTS `entry_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entry_categories` (
  `blog_id` varchar(50) NOT NULL,
  `entry_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entry_id`,`blog_id`,`category_id`),
  KEY `category_id_idx` (`blog_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entry_tags`
--

DROP TABLE IF EXISTS `entry_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entry_tags` (
  `blog_id` varchar(50) NOT NULL,
  `entry_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`blog_id`,`tag_id`,`entry_id`),
  KEY `entry_id_idx` (`blog_id`,`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL,
  `name` text NOT NULL,
  `ext` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`,`blog_id`),
  KEY `blog_id_idx` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` text NOT NULL,
  `body` text,
  `list` text NOT NULL,
  `contents` text NOT NULL,
  `attribute` text NOT NULL,
  `device_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id_idx` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` varchar(50) NOT NULL,
  `name` varchar(45) NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`blog_id`),
  KEY `blog_id_idx` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login_id` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `login_blog_id` varchar(50) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `logged_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-03-13  3:02:15
