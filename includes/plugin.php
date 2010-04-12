<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
				$plugin_class = 'failnet_plugin_' . $name;
				$this->plugins_loaded[$name] = $plugin_class;
				$this->plugins[$name] = new $plugin_class($this);

				if(!$plugin->checkDependencies())
				{
					failnet::core('ui')->system("--- Plugin '$name' load failed, unmet dependencies found");
					$this->pluginRemove($name);
					return false;
				}
				failnet::core('ui')->system("--- Plugin '$name' loaded");
				return true;
			}
			failnet::core('ui')->system("--- Plugin '$name' load failed, plugin does not exist or has been loaded already");
			return false; // If a plugin was removed, we don't want to reinstantiate it...
		}
	}

	/**
	 * Check to see if a plugin has already been loaded, for sanity's sake.
	 * @param string $name - The name of the plugin we are checking
	 * @return boolean - Whether or not the plugin has been loaded
	 */
	public function pluginLoaded($name)
	{
		return isset($this->plugins_loaded[$name]);
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
	 * Removes a plugin from the list of loaded plugins.
	 * @param string $name - The name of the plugin to remove.
	 * @return void
	 */
	public function pluginRemove($name)
	{
		if($this->pluginLoaded($name) && isset($this->plugins[$name]))
			unset($this->plugins[$name]);
	}

	/**
	 * Plugin tick call, fires off tick calls to any plugins that handle them.
	 * @return array - Returns the array of events to fire off
	 */
	public function handleTick()
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

	/**
	 * Plugin connect call, fires off connect calls to any plugins that handle them.
	 * @return void
	 */
	public function handleConnect()
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
	 * Event chain-handler
	 * @param failnet_event_response|failnet_event_request $event - The event to hand down to the other plugins.
	 * @return void
	 */
	public function handleEvent(failnet_event_common $event)
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

	/**
	 * Handles the dispatch of created events from individual plugins
	 * @return mixed - True if successful, event that contains a quit call if not.
	 */
	public function handleDispatch()
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
	 * Plugin disconnect call, fires off disconnect calls to any plugins that handle them.
	 * @return void
	 */
	public function handleDisconnect()
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
