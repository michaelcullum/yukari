#
# Reminders table
#

CREATE TABLE reminders (
	user_id INTEGER PRIMARY KEY NOT NULL,
	sender_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	create_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	remind_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	message text(65535) NOT NULL DEFAULT '',
	channel varchar(255) NOT NULL DEFAULT '',
);