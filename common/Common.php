<?php
/**
 * Encripts the password using the username as modifier and a magic string.
 * @param string $username The user's username
 * @param string $password The user's password decripted
 * @return string Returns a user's password encripted using the username as modifier with the format username:MAGIC_STRING:password
 */
function CreateSha1Pass ($username, $password)
{
    return sha1(strtolower($username) . ":" . MAGIC_STRING . ":" . $password);
}

/**
 * Encripts the password using the username as modifier for a MaNGOS based World of Warcraft server emulator.
 * @param string $username The user's username
 * @param string $password The user's password decripted
 * @return string Returns a user's password encripted using the username as modifier with the format username:password
 */
function CreateWoWServerSha1Pass($username, $pass)
{
    return sha1(strtoupper($username) . ":" . strtoupper($pass));
}

/**
 * Gets a username from a user's ID without creating a full user object.
 * @param long $id The user's unique ID.
 * @return string Returns a string with the username, USER_DOESNT_EXISTS if no result or false if something fails.
 */
function GetUsernameFromId($id)
{
    global $DATABASES, $SERVER_INFO;
    $DB = new Database($DATABASES['USERS']);
    $result = $DB->ExecuteStmt(Statements::SELECT_USERS_USERNAME, $DB->BuildStmtArray("i", $id));
    if ($result)
    {
        if($row = $result->fetch_assoc())
            return $row['username'];
        return USER_DOESNT_EXISTS;
    }
    return false;
}

/**
 * Gets a ID from a user's username without creating a full user object.
 * @param string $username The user's username.
 * @return long Returns a long integer representing the user's ID, USER_DOESNT_EXISTS if no result or false if something fails.
 */
function GetIdFromUsername($username)
{
    global $DATABASES;
    $DB = new Database($DATABASES['USERS']);
    $result = $DB->ExecuteStmt(Statements::SELECT_USER_DATA_ID, $DB->BuildStmtArray("s", $username));
    if ($result)
    {
        if ($row = $result->fetch_assoc())
            return $row['id'];
        return USER_DOESNT_EXISTS;
    }
    return false;
}

/**
 * Determines if a user exists in the database, given an email, an username or an ID.
 * @param string $identifier An ID, username or email.
 * @return mixed False if the user doesn't exist, else the user ID. Note that the ID can be 0.
 */
function UserExists($identifier)
{
    global $DATABASES;
    $db = new Database($DATABASES['USERS']);
    if (is_numeric($identifier))
        $result = $db->ExecuteStmt(Statements::SELECT_USERS_BY_ID, $db->BuildStmtArray("i", $identifier));
    elseif (IsValidEmail($identifier))
        $result = $db->ExecuteStmt(Statements::SELECT_USERS_EMAIL, $db->BuildStmtArray("s", $identifier));
    else
        $result = $db->ExecuteStmt(Statements::SELECT_USERS_BY_USERNAME, $db->BuildStmtArray("s", $identifier));
    if ($row = $result->fetch_assoc())
        return $row['id'];
    return false;
}

/**
 * Generates a Gravatar URL from an email.
 * @param string $email The email.
 * @param string $size The size of the image in px.
 * @return mixed Returns a string representing the url for that email.
 */
function GenerateGravatarUrl($email, $size = 0)
{
    if ($size == 0)
        $size = 80;
    $url="http://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=" . $size;
    return $url;
}

/**
 * Obtains basic information about the Team Speak Server.
 * TODO: Create an object "ServerStatus" and return it instead of an array, for all the servers.
 * @return array Return an array with basic data about the server status.
 */
function GetTs3Status()
{
    $ts3Status = array(
        'isOnline'      => false,
        'maxOnline'     => 0,
        'currentOnline' => 0,
        'error'         => false, 
    );
    global $TEAMSPEAK3;
    TeamSpeak3::init();
    $error = false;
    try
    {
        $ts3_ServerInstance = TeamSpeak3::factory("serverquery://" . $TEAMSPEAK3['USER'] . ":" . $TEAMSPEAK3['PASSWORD'] . "@" . $TEAMSPEAK3['HOST'] . ":" . $TEAMSPEAK3['QUERY_PORT'] . "/?server_port=" . $TEAMSPEAK3['VOICE_PORT'] . "&use_offline_as_virtual=1&no_query_clients=1");
        $ts3_isOnline = $ts3_ServerInstance->getProperty("virtualserver_status");
        $ts3_usersOnline = $ts3_ServerInstance->getProperty("virtualserver_clientsonline") - $ts3_ServerInstance->getProperty("virtualserver_queryclientsonline");
        $ts3_maxUsers = $ts3_ServerInstance->getProperty("virtualserver_maxclients");
    }
    catch(Exception $e)
    {
        $error = true;
    }
    if ($error)
        $ts3Status['error'] = true;
    elseif ($ts3_isOnline == "online")
    {
        $ts3Status['isOnline'] = true;
        $ts3Status['maxOnline'] = $ts3_maxUsers;
        $ts3Status['currentOnline'] = $ts3_usersOnline;
    }
    else
        $ts3Status['isOnline'] = false;
    return $ts3Status;
}

