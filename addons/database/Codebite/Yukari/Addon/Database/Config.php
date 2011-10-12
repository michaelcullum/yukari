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
use \OpenFlame\Dbal\Query;
use \OpenFlame\Dbal\QueryBuilder;

/**
 * Yukari - Config addon object,
 *      Handles fetching, setting of config variables in the database.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Config
{
	public function initTables()
	{
		$q = Query::newInstance();
		$q->sql('CREATE TABLE IF NOT EXISTS config
		(
			config_id INTEGER NOT NULL,
			config_name TEXT NOT NULL DEFAULT "",
			config_value TEXT DEFAULT "",
			num_value INTEGER DEFAULT 0

			PRIMARY KEY (config_id)
		)')->exec();
	}

	public function setConfig($config_name, $value)
	{
		$q = QueryBuilder::newInstance();
		$q->upsert('config')
			->set(array(
				'config_name'		=> $config_name,
				'config_value'		=> $value,
			))
			->exec();

		return $this;
	}

	public function getConfig($config_name)
	{
		$q = QueryBuilder::newInstance();
		$q->select('config_name, config_value, num_value')
			->from('config')
			->where('config_name = ?', $config_name)
			->limit(1);

		$row = $q->fetchRow();

		return (!empty($row['config_value'])) ? $row['config_value'] : (int) $row['num_value'];
	}

	public function configExists($config_name)
	{
		$q = QueryBuilder::newInstance();
		$q->select('config_id')
			->from('config')
			->where('config_name = ?', $config_name)
			->limit(1);

		$rowset = $q->fetchRowset();

		return (count($rowset)) ? true : false;
	}

	public function dropConfig($config_name)
	{
		$q = QueryBuilder::newInstance();
		$q->delete('config')
			->where('config_name = ?', $config_name);

		return $this;
	}

	public function incrementConfig($config_name, $amount = 1)
	{
		$q = QueryBuilder::newInstance();
		$q->update('config')
			->increment('num_value', (int) $amount)
			->where('config_name = ?', $config_name);

		return $this;
	}

	public function decrementConfig($config_name, $amount = 1)
	{
		$q = QueryBuilder::newInstance();
		$q->update('config')
			->increment('num_value', (int) ($amount * -1))
			->where('config_name = ?', $config_name);

		return $this;
	}
}
