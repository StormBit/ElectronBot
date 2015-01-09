<?php
//RSS reader system. Outputs new RSS feed updates to a channel
//NEEDS the PHP DOM module thing loaded.

//this is just the RSS parser function! you don't need this in your module's code!
//sourced from http://onwebdev.blogspot.com/2011/08/php-converting-rss-to-json.html because I'm lazy
class rssreaderasync extends Thread {
	public function run() {
		$feed = new DOMDocument();
		$feed->load($url);
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
		//do JSON parsing
		
	}
}


//actual legwork
class rssreader {

	function readRSS($url) {//because this is clean and totally safe and nice
		$rssreader = new rssreader($url);
	}
	
	function event_fire($type,$message,$server = NULL) {
		//triggered whenever something happens. time-based triggers 'time', IRC-server based triggers 'irc', and the listener socket-based triggers 'listen' will be sent in the first argument
		//the IRC server output line, the current time in unixtime, or the listen socket's input will be sent as $message.
		//$server is basically the server-specific array and that's only needed with the IRC server trigger.
		if ($type='irc' && $server != null){
			//IRC server action. processes things like PRIVMSGs and NOTICEs if you really want them to. All the output gets passed to you. Pingchecks etc are handled with PONGs but are still passed to you.
			
		}elseif($type='time'){
			//once-per-second actions
			//we'll be doing our own iteration outside this function that checks every 15 minutes for an RSS feed update but that's basically it.
			if($message%15){
				
			}
		}elseif($type="listen"){
			//listen socket
			//check data from the listen socket (authorization'll be handled in the main bot's code) and if it's something that interests us we do things about it.
		}
	}
}

//asyncronousish thread thing

?>