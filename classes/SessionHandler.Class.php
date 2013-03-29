<?php
/**
 * Implementation of a custom sessions handler over the PHP's default, using a MySQL database to store sessions
 * @author Ankso
 */
class CustomSessionsHandler
{
    /**
     * Establishes the connection with the MySQL database.
     * @return true, or kills the script if something fails.
     */
    public function open()
    {
        global $DATABASES;
        $this->_sessionsDb = New Database($DATABASES['SESSIONS']);
        // We don't need to return false if the connection to the DB can't be stablished, because the Database contructor kill the script execution if something fails (with die())
        return true;
    }
    
    /**
     * Closes the connection to the MySQL database.
     * @returns true
     */
    public function close()
    {
        // Nothing to do here really
        unset($this->_sessionsDb);
        return true;
    }
    
    /**
     * Gets the session data from the MySQL database.
     * @param string id The PHPSESSID of this session
     * @return string Returns the data retrieved ("" if no data was found).
     */
    public function read($id)
    {
        $id = $this->_sessionsDb->RealEscapeString($id);
        if ($result = $this->_sessionsDb->Execute("SELECT data FROM sessions WHERE id = '" . $id . "'"))
        {
            if ($result->num_rows !== 0)
            {
                $row = $result->fetch_assoc();
                return $row['data'];
            }
        }
        return "";
    }

    /** 
     * Saves session data in the MySQL database
     * @param string id The PHPSESSID of this session
     * @param string data The data that needs to be stores, as a string.
     * @return boolean Returns true on success, false in case of failure.
     */
    public function write($id, $data)
    {
        $id = $this->_sessionsDb->RealEscapeString($id);
        $data = $this->_sessionsDb->RealEscapeString($data);
        $lastUpdate = $this->_sessionsDb->RealEscapeString(time());
        if ($this->_sessionsDb->Execute("REPLACE INTO sessions VALUES ('" . $id . "', '" . $data . "', " . $lastUpdate . ")"))
            return true;
        return false;
    }
    
    /**
     * Destroys a session, removing the data from the MySQL database.
     * @param string id The PHPSESSID.
     * @return boolean Returns true on success, or false on failure.
     */
    public function destroy($id)
    {
        $id = $this->_sessionsDb->RealEscapeString($id);
        if ($this->_sessionsDb->Execute("DELETE FROM sessions WHERE id = '" . $id . "'"))
            return true;
        return false;
    }
    
    /**
     * Removes expired session's data from the MySQL database when called.
     * @param integer max The maximun time a session can be stored without updates, defined in the PHP config file.
     * @return Returns true on success, or false on failure.
     */
    public function gc($max)
    {
        $outdated = $this->_sessionsDb->RealEscapeString(time() - $max);
        if ($this->_sessionsDb->Execute("DELETE FROM sessions WHERE last_update < " . $outdated))
            return true;
        return false;
    }
    
    private $_sessionsDb;     // The connection to the MySQL database.
}
?>