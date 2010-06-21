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
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Failnet\Node;
use Failnet;

/**
 * Failnet - Help class,
 * 	    Failnet's handler for the dynamic help system
 *
 *
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
