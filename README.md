### getTweets.php (extension to codebird-php)

 Originally written for twitter api 1 from http://mitgux.com/get-your-latest-tweets-with-php-and-cache-them
 Modified by http://pure-essence.net for twitter API 1.1 by using lib codebird https://github.com/mynetx/codebird-php

### For more step by step tutorial
Visit

Upload all files from src to your a folder on your server
Update getTweets.php with variables cache variables
Call getTweets.php?user={screenname}&count={tweets} to get a json array of the twitter timeline.
Handle the json using an ajax call like this (this jQuery):

```
// get latest tweet
$.ajax({
	url: "{PATH TO}/getTweets.php",
	type: "GET",
	data: { count : '2', user : 'dodozhang21' },
	success: function(data, textStatus, jqXHR){
		var html = '<ul>';
		for(var x in data) {
			var tweet = data[x];
			//console.log(tweet);
			html += '<li>';
			html += tweet.text;
			html += '<span>';
			html += tweet.created_at;
			html += '</span></li>';
		}
		html += '</ul>';
		$('#latestTweet').removeClass('loading');
		$('#latestTweet').html(html);
	},
	error: function (jqXHR, textStatus, errorThrown){
		//console.log('Error ' + jqXHR);
	}
});
```


### codebird-php

For more information on codebird, visit https://github.com/mynetx/codebird-php