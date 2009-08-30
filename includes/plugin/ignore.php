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
 * Failnet - Ignore handling plugin,
 * 		Used as the shell for Failnet's user ignore system. 
 * 
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_ignore extends failnet_plugin_common
{
	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = $this->event->nick;
		$hostmask = $this->event->gethostmask();
		switch ($cmd)
		{
			case 'addignore':
			case 'ignore':
			case '+ignore':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->ignore->add_ignore($hostmask, $text);
				if(is_null($success))
				{
					$this->call_privmsg($sender, $this->failnet->deny());
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'User successfully ignored' : 'Unable to ignore user -- user hostmask already ignored');
			break;

			case 'delignore':
			case 'unignore':
			case '-ignore':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->ignore->del_ignore($hostmask, $text);
				if(is_null($success))
				{
					$this->call_privmsg($sender, $this->failnet->deny());
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'User successfully unignored' : 'Unable to ignore user -- user hostmask not ignored');
			break;
		}
	}
}

?>