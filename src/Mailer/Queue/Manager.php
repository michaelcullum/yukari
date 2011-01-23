<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     mailer
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
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
 * Failnet - Mailer queue management,
 * 	    Provides a sleep() free alternative to the Throttler Plugin in Swiftmailer, for throttling the number of messages sent in a certain time period.
 *
 * @category    Failnet
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Manager
{
	/**
	 * @var array - Array of Failnet\Mailer\Queue\Entry objects, which are embedded with Swift_Mail objects waiting to be sent.
	 */
	protected $queue = array();

	/**
	 * @var array - Array of DateTime objects from when previous queue entries were dispatched; this is used to determine if we can send any more emails yet.
	 */
	protected $dispatch_times = array();

	/**
	 * @var integer - The maximum amount of messages to send in an hour.  Any more messages to be sent will be automatically queued.
	 */
	protected $throttle = 0;

	public function sendMail(Swift_Message $message)
	{
		// Wrap the new message as a queue entry whether or not queueing is needed (or even enabled)
		$entry = new Failnet\Mailer\Queue\Entry($message);

		// Check to see if we have a throttle at all.
		if($this->throttle !== 0)
		{
			$send_limit = $this->throttle - sizeof($this->dispatch_times);
		}
		else
		{
			$send_limit = sizeof($this->dispatch_times);
		}

		// Do we need to queue?
		if($send_limit > 0)
		{
			// We can send immediately...
			$this->dispatchEntry($entry);
		}
		else
		{
			// We need to queue the message, we're throttling at the moment.
			array_push($this->queue, $entry);
		}
	}

	/**
	 * Run through the message queue and see if we can send any messages yet, and if so, fire off some emails
	 * @return mixed - Boolean false returned if we couldn't send any emails yet, or an integer containing the number of emails sent.
	 */
	public function runQueue()
	{
		// If we don't have a queue of messages, return integer 0 to indicate that no messages were sent and that the queue is not in use
		if(empty($this->queue))
			return 0;

		$now = new DateTime('now', Bot::getObject('core.timezone'));
		foreach($this->dispatch_times as $key => $dispatch_time)
		{
			$diff = $dispatch_time->diff($now, true);

			/* @var $diff DateInterval */
			if($diff->h >= 1)
				unset($this->dispatch_times[$key]);
		}

		// Are we not under our throttle limit still?
		// @note if $this->throttle is set to 0, the throttling is disabled, and we'll just dump all of our messages to the SMTP server.
		if(sizeof($this->dispatch_times) >= $this->throttle && $this->throttle !== 0)
			return false;

		// I guess not.  How many openings do we have?
		if($this->throttle !== 0)
		{
			$send_limit = $this->throttle - sizeof($this->dispatch_times);
		}
		else
		{
			$send_limit = sizeof($this->dispatch_times);
		}

		for($i = 0; $i < $send_limit; $i++)
		{
			$this->dispatchNextEntry();
		}

		// That's it for now, return the number of emails sent.
		return $i;
	}

	protected function dispatchNextEntry()
	{
		$entry = array_shift($this->queue);
		return $this->dispatchEntry($entry);
	}

	/**
	 * asdf
	 */
	protected function dispatchEntry(Failnet\Mailer\Queue\Entry $entry)
	{
		// asdf
		// will handle the actual sending of emails from their Failnet\Mailer\Queue\Entry wrapper, along with adding in the necessary dispatch time entry
	}

	protected function addDispatchTime()
	{
		$dispatch = new DateTime('now', Bot::getObject('core.timezone'));
		array_push($this->dispatch_times, $dispatch);
	}

	public function usingQueue()
	{
		// return whether or not we currently have to store emails in the queue
	}

	/**
	 * Get the hourly email throttle limit.
	 * @return integer - The number of emails we're limited to sending in an hour.
	 */
	public function getThrottle()
	{
		return $this->throttle;
	}

	/**
	 * Set the hourly email throttle limit.
	 * @param integer $throttle - The number of emails to limit ourselves to sending in an hour.
	 * @return void
	 */
	public function setThrottle($throttle = 0)
	{
		$this->throttle = (int) $throttle;
	}
}
