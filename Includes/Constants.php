<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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

// Version constant
const FAILNET_VERSION = '3.0.0-DEV';

/**
 * DO NOT _EVER_ CHANGE THIS, FOR THE SAKE OF HUMANITY.
 * @link http://xkcd.com/534/
 */
const CAN_BECOME_SKYNET = false;
const COST_TO_BECOME_SKYNET = 999999999;

// Output levels
const OUTPUT_SILENT = 0;
const OUTPUT_NORMAL = 1;
const OUTPUT_DEBUG = 2;
const OUTPUT_DEBUG_FULL = 3;
const OUTPUT_RAW = 4;
const OUTPUT_SPAM = 4; // ;D

// Hook types
const HOOK_NULL = 0;
const HOOK_STACK = 1;
const HOOK_OVERRIDE = 2;

// Auth classes
const AUTH_OWNER = 6;
const AUTH_SUPERADMIN = 5;
const AUTH_ADMIN = 4;
const AUTH_TRUSTEDUSER = 3;
const AUTH_KNOWNUSER = 2;
const AUTH_REGISTEREDUSER = 1;
const AUTH_UNKNOWNUSER = 0;

// Cron task states
const TASK_ACTIVE = 10;
const TASK_MANUAL = 20;
const TASK_ZOMBIE = 30;
