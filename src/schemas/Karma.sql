/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
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
 * Karma table
 */
CREATE TABLE karma (
	karma_value INTEGER NOT NULL,
	term TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX term ON karma ( term ) ;
