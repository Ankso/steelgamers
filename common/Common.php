<?php
/**
 * Encripts the password using the username as modifier.
 * @param string $username The user's username
 * @param string $password The user's password decripted
 * @return string Returns a user's password encripted using the username as modifier with the format username:password
 */
function CreateSha1Pass ($username, $password)
{
    return sha1(strtolower($username) . ":" . MAGIC_STRING . ":" . $password);
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
    $result = $DB->ExecuteStmt(Statements::SELECT_USER_DATA_USERNAME, $DB->BuildStmtArray("i", $id));
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
 * Prints the topbar for a specified user, or the default topbar if the user is not logged in.
 * @param User $user [DEPRECATED] The User class initilized, or NULL if the user is not logged in.
 */
function PrintTopBar(/* $user */)
{
    echo '        <div class="topBarWrapper">
        		<div class="topBarContainer">
            		<div id="topBarItemJuegos" class="topBarItem">Juegos</div>
            		<div id="topBarItemForos" class="topBarItem">Foros</div>
            		<div id="topBarItemComunidad" class="topBarItem">Comunidad</div>
            		<div id="topBarItemServidores" class="topBarItem">Servidores</div>
            	</div>
            	<div class="subMenuWrapper">
                	<div id="topBarSubMenuJuegos" class="topBarSubMenu">
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://arma2.steelgamers.es"><img src="/images/games/topbar_covers/arma2_cover.jpg"></a></div>
            				<div>ArmA 2</div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://battlefield.steelgamers.es"><img src="/images/games/topbar_covers/battlefield3_cover.jpg"></a></div>
            				<div>Battlefield 3</div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://dayz.steelgamers.es"><img src="/images/games/topbar_covers/dayz_cover.jpg"></a></div>
            				<div>DayZ</div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://dota2.steelgamers.es"><img src="/images/games/topbar_covers/dota2_cover.jpg"></a></div>
            				<div>Dota 2</div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://minecraft.steelgamers.es"><img src="/images/games/topbar_covers/minecraft_cover.jpg"></a></div>
            				<div>Minecraft</div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://warthunder.steelgamers.es"><img src="/images/games/topbar_covers/warthunder_cover.jpg"></a></div>
            				<div>War Thunder</div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://worldoftanks.steelgamers.es"><img src="/images/games/topbar_covers/worldoftanks_cover.jpg"></a></div>
            				<div>World of Tanks</div>
            			</div>
            		</div>
            		<div id="topBarSubMenuForos" class="topBarSubMenu">
            			<div>
            				<a class="plainLink" href="http://steelgamers.es/foro"><div class="topBarSubMenuItem forumItem" style="float:none; text-align:center;">General</div></a>
            			</div>
            			<div style="margin-top:10px;">
            				<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://steelgamers.es/foro/index.php?p=/categories/arma">ArmA 2</a></div>
                			<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://steelgamers.es/foro/index.php?p=/categories/battlefield-3">Battlefield 3</a></div>
                			<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://steelgamers.es/foro/index.php?p=/categories/dayz">DayZ</a></div>
                			<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://steelgamers.es/foro/index.php?p=/categories/dota-2">Dota 2</a></div>
                			<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://mitracraft.es">Minecraft</a></div>
                			<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://steelgamers.es/foro/index.php?p=/categories/war-thunder">War Thunder</a></div>
                			<div class="topBarSubMenuItem forumItem"><a class="plainLink" href="http://steelgamers.es/foro/index.php?p=/categories/world-of-tanks">World of Tanks</a></div>
            			</div>
            		</div>
            		<div id="topBarSubMenuComunidad" class="topBarSubMenu">
            			<div class="topBarSubMenuItem communityItem"><a class="plainLink" href="http://steelgamers.es/members.php">Miembros</a></div>
            			<div class="topBarSubMenuItem communityItem">Normas</div>
            			<div class="topBarSubMenuItem communityItem"><a class="plainLink" href="http://steelgamers.es/faq.php">FAQ</a></div>
            			<div class="topBarSubMenuItem communityItem">Noticias antiguas</div>
            		</div>
            		<div id="topBarSubMenuServidores" class="topBarSubMenu">
            			<div class="topBarSubMenuItem gameItem" style="margin-top:13px;">
            				<div><a href="http://minecraft.steelgamers.es/servidores/mitracraft.php"><img src="/images/servers/topbar/mitracraft.png"></a></div>
            			</div>
            			<div class="topBarSubMenuItem gameItem">
            				<div><a href="http://arma2.steelgamers.es/servidores/sgc1_arma2.php"><img src="/images/servers/topbar/sgc1_arma2.png"></a></div>
            			</div>
            		</div>
        		</div>
    		</div>';
}

/**
 * Prints the TeamSpeak 3 status (server online, number of online players, etc)
 */
function PrintTs3Status()
{
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
    echo '		<div class="rightItem">
        			<div class="teamSpeak3StatusContainer">
            			<div><h3>Servidor TeamSpeak 3</h3></div>';
    if ($error)
    {
        echo '			<div class="teamSpeak3Status unknown">Desconocido</div>
        				<div class="teamSpeakStatusLabel">Gamers conectados: -/-</div>';
    }
    elseif ($ts3_isOnline == "online")
    {
        echo '			<div class="teamSpeak3Status online">Online</div>
        				<div class="teamSpeakStatusLabel">Gamers conectados: ' . $ts3_usersOnline . '/' . $ts3_maxUsers . '</div>';
    }
    else
    {
        echo '			<div class="teamSpeak3Status offline">Offline</div>
        				<div class="teamSpeakStatusLabel">Gamers conectados: -/-</div>';
    }
    echo '				<div class="teamSpeakStatusLabel">steelgamers.es:9987</div>
					</div>
				</div>';
}

/**
 * Gets the total number of online users based in the number of active PHP sessions.
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
?>