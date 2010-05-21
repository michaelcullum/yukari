<?php
/**
 *
 *===================================================================
 *
 *  twitterPHP5
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		0.2.0
 * Copyright:	(c) 2009 - Obsidian
 * License:		GNU General Public License, Version 3
 *
 *===================================================================
 *
 * @todo check twitter API documentation for exact format of the "HTTP-formatted date" strings, see what RFC or ISO specification it follows, exactly.
 */

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 

/**
 * twitterPHP5
 * 		A rewrite of the twitterPHP class (version 0.1) by David Billingham (@link http://twitter.slawcup.com/twitter.class.phps twitterPHP)
 *  	Rewritten to use proper PHP5 OOP and to clean up the code, along with adding a streams method for those that do not have curl available
 * @note No license was specified in the original code for twitterPHP.
 * 
 *
 * @author Damian Bushong (a.k.a. Obsidian)
 * @copyright (c) 2009 - Obsidian
 * @license GNU General Public License, Version 3
 */
class twitter
{
	/**
	 * @var string - Twitter account username
	 */
	private $username = '';

	/**
	 * @var string - Twitter account password
	 */
	private $password = '';

	/**
	 * @var string - User agent for this request
	 */
	public $user_agent = '';

	/**
	 * @var string - Name of the twitter client
	 */
	public $client_name = 'twitterPHP5'; //'X-Twitter-Client: '

	/**
	 * @var string - Version stamp for the twitter client
	 */
	public $client_version = '0.2.0'; //'X-Twitter-Client-Version: '

	/**
	 * @var string - Home page for the twitter client
	 */
	public $client_url = 'http://phpbbmodders.net/'; //'X-Twitter-Client-URL: '

	/**
	 * @var boolean - Are we using curl or streams?
	 */
	public $use_curl = false;

	/**
	 * @var array - cURL response info
	 */
	public $response_info = array();

	/**
	 * @const - Twitter API URL
	 */
	const TWITTER_URL = 'http://twitter.com/';

	/**
	 * @const - Twitter status API URL
	 */
	const STATUS_URL = 'http://twitter.com/statuses/';

	/**
	 * Constructor method for class, loads username/password if necessary, also sets if curl should be used or not
	 * @param boolean $use_curl - Should we use curl or streams for this set of requests?
	 * @param string $username - What is the username of the user we are authenticating as, if we need to do so?
	 * @param string $password - What is the password of the user we are authenticating as, if we need to do so?
	 * @return void
	 */
	public function __construct($use_curl = true, $username = false, $password = false)
	{
		if($username && $password)
		{
			$this->username = $username;
			$this->password = $password;
		}
		$this->use_curl = (bool) $use_curl;
	}

