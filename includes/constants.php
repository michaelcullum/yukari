<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
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
 *
 */

namespace Failnet;

// Version constant
define('FAILNET_VERSION', '2.1.0-DEV');

// Output levels
define('OUTPUT_SILENT', 0);
define('OUTPUT_NORMAL', 1);
define('OUTPUT_DEBUG', 2);
define('OUTPUT_DEBUG_FULL', 3);
define('OUTPUT_RAW', 4);
define('OUTPUT_SPAM', 4); // ;D

// Hook types
define('HOOK_NULL', 0);
define('HOOK_STACK', 1);
define('HOOK_OVERRIDE', 2);
