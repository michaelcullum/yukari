<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     install
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

namespace Failnet\Install;
use Failnet;
//use Failnet\Core as Core;
use Failnet\Lib as Lib;

/**
 * Failnet - Installer core class,
 *      Failnet's installer core.  This will handle all of the juicy stuff.
 *
 *
 * @category    Failnet
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Core extends Base
{
	public function __construct()
	{
		// Load the UI out of cycle so we can do this the right way
		Bot::setCore('ui', 'Failnet\\Install\\UI');
		Bot::core('ui')->output_level = Bot::arg('debug') ? Bot::arg('debug') : OUTPUT_NORMAL;

		// Fire off the startup text.
		Bot::core('ui')->startup();

		// Begin loading our core objects
		$core_objects = array(
			//'lang'		=> 'Failnet\\Core\\Language',
			//'socket'	=> 'Failnet\\Core\\Socket',
			'generator' => 'Failnet\\Install\\Generator',
			'db'		=> 'Failnet\\Core\\Database',
			//'log'		=> 'Failnet\\Core\\Log',
			//'irc'		=> 'Failnet\\Core\\IRC',
			//'plugin'	=> 'Failnet\\Core\\Plugin',
			//'cron'		=> 'Failnet\\Core\\Cron',
			'hash'		=> 'Failnet\\Lib\\Hash',
		);

		Bot::core('ui')->status('- Loading Failnet core objects');
		foreach($core_objects as $core_object_name => $core_object_class)
		{
			Bot::setCore($core_object_name, $core_object_class);
			Bot::core('ui')->system("--- Loaded core object $core_object_class");
		}
		unset($core_objects);
	}
}
