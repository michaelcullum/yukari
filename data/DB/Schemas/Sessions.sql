/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     schemas
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 */

/**
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
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
