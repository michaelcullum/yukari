#
# Config table
#

CREATE TABLE config (
	name TEXT NOT NULL DEFAULT '',
	value TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (name)
);