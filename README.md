## twitter.php (extension to codebird-php)

For getting the last _n_ tweets from a specific user's timeline.  
<sup><sub>(Can now automagically parse newlines and URLs!)</sub></sup>

#### Dependencies:

Requires `php5-curl` and a valid Twitter application API key.

#### Usage:

```php
require_once ('twitter.php'); 
// This code needs to be in the same directory as the 'twitter' folder!

$data = new get_tweets(1, 'kdude63', 'CONSUMER_KEY', 'CONSUMER_SECRET')->data();
echo $data[0]['text'];
```
    	
This will return [kdude63](https://twitter.com/kdude63)'s latest tweet, and echo it onto the page.

`data()` or `data(true)` will return tweet data that is ready to be displayed on a page as HTML...  
I.e - Turns URLs into links, newlines into line breaks, and makes the timestamp relative instead of absolute.

![](http://i.imgur.com/WEg1aqt.png "Just testing...<br>some stuff...<br>With a link: <a href='https://t.co/jZ5igXzcfk' target='_blank'>https://t.co/jZ5igXzcfk</a>")

    Just testing...<br>some stuff...<br>With a link: <a href="https://t.co/jZ5igXzcfk" target="_blank">https://t.co/jZ5igXzcfk</a>

While `data(false)` will return the raw data without doing anything to it.

![](http://i.imgur.com/BdkzXVi.png "Just testing...\nsome stuff...\nWith a link: https://t.co/jZ5igXzcfk")

    Just testing...\nsome stuff...\nWith a link: https://t.co/jZ5igXzcfk
    
___
    
For more information on Codebird, see https://github.com/mynetx/codebird-php.
