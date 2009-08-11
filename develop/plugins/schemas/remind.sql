/**
 * Reminders table
 * $Id: access.sql 196 2009-08-10 13:15:49Z Obsidian $
 */

CREATE TABLE reminders (
	user_id INTEGER PRIMARY KEY NOT NULL,
	sender_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	create_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	remind_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	message TEXT NOT NULL DEFAULT '',
	channel TEXT NOT NULL DEFAULT '',
);