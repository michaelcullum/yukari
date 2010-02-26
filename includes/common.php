<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
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
 * Failnet - Base class,
 * 		Used as the common base class for all of Failnet's class files (at least the ones that need one)
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
abstract class failnet_common
{
	/**
	 * @var object failnet_core - The mothership itself.
	 */
	protected $failnet;

	/**
	 * Constants for Failnet.
	 */

	/**
	 * Auth levels for Failnet
	 */
	const AUTH_OWNER = 6;
	const AUTH_SUPERADMIN = 5;
	const AUTH_ADMIN = 4;
	const AUTH_TRUSTEDUSER = 3;
	const AUTH_KNOWNUSER = 2;
	const AUTH_REGISTEREDUSER = 1;
	const AUTH_UNKNOWNUSER = 0;

	/**
	 * IRC mode flags
	 * @deprecated
	 */
	const IRC_FOUNDER = 32;
	const IRC_ADMIN = 16;
	const IRC_OP = 8;
	const IRC_HALFOP = 4;
	const IRC_VOICE = 2;
	const IRC_REGULAR = 1;

	/**
	 * Constructor method.
	 * @param object failnet_core $failnet - The Failnet core object.
	 * @return void
	 */
	public function __construct(failnet_core $failnet)
	{
		$this->failnet = $failnet;
		$this->init();
	}

	/**
	 * Handler method for class load
	 * @return void
	 */
	abstract public function init();

	/**
	 * Magic method __call, checks to see if a method that is called exists in the master class, and if not it throws a warning accordingly.
	 * @param string $name - The name of the method that is being called
	 * @param array $arguments - The arguments that are being passed to the specified method
	 * @return mixed
	 */
	public function __call($name, array $arguments)
	{
		if(!method_exists($this->failnet, $name))
		{
			trigger_error('Call to undefined method "' . $name . '" in class "' . __CLASS__ . '"', E_USER_WARNING);
		}
		else
		{
			return call_user_func_array(array($this->failnet, $name), $arguments);
		}
	}
}
