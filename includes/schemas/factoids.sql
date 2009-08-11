/**
 * Factoids table
 * $Id$
 */

CREATE TABLE factoids (
	factoid_id INTEGER PRIMARY KEY NOT NULL,
	direct INTEGER UNSIGNED NOT NULL DEFAULT 0,
	pattern TEXT NOT NULL DEFAULT '',
);