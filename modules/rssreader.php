<?php
//RSS reader system. Outputs new RSS feed updates to a channel
//NEEDS the PHP DOM module thing loaded.

//sourced from http://onwebdev.blogspot.com/2011/08/php-converting-rss-to-json.html because I'm lazy

class rssreader {
	function readRSS($url) {
		$feed = new DOMDocument();
		$feed->load('blog-feed.xml');
		$json = array();
		
		$json['title'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
		$json['description'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
		$json['link'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('link')->item(0)->firstChild->nodeValue;
		
		$items = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('item');
		
		$json['item'] = array();
		$i = 0;
		
		
		foreach($items as $item) {
		
			$title = $item->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
			$description = $item->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
			$pubDate = $item->getElementsByTagName('pubDate')->item(0)->firstChild->nodeValue;
			$guid = $item->getElementsByTagName('guid')->item(0)->firstChild->nodeValue;
			
			$json['item'][$i++]['title'] = $title;
			$json['item'][$i++]['description'] = $description;
			$json['item'][$i++]['pubdate'] = $pubDate;
			$json['item'][$i++]['guid'] = $guid;	
			
		}
		return $json;
	}
	
	function event_fire($type,$message,$server) {
	//triggered whenever something happens. time-based triggers 'time', IRC-server based triggers 'irc', and the listener socket-based triggers 'listen' will be sent in the first argument
	//the IRC server output line, the current time in unixtime, or the listen socket's input will be sent as $message.
	//$server is basically the server-specific array. That's it.
	
	
	}


?>