/**
 * Prints the WoW TBC server status basic template.
 */
function PrintWowTbcServerStatus()
{
    echo '		<div class="rightItem">
        			<div class="serverStatusContainer">
            			<div><h3>Servidor World of Warcraft:<br>The Burning Crusade</h3></div>
                        <div id="wowServerStatusLabel" class="serverStatus unknown">Comprobando...</div>
                        <div id="wowServerGamersOnlineLabel" class="serverStatusLabel">Gamers conectados: -/-</div>
        				<div class="serverStatusLabel">wowserver.steelgamers.es</div>
					</div>
				</div>';
}

/**
 * Obtains basic information about the WoW TBC server.
 * TODO: Create an object "ServerStatus" and return it instead of an array, for all the servers.
 * @return array Returns an array with basic data about the server status.
 */
function GetWowTbcServerStatus()
{
    global $DATABASES, $TBCSERVER_INFO;
    $wowStatus = array(
        'isOnline'      => false,
    	'maxOnline'     => 0,     // Not used for the moment
        'currentOnline' => 0,     // Not used for the moment
        'error'         => false, // Not used for the moment
    );
    $err = array('no' => NULL, 'str' => NULL);
    $isOnline = @fsockopen($TBCSERVER_INFO['host'], 8085, $err['no'], $err['str'], (float)1.0);
    if(!$isOnline)
        $wowStatus['isOnline'] = false;
	else
	{
		$wowStatus['isOnline'] = true;
		$wowStatus['maxOnline'] = 200; // TODO: This should be asked to the server _or_ a constant in config.php
		$wowCharactersDb = new Database($DATABASES['TBCSERVER_CHARS'], $TBCSERVER_INFO);
		if ($result = $wowCharactersDb->Execute(Statements::SELECT_TOTAL_ONLINE_USERS))
		    if ($row = $result->fetch_assoc())
		        $wowStatus['currentOnline'] = $row['totalOnline'];
		
		fclose($isOnline);
	}
	return $wowStatus;
}

/**
 * Obtains basic information about the minecraft server.
 * TODO: Create an object "ServerStatus" and return it instead of an array, for all the servers.
 * @return array Returns an array with basic data about the server status.
 */
function GetMinecraftServerStatus()
{
    $minecraftStatus = array(
        'isOnline'      => false,
    	'maxOnline'     => 0,
        'currentOnline' => 0,
        'error'         => false,
    );
    if ( $sock = @stream_socket_client("tcp://" . $MITRACRAFT_INFO['host'] . ":25565", $errno, $errstr, 1) )
    {
        $minecraftStatus['isOnline'] = true;

        fwrite($sock, "\xfe");
        $h = fread($sock, 2048);
        $h = str_replace("\x00", '', $h);
        $h = substr($h, 2);
        $data = explode("\xa7", $h);
        unset($h);
        fclose($sock);

        if (sizeof($data) == 3)
        {
            $minecraftStatus['currentOnline'] = (int) $data[1];
            $minecraftStatus['maxOnline'] = (int) $data[2];
        }
        else
            $minecraftStatus['error'] = true;
    }
    else
        $minecraftStatus['isOnline'] = false;
    return $minecraftStatus;
}

/**
 * Obtains basic information about the ArmA 2 server.
 * TODO: Create an object "ServerStatus" and return it instead of an array, for all the servers.
 * @param integer $port Port number of the server instance that must be checked.
 * @return array Returns an array with basic data about the server status.
 */
