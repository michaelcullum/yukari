<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
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

// Version
define('FAILNET_VERSION', '2.0.0A1');

define('DEBUG_SILENT', 0);
define('DEBUG_OFF', 1);
define('DEBUG_ON', 2);
define('DEBUG_FULL', 3);
define('DEBUG_EXTRA', 4);
//define('DEBUG_SPAM', 4); // ;D

define('MSG_NORMAL', 0);
define('MSG_DEBUG', 2);
define('MSG_DEBUG_EXTRA', 3);
