CREATE TABLE sessions (
	key_id char(32) NOT NULL DEFAULT '',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	login_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (key_id)
);