function GetArma2ServerStatus($port)
{
    $arma2ServerStatus = array(
        'isOnline'      => false,
    	'maxOnline'     => 0,
        'currentOnline' => 0,
        'error'         => false,
    );
    $sock = fsockopen("udp://armaserver.steelgamers.es", $port, $errno, $errdesc);
    fwrite($sock, "\xFE\xFD\x09\xFF\xFF\xFF\x01");
    $challenge_packet = fread($sock, 4096);
    
    if (!$challenge_packet)
    {
        $arma2ServerStatus['error'] = false;
        return $arma2ServerStatus;
    }
    
    $arma2ServerStatus['isOnline'] = true;
    $challenge_code = substr($challenge_packet, 5, -1);
    $challenge_code = $challenge_code ? chr($challenge_code >> 24) . chr($challenge_code >> 16) . chr($challenge_code >> 8) . chr($challenge_code >> 0) : "";
    fwrite($sock, "\xFE\xFD\x00\x10\x20\x30\x40{$challenge_code}\xFF\xFF\xFF\x01");
    $buffer = array();
    $packet_count = 0;
    $packet_total = 4;
    do
    {
        $packet_count++;
        $packet = fread($sock, 4096);
        if (!$packet)
        {
            return false;
        }
        $packet = substr($packet, 14);
        $packet_order = ord(cut_byte($packet, 1));
        if ($packet_order >= 128)
        {
            $packet_order -= 128;
            $packet_total = $packet_order + 1;
        }
        $buffer[$packet_order] = $packet;
    } while ($packet_count < $packet_total);
    
    foreach ($buffer as $key => $packet)
    {
        $packet = substr($packet, 0, -1);
        if (substr($packet, -1) != "\x00")
        {
            $part = explode("\x00", $packet);
            array_pop($part);
            $packet = implode("\x00", $part) . "\x00";
        }
        if ($packet[0] != "\x00")
        {
            $pos = strpos($packet, "\x00") + 1;
            if (isset($packet[$pos]) && $packet[$pos] != "\x00")
            {
                $packet = substr($packet, $pos + 1);
            }
            else
            {
                $packet = "\x00" . $packet;
            }
        }
        $buffer[$key] = $packet;
    }
    ksort($buffer);
    $buffer = implode("", $buffer);
    //  SERVER SETTINGS
    $buffer = substr($buffer, 1);
    while ($key = strtolower(cut_string($buffer)))
    {
        $server['e'][$key] = cut_string($buffer);
    }
    $lgsl_conversion = array("name" => "hostname", "game" => "gamename", "map" => "mapname", "players" => "numplayers", "playersmax" => "maxplayers", "password" => "password");
    foreach ($lgsl_conversion as $s => $e)
    {
        if (isset($server['e'][$e]))
        {
            $server['s'][$s] = $server['e'][$e];
            unset($server['e'][$e]);
        }
    }
    $arma2ServerStatus['currentOnline'] = $server['s']['players'];
    $arma2ServerStatus['maxOnline'] = $server['s']['playersmax'];
    $arma2ServerStatus['map'] = $server['s']['map'];
    
    // Not used for the moment
    /*
    if ($server['s']['players'] == "0")
    {
        return true;
    }
    //  PLAYER DETAILS
    $buffer = substr($buffer, 1);
    $playercount = $server['s']['players'];
    $playerdata = explode("\x00",$buffer);
    $playersout = 1;
    for ($i = 2; $i <= ($server['s']['players'] + 1) ; $i++)
    {
       $playersout++;
       $server['p'][$playersout]['name'] = $playerdata[$i];
       $server['p'][$playersout]['team'] = $playerdata[($playercount + $i) + 3];
       $server['p'][$playersout]['score'] = $playerdata[($playercount + $playercount + $i) + 6];
       $server['p'][$playersout]['deaths'] = $playerdata[($playercount + $playercount + $playercount + $i) + 9];
    }
    */
    
    return $arma2ServerStatus;
}

/**
 * Gets the total number of online users based in the number of active PHP sessions. NOTE: Not reliable outside the GamersHub infrastructure.
 * @return mixed Returns an integer representing the number of online users, or false if something fails.
 */
function GetOnlineUsersCount()
{
    global $DATABASES;
    
    $sessionsDb = New Database($DATABASES['SESSIONS']);
    // We don't need a prepared statement here because there are no variables in the query
    if ($result = $sessionsDb->Execute("SELECT COUNT(*) FROM sessions"))
    {
        if ($row = $result->fetch_array(MYSQLI_NUM))
            return $row[0];
    }
    return false;
}

/**
 * Determines if an user is connected to the website right now.
 * @param long $userId The unique user identifier.
 * @return boolean Returns true if the user is online, else false. Note that the function also returns false if the ID is invalid or other problems.
 */
function IsUserOnline($userId)
{
    global $DATABASES;
    
    $usersDb = New Database($DATABASES['USERS']);
    if ($result = $usersDb->ExecuteStmt(Statements::SELECT_USER_IS_ONLINE, $usersDb->BuildStmtArray("i", $userId)))
    {
        if ($row = $result->fetch_assoc())
            if ($row['is_online'])
                return true;
    }
    return false;
}

/**
 * Determines if a given game title is already in the database.
 * @param string $gameTitle The title of the game to check.
 * @return mixed Returns the game ID if the game exists, else false. Also it returns false if something goes wrong.
 */
function GameExists($gameTitle)
{
    global $DATABASES;
    
    $gamesDb = New Database($DATABASES['GAMES']);
    if ($result = $gamesDb->ExecuteStmt(Statements::SELECT_GAME_EXISTS, $gamesDb->BuildStmtArray("s", $gameTitle)))
    {
        if ($result->num_rows > 0)
            if ($row = $result->fetch_assoc())
                return $row['id'];
    }
    return false;
}

/**
 * Substitues the "%v" chars in the first param by the strings in the next parameters
 * @param string $string The target string.
 * @return string Returns the string parsed with the other params inserted.
 */
function InsertVarInString($string)
{
    $vars = func_get_args();
    $max = 1;
    foreach ($vars as $i => $value)
    {
        if ($i == 0)
            continue;
        
        $string = str_replace("%v", $value, $string, $max);
    }
    return $string;
}

/**
 * Obtains the preferred language by the user's web browser, in the web supported languages.
 * @param string $httpAcceptLanguage The HTTP_ACCEPT_LANGUAGE header.
 * @return string Returns one of the supported languages by the website, if the user's language is not supported, it returns english by default.
 */
function GetPreferredLanguage($httpAcceptLanguage)
{
    $lang_data = explode(",", $httpAcceptLanguage);
    foreach ($lang_data as $i => $value)
    {
        $lang_data[$i] = explode(";", $lang_data[$i]);
        if (!isset($lang_data[$i][1]))
            $lang_data[$i][1] = "1.0";
    }
    $language = "enUS"; $quality = 0;
    foreach ($lang_data as $i => $value)
    {
        if ($quality < (float)$value[1])
        {
            $language = $value[0];
            $quality = (float)$value[1];
        }
    }
    switch($language)
    {
        case "es":
        case "es-ES":
        case "es-MX":
            return "es";
        case "en":
        case "en-GB":
        case "en-US":
            return "en";
        default:
            return "en";
    }
}

/**
 * Checks an email adress to ensure it's a valid one. From <a href="http://www.linuxjournal.com/article/9585?page=0,0">http://www.linuxjournal.com/article/9585?page=0,0</a>.
 * @param string $email
 * @author Douglas Lovell
 */
function IsValidEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
               str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

/*
 * Couple of functions to decode the GameSpy 3 protocol response.
 * Used to query the ArmA 2 OA servers.
 */

/**
 * cut_string()
 * 
 * @param mixed $buffer
 * @param integer $start_byte
 * @param string $end_marker
 * @return
 */
function cut_string(&$buffer, $start_byte = 0, $end_marker = "\x00")
{
    $buffer = substr($buffer, $start_byte);
    $length = strpos($buffer, $end_marker);
    if ($length === false)
    {
        $length = strlen($buffer);
    }
    $string = substr($buffer, 0, $length);
    $buffer = substr($buffer, $length + strlen($end_marker));
    return $string;
}
/**
 * cut_byte()
 * 
 * @param mixed $buffer
 * @param mixed $length
 * @return
 */
function cut_byte(&$buffer, $length)
{
    $string = substr($buffer, 0, $length);
    $buffer = substr($buffer, $length);
    return $string;
}

/**
 * Checks if the given url is valid.
 * @param string $url
 * @return boolean True if the url exists.
 */
function IsUrl($url)
{
    $file_headers = @get_headers($url);
    if($file_headers[0] == 'HTTP/1.1 404 Not Found')
        return false;
    else 
        return true;
}

/**
 * Determines if the given url is valid and if it is an image.
 * @param url string
 * @return boolean True if the url is an image, else false.
 */
function IsImage($url)
{
    $params = array(
    	'http' => array(
			'method' => 'HEAD'
        )
    );
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
    if (!$fp) 
        return false;  // Problem with url

    $meta = stream_get_meta_data($fp);
    if ($meta === false)
    {
        fclose($fp);
        return false;  // Problem reading data from url
    }

    $wrapper_data = $meta["wrapper_data"];
    if(is_array($wrapper_data))
    {
        foreach(array_keys($wrapper_data) as $hh)
        {
            if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") // strlen("Content-Type: image") == 19 
            {
                fclose($fp);
                return true;
            }
        }
    }
    fclose($fp);
    return false;
}
?>