<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 2
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
define('FAILNET_VERSION', '2.0.0A2');

// Output levels
define('OUTPUT_SILENT', 0);
define('OUTPUT_NORMAL', 1);
define('OUTPUT_DEBUG', 2);
define('OUTPUT_DEBUG_FULL', 3);
define('OUTPUT_RAW', 4);
define('OUTPUT_SPAM', 4); // ;D

// @depreciated
define('MSG_NORMAL', 0);
define('MSG_DEBUG', 2);
define('MSG_DEBUG_EXTRA', 3);
