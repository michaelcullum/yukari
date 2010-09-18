<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
  * @version     3.0.0 DEV
 * @category    Failnet
 * @package     node
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

namespace Failnet\Node;
use Failnet as Root;

/**
 * Failnet - Help class,
 * 	    Failnet's handler for the dynamic help system
 *
 *
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Help extends Base
{
	/**
	 * @var array - Index list of plugins with commands
	 */
	public $index = array();

	/**
	 * @var array - Index list of commands available within a specified plugin
	 */
	public $commands = array();

	/**
	 * Collects and merges in the command information from a plugin
	 * @param string $name - The name of the plugin
	 * @param array $commands - The command set information
	 * @return void
	 */
	public function collect($name, array $commands)
	{
		$commands_index = array();
		foreach($commands as $c_name => $c_value)
		{
			$commands_index[] = $c_name;
			if(isset($this->commands[$c_name]))
			{
				Bot::core('ui')->debug('Duplicate command name within dynamic help system detected');
			}
			$this->commands[$c_name] = $c_value;
		}
		$this->index[$name] = $commands_index;
	}
}
