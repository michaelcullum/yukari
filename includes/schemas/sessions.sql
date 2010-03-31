/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.1.0 DEV
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 */


/**
 * Sessions table
 */
CREATE TABLE sessions (
	key_id TEXT NOT NULL DEFAULT '',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT 0,
	login_time INTEGER UNSIGNED NOT NULL DEFAULT 0,
	hostmask TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (key_id)
);
