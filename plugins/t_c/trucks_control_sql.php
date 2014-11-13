CREATE TABLE tc_saldo (
   id                 int(11)        UNSIGNED NOT NULL auto_increment,
   datestamp          int(10)        NOT NULL default 0,
   income			  int(10)		 NOT NULL default 0,
   outcome			  int(10)		 NOT NULL default 0,
   reasons			  text							   ,
   truck			  int(30)		 NOT NULL default 0,
   PRIMARY KEY (id)
) ENGINE=MyISAM;