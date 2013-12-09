<?php
// originally written for twitter api 1 from http://mitgux.com/get-your-latest-tweets-with-php-and-cache-them
// modified by http://pure-essence.net for twitter API 1.1 by using lib codebird https://github.com/mynetx/codebird-php
// further modified by kdude63 to allow app-only requests without user token https://github.com/kdude63/twitter.php

require_once ('twitter/codebird.php');
function set_keys($ckey, $csecret) {
	\Codebird\Codebird::setConsumerKey($ckey, $csecret) ; // static, see 'Using multiple Codebird instances'
}

class get_tweets {
	// How old can the cache be before we refresh it?
	private $max_age = 120;
	// Who do we retrieve the tweets from?
	private $user;
	// How many do we retrieve?
	private $count;
	// Trim the user's information from the data?
	private $trim_user = true;

	private $cb;
	private $bearer_token;

	public function __construct($count = 1, $user = 'kdude63', $ckey, $csecret) {

		// We make a default username in case the username is not set.
		$this->count = $count;
		$this->user = $user;
		$this->cb = \Codebird\Codebird::getInstance();
		set_keys($ckey, $csecret);

		// Check if token exists already.
		if (!file_exists('twitter/cache/oauth-token.txt')) {
			// If not, request a new one
			$reply = $this->cb->oauth2_token();
			$this->bearer_token = $reply->access_token;
			// and save it.
			$handle = fopen('twitter/cache/oauth-token.txt', 'w');
			fwrite($handle, $this->bearer_token);
			fclose($handle);
		}

		$this->bearer_token = file_get_contents('twitter/cache/oauth-token.txt');
		\Codebird\Codebird::setBearerToken($this->bearer_token);
	}

	// Read tweet(s) from cache.
	private function read_cache() {
		$tweets = json_decode(file_get_contents('twitter/cache/tweets.json'));
		return $tweets;
	}

	// Save tweet and current time to cache.
	private function save_cache($data) {
		$handle = fopen('twitter/cache/tweets.json', 'w');
		fwrite($handle, json_encode($data));
		fclose($handle);
		$handle = fopen('twitter/cache/last-cache-time.txt', 'w');
		fwrite($handle, date('c'));
		fclose($handle);
	}

	// Fetch data from Twitter servers.
	private function fetch_data() {
		$params = array(
			'screen_name' => $this->user,
			'count' => $this->count,
			'trim_user' => $this->trim_user
		);
		$reply = $this->cb->statuses_userTimeline($params);
		if ($reply->httpstatus == 429) {
			return false;
		} else {
			return $reply;
		}
	}

	// Get difference in seconds from last cache and now.
	private function time_difference(){
		if (!file_exists('twitter/cache/last-cache-time.txt')) {
			$handle = fopen('twitter/cache/last-cache-time.txt', 'w');
			fwrite($handle, '2000-01-01T12:12:12+00:00');
			fclose($handle); 
		}
		$prevDate = file_get_contents('twitter/cache/last-cache-time.txt');
		$dateOne = new DateTime($prevDate);
		$dateTwo = new DateTime(date('c'));
		$diff = $dateTwo->format('U') - $dateOne->format('U');

		return $diff;
	}

	private function timeago($date) {
		$dateOne = new DateTime($date);
		$dateTwo = new DateTime(date('c'));
		$interval = $dateOne->diff($dateTwo);
		if ($interval->y != 0) {
			$unit = $interval->y == 1 ? 'year' : 'years';
			$ts= 'About '.$interval->y. ' ' . $unit .' ago';
		} elseif($interval->m != 0){
			$unit = $interval->m == 1 ? 'month' : 'months';
			$ts= 'About '.$interval->m. ' ' . $unit .' ago';
		} elseif($interval->d != 0){
			$unit = $interval->d == 1 ? 'day' : 'days';
			$ts= 'About '.$interval->d. ' ' . $unit .' ago';
		} elseif($interval->h != 0){
			$unit = $interval->h == 1 ? 'hour' : 'hours';
			$ts= 'About '.$interval->h. ' ' . $unit .' ago';
		} elseif($interval->i != 0){
			$unit = $interval->i == 1 ? 'minute' : 'minutes';
			$ts= 'About '.$interval->i. ' ' . $unit .' ago';
		} elseif($interval->s != 0){
			$unit = $interval->s == 1 ? 'second' : 'seconds';
			$ts= $interval->s. ' ' . $unit .' ago';
		}
		return $ts;
	}

	public function data($returnJson = true, $processTweets = true) {
		if ($this->time_difference() < $this->max_age) {
			$tweets = $this->read_cache();
		} else {
			$tweets = $this->fetch_data();
			if ($tweets == false) {
				// If false, request limit has been reached so read from cache.
				$tweets = $this->read_cache();
			} else {
				if(!empty($tweets) && $processTweets) {
					$processedTweets = array();
					foreach ($tweets as $tweet) {
						if(is_object($tweet)) {
							// Turns newlines into <br> tags
							$tweet->text = nl2br($tweet->text);
							// Turns URLs into links
							$tweet->text = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $tweet->text);
							// Turns the timestamp into relative time (eg "about an hour ago")
							$tweet->created_at = $this->timeago($tweet->created_at);

							$processedTweets[] = $tweet;
						}
					}
					$tweets = $processedTweets;
				}
				// Get the newest data and save it to the cache.
				$this->save_cache($tweets);
			}
		}

		if($returnJson) {
			return json_encode($tweets);
		} else {
			return $tweets;
		}
	}
}

// Create a new instance
$count = isset($_GET['count']) ? $_GET['count'] : 4;
$user = isset($_GET['user']) ? $_GET['user'] : 'kdude63';

$ckey = 'YOUR CONSUMER KEY HERE';
$csecret = 'YOUR CONSUMER SECRET HERE';

$get_tweets = new get_tweets($count, $user, $ckey, $csecret);

// Get data
$tweets = $get_tweets->data(true, true);
header('Content-Type: application/json');
echo($tweets);

?>
