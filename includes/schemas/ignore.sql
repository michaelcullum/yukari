#
# Ignored users table
# $Id$
#

CREATE TABLE ignore (
	ignore_date INTEGER UNSIGNED NOT NULL DEFAULT '0',
	hostmask TEXT NOT NULL DEFAULT '',
);