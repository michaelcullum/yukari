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
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Codebite\Yukari;
use \Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Dependency\Injector;

$injector = Injector::getInstance();
$injector->setInjector('yukari.ui', function() {
	$ui = new \Codebite\Yukari\Environment\Display();
	$ui->setOutputLevel(Kernel::getConfig('ui.output_level'))
		->registerListeners();

	return $ui;
});

$injector->setInjector('yukari.timezone', function() {
	return new \DateTimeZone((Kernel::getConfig('yukari.timezonestring') ?: 'UTC'));
});

$injector->setInjector('yukari.starttime', function() {
	return new \DateTime('@' . \Codebite\Yukari\START_TIME, Kernel::get('yukari.timezone'));
});

$injector->setInjector('yukari.ui', function() {
	return new \Codebite\Yukari\Addon\Loader();
});
