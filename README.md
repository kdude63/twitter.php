## twitter.php (extension to codebird-php)

For getting the last _n_ tweets from a specific user's timeline.  
Requires `php5-curl` to work.  

#### Usage:

Insert your application's `consumer key` and `consumer secret` and send a request to the file with `user` and `count` in the URI.

E.g - `https://www.my-server.com/twitter.php?count=1&user=kdude63` will return the most recent tweet from user 'kdude63'.

    
For more information on codebird, see https://github.com/mynetx/codebird-php
