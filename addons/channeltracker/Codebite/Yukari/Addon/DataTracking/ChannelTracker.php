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

namespace Codebite\Yukari\Addon\DataTracking;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;

/**
 * Yukari - Channel tracking object,
 *      Handles tracking of the channels that the bot is currently inhabiting, and provides that data to other addons.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class ChannelTracker
{
	/**
	 * @var array - Array of the channels we are inhabiting.
	 */
	protected $channels = array();

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Codebite\Yukari\Addon\DataTracker\ChannelTracker - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		Kernel::registerListener('irc.input.join', 0, array($this, 'trackChannelJoin'));
		Kernel::registerListener('irc.input.part', 0, array($this, 'trackChannelPart'));
		Kernel::registerListener('irc.input.kick', 0, array($this, 'trackChannelKick'));

		return $this;
	}

	/**
	 * Tracks us joining a channel.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function trackChannelJoin(Event $event)
	{
		if($event->get('hostmask')->getNick() == Kernel::get('irc.stack')->getNetworkOption($event->get('network'), 'nickname'))
		{
			if(!in_array($event->get('channel'), $this->channels))
			{
				array_push($this->channels, $event->get('channel'));
			}
		}
	}

	/**
	 * Tracks us parting a channel.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function trackChannelPart(Event $event)
	{
		if($event->get('hostmask')->getNick() == Kernel::get('irc.stack')->getNetworkOption($event->get('network'), 'nickname'))
		{
			$key = array_search($event->get('channel'), $this->channels);
			if($key !== false)
			{
				unset($this->channels[$key]);
			}
		}
	}

	/**
	 * Tracks us being kicked from a channel (oh noes!).
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function trackChannelKick(Event $event)
	{
		if($event->get('hostmask')->getNick() == Kernel::get('irc.stack')->getNetworkOption($event->get('network'), 'nickname'))
		{
			$key = array_search($event->get('channel'), $this->channels);
			if($key !== false)
			{
				unset($this->channels[$key]);
			}
		}
	}

	/**
	 * Check to see if we're in a specific channel
	 * @param string $channel_name - The name of the channel to check.
	 * @return boolean - Are we in the specified channel?
	 */
	public function inChannel($channel_name)
	{
		return (bool) in_array($channel_name, $this->channels);
	}

	/**
	 * Get the array of channels we are currently inhabiting.
	 * @return array - Array of channels that we have joined.
	 */
	public function getJoinedChannels()
	{
		return $this->channels;
	}
}
