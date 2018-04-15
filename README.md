If you're reading this I must've uploaded it to Github again which means I got it working (mostly).

It's still god-awful code, Not for production. ..or for development.

Maybe not for running at all.
Use Adminer instead: https://www.adminer.org/

The garbage-name file being requested is a simple return-delimited file holding servernames.
It's a rubbish name you can change to something else so people who somehow have access to that directory can't guess your servernames (yes, it's a rubbish method)

The auth.inc.php file can filter on ip or whatever, and is ***NOT*** for storing passwords or any kind of MySQL account credentials.

ps. also, I found the *other* updated version of this minutes after uploading the one I just spent over an hour one, after putting it off for months, not being able to find that other hypothetical version that I already fixed. That I now just found. But THIS one runs on PHP 7, and the other one doesn't. So there.

Really, use Adminer, it's great!
