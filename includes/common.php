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
 * Failnet - Base class,
 * 		Used as the common base class for all of Failnet's class files (at least the ones that need one) 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
abstract class failnet_common
{
	/**
	 * The mothership itself.
	 * @var failnet_core object
	 */
	protected $failnet;
	
	/**
	 * Constants for Failnet.
	 */
	const HR = '---------------------------------------------------------------------';
	const ERROR_LOG = 'error';
	const USER_LOG = 'user';
	
	public function __construct(failnet_core &$failnet)
	{
		$this->failnet = &$failnet;
		$this->init();
	}

	abstract public function init();
	
	public function __call($funct, $params)
	{
		trigger_error('Bad function call "' . $funct . '" with params "' . implode(', ', $params) . '" to "' . __CLASS__ . '" class.', E_USER_WARNING);
	}
}

?>