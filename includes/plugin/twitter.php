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
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 * @todo check twitter API documentation for exact format of the "HTTP-formatted date" strings, see what RFC or ISO specification it follows, exactly.
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
 * twitterPHP5
 * 		A rewrite of the twitterPHP class (version 0.1) by David Billingham (@link http://twitter.slawcup.com/twitter.class.phps twitterPHP)
 *  	Rewritten to use proper PHP5 OOP and to clean up the code, along with adding a streams method for those that do not have curl available
 * @note No license was specified in the original code for twitterPHP.
 * 
 *
 * @author Damian Bushong (a.k.a. Obsidian)
 * @copyright (c) 2009 - Obsidian
 * @license GNU General Public License - Version 2
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
	 * @param string $status - Text for the status update.  Should not be more than 140 characters.
	 * @return mixed - Either SimpleXMLElement object or raw XML string if successful, false if not.
	 */
	public function tweet($status)
	{
		return $this->process(self::STATUS_URL . 'update.xml', 'status=' . urlencode($status));
	}

// Returns the 20 most recent statuses from non-protected users who have
// set a custom user icon.  Does not require authentication.
//
// sinceid. (int) Optional.  Returns only public statuses with an ID greater
//                           than (that is, more recent than) the specified ID.
//
	/**
	 * Returns the 20 most recent tweets from the public timeline.
	 * @note Authentication is not required.
	 * @param integer $since - Allows specifying statuses with an ID that is greater than this ID.
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

// Returns the authenticating user's friends, each with current status inline.  It's
// also possible to request another user's friends list via the id parameter below.
//
// id. (string OR int) Optional.  The ID or screen name of the user for whom to request
//                                a list of friends.
//
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
	
// Returns the authenticating user's followers, each with current status inline.
	public function followers()
	{
		return $this->process(self::STATUS_URL . 'followers.xml');
	}
	
// Returns a list of the users currently featured on the site with their current statuses inline.
	public function featured()
	{
		return $this->process(self::STATUS_URL . 'featured.xml');
	}
	
// Returns extended information of a given user, specified by ID or screen name as per the required
// id parameter below.  This information includes design settings, so third party developers can theme
// their widgets according to a given user's preferences.
//
// id. (string OR int) Required.  The ID or screen name of a user.
//
	public function show_user($id)
	{
		return $this->process(self::TWITTER_URL . 'users/show/' . urlencode($id) . '.xml');
	}

// Returns a list of the direct messages sent to the authenticating user.
//
// since. (HTTP-formatted date) Optional.  Narrows the resulting list of direct messages to just those
//                                         sent after the specified date.  
//
	public function get_direct_messages($since=false)
	{
		return $this->process(self::TWITTER_URL . 'direct_messages.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
	}

// Sends a new direct message to the specified user from the authenticating user.  Requires both the user
// and text parameters below.
//
// user. (string OR int) Required.  The ID or screen name of the recipient user.
// text. (string) Required.  The text of your direct message.  Be sure to URL encode as necessary, and keep
//                           it under 140 characters.  
//
	public function send_direct_message($user, $text)
	{
		return $this->process(self::TWITTER_URL . 'direct_messages/new.xml', 'user=' . urlencode($user) . '&text=' . urlencode($text));
	}
	
	public function process($url, $postargs = false)
	{
		if($this->use_curl === true)
		{
			return $this->process_curl($url, $postargs);
		}
		else
		{
			return $this->process_streams($url, $postargs);
		}
	}

// begin old code

/////////////////////////////////////////
//
// Twitter API calls
//
// $this->update($status)
// $this->publicTimeline($sinceid=false)
// $this->friendsTimeline($id=false,$since=false)
// $this->userTimeline($id=false,$count=20,$since=false)
// $this->showStatus($id)
// $this->friends($id=false)
// $this->followers()
// $this->featured()
// $this->showUser($id)
// $this->directMessages($since=false)
// $this->sendDirectMessage($user,$text)
//
// If SimpleXMLElement exists the results will be returned as a SimpleXMLElement
// otherwise the raw XML will be returned for a successful request.  If the request
// fails a FALSE will be returned.
//
//
/////////////////////////////////////////

	// internal function where all the juicy curl fun takes place
	// this should not be called by anything external unless you are
	// doing something else completely then knock youself out.
	public function process_curl($url, $postargs = false)
	{
		$curl = curl_init($url);
	
		if($postargs)
		{
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		}
		
		if($this->username && $this->password)
		{
			curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
		
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_NOBODY, 0);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
	
		$return = curl_exec($curl);
		$this->response_info = curl_getinfo($curl);
		curl_close($curl);

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
	
	public function process_streams($url, $postargs = false)
	{
		// set up to work with all of the twitter class methods
		$headers = sprintf("Authorization: Basic %s\r\n", base64_encode($this->username . ':' . $this->password));

		$context = stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => $headers . "Content-type: application/x-www-form-urlencoded\r\n",
				'content' => http_build_query($postargs),
				'timeout' => 5,
			),
		));
		$return = file_get_contents('http://twitter.com/statuses/update.xml', false, $context);

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

}


?> 