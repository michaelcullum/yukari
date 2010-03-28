<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	2.0.0 Alpha 2
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
	 * @var array - Array of plugin-generated events that we want to dispatch
	 */
	public $event_queue = array();

	/**
	 * Quick method to make event queue management easier
	 * @param array $events - Array of events that we're merging into the event queue.
	 * @return array - Empty array used to clear out event queue within plugins
	 */
	public function queue(array $events)
	{
		$this->event_queue = array_merge($this->event_queue, $events);
		return array();
	}

	/**
	 * Loads a specified plugin if possible
	 * @param string $name - The name of the plugin to load
	 * @return boolean - Was the plugin load successful?
	 */
	public function pluginLoad($name)
	{
		if(is_array($sub))
		{
			foreach($name as $sub)
			{
				$this->pluginLoad($sub);
			}
		}
		else
		{
			if(!$this->pluginLoaded($name) && $this->pluginExists($name))
			{
				$this->plugins_loaded[] = $name;
				$plugin_class = 'failnet_plugin_' . $name;
				$plugin = new $plugin_class($this);

				if(!$plugin->checkDependencies())
				{
					failnet::core('ui')->system("--- Plugin '$name' not loaded, dependency check failed");
					return false;
				}
				$this->plugins[] = $plugin;
				failnet::core('ui')->system("--- Plugin '$name' loaded");
				return true;
			}
			failnet::core('ui')->system("--- Plugin '$name' not loaded, plugin does not already exist or is loaded already");
			return false; // No double-loading of plugins.
		}
	}

	/**
	 * Check to see if a plugin has already been loaded, for sanity's sake.
	 * @param string $name - The name of the plugin we are checking
	 * @return boolean - Whether or not the plugin has been loaded
	 */
	public function pluginLoaded($name)
	{
		return in_array($name, $this->plugins_loaded);
	}

	/**
	 * Check if a plugin exists or not, and check every load path available
	 * @param string $name - The name of the plugin that we are checking
	 * @return boolean - Does the plugin exist?
	 */
	public function pluginExists($name)
	{
		return (bool) failnet_autoload::fileExists('failnet_plugin_' . $name);
	}

	/**
	 * Plugin tick call, fires off tick calls to any plugins that handle them.
	 * @return array - Returns the array of events to fire off
	 */
	public function tick()
	{
		// Upon each iteration of the loop, we let plugins run if they want tow
		foreach($this->plugins as $name => $plugin)
		{
			if(method_exists($plugin, 'tick'))
			{
				failnet::core('ui')->event('tick call: plugin "' . $name . '"');
				$plugin->tick();
				if(!empty($plugin->events))
					$plugin->events = $this->queue($plugin->events);
			}
		}
	}

	public function event(&$event)
	{
		$event_type = ($event instanceof failnet_event_response) ? 'response' : $event_type;
		foreach($this->plugins as $name => $plugin)
		{
			if(method_exists($plugin, 'cmd_' . $event_type))
			{
				failnet::core('ui')->event('command event call (' . $event_type . '): plugin "' . $name . '"');
				$plugin->event = $event;
				$plugin->{'cmd_' . $event_type}();
				if(!empty($plugin->events))
					$plugin->events = $this->queue($plugin->events);
			}
		}
	}

	public function dispatch()
	{
		//Execute pre-dispatch callback for plugin events
		foreach($this->plugins as $name => $plugin)
		{
			failnet::core('ui')->event('pre-dispatch call: plugin "' . $name . '" - events: ' . sizeof($this->event_queue));
			$plugin->pre_dispatch($this->event_queue);
		}

		// Time to fire off our events
		$quit = NULL;
		foreach($this->event_queue as $event)
		{
			if(strcasecmp($event->type, 'quit') != 0)
			{
				failnet::core('ui')->event('event dispatch call: type "' . $event->type . '"');
				call_user_func_array(array($this->failnet->irc, $event->type), $event->arguments);
			}
			elseif(empty($quit))
			{
				$quit = $event;
			}
		}

		// Post-dispatch events
		foreach($this->plugins as $name => $plugin)
		{
			failnet::core('ui')->event('post-dispatch call: plugin "' . $name . '" - events: ' . sizeof($this->event_queue));
			$plugin->post_dispatch($this->event_queue);
		}

		if($quit)
			return $quit;
		return true;
	}

	/**
	 * Plugin connect call, fires off connect calls to any plugins that handle them.
	 * @return void
	 */
	public function connect()
	{
		foreach($this->plugins as $name => $plugin)
		{
			if(method_exists($plugin, 'connect'))
			{
				failnet::core('ui')->event('connection established call: plugin "' . $name . '"');
				$plugin->cmd_connect();
				if(!empty($plugin->events))
					$plugin->events = $this->queue($plugin->events);
			}
		}
	}

	/**
	 * Plugin disconnect call, fires off disconnect calls to any plugins that handle them.
	 * @return void
	 */
	public function disconnect()
	{
		foreach($this->plugins as $name => $plugin)
		{
			if(method_exists($plugin, 'disconnect'))
			{
				failnet::core('ui')->event('disconnect call: plugin "' . $name . '"');
				$plugin->cmd_disconnect();
			}
		}
	}
}
