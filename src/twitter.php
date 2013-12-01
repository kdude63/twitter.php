<?php 
// originally written for twitter api 1 from http://mitgux.com/get-your-latest-tweets-with-php-and-cache-them
// modified by http://pure-essence.net for twitter API 1.1 by using lib codebird https://github.com/mynetx/codebird-php
// further modified by kdude63 to allow app-only requests without user token

require_once ('twitter/codebird.php');
\Codebird\Codebird::setConsumerKey('CONSUMER_KEY', 'CONSUMER_SECRET'); // static, see 'Using multiple Codebird instances'

class Get_Tweets {
	// Time between cache (Unit is second)
	private $max_age = 120; // 5 minutes

	private $bearer_token;
	// The max number of tweets
	private $count;
	// Trim the user informations from the data
	private $trim_user = true;
	// Twitter username
	private $user;
	private $cb;


	public function __construct($count, $user = 'kdude63') {
		// We make a default username in case the username is not set.
		$this->count = $count;
		$this->user = $user;
		$this->cb = \Codebird\Codebird::getInstance();

		// Check if token exists already.
		if (!file_exists('twitter/cache/oauth-token.txt')) {
			// If not, request a new one
			$reply = $this->cb->oauth2_token();
			$bearer_token = $reply->access_token;
			// and save it.
			$handle = fopen('twitter/cache/oauth-token.txt', 'w');
			fwrite($handle, $this->bearer_token);
			fclose($handle);
		}

		$this->bearer_token = file_get_contents('twitter/cache/oauth-token.txt');
		\Codebird\Codebird::setBearerToken($this->bearer_token);
		echo $this->bearer_token;
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

	// Read tweet(s) from cache.
	private function read_cache() {
		$tweets = json_decode(file_get_contents('twitter/cache/tweets.json'));
		return $tweets;
	}

	private function fetch_url() {
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

	public function data($returnRaw = true) {
		if ($this->time_difference() < $this->max_age) {
			$tweets = $this->read_cache();
		} else {
			$tweets = $this->fetch_url();
			if ($tweets == false) {
				// If false, request limit has been reached so read from cache.
				$tweets = $this->read_cache();
			} else {
				if(!empty($tweets)) {
					$processedTweets = array();
					foreach ($tweets as $tweet) {
						if(is_object($tweet)) {
							$tweet->text = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $tweet->text);
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

		if($returnRaw) {
			return json_encode($tweets);
		} else {
			return $tweets;
		}
	}

}

// Create a new instance
$count = isset($_GET['count']) ? $_GET['count'] : 4;
$user = isset($_GET['user']) ? $_GET['user'] : 'kdude63';
$get_tweets = new Get_Tweets($count, $user);

// Get data
$tweets = $get_tweets->data();
header('Content-Type: application/json');
echo($tweets);

?>
