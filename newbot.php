<?php

//dec 2...2nd? 2014 - all code in order
//ideas: add a user remover?
//check users every so often for bad stuff like undernet or failing to connect to the BNC channel


	//Eliminated Bug Number 0000000001! Woo!
	set_time_limit(0);

	// Set the server array as global
	global $servers;

	$servers["StormBit"]["name"] = "StormBit";
	$servers["StormBit"]["serverip"] = "ssl://irc.stormbit.net";
	$servers["StormBit"]["serverport"] = "6697";
	$servers["StormBit"]["serverpass"] = "";
	$servers["StormBit"]["botnick"] = "Electron2";
	$servers["StormBit"]["botuser"] = "Electron2";
	$servers["StormBit"]["botnspass"] = "Electron2";
	$servers["StormBit"]["botrealname"] = "Electronasd - the second coming. sounds ominous, huh?";
	//fancy
	$servers["StormBit"]["autoexec"][0] = "PRIVMSG #stormbitgames :PHPBot Online\r\n";
	
	$botport = '22228';
	
	// Multi-delimiter explode.
	function explodeX($delimiters,$string) {
		$return_array = Array($string); // The array to return
		$d_count = 0;
		while (isset($delimiters[$d_count])) // Loop to loop through all delimiters
		{
			$new_return_array = Array();
			foreach($return_array as $el_to_split) // Explode all returned elements by the next delimiter
			{
				$put_in_new_return_array = explode($delimiters[$d_count],$el_to_split);
				foreach($put_in_new_return_array as $substr) // Put all the exploded elements in array to return
				{
					$new_return_array[] = $substr;
				}
			}
			$return_array = $new_return_array; // Replace the previous return array by the next version
			$d_count++;
		}
		return $return_array; // Return the exploded elements
	}

	function array_trim($a) {
	  $j = 0;
	  for($i = 0; $i < count($a); $i++) {
		if($a[$i] != "") {
		  $b[$j++] = $a[$i];
		}
	  }
	  return $b;
	}

	// Parser
	function parseIrcMessage($message,$server,$socket,$registered) {
		global $chars;
		$message = ltrim($message,":");
		$strings = explode(":", $message, 2);

		$exploded_output = explode(" ", $strings[0]);
		if (array_key_exists(1,$strings)) {
			$exploded_output[] = $strings[1];
		}

		$exploded_output = array_trim($exploded_output);

		$server_output = array();

		$server_output["from"] = explodeX("!@",$exploded_output[0]);
		$server_output["data"] = $exploded_output;

		@consoleout($server,$server_output["data"][0] . " " . $server_output["data"][1] . " " . $server_output["data"][2] . " " . $server_output["data"][3], $socket, $registered);

		if ($server_output["data"][1] == "PRIVMSG") { //sometimes users want to parse the message
			$server_output["PRIVMSG"] = array_map('trim',explode(' ',$server_output["data"][3]));
		}
		return $server_output;
	}

	// Function to display console output
	function consoleout($name, $message, $server = NULL, $registered = false, $outgoing = false) {
		global $servers;
		global $chars;
		if ($outgoing !== false) {
			echo(">> [ConsoleOut:" . $name . "] " . $message . "\n");
			$fp = fopen("console.log", "a");
			fwrite($fp,">> [ConsoleOut:" . $name . "] " . $message . "\n");
			fclose($fp);
		}else{
			echo("<< [ConsoleOut:" . $name . "] " . $message . "\n");
			$fp = fopen("console.log", "a");
			fwrite($fp,"<< [ConsoleOut:" . $name . "] " . $message . "\n");
			fclose($fp);
		}
	}
	
	//send a message someplace
	function send($server,$message) {
		fwrite($server["socket"],$message);
		consoleout($server["name"],">> " . $message,NULL,false,true);
	}

	//new modular code. loads all the modules from a folder and stuff
	
	
	
	// Main loop
	$first = true;
	$server["registered"] = false;

	while(1) {
		foreach ($servers as &$server) {
			$server["pinged"] = false;
			if ($first) {
				consoleout($server["name"],"Connecting to " . $server["name"] . " on port " . $server["serverport"] . "...");
				$server["socket"] = fsockopen($server["serverip"],$server["serverport"]);
				stream_set_blocking($server["socket"],0);
				consoleout($server["name"],"Socket established, waiting for server to respond...");
				$server["registered"] = false;
				
				//moved since BNC
				if (!$server["registered"]) {
					send($server,"NICK " . $server["botnick"] . "\r\nUSER " . $server["botuser"] . " 0 * :" . $server["botrealname"] . "\r\n");
					if ($server["serverpass"] != null){
						fwrite($server["socket"],"PASS " . $server["serverpass"] . "\r\n");
						//didn't use send(); because it's a password.
					}
					$server["registered"] = true;
					consoleout($server["name"],"Registered!");
					if (!isset($server["autoexec"])){
						foreach($server["autoexec"] as &$execcmd) {
							send($server,$execcmd);
						}
					}
				}
				
				$server["sockout"] = "";
				$randvar = 0;
			}
			$server["sockout"] = trim(trim(fgets($server["socket"]), "\n"),"\r");
			if ($server["sockout"] != "") {
				$server_output = parseIrcMessage($server["sockout"],$server["name"], $server["socket"], $server["registered"]);
				if (!$server["registered"]) {
					send($server,"NICK " . $server["botnick"] . "\r\nUSER " . $server["botuser"] . " 0 * :" . $server["botrealname"] . "\r\n");
					if ($server["serverpass"] != null){
						send($server,"PASS " . $server["serverpass"] . "\r\n");
					}
					$server["registered"] = true;
					consoleout($server["name"],"Registered!");
				}

				if ($server_output["data"][1] == "001") {
					send($server,"PRIVMSG NickServ :IDENTIFY " . $server["botnspass"] . "\r\n");
					foreach($server["channels"] as &$chan) {
						send($server,"JOIN #" . $chan . "\r\n");
					}
					if (!isset($server["autoexec"])){
						foreach($server["autoexec"] as &$execcmd) {
							send($server,$execcmd);
						}
					}
				}
				if ($server_output["data"][0] == "PING") {
					send($server,"PONG :" . $server_output["data"][1] . "\r\n");
					$server["pinged"] = true;
				}

				if ($server_output["data"][1] == "KICK") {
					if ($server_output["data"][3] == $server["botnick"]) {
						sleep(1);
						send($server, "JOIN " . $server_output["data"][2] . "\r\n");
						sleep(1);
						send($server, "JOIN " . $server_output["data"][2] . "\r\n");
					}
				}
				
			$server_output = array(); //clear after each round
			}
		}//end IRC server specific code
		if (isset($botport)) {
			consoleout("[Bot Global]","Establishing listen socket for various bot commands.")
			//actual socket stuff.
			if(!isset($botportopen)){
				$botportsock = socket_create(AF_INET,SOCK_STREAM,tcp);
				
			}
		}
		usleep(1);
		$iteration=$iteration+1; //halfassed time counter
		$first = false;
	}
?>
