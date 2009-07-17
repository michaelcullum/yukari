# 
# SQLite 3 Schema file - Core DB
# Rough schema outline for the Failnet Core DB.
# $Id$
#

# Table: 'config'
# Config table
CREATE TABLE config (
	config_name varchar(255) NOT NULL DEFAULT '',
	config_value varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (config_name)
);

# Table: 'users'
# Users table
CREATE TABLE users (
	user_id INTEGER PRIMARY KEY NOT NULL ,
	nick varchar(255) NOT NULL DEFAULT '',
	user_authlevel int(4) NOT NULL DEFAULT '0',
	password varchar(40) UNSIGNED NOT NULL DEFAULT '',
);

# Table: 'access'
# User hostmask access table
CREATE TABLE access (
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask text(65535) NOT NULL DEFAULT '',
);

# Table: 'ignore'
# Hostmask ignore table
CREATE TABLE ignore (
	ignore_date INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask text(65535) NOT NULL DEFAULT '',
);