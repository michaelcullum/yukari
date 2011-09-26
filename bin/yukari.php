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

define('YUKARI', dirname(dirname(__FILE__)));
define('YUKARI_PHAR', 'lib/yukari.phar');
define('YUKARI_MIN_PHP', '5.3.0');
define('Codebite\\Yukari\\RUN_PHAR', false);
error_reporting(-1);

// Check running PHP version against the minimum supported PHP version...
if(version_compare(YUKARI_MIN_PHP, PHP_VERSION, '>'))
{
	echo sprintf('Yukari requires PHP version %1$s or better, while the currently installed PHP version is %2$s.' . PHP_EOL, YUKARI_MIN_PHP, PHP_VERSION);
	exit(1);
}

// If we are running Yukari using the Phar archive, then
if(\Codebite\Yukari\RUN_PHAR === true)
{
	require 'phar://' . YUKARI_PHAR . '/Codebite/Yukari/Bootstrap.php';
}
else
{
	require YUKARI . '/src/Codebite/Yukari/Bootstrap.php';
}
