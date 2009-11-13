<?php


///////////////////////////////////////////
//
// twitterPHP
// version 0.1
// By David Billingham
// david [at] slawcup [dot] com
// http://twitter.slawcup.com/twitter.class.phps
//
//
// Example 1:
//
// $t= new twitter();
// $res = $t->publicTimeline();
// if($res===false){
//   echo "ERROR<hr/>";
//     echo "<pre>";
//   print_r($t->responseInfo);
//     echo "</pre>";
// }else{
//   echo "SUCCESS<hr/>";
//     echo "<pre>";
//   print_r($res);
//     echo "</pre>";
// }
//
//
// Example 2:
//
// $t= new twitter();
// $t->username='username';
// $t->password='password';
// $res = $t->update('i am testing twitter.class.php');
// if($res===false){
//   echo "ERROR<hr/>";
//     echo "<pre>";
//   print_r($t->responseInfo);
//     echo "</pre>";
// }else{
//   echo "SUCCESS<hr/>Status Posted";
// }
//
//
//////////////////////////////////////////

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

	public function __construct($username = false, $password = false, $use_curl = false)
	{
		if($username && $password)
		{
			$this->username = $username;
			$this->password = $password;
		}
		$this->use_curl = $use_curl;
	}

// Updates the authenticating user's status.  
// Requires the status parameter specified below.
//
// status. (string) Required.  The text of your status update.  Must not be
//                             more than 160 characters and should not be
//                             more than 140 characters to ensure optimal display.
//
	public function update($status)
	{
		return $this->process(self::STATUS_URL . 'update.xml', 'status=' . urlencode($status));
	}

// Returns the 20 most recent statuses from non-protected users who have
// set a custom user icon.  Does not require authentication.
//
// sinceid. (int) Optional.  Returns only public statuses with an ID greater
//                           than (that is, more recent than) the specified ID.
//
	public function public_timeline($since = false)
	{
		return $this->process(self::STATUS_URL . 'public_timeline.xml' . ((!$since) ? '?since_id=' . (int) $since : ''));
	}
	
// Returns the 20 most recent statuses posted in the last 24 hours from the
// authenticating user and that user's friends.  It's also possible to request
// another user's friends_timeline via the id parameter below.
//
// id. (string OR int) Optional.  Specifies the ID or screen name of the user for whom
//                                to return the friends_timeline. (set to false if you
//                                want to use authenticated user).
// since. (HTTP-formatted date) Optional.  Narrows the returned results to just those
//                                         statuses created after the specified date.  
//
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

// Returns the 20 most recent statuses posted in the last 24 hours from the
// authenticating user.  It's also possible to request another user's timeline
// via the id parameter below.
//
// id. (string OR int) Optional.  Specifies the ID or screen name of the user for whom
//                                to return the user_timeline.
// count. (int) Optional.  Specifies the number of statuses to retrieve.  May not be
//                         greater than 20 for performance purposes.
// since. (HTTP-formatted date) Optional.  Narrows the returned results to just those
//                                         statuses created after the specified date.
//
	public function user_timeline($id = false, $count = 20, $since = false)
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

// Returns a single status, specified by the id parameter below.  The status's author
// will be returned inline.
//
// id. (int) Required.  Returns status of the specified ID.
//
	public function show_status($id)
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