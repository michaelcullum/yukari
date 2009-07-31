#
# Users table
#

CREATE TABLE users (
	user_id INTEGER PRIMARY KEY NOT NULL,
	nick varchar(255) NOT NULL DEFAULT '',
	authlevel int(4) NOT NULL DEFAULT '0',
	confirm_key varchar(10) NOT NULL DEFAULT '',
	password varchar(40) UNSIGNED NOT NULL DEFAULT '',
);
CREATE UNIQUE INDEX unique_nick ON users ( nick ) ;