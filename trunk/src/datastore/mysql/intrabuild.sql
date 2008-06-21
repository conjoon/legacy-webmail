-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 18. Juni 2008 um 19:20
-- Server Version: 5.0.51
-- PHP-Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `intrabuild`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_accounts`
--

CREATE TABLE IF NOT EXISTS `groupware_email_accounts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `address` varchar(255) NOT NULL,
  `reply_address` varchar(255) default NULL,
  `is_standard` tinyint(1) NOT NULL default '0',
  `protocol` enum('POP3','IMAP') NOT NULL default 'POP3',
  `server_inbox` varchar(255) NOT NULL,
  `server_outbox` varchar(255) NOT NULL,
  `username_inbox` varchar(64) NOT NULL,
  `username_outbox` varchar(64) default NULL,
  `user_name` varchar(255) NOT NULL,
  `is_outbox_auth` tinyint(1) NOT NULL default '0',
  `password_inbox` varchar(32) NOT NULL,
  `password_outbox` varchar(32) default NULL,
  `signature` varchar(255) default NULL,
  `is_signature_used` tinyint(1) NOT NULL default '0',
  `port_inbox` smallint(5) unsigned NOT NULL default '110',
  `port_outbox` smallint(5) unsigned NOT NULL default '25',
  `is_copy_left_on_server` tinyint(1) NOT NULL default '1',
  `is_deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='table for storing email accounts' AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_folders`
--

CREATE TABLE IF NOT EXISTS `groupware_email_folders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `is_child_allowed` tinyint(1) NOT NULL default '1',
  `is_locked` tinyint(1) NOT NULL default '0',
  `type` enum('accounts_root','root','inbox','spam','trash','draft','sent','outbox','folder') NOT NULL,
  `meta_info` enum('inbox','draft','sent','outbox') NOT NULL default 'inbox',
  `parent_id` int(10) unsigned NOT NULL,
  `is_deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`parent_id`,`name`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=93 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_folders_accounts`
--

CREATE TABLE IF NOT EXISTS `groupware_email_folders_accounts` (
  `groupware_email_folders_id` int(10) unsigned NOT NULL,
  `groupware_email_accounts_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`groupware_email_folders_id`,`groupware_email_accounts_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_items`
--

CREATE TABLE IF NOT EXISTS `groupware_email_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `groupware_email_folders_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `subject` text,
  `from` text NOT NULL,
  `to` text NOT NULL,
  `cc` text,
  `bcc` text,
  `in_reply_to` text,
  `references` text,
  `content_text_plain` longtext,
  `content_text_html` longtext,
  PRIMARY KEY  (`id`),
  KEY `groupware_email_folders_id` (`groupware_email_folders_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Table for storing incomin emails in a readable format.' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_items_attachments`
--

CREATE TABLE IF NOT EXISTS `groupware_email_items_attachments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `groupware_email_items_id` int(11) unsigned NOT NULL,
  `file_name` tinytext NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `encoding` varchar(32) NOT NULL,
  `content` blob NOT NULL,
  `content_id` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `groupware_email_items_id` (`groupware_email_items_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_items_flags`
--

CREATE TABLE IF NOT EXISTS `groupware_email_items_flags` (
  `groupware_email_items_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL default '0',
  `is_spam` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`groupware_email_items_id`,`user_id`),
  KEY `is_read` (`user_id`,`is_read`,`groupware_email_items_id`),
  KEY `is_spam` (`groupware_email_items_id`,`user_id`,`is_spam`),
  KEY `flags` (`groupware_email_items_id`,`user_id`,`is_read`,`is_spam`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_email_items_inbox`
--

CREATE TABLE IF NOT EXISTS `groupware_email_items_inbox` (
  `groupware_email_items_id` int(10) unsigned NOT NULL,
  `raw_header` longblob NOT NULL,
  `raw_body` longblob NOT NULL,
  `hash` varchar(32) default NULL,
  `message_id` varchar(255) default NULL,
  `reply_to` text,
  `uid` varchar(255) default NULL,
  `fetched_timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`groupware_email_items_id`),
  KEY `hash` (`hash`),
  KEY `fetched_timestamp` (`groupware_email_items_id`,`fetched_timestamp`),
  KEY `uid` (`uid`),
  KEY `message_id` (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_feeds_accounts`
--

CREATE TABLE IF NOT EXISTS `groupware_feeds_accounts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `uri` varchar(255) NOT NULL,
  `link` tinytext,
  `description` tinytext,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `update_interval` int(10) unsigned NOT NULL default '3600',
  `delete_interval` int(10) unsigned NOT NULL default '172800',
  `last_updated` int(10) unsigned NOT NULL,
  `is_deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupware_feeds_items`
--

CREATE TABLE IF NOT EXISTS `groupware_feeds_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `groupware_feeds_accounts_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `pub_date` datetime NOT NULL,
  `link` tinytext NOT NULL,
  `guid` tinytext NOT NULL,
  `author` varchar(255) default NULL,
  `is_read` tinyint(1) NOT NULL default '0',
  `saved_timestamp` int(10) unsigned NOT NULL,
  `content` text,
  PRIMARY KEY  (`id`),
  KEY `groupware_feeds_accounts_id` (`groupware_feeds_accounts_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(64) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
