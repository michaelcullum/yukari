<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
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
 * Failnet - Help plugin,
 * 		Aids users in learning to control Failnet.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_help extends failnet_plugin_common
{
	public function help(&$name, &$commands)
	{
		$name = 'help';
		$commands = array(
			'help-p'		=> 'help-p - (no auth) - Lists the plugins with help entries available',
			'help-c'		=> 'help-c {$plugin} - (no auth) - Lists the commands with help entries available under the specified plugin',
			'help'			=> 'help {$command} - (no auth) - Returns the help entry for the specified command, if there is one available',
		);
	}

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$this->set_msg_args(($this->failnet->config('speak')) ? $this->event->source() : $this->event->hostmask->nick);

		$sender = $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch ($cmd)
		{
			case 'help-p':
				$this->msg('Plugins with commands: ' . implode(', ', array_keys($this->failnet->help->index)));
			break;

			case 'help-c':
				if($text !== false)
				{
					$this->msg((isset($this->failnet->help->index[$text])) ? 'Commands available in plugin: ' . implode(', ', $this->failnet->help->index[$text]) : 'Invalid plugin specified.');
				}
				else
				{
					$this->msg('Please specify a plugin for retrieval of command help documentation');
				}
			break;

			case 'help':
				if($text !== false)
				{
					$this->msg((isset($this->failnet->help->commands[$text])) ? 'Command documentation: ' . $this->failnet->config('cmd_prefix') . $this->failnet->help->commands[$text] : 'Invalid command specified.');
				}
				else
				{
					$this->msg('Please specify a command for retrieval of command help documentation');
					$this->msg('For general help, please try ' . $this->failnet->config('cmd_prefix') . 'help help, ' . $this->failnet->config('cmd_prefix') . 'help help-p, and ' . $this->failnet->config('cmd_prefix') . 'help help-c');
				}
			break;
		}
	}
}
