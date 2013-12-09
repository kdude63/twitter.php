## twitter.php (extension to codebird-php)

For getting the last _n_ tweets from a specific user's timeline.  
(Now can automagically parse newlines and URLs!)

#### Dependencies:

Only requires `php5-curl` to work.

#### Usage:

Insert your application's `consumer key` and `consumer secret` and send a request to the file with `user` and `count` in the URI.

E.g - `https://www.my-server.com/twitter.php?count=1&user=kdude63` will return the most recent tweet from user 'kdude63'.

You can also call it internally by doing something like this:

    	require_once ('twitter.php'); 
    	// This code needs to be in the same directory as the 'twitter' folder!
    	
    	$data = new get_tweets(1, kdude63, CONSUMER_KEY, CONSUMER_SECRET)->data();
    	echo $data[0]['text'];
    	
This will also return [kdude63](https://twitter.com/kdude63)'s latest tweet, and echo it onto the page.

`data()` accepts two booleans as options:
* The first one determines whether or not a JSON object is returned instead of an stdClass array. This is true by default.  
* The second one determines whether or not the tweets(s) are parsed for URLs, and newlines and the timestamp converted into a an easier-to-read relative time instead of absolute time. This is also true by default.

E.g - `data(true, true)` will return a JSON object, with tweet data that is ready to be displayed on a page as HTML.  
While `data(false, false)` would return the raw data without doing anything to it.
    
For more information on codebird, see https://github.com/mynetx/codebird-php
