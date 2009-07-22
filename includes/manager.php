<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Failnet Project
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 *
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
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
  * @ignore
  */
if(!defined('IN_FAILNET')) exit(1);

/**
 * Failnet - Plugin base class,
 * 		Used as the common base class for all of Failnet's plugin class files 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_manager extends failnet_common
{
	public $plugins_loaded = array();
 	
	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init() {  }
	
	/**
	 * Load a specific plugin
	 * @param string $plugin - The name of the plugin to load, omitting the failnet_plugin_ class prefix 
	 * @return boolean - If plugin loading was successful
	 */
 	public function load($plugin)
	{
		if(!in_array($plugin, $plugins_loaded))
		{
			$plugins_loaded[] = $plugin;
			$plugin = 'failnet_plugin_' . $plugin;
			$this->failnet->plugins[$plugin] = new $plugin($this->failnet);
			return true;
		}
		return false; // No double-loading of plugins.
	}
 	
	/**
	 * Load an array of plugins
	 * @param array $plugins - An array of plugin names, omitting the failnet_plugin_ class prefix
	 * @return void
	 */
	public function multiload(array $plugins)
	{
		foreach ($plugins as $plugin)
		{
			$this->load($plugin);
		}
	}
}
 
 ?>