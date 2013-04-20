<?php
/**
 * Main User class. Can be initilized with a valid username or a valid user ID.<br />It stores data about an user, and has all the methods to access that data.<br />It also has methods representing user actions, like sending a private message to another user.<br />
 * TODO: Create classes for some objects like Board messages, Board message replies, Private messages, Custom options, Privacy options, etc, instead of returning bi-dimensional arrays or even tri-dimensional arrays with the values.
 * @author Ankso
 */
Class User
{
    /**
     * Initializes the user class, loading all user data from database.
     * @param string/long $source A string representing the user's username or a long unsigned integer as the user's unique ID
     */
    function __construct($source)
    {
        global $DATABASES;
        if (is_int($source))
            $this->_id = $source;
        elseif (is_string($source))
            $this->_username = $source;
        else
            die("Error initializing User Class: invalid source.");
            
        $this->_db = new Database($DATABASES['USERS']);
        if (!$this->LoadFromDB())
            die("Error initializing User Class");
    }
    
    /**
     * Class destructor
     */
    function __destruct()
    {
    }
    
    /**
     * Load all user's data from DB into the Class variables.
     * @return bool Returns true if the user is loaded successfully, or false if something fails.
     */
    private function LoadFromDB()
    {
        if (!isset($this->_id))
            $result = $this->_db->ExecuteStmt(Statements::SELECT_USERS_BY_USERNAME, $this->_db->BuildStmtArray("s", $this->_username));
        else
            $result = $this->_db->ExecuteStmt(Statements::SELECT_USERS_BY_ID, $this->_db->BuildStmtArray("i", $this->_id));
        if ($result && ($userData = $result->fetch_assoc()))
        {
            $this->_id = $userData['id'];
            $this->_username = $userData['username'];
            $this->_passwordSha1 = $userData['password_sha1'];
            $this->_email = $userData['email'];
            if (is_null($userData['ip_v6']))
                $this->_ip = $userData['ip_v4'];
            else
                $this->_ip = $userData['ip_v6'];
            $this->_isOnline = $userData['is_online'];
            $this->_lastLogin = $userData['last_login'];
            $this->_registerDate = $userData['register_date'];
            $this->_active = $userData['active'];
            $this->_ranks = NULL; // This is initialized on demand
            return true;
        }
        return false;
    }
    
    /**
     * Saves the users data sotred in the class variables to the DB.
     * @return bool Returns true if the user is saved to the DB successfully, else it returns false.
     */
    private function SaveToDB()
    {
        $data;
        if (filter_var($this->GetLastIp(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
            $data = $this->_db->BuildStmtArray("issssssss", $this->GetId(), $this->GetUsername(), $this->GetPasswordSha1(), $this->GetRandomSessionId(), $this->GetEmail(), $this->GetLocales(), $this->GetLastIp(), NULL, $this->_lastLogin);
        else
            $data = $this->_db->BuildStmtArray("issssssss", $this->GetId(), $this->GetUsername(), $this->GetPasswordSha1(), $this->GetRandomSessionId(), $this->GetEmail(), $this->GetLocales(), NULL, $this->GetLastIp(), $this->_lastLogin);
        $this->_db->BeginTransaction();
        if ($this->_db->ExecuteStmt(Statements::REPLACE_USERS, $data))
        {
            $this->_db->CommitTransaction();
            return true;
        }
        return false;
    }
    
    /***********************************************************\
    *  	                    PROFILE SYSTEM                      *
    \***********************************************************/
    
    /**
     * Gets the user's unique ID
     * @return long Returns a long unsigned integer representing the user's ID
     */
    public function GetId()
    {
        return $this->_id;
    }
    
    /**
     * Sets the user's ID to a new one. This function is private and must be used with a _lot_ of caution.
     * @param long $newId The new user's unique ID
     * @return bool Returns true if success, or false in case of failure.
     */
    private function SetId($newId)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_ID, $this->_db->BuildStmtArray("ii", $newId, $this_>GetId())))
        {
            $this->_id = $newId;
            return true;
        }
        return false;
    }
    
    /**
     * Gets the user's username.
     * @return string Returns a string representing the user's username (nick)
     */
    public function GetUsername() 
    {
        return $this->_username;
    }
    
    /**
     * Sets the user's username.
     * @param string $newUsername The new user's username.
     * @return bool Returns true if success, or false in case of failure.
     */
    public function SetUsername($newUsername)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_USERNAME, $this->_db->BuildStmtArray("ss", $newUsername, $this->GetId())))
        {
            $this->_username = $newName;
            return true;
        }
        return false;
    }
    
    /**
     * Gets the encripted user's password.
     * @return string The encrypted user's password.
     */
    public function GetPasswordSha1()
    {
        return $this->_passwordSha1;
    }
    
    /**
     * Sets the encripted user's password to a new one.<p>Note that this function doesn't check if the password is encripted or anything, just sets the password to the passed string.</p>
     * @param string $newPasswordSha1 The new encripted user's password as a string. The param must be checked _after_ the function is called.
     * @return bool Returns true if success, or false in case of failure.
     */
    public function SetPasswordSha1($newPasswordSha1)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_PASSWORD, $this->_db->BuildStmtArray("ss", $newPasswordSha1, $this->GetId())))
        {
            $this->_passwordSha1 = $newPasswordSha1;
            return true;
        }
        return false;
    }
    
    /**
     * Gets the user's e-mail.
     * @return string Returns a string with the user's e-mail adress
     */
    public function GetEmail()
    {
        return $this->_email;
    }
    
    /**
     * Set's the user's e-mail.
     * @param string $newEmail The new user's e-mail.
     * @return bool Returns true if success, or false in case of failure.
     */
    public function SetEmail($newEmail)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_EMAIL, $this->_db->BuildStmtArray("ss", $newEmail, $this->GetId())))
        {
            $this->_email = $newEmail;
            return true;
        }
        return false;
    }
    
    /**
     * Gets the last used IP by the user.
     * @return string Returns a string with the last user registered IP, in v6 format if available
     */
    public function GetLastIp()
    {
        return $this->_ip;
    }
    
    /**
     * Sets the last IP. The IP can be in v4 format or in v6 form.
     * @param string $newIp The new IP, in v4 or v6 format.
     */
    public function SetLastIp($newIp)
    {
        if ($this->_db->ExecuteStmt((filter_var($newIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? Statements::UPDATE_USERS_IPV4 : Statements::UPDATE_USERS_IPV6),
        	$this->_db->BuildStmtArray("si", $newIp, $this->GetId())))
        {
            $this->_ip = $newIp;
            return true;
        }
        return false;
    }
    
    /**
     * Gets the user's online status
     * @return bool Returns a boolean value (true if the user is online, else false)
     */
    public function IsOnline()
    {
        return $this->_isOnline;
    }
    
    /**
     * Changes the user's online status.
     * @param bool $isOnline Obiusly, true if the user is online.
     * @return bool Returns true if success or false if failure.
     */
    public function SetOnline($isOnline)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_ONLINE, $this->_db->BuildStmtArray("ii", ($isOnline ? "1" : "0"), $this->GetId())))
        {
            $this->_isOnline = (bool)$isOnline;
            return true;
        }
        return false;
    }
    
    /**
     * Gets the date and time of the last user's login, in direct MySQL format (YYYY-MM-DD HH:MM:SS)
     * @return string Returns a string as a direct DATETIME MySQL format.
     */
    public function GetLastLogin()
    {
        return $this->_lastLogin;
    }
    
    /**
     * Sets the last login of this user.
     * @param string $lastLogin A string representing a MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
     * @return boolean Returns true on success or false if failure.
     */
    public function SetLastLogin($lastLogin)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_LAST_LOGIN, $this->_db->BuildStmtArray("si", $lastLogin, $this->GetId())))
        {
            $this->_lastLogin = $lastLogin;
            return true;
        }
        return false;
    }
    
    /**
     * Returns the status of the account, true if it has been email-verified, else false.
     * @return boolean
     */
    public function IsActive()
    {
        return $this->_active == 1 ? true : false;
    }
    
    /**
     * Sets the activation status for the user's account.
     * @param boolean $active The account status (true = activated)
     * @return boolean Returns true on success, else false.
     */
    public function SetActive($active)
    {
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_ACTIVE, $this->_db->BuildStmtArray("ii", $active, $this->GetId())))
        {
            $this->_active = $active;
            return true;
        }
        return false;
    }
    
    
    /**
     * Activates the user account.
     * @return boolean Returns true on success, else false.
     */
    public function ActivateAccount($hash)
    {
        // First, check if the hash is valid
        if ($result = $this->_db->ExecuteStmt(Statements::SELECT_USERS_EMAIL_VERIFICATION, $this->_db->BuildStmtArray("i", $this->GetId())))
        {
            if ($row = $result->fetch_assoc())
            {
                if ($hash == $row['verification_hash'])
                {
                    // Delete the used verification hash
                    if ($this->_db->ExecuteStmt(Statements::DELETE_USERS_EMAIL_VERIFICATION, $this->_db->BuildStmtArray("i", $this->GetId())))
                    {
                        // Set user rank to member
                        if ($this->SetRanks(USER_RANK_MEMBER))
                        {
                            // And finally activate the account
                            if ($this->SetActive(true))
                                return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * [DEPRECATED] Sets the avatar path for this user.
     * @param string $avatarPath The avatar's relative path from the root server directory.
     * @return bool Returns true on success or false if failure.
     */
    public function SetAvatarHost($avatarHost)
    {
        /* 
        if ($this->_db->ExecuteStmt(Statements::UPDATE_USER_DETAILED_DATA_AVATAR, $this->_db->BuildStmtArray("si", $avatarHost, $this->GetId())))
            return true;
        */
        return false;
    }
    
    /**
     * [DEPRECATED] Gets the url for the avatar of this user.
     * @return mixed Returns a string with the full url to the avatar's location, a string representing a relative path if the avatar is the default one, or false if something fails.
     */
    public function GetAvatarHostPath()
    {
        /*
        if (($result = $this->_db->ExecuteStmt(Statements::SELECT_USER_DETAILED_DATA_AVATAR, $this->_db->BuildStmtArray("i", $this->GetId()))))
        {
            $row = $result->fetch_assoc();
            return $row['avatar_path'];
        }
        */
        return false;
    }
    
    /**
     * [DEPRECATED] Determines if the user is using gravatar to get his or her avatar
     * @return boolean Returns true if the user is using gravatar, else it returns false
     */
    public function IsUsingGravatar()
    {
        /*
        if (($avatarLink = $this->GetAvatarHostPath()))
        {
            if (strpos($avatarLink, "http://www.gravatar.com/") !== false)
                return true;
        }
        */
        return true;
    }
    
    /**
     * Obtains the user rank in the main page and all subpages, loading them once from the DB if necessary.
     * @param integer $gameId [Optional] A game ID, used for returning only the rank in a specified web part.
     * @return mixed Returns an array with all the user ranks, or a specified rank for a web area if $gameId is provided. Returns false if something goes wrong.
     */
    public function GetRanks($gameId = GAME_NONE)
    {
        // The user ranks should be loaded from the DB
        if ($this->_ranks === NULL)
        {
            if ($result = $this->_db->ExecuteStmt(Statements::SELECT_USERS_RANKS, $this->_db->BuildStmtArray("i", $this->GetId())))
            {
                if ($row = $result->fetch_assoc())
                {
                    $ranks = str_split($row['rank_mask']);
                    $this->_ranks = $ranks;
                }
                else
                    return false;
            }
            else
                return false;
        }
        if ($gameId != GAME_NONE)
            return $this->_ranks[$gameId];
        return $this->_ranks;
    }
    
    /**
     * Sets the rank for the user globally or specifically to only one web area.
     * @param string $rank The rank ID
     * @param integer $gameId [Optional] If specified, it sets the rank for only one web area.
     * @return boolean True on success, else false.
     */
    public function SetRanks($rank, $gameId = GAME_NONE)
    {
        if ($oldRanks = $this->GetRanks())
        {
            $newRanks = "";
            if ($gameId != GAME_NONE)
            {
                $oldRanks[$gameId] = $rank;
                foreach($oldRanks as $i => $value)
                    $newRanks .= $value;
            }
            else
            {
                for ($i = GAME_OVERALL; $i <= GAMES_COUNT; ++$i)
                    $newRanks .= $rank;
            }
            if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_RANKS, $this->_db->BuildStmtArray("si", $newRanks, $this->GetId())))
                return true;
        }
        return false;
    }
    
    /**
     * Determines if the user is banned. Note that this function executes queries in the DB each time it is called.
     * @return boolean Returns true if the user is banned, else false.
     */
    public function IsBanned()
    {
        if ($result = $this->_db->ExecuteStmt(Statements::SELECT_USERS_BANNED, $this->_db->BuildStmtArray("i", $this->GetId())))
        {
            if ($row = $result->fetch_assoc())
            {
                $banEnd = strtotime($row['ban_end']);
                if ($banEnd >= time() && $row['active'] == 1)
                    return true;
                // Automatically unban the user if the time has expired
                elseif ($banEnd < time() && $row['active'] == 1)
                    $this->SetBanned(false);
            }
        }
        return false;
    }
    
    /**
     * Bans/unbans the current user. Note that this function bans the user from all the servers of the system, so it executes queries in multiple DBs.
     * @param boolean $ban True if the user must be banned, false if the user should be unbanned.
     * @param datetime $banEnd [Optional] When the ban expires.
     * @param integer $bannedBy [Optional] The UID of the mod that banned the user.
     * @param string $banReason [Optional] Why the user was banned.
     * @return boolean True on success, else false.
     */
    public function SetBanned($ban, $banEnd = NULL, $bannedBy = NULL, $banReason = NULL)
    {
        global $DATABASES, $MITRACRAFT_INFO, $TBCSERVER_INFO;
        
        if (!isset($ban))
            return false;
        
        if ($ban)
        {
            if ($bannedBy == NULL || $banEnd == NULL)
                return false;
            
            if ($banReason == NULL)
                $banReason = "Sin especificar";
            
            if ($this->_db->ExecuteStmt(Statements::INSERT_USERS_BANNED, $this->_db->BuildStmtArray("isssii", $this->GetId(), date("Y-m-d H:i:s"), $banEnd, $banReason, $bannedBy, 1)))
            {
                // Ban user in other databases
                if ($mitracraftDb = new Database($DATABASES['MITRACRAFT'], $MITRACRAFT_INFO))
                {
                    $mitracraftDb->ExecuteStmt(Statements::UPDATE_USER_BANNED, $mitracraftDb->BuildStmtArray("is", 0, $this->GetEmail()));
                }
                if ($wowAccountsDb = new Database($DATABASES['TBCSERVER_ACCOUNTS'], $TBCSERVER_INFO))
                {
                    if ($result = $wowAccountsDb->ExecuteStmt(Statements::SELECT_USER_WOW_ACCOUNT, $wowAccountsDb->BuildStmtArray("s", $this->GetUsername())))
                    {
                        if ($row = $result->fetch_assoc())
                        {
                            $wowAccountsDb->ExecuteStmt(Statements::INSERT_USER_WOW_ACCOUNT_BANNED, $wowAccountsDb->BuildStmtArray("iiissi", $row['id'], time(), strtotime($banEnd), GetUsernameFromId($bannedBy), $banReason, 1));
                        }
                    }
                }
            }
        }
        else
        {
            if ($this->_db->ExecuteStmt(Statements::UPDATE_USERS_BANNED_STATUS, $this->_db->BuildStmtArray("ii", 0, $this->GetId())))
            {
                // Unban user in other databases
                if ($mitracraftDb = new Database($DATABASES['MITRACRAFT'], $MITRACRAFT_INFO))
                {
                    $mitracraftDb->ExecuteStmt(Statements::UPDATE_USER_BANNED, $mitracraftDb->BuildStmtArray("is", 1, $this->GetEmail()));
                }
                if ($wowAccountsDb = new Database($DATABASES['TBCSERVER_ACCOUNTS'], $TBCSERVER_INFO))
                {
                    if ($result = $wowAccountsDb->ExecuteStmt(Statements::SELECT_USER_WOW_ACCOUNT, $wowAccountsDb->BuildStmtArray("s", $this->GetUsername())))
                    {
                        if ($row = $result->fetch_assoc())
                        {
                            $wowAccountsDb->ExecuteStmt(Statements::UPDATE_USER_WOW_ACCOUNT_BANNED, $wowAccountsDb->BuildStmtArray("ii", 0, $row['id']));
                        }
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * Obtains the token of this user in the TS3
     * @return string The user's ts3 token, or false if something fails.
     */
    public function GetTs3Token()
    {
        if ($result = $this->_db->ExecuteStmt(Statements::SELECT_USERS_TS3_TOKEN, $this->_db->BuildStmtArray("i", $this->GetId())))
            if ($row = $result->fetch_assoc())
                return $row['token'];
        
        return false;
    }
    
    /**
     * Sets the TS3 token for this user.
     * @param string $newToken The new user token.
     * @return boolean True if nothing fails, else false.
     */
    public function SetTs3Token($newToken)
    {
        if ($this->_db->ExecuteStmt(Statements::INSERT_USERS_TS3_TOKEN, $this->_db->BuildStmtArray("is", $this->GetId(), $newToken)))
            return true;
        
        return false;
    }
    
    private $_id;                // The user's unique ID
    private $_username;          // The user's username (nickname)
    private $_passwordSha1;      // The encripted user's password
    private $_email;             // The user's e-mail
    private $_ip;                // The user's last used IP address
    private $_isOnline;          // True if the user is online, else false
    private $_lastLogin;         // Date and time of the last user's login.
    private $_registerDate;      // Date of the registration.
    private $_active;            // 0 if the user's account has not been activated yet, else 1.
    private $_db;                // The database object
    private $_ranks;             // Array with all the ranks of the user for the main page and subpages.
}

?>