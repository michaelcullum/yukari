#! /usr/bin/php
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

/**
 * @ignore
 */

define('FAILNET', dirname(__FILE__));
define('FAILNET_MIN_PHP', '5.3.0');
//define('Failnet\\RUN_PHAR', true);

// Check running PHP version against the minimum supported PHP version...
if(version_compare(FAILNET_MIN_PHP, PHP_VERSION, '>'))
{
	echo sprintf('Failnet requires PHP version %1$s or better, while the currently installed PHP version is %2$s', FAILNET_MIN_PHP, PHP_VERSION);
	exit(1);
}

// If we are running Failnet using the Phar archive, then
if(defined('Failnet\\RUN_PHAR'))
{
	require FAILNET . 'failnet.phar';
}
else
{
	require FAILNET . 'src/Bootstrap.php';
}
