#
# Users table
#

CREATE TABLE users (
	user_id INTEGER PRIMARY KEY NOT NULL,
	nick TEXT NOT NULL DEFAULT '',
	authlevel INTEGER NOT NULL DEFAULT '0',
	confirm_key TEXT NOT NULL DEFAULT '',
	password TEXT UNSIGNED NOT NULL DEFAULT '',
);
CREATE UNIQUE INDEX unique_nick ON users ( nick ) ;