CREATE TABLE users (
	user_id INTEGER PRIMARY KEY NOT NULL ,
	nick varchar(255) NOT NULL DEFAULT '',
	authlevel int(4) NOT NULL DEFAULT '0',
	password varchar(40) UNSIGNED NOT NULL DEFAULT '',
);