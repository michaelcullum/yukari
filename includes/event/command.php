<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		3.0.0 DEV
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 */

/**
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
 */


/**
 * Failnet - Plugin command handler class,
 * 		Used to handle commands originating with Failnet's plugins 
 * 
 *
 * @package connection
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_event_command extends failnet_event_request
{
	/**
	 * @var object failnet_plugin_common - Reference to the plugin instance that created the event
	 */
	public $plugin;

	/**
	 * Method for simplifying the loading of data into the command event for execution.
	 * @param object failnet_plugin_common $plugin - The plugin object that created the event (should be passed as $this)
	 * @param string $type - The type of IRC event to generate
	 * @param array $arguments - The arguments to load the IRC event with
	 * @return void
	 */
	public function load_data($plugin, $type, array $arguments)
	{
		$this->plugin = $plugin;
		$this->type = $type;
		$this->arguments = $arguments;
	}
}

