<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		3.0.0 DEV
 * @category	Failnet
 * @package		Failnet
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		GNU General Public License, Version 3
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Failnet;

/**
 * @ignore
 */

// Absolute essentials first
require FAILNET_ROOT . 'includes/constants.php';
require FAILNET_ROOT . 'includes/exception.php';

/**
 * We need to start checking to see if the requirements for Failnet can be met
 *
 * Things we check:
 *  - PHP_SAPI
 *  - PDO availability
 *  - PDO+SQlite availability
 *  - DB dir accessibility
 */
if(strtolower(PHP_SAPI) != 'cli')
	throw new Exception(ex(Exception::ERR_STARTUP_PHP_SAPI));
if(!extension_loaded('PDO'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_PDO));
if(!extension_loaded('pdo_sqlite'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_PDO_SQLITE));
if(!file_exists(FAILNET_ROOT . 'data/db/') || !is_readable(FAILNET_ROOT . 'data/db/') || !is_writeable(FAILNET_ROOT . 'data/db/') || !is_dir(FAILNET_ROOT . 'data/db/'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_ACCESS_DB_DIR));

// Load up the common files, setup our JIT class autoloading, and get going.
require FAILNET_ROOT . 'includes/common.php';
require FAILNET_ROOT . 'includes/autoload.php';
require FAILNET_ROOT . 'includes/functions.php';

Autoload::register();
Bot::setCore('core', 'Failnet\\Core\\Core');
Bot::core()->run();
