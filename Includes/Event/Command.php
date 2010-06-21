<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		3.0.0 DEV
 * Copyright:	(c) 2009 - 2010 -- Damian Bushong
 * License:		MIT License
 *
 *===================================================================
 *
 */

/**
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */


/**
 * Failnet - Plugin command handler class,
 * 		Used to handle commands originating with Failnet's plugins 
 * 
 *
 * @package connection
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Damian Bushong
 * @license MIT License
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

