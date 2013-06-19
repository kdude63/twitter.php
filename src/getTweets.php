<?php 
// originally written for twitter api 1 from http://mitgux.com/get-your-latest-tweets-with-php-and-cache-them
// modified by http://pure-essence.net for twitter API 1.1 by using lib codebird https://github.com/mynetx/codebird-php

require_once ('codebird.php');
\Codebird\Codebird::setConsumerKey('CONSUMER_KEY', 'CONSUMER_SECRET'); // static, see 'Using multiple Codebird instances'

// Define the main class
class Get_Tweets {
	// Time between cache (Unit is second)
	private $time = 300; // 5 min


	// The max number of tweets
	private $count;
	// Trim the user informations from the data
	private $trim_user = true;
	// Twitter username
	private $user;
	private $cb;


	// Define the constructor
	public function __construct($count, $user = "dodozhang21") {
		// We make a default username in case the username is not set
		$this->count = $count;
		$this->user = $user;
		$this->cb = \Codebird\Codebird::getInstance();
		$this->cb->setToken('ACCESS_TOKEN', 'ACCESS_TOKEN_SECRET');
	}

	// Get the data from the URL
	private function fetch_url() {
		// check limit
		$resources = array('resources' => 'statuses');
		$limits = $this->cb->application_rateLimitStatus($resources);
		$remainingForUserTimeline = $limits->resources->statuses->{'/statuses/user_timeline'}->remaining;

		if($remainingForUserTimeline > 0) {
			$params = array(
				'screen_name' => $this->user,
				'count' => $this->count,
				'trim_user' => $this->trim_user
			);
			return $this->cb->statuses_userTimeline($params);
		} else {
			echo 'remainingForUserTimeline <= 0';
			// limit reached
			return false;
		}
	}

	// Save Cache to files
	private function save_cache($data) {
	  // Save Json data
	  $handle = fopen("twitter-cache.json", 'w');
	  fwrite($handle, json_encode($data));
	  fclose($handle);
	  // Save Last date
	  $handle = fopen("twitter-last-cache.txt", 'w');
	  fwrite($handle, date("c"));
	  fclose($handle); 
	}

	// Get second from last cache to now
	private function second(){
		if (!file_exists("twitter-last-cache.txt")) {
		// Save Last date
		$handle = fopen("twitter-last-cache.txt", 'w');
			fwrite($handle, "2000-01-01T12:12:12+00:00");
			fclose($handle); 
		}
		$prevDate = file_get_contents("twitter-last-cache.txt");
		$dateOne = new DateTime($prevDate);
		$dateTwo = new DateTime(date("c"));
		$diff = $dateTwo->format("U") - $dateOne->format("U");

		return $diff;
	}

	// Get data from cache file
	private function get_data(){
	  $tweets = json_decode(file_get_contents("twitter-cache.json"));
	  return $tweets;
	}

	// TimeStamp
	private function timeago($date) {
		$dateOne = new DateTime($date);
		$dateTwo = new DateTime(date("c"));
		$interval = $dateOne->diff($dateTwo);
		if($interval->y != 0){
			$unit = $interval->y == 1 ? 'year' : 'years';
			$ts= "About ".$interval->y. " " . $unit ." ago";
		}elseif($interval->m != 0){
			$unit = $interval->m == 1 ? 'month' : 'months';
			$ts= "About ".$interval->m. " " . $unit ." ago";
		}elseif($interval->d != 0){
			$unit = $interval->d == 1 ? 'day' : 'days';
			$ts= "About ".$interval->d. " " . $unit ." ago";
		}elseif($interval->h != 0){
			$unit = $interval->h == 1 ? 'hour' : 'hours';
			$ts= "About ".$interval->h. " " . $unit ." ago";
		}elseif($interval->i != 0){
			$unit = $interval->i == 1 ? 'minute' : 'minutes';
			$ts= "About ".$interval->i. " " . $unit ." ago";
		}elseif($interval->s != 0){
			$unit = $interval->s == 1 ? 'second' : 'seconds';
			$ts= $interval->s. " " . $unit ." ago";
		}
		return $ts;
	}

	// Get the tweets
	public function data($returnRaw = true) {
		//echo " in data";
		// Check if we update the cache or no
		if($this->second() < $this->time){
			// Because it's less we read from the cache
			//echo " read from cache";
			$tweets = $this->get_data();
		}else{
			//echo " update cache";
			// We can update the cache
			$tweets = $this->fetch_url();
			if($tweets == false){
				// We check here if the the fetch return false, because this case mean that 
				// we are reach the request limit, so we read from the cache
				$tweets = $this->get_data();
			}else{
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
				// Here we get the newest data and save the to the cache
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


// Crate a new instance
$count = isset($_GET['count']) ? $_GET['count'] : 4;
$user = isset($_GET['user']) ? $_GET['user'] : 'dodozhang21';
$get_tweets = new Get_Tweets($count, $user);

// Get Data
$tweets = $get_tweets->data();
header('Content-Type: application/json');
echo($tweets);

?>