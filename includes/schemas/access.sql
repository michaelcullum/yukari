#
# Access list table
# $Id$
#

CREATE TABLE access (
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask TEXT NOT NULL DEFAULT '',
);