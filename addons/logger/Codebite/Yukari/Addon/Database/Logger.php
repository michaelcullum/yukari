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
use \Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;
use \OpenFlame\Framework\Utility\JSON;
use \OpenFlame\Dbal\Query;
use \OpenFlame\Dbal\QueryBuilder;


/**
 * Yukari - Database logging class,
 * 	    Used to log events as they occur to a database.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Logger
{
	protected $log_cache = array();

	protected $log_cache_size = 0;

	public function __construct()
	{
		$this->log_cache_size = (int) Kernel::getConfig('log.cache');
		if($this->log_cache_size < 0)
		{
			$this->log_cache = 0;
		}
	}

	public function initTables()
	{
		$q = Query::newInstance();
		$q->sql('CREATE TABLE IF NOT EXISTS logs
		(
			log_id INTEGER NOT NULL,
			ident TEXT NOT NULL DEFAULT "",
			time INTEGER NOT NULL DEFAULT 0,
			network TEXT NOT NULL DEFAULT "",
			event_type TEXT NOT NULL DEFAULT "",
			source TEXT NOT NULL DEFAULT "",
			destination TEXT NOT NULL DEFAULT "",
			data TEXT NOT NULL DEFAULT "",

			PRIMARY KEY (log_id)
		)')->exec();
	}

	public function newLogEntry($event_type, $network, $source, $destination, $data, $ident = '')
	{
		$this->log_cache[] = array(
			'event_type'		=> $event_type ?: "NONE",
			'network'			=> $network ?: "NONE",
			'source'			=> $source ?: "NONE",
			'destination'		=> $destination ?: "NONE",
			'data'				=> JSON::encode($data) ?: "NONE",
			'ident'				=> $ident ?: "NONE",
			'time'				=> time(),
		);

		if(sizeof($this->log_cache) > $this->log_cache_size)
		{
			$this->insertEntry($this->log_cache);
			$this->log_cache = array();
		}

		return $this;
	}

	protected function insertEntry(array $inserts)
	{
		foreach($inserts as $insert)
		{
			$q = QueryBuilder::newInstance();
			$q->insert('logs')
				->set($insert)
				->exec();
		}
	}

	public function __destruct()
	{
		foreach($this->log_cache as $insert)
		{
			$q = QueryBuilder::newInstance();
			$q->insert('logs')
				->set($insert)
				->exec();
		}
	}
}