	/**
	 * Posts a tweet as the specified user.
	 * @param string $status - Text for the tweet.  Should not be more than 140 characters.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function tweet($status)
	{
		return $this->process(self::STATUS_URL . 'update.xml', array('status' => urlencode($status)));
	}

	/**
	 * Returns the 20 most recent tweets from the public timeline.
	 * @note Authentication is not required.
	 * @param integer $since - Allows specifying tweets with an ID that is greater than this ID.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function public_timeline($since = false)
	{
		return $this->process(self::STATUS_URL . 'public_timeline.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
	}

	/**
	 * Returns the 20 most recent tweets posted in the last 24 hours from the authenticating user and that user's friends.
	 * If a user is specified with the first param, $id, you may be able to request that user's friends timeline instead.
	 * @param mixed $id - The user ID or screen name of the user if you wish to return a timeline for a user other than the one authenticating.
	 * @param string $since - HTTP-formatted date, used for narrowing down the results to those after this date.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function friends_timeline($id = false, $since = false)
	{
		if(!$id)
		{
			return $this->process(self::STATUS_URL . 'friends_timeline.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
		}
		else
		{
			return $this->process(self::STATUS_URL . 'friends_timeline/' . urlencode($id) . '.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
		}
	}

	/**
	 * Retrieves the 20 most recent tweets from the user that we're logging in as, for the last 24 hours.
	 * If a user is specified with the first param, $id, you may be able to request that user's timeline instead.
	 * @param mixed $id - The user ID or screen name of the user if you wish to return a timeline for a user other than the one authenticating.
	 * @param string $since - HTTP-formatted date, used for narrowing down the results to those after this date.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function user_timeline($id = false, $since = false)
	{
		if(!$id)
		{
			return $this->process(self::STATUS_URL . 'user_timeline.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
		}
		else
		{
			return $this->process(self::STATUS_URL . 'user_timeline/' . urlencode($id) . '.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
		}
	}

	/**
	 * Returns a specific tweet.
	 * @param integer $id - The ID that we are pulling the tweet data for.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function show_tweet($id)
	{
		return $this->process(self::STATUS_URL . 'show/' . (int) $id . '.xml');
	}

	/**
	 * Retrieves the friends of the specified user, including their latest tweet.
	 * @param integer $id - The user ID or screen name of the user if you wish to return the friends of for a user other than the one authenticating.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function friends($id = false)
	{
		if(!$id)
		{
			return $this->process(self::STATUS_URL . 'friends.xml');
		}
		else
		{
			return $this->process(self::STATUS_URL . 'friends/' . urlencode($id) . '.xml');
		}
	}

	/**
	 * Returns the user's followers, and their latest tweet.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function followers()
	{
		return $this->process(self::STATUS_URL . 'followers.xml');
	}

	/**
	 * Returns a list of the users currently featured on Twitter and their latest tweet.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function featured()
	{
		return $this->process(self::STATUS_URL . 'featured.xml');
	}

	/**
	 * Returns extended information about the user, specified by ID or screen name as per the $id param.
	 * @param mixed $id - Either the screen name or user ID of the specified user to look up.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function show_user($id)
	{
		return $this->process(self::TWITTER_URL . 'users/show/' . urlencode($id) . '.xml');
	}

	/**
	 * Retrieves the direct messages sent to the user
	 * @param string $since - HTTP-formatted date, used for narrowing down the results to those after this date.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function get_direct_messages($since = false)
	{
		return $this->process(self::TWITTER_URL . 'direct_messages.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
	}

	/**
	 * Sends a direct message from the specified user as the authenticating user.
	 * @param mixed $user - The user ID or screen name of the intended recipient.
	 * @param string $text - The text of the direct message, should not exceed 140 characters.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function send_direct_message($user, $text)
	{
		return $this->process(self::TWITTER_URL . 'direct_messages/new.xml', 'user=' . urlencode($user) . '&text=' . urlencode($text));
	}

	/**
	 * Trainswitch method.  Directs the processing method call to the appropriate method, depending on whether or not we want to use curl.
	 * @param string $url - The URL to direct the request to.
	 * @param array $postargs - Any extra data necessary to send.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function process($url, $postargs = false)
	{
		if($this->use_curl === true)
		{
			$postarg_str = '';
			foreach($postargs as $postarg_k => $postarg_v)
			{
				$postarg_str .= $postarg_k . '=' . $postarg_v;
			}
			return $this->process_curl($url, $postargs);
		}
		else
		{
			return $this->process_streams($url, $postargs);
		}
	}

	/**
	 * Internal method for processing the request and sending the curl shiz through.
	 * This method is protected for a reason.  If you make it a public method, you will be attacked by raptors, no questions asked.
	 * @param string $url - The URL to direct the request to.
	 * @param string $postargs - Any extra data necessary to send.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	private function process_curl($url, $postargs = false)
	{
		// Begin the curl.
		$curl = curl_init($url);

		// If we've post args, we should include them.
		if($postargs)
		{
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		}

		// If we are authenticating, we may want to do so.  Just a thought.
		if($this->username && $this->password)
		{
			curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}

		// And curl stuff that noooobody caaarrres about.
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_NOBODY, 0);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);

		// Sent that curl request and get our data.
		$return = curl_exec($curl);
		$this->response_info = curl_getinfo($curl);
		curl_close($curl);

		// Is everything okay?
		if((int) $this->response_info['http_code'] === 200)
		{
			if(class_exists('SimpleXMLElement'))
			{
				$xml = new SimpleXMLElement($return);
				return $xml;
			}
			else
			{
				return $return;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Internal method for processing the request and sending the streams shiz through.
	 * This method is protected for a reason.  If you make it a public method, you will be attacked by raptors, no questions asked.
	 * @param string $url - The URL to direct the request to.
	 * @param string $postargs - Any extra data necessary to send.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	private function process_streams($url, $postargs = false)
	{
		// If we want to authenticate, probably want to do so.
		if($this->username && $this->password)
		{
			$headers = sprintf("Authorization: Basic %s\r\n", base64_encode($this->username . ':' . $this->password));
		}

		$context = stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => $headers . "Content-type: application/x-www-form-urlencoded\r\n",
				'content' => (!empty($postargs)) ? http_build_query($postargs) : '',
				'timeout' => 5,
			),
		));

		// Send the file_get_contents request.
		$return = file_get_contents($url, false, $context);

		// Can we do SimpleXML?
		if($return !== false)
		{
			if(class_exists('SimpleXMLElement'))
			{
				$xml = new SimpleXMLElement($return);
				return $xml;
			}
			else
			{
				return $return;
			}
		}
		else
		{
			return false;
		}
	}

}

 