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
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet;

// Version constant
const FAILNET_VERSION = '3.0.0-DEV';

/**
 * DO NOT _EVER_ CHANGE THESE, FOR THE SAKE OF HUMANITY.
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

// Auth classes
const AUTH_OWNER = 6;
const AUTH_SUPERADMIN = 5;
const AUTH_ADMIN = 4;
const AUTH_TRUSTEDUSER = 3;
const AUTH_KNOWNUSER = 2;
const AUTH_REGISTEREDUSER = 1;
const AUTH_UNKNOWNUSER = 0;
