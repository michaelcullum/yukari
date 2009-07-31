#
# Access list table
#

CREATE TABLE access (
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask text(65535) NOT NULL DEFAULT '',
);