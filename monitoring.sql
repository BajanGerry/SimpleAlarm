CREATE DATABASE monitoring;
USE monitoring;

CREATE TABLE IF NOT EXISTS `mitel_alarms` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(25) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `state` varchar(25) NOT NULL,
  `alarm` varchar(250) NOT NULL,
  `date` datetime NOT NULL,
  `last` datetime NOT NULL,
  `company` varchar(25) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=174 ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate utf8_unicode_ci NOT NULL,
  `password` char(64) collate utf8_unicode_ci NOT NULL,
  `salt` char(16) collate utf8_unicode_ci NOT NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `company` varchar(25) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=19 ;

CREATE TABLE IF NOT EXISTS `tokens` (
  `token` varchar(10) NOT NULL,
  `email` varchar(25) NOT NULL,
  `used` int(1) NOT NULL default '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE USER 'monitor'@'localhost' IDENTIFIED BY 'Adm1n!';
GRANT ALL PRIVILEGES ON *.* TO 'monitor'@'localhost';