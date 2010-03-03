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

	public function load($name)
	{
		// meh
	}

	public function loaded($name)
	{
		// meh
	}

	public function exists($name)
	{
		// meh
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
