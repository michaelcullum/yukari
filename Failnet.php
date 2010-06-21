#!/usr/bin/php
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
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

/**
 * @ignore
 */

define('FAILNET_ROOT', dirname(__FILE__));
define('FAILNET_MIN_PHP', '5.3.0');

if(version_compare(FAILNET_MIN_PHP, PHP_VERSION, '>'))
	throw new Exception('Failnet requires PHP ' . FAILNET_MIN_PHP . ' or better, while the currently installed PHP version is ' . PHP_VERSION, 1000);

require FAILNET_ROOT . 'Includes/Bootstrap.php';
