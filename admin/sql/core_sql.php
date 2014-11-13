<?php
header("location:../../index.php");
exit;
?>

--
-- Структура на таблица `categories`
--

CREATE TABLE categories (
  id smallint(6) NOT NULL,
  name varchar(256) NOT NULL,
  description text NOT NULL,
  position smallint(6) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `cmr`
--

CREATE TABLE cmr (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  date varchar(15) NOT NULL,
  truckid int(2) NOT NULL,
  numb varchar(7) NOT NULL,
  chass varchar(17) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `core`
--

CREATE TABLE core (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  maintenance varchar(10) NOT NULL DEFAULT '0',
  maintenance_text text NOT NULL,
  upload_path varchar(30) NOT NULL DEFAULT 'upload/',
  upload_filetypes char(30) NOT NULL,
  upload_maxsize int(8) NOT NULL,
  color_cash varchar(7) NOT NULL,
  color_empty varchar(7) NOT NULL,
  broi_kamioni int(10) NOT NULL DEFAULT '1',
  trucks_plate varchar(300) NOT NULL,
  truck_off varchar(300) NOT NULL,
  year int(10) NOT NULL DEFAULT '2013',
  garant_trip int(10) NOT NULL DEFAULT '10000',
  mail_user varchar(50) NOT NULL,
  mail_pass varchar(50) NOT NULL,
  mail_from varchar(30) NOT NULL,
  mail_to varchar(30) NOT NULL,
  mail_to_additional varchar(30) NOT NULL,
  mail_subject varchar(20) NOT NULL DEFAULT 'ЧМР',
  mail_message varchar(200) NOT NULL DEFAULT 'Trailer number:<b> $numb</b> <br>Trailer chassis:<b>  $chass</b>',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `pm`
--

CREATE TABLE pm (
  id bigint(20) NOT NULL,
  id2 int(11) NOT NULL,
  title varchar(256) NOT NULL,
  user1 bigint(20) NOT NULL,
  user2 bigint(20) NOT NULL,
  message text NOT NULL,
  timestamp int(10) NOT NULL,
  user1read varchar(3) NOT NULL,
  user2read varchar(3) NOT NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `tacho`
--

CREATE TABLE tacho (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  date varchar(10) NOT NULL,
  user varchar(20) NOT NULL,
  fname varchar(60) NOT NULL,
  fsize int(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `topics`
--

CREATE TABLE topics (
  parent smallint(6) NOT NULL,
  id int(11) NOT NULL,
  id2 int(11) NOT NULL,
  title varchar(256) NOT NULL,
  message longtext NOT NULL,
  authorid int(11) NOT NULL,
  timestamp int(11) NOT NULL,
  timestamp2 int(11) NOT NULL,
  PRIMARY KEY (id,id2)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `truck1_cg`
--

CREATE TABLE truck1_cg (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  date varchar(15) NOT NULL,
  road varchar(100) NOT NULL,
  trip int(30) NOT NULL,
  empty varchar(10) NOT NULL,
  userid int(10) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `truck1_lt`
--

CREATE TABLE truck1_lt (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  date varchar(15) NOT NULL,
  liters int(30) NOT NULL,
  literst int(10) NOT NULL,
  trip int(30) NOT NULL,
  cash text NOT NULL,
  full text NOT NULL,
  userid int(10) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `users`
--

CREATE TABLE users (
  userid int(25) NOT NULL AUTO_INCREMENT,
  first_name varchar(25) NOT NULL DEFAULT '',
  last_name varchar(25) NOT NULL DEFAULT '',
  user_email varchar(50) NOT NULL DEFAULT '',
  username varchar(25) NOT NULL DEFAULT '',
  password varchar(255) NOT NULL DEFAULT '',
  info varchar(50) NOT NULL,
  avatar text NOT NULL,
  last_loggedin varchar(100) NOT NULL DEFAULT 'never',
  user_level enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  forgot varchar(100) DEFAULT NULL,
  approved int(1) NOT NULL,
  class int(10) NOT NULL DEFAULT '1',
  timeout varchar(30) NOT NULL,
  banned int(1) NOT NULL,
  ckey varchar(220) NOT NULL,
  ctime varchar(220) NOT NULL,
  PRIMARY KEY (userid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Структура на таблица `user_classes`
--

CREATE TABLE user_classes (
  id int(10) NOT NULL AUTO_INCREMENT,
  user_id int(10) NOT NULL,
  class int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;
?>