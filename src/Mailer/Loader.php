<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     mailer
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

namespace Failnet\Mailer;
use Failnet\Bot as Bot;

/**
 * Failnet - Swiftmailer loader,
 * 	    Loads up and prepares Swiftmailer.
 *
 * @category    Yukari
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Loader
{
	public function __construct()
	{
		// Load the Swiftmailer autoloader.
		require FAILNET . 'vendor/swiftmailer/lib/swift_required.php';

		// setup transport here, store in Env(mailer.transport)
		Bot::getEnvironment()->storeObject('mailer.mailer', Swift_Mailer::newInstance(Bot::getObject('mailer.transport')));

		// Add the decorator plugin, using our replacement object.
		Bot::getEnvironment()->storeObject('mailer.replacements', new Failnet\Mailer\Replacements());
		Bot::getObject('mailer.mailer')->registerPlugin(new Swift_Plugins_DecoratorPlugin(Bot::getObject('mailer.replacements')));
	}
}
