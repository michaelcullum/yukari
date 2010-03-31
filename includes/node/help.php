<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	2.1.0 DEV
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 *
 *===================================================================
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 *
 */


/**
 * Failnet - Help class,
 * 		Failnet's handler for the dynamic help system
 *
 *
 * @package nodes
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_node_help extends failnet_common
{
	public $index = array();

	public $commands = array();

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init() { }

	public function collect($name, $commands)
	{
		$commands_index = array();
		foreach($commands as $c_name => $c_value)
		{
			$commands_index[] = $c_name;
			if(isset($this->commands[$c_name]))
			{
				$this->failnet->ui->ui_debug('Duplicate command name within dynamic help system detected');
			}
			$this->commands[$c_name] = $c_value;
		}
		$this->index[$name] = $commands_index;
	}
}
