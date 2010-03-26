<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 2
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
 * Failnet - Plugin management class,
 * 		Handles plugins, loading of plugins, calling plugin methods, and so on.
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin extends failnet_common
{
	/**
	 * @var array - Loaded Failnet plugins
	 */
	private $plugins = array();

	/**
	 * @var array - List of loaded plugins
	 */
	public $plugins_loaded = array();

	/**
	 * Loads a specified plugin if possible
	 * @param string $name - The name of the plugin to load
	 * @return boolean - Was the plugin load successful?
	 */
	public function load($name)
	{
		if(is_array($sub))
		{
			foreach($name as $sub)
			{
				$this->load($sub);
			}
		}
		else
		{
			if(!$this->loaded($name) && $this->exists($name))
			{
				$this->plugins_loaded[] = $name;
				$plugin = 'failnet_plugin_' . $name;
				$this->plugins[] = new $plugin($this);
				$this->failnet->ui->ui_system('--- Plugin "' . $name. '" loaded');
				return true;
			}
			$this->failnet->ui->ui_system('--- Plugin "' . $name . '" not loaded, plugin does not already exist or is loaded already');
			return false; // No double-loading of plugins.
		}
	}

	/**
	 * Check to see if a plugin has already been loaded, for sanity's sake.
	 * @param string $name - The name of the plugin we are checking
	 * @return boolean - Whether or not the plugin has been loaded
	 */
	public function loaded($name)
	{
		return in_array($name, $this->plugins_loaded);
	}

	/**
	 * Check if a plugin exists or not, and check every load path available
	 * @param string $name - The name of the plugin that we are checking
	 * @return boolean - Does the plugin exist?
	 */
	public function exists($name)
	{
		return (bool) failnet_autoload::exists('failnet_plugin_' . $name);
	}

// the plugin method chain-calls
	private function call($method, $params)
	{
		// meh
	}

	public function tick()
	{
		// meh
	}

	public function connect()
	{
		// meh
	}

	public function event(&$event)
	{
		// meh
	}

	public function dispatch(&$queue)
	{
		// meh
	}

	public function disconnect(&$queue)
	{
		// meh
	}
}
