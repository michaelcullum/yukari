#! /usr/bin/php
<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/damianb/yukari
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

define('YUKARI', dirname(__FILE__) . '/../');
define('YUKARI_MIN_PHP', '5.3.0');
//define('Yukari\\RUN_PHAR', true);

// Check running PHP version against the minimum supported PHP version...
if(version_compare(YUKARI_MIN_PHP, PHP_VERSION, '>'))
{
	echo sprintf('Yukari requires PHP version %1$s or better, while the currently installed PHP version is %2$s', YUKARI_MIN_PHP, PHP_VERSION);
	exit(1);
}

// If we are running Yukari using the Phar archive, then
if(defined('Yukari\\RUN_PHAR'))
{
	require YUKARI . 'lib/yukari.phar';
}
else
{
	require YUKARI . 'src/Bootstrap.php';
}
