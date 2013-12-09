## twitter.php (extension to codebird-php)

For getting the last _n_ tweets from a specific user's timeline.  
(Now automagically parses newlines _and_ URLs!)

#### Dependencies:

Only requires `php5-curl` to work.

#### Usage:

Insert your application's `consumer key` and `consumer secret` and send a request to the file with `user` and `count` in the URI.

E.g - `https://www.my-server.com/twitter.php?count=1&user=kdude63` will return the most recent tweet from user 'kdude63'.

You can also call it internally by doing something like this:

    	require_once ('twitter.php'); 
    	// This code needs to be in the same directory as the 'twitter' folder!
    	
    	$data = new get_tweets(1, kdude63, CONSUMER_KEY, CONSUMER_SECRET)->data(true);
    	echo $data[0]['text'];
    	// 'data(true)' returns a JSON object, and 'data(false)' returns an stdClass array.

This will also return [kdude63](https://twitter.com/kdude63)'s latest tweet, and echo it onto the page.
    
For more information on codebird, see https://github.com/mynetx/codebird-php
