## twitter.php (extension to codebird-php)

For getting the last _n_ tweets from a specific user's timeline.  
<sup><sub>(Can now automagically parse newlines and URLs!)</sub></sup>

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

`data()` or `data(true)` will return tweet data that is ready to be displayed on a page as HTML...  
I.e - Turns URLs into links, newlines into line breaks, and makes the timestamp relative instead of absolute.

![Just testing...<br>some stuff...<br>With a link: <a href="https://t.co/jZ5igXzcfk" target="_blank">https://t.co/jZ5igXzcfk</a>](http://i.imgur.com/WEg1aqt.png "Just testing...<br>some stuff...<br>With a link: <a href="https://t.co/jZ5igXzcfk" target="_blank">https://t.co/jZ5igXzcfk</a>")

While `data(false)` will return the raw data without doing anything to it.

![Just testing...\nsome stuff...\nWith a link: https://t.co/jZ5igXzcfk](http://i.imgur.com/BdkzXVi.png "Just testing...\nsome stuff...\nWith a link: https://t.co/jZ5igXzcfk")
    
For more information on codebird, see https://github.com/mynetx/codebird-php
