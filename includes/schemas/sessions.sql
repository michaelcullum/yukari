#
# Sessions table
#

CREATE TABLE sessions (
	key_id TEXT NOT NULL DEFAULT '',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	login_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (key_id)
);