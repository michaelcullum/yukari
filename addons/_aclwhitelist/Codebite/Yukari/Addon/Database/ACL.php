<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Codebite\Yukari\Addon\Database;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;
use \OpenFlame\Framework\Utility\JSON;
use \OpenFlame\Dbal\Query;
use \OpenFlame\Dbal\QueryBuilder;

/**
 * Yukari - ACL Whitelist database-interaction object,
 *      Provides configurable access whitelisting functionality.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class ACL
{
	public function initTables()
	{
		$q = Query::newInstance();
		$q->sql('CREATE TABLE IF NOT EXISTS acl_users
		(
			user_id INTEGER NOT NULL,
			ident TEXT NOT NULL DEFAULT "",
			is_root INTEGER NOT NULL DEFAULT 0,
			add_time INTEGER NOT NULL DEFAULT 0,
			group_id INTEGER NOT NULL DEFAULT 0,
			hostmask TEXT NOT NULL DEFAULT "",

			PRIMARY KEY (user_id)
		)')->exec();

		$q->sql('CREATE TABLE IF NOT EXISTS acl_flags
		(
			flag_id INTEGER NOT NULL,
			flag_name TEXT NOT NULL DEFAULT "",
			flag_default INTEGER NOT NULL DEFAULT 0,

			PRIMARY KEY (flag_id)
		)')->exec();

		$q->sql('CREATE TABLE IF NOT EXISTS acl_groups
		(
			group_id INTEGER NOT NULL,
			group_name TEXT NOT NULL DEFAULT "",
			group_parent INTEGER NOT NULL DEFAULT "",
			authcache TEXT NOT NULL DEFAULT "",

			PRIMARY KEY (group_id)
		)')->exec();

		$q->sql('CREATE TABLE IF NOT EXISTS acl_group_auths
		(
			group_id INTEGER NOT NULL DEFAULT 0,
			flag_id INTEGER NOT NULL DEFAULT 0,
			flag_setting INTEGER NOT NULL DEFAULT 0
		)')->exec();
	}

	public function addUser($ident, $hostmask, $is_root = false)
	{
		// asdf
	}

	public function deleteUser($user_id)
	{
		// asdf
	}

	public function setUserGroup($user_id, $group_id)
	{
		// asdf
	}

	public function addGroup($group_name)
	{
		// asdf
	}

	public function deleteGroup($group_id)
	{
		// asdf
	}

	public function setGroupParent()
	{
		// asdf
	}

	public function buildGroupAuthCache()
	{
		// asdf
	}

	public function addFlag()
	{
		// asdf
	}

	public function deleteFlag()
	{
		// asdf
	}

	public function setFlag()
	{
		// asdf
	}

	public function getAuth()
	{
		// asdf
	}

	protected function checkAuthCache()
	{
		// asdf
	}

	protected function dumpAuthCache()
	{
		// asdf
	}
}
