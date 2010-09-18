/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     schemas
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 */

/**
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */


/**
 * Users table
 */
CREATE TABLE users (
	user_id INTEGER PRIMARY KEY NOT NULL,
	nick TEXT NOT NULL DEFAULT '',
	authlevel INTEGER UNSIGNED NOT NULL DEFAULT 0,
	confirm_key TEXT NOT NULL DEFAULT '',
	password TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX nick ON users ( nick );
