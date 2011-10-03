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
 * @copyright   (c) 2009 - 2011 Damian Bushong
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
use \OpenFlame\Framework\Autoloader;
use \OpenFlame\Framework\Dependency\Injector;
use \OpenFlame\Framework\Utility\JSON;

$injector = Injector::getInstance();

$injector->setInjector('yukari.argparser', function() {
	// The first chunk always gets in the way, so we drop it.
	array_shift($_SERVER['argv']);
	return new \Codebite\Yukari\Environment\ArgumentParser($_SERVER['argv']);
});

$injector->setInjector('yukari.ui', function() {
	$ui = new \Codebite\Yukari\Environment\Display();
	$ui->setOutputLevel(Kernel::getConfig('ui.output_level'))
		->registerListeners();

	return $ui;
});

$injector->setInjector('yukari.addonloader', function() {
	$loader = new \emberlabs\materia\Loader(YUKARI);
	$loader->setAddonDirs('/addons/', 'lib/addons/')
		->setCallback(function($set_path) {
			Autoloader::getInstance()->setPath($set_path);
		});
	return $loader;
});

$injector->setInjector('yukari.timezone', function() {
	return new \DateTimeZone((Kernel::getConfig('yukari.timezonestring') ?: 'UTC'));
});

$injector->setInjector('yukari.starttime', function() {
	return new \DateTime('@' . \Codebite\Yukari\START_TIME, Kernel::get('yukari.timezone'));
});

$injector->setInjector('yukari.daemon', function() {
	return new \Codebite\Yukari\Daemon();
});

$injector->setInjector('language', function() {
	$language = new \OpenFlame\Framework\Language\Handler();
	$locale = Kernel::getConfig('yukari.locale') ?: 'en-us';
	$language->loadEntries(JSON::decode(YUKARI . "/data/language/{$locale}.json"));
	return $language;
});
