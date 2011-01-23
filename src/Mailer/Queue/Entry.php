<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     mailer
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Mailer\Queue;
use Failnet\Bot as Bot;

/**
 * Failnet - Mailer queue entry,
 * 	    Wraps around Swift_Message entries handed to the queue.
 *
 * @category    Yukari
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Entry
{
	/**
	 * @var Swift_Message - The message stored in this queue entry.
	 */
	protected $message;

	/**
	 * @var DateTime - The time this queue entry was created.
	 */
	protected $queued_time;

	/**
	 * Constructor
	 * @param Swift_Message $message - The message to queue
	 * @return void
	 */
	public function __construct(Swift_Message $message)
	{
		$this->setMessage($message);
		$this->setTime(new DateTime('now', Bot::getObject('core.timezone')));
	}

	/**
	 * Get the queued message.
	 * @return Swift_Message - The message that was stored in this queue entry.
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Set the message that we are queueing.
	 * @param Swift_Message $message - The message to store.
	 * @return void
	 */
	public function setMessage(Swift_Message $message)
	{
		$this->message = $message;
	}

	/**
	 * Get the time that the message was queued.
	 * @return DateTime - The time that the message was queued.
	 */
	public function getTime()
	{
		return $this->queued_time;
	}

	/**
	 * Set the time that the message is being queued at.
	 * @param DateTime $time - The time that the message is being queued at.
	 * @return void
	 */
	public function setTime(DateTime $time)
	{
		$this->queued_time = $time;
	}
}
