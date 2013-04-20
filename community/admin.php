<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");

$sessionsHandler = new CustomSessionsHandler();
session_set_save_handler(
    array($sessionsHandler, "open"),
    array($sessionsHandler, "close"),
    array($sessionsHandler, "read"),
    array($sessionsHandler, "write"),
    array($sessionsHandler, "destroy"),
    array($sessionsHandler, "gc")
    );
register_shutdown_function("session_write_close");
session_start();
// Check if the user is logged in
if (!isset($_SESSION['userId']))
{
    header("location:login.php");
    exit();
}
$loggedIn = true;
$user = new User($_SESSION['userId']);
$userRank = $user->GetRanks(GAME_OVERALL);
$isAdmin = ($userRank > 2);
if ($isAdmin)
{
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {
        $extraParams = "";
        $error = true;
        // Admin wants to insert a new
        if (isset($_POST['new_title']) && isset($_POST['new_body']))
        {
            $title = strip_tags($_POST['new_title'], HTML_ALLOWED_TAGS);
            $body = strip_tags($_POST['new_body'], HTML_ALLOWED_TAGS);
            // Parse all links:
            $body = preg_replace("#(http(s)?://)([a-z0-9_\-\?\/.=&~]*)#i", '<a href="http$2://$3" target="_blank">http$2://$3</a>', $body);
            // Parse youtube links and embed the video instead of the simple link:
            $body = preg_replace("#(<a href=\"http://(www.)?youtube.com)?/(v/|watch\?v=)([a-z0-9\-_~]+)([^<]+)(</a>)#i", '<div style="text-align:center;"><iframe width="640" height="480" src="http://www.youtube.com/embed/$4?wmode=transparent" frameborder="0" allowfullscreen></iframe></div><br />', $body);
        
            // Insert new in the database
            if ($title != "" && $body != "")
            {
                $db = new Database($DATABASES['USERS']);
                if ($db->ExecuteStmt(Statements::INSERT_LATEST_NEWS, $db->BuildStmtArray("issss", $user->GetId(), $user->GetUsername(), $title, $body, date("Y-m-d H:i:s"))))
                    $error = false;
            }
        }
        // Admin wants to delete a new
        if (isset($_POST['new_delete']) && $_POST['new_delete'] != "")
        {
            $db = new Database($DATABASES['USERS']);
            if ($db->ExecuteStmt(Statements::DELETE_LATEST_NEWS, $db->BuildStmtArray("i", $_POST["new_delete"])))
                $error = false;
        }
        // Admin Wants to insert a new FAQ question/answer
        if (isset($_POST['faq_question']) && isset($_POST['faq_answer']))
        {
            $question = strip_tags($_POST['faq_question'], HTML_ALLOWED_TAGS);
            $answer = strip_tags($_POST['faq_answer'], HTML_ALLOWED_TAGS);
            // Parse all links:
            $answer = preg_replace("#(http(s)?://)([a-z0-9_\-\?\/.=&~]*)#i", '<a href="http$2://$3" target="_blank">http$2://$3</a>', $answer);
            // Parse youtube links and embed the video instead of the simple link:
            $answer = preg_replace("#(<a href=\"http://(www.)?youtube.com)?/(v/|watch\?v=)([a-z0-9\-_~]+)([^<]+)(</a>)#i", '<div style="text-align:center;"><iframe width="640" height="480" src="http://www.youtube.com/embed/$4?wmode=transparent" frameborder="0" allowfullscreen></iframe></div><br />', $answer);
            
            // Insert new in the database
            if ($question != "" && $answer != "")
            {
                $db = new Database($DATABASES['USERS']);
                if ($db->ExecuteStmt(Statements::INSERT_FAQ, $db->BuildStmtArray("issss", $user->GetId(), $user->GetUsername(), $question, $answer, date("Y-m-d H:i:s"))))
                    $error = false;
            }
        }
        // Admin wants to delete a FAQ question/answer
        if (isset($_POST['faq_delete']) && $_POST['faq_delete'] != "")
        {
            $db = new Database($DATABASES['USERS']);
            if ($db->ExecuteStmt(Statements::DELETE_FAQ, $db->BuildStmtArray("i", $_POST["faq_delete"])))
                $error = false;
        }
        // Admin wants to edit an user
        if (isset($_POST['editUserId']) && $_POST['action'] == "permissions")
        {
            $db = new Database($DATABASES['USERS']);
            $rankMask = "";
            for ($i = GAME_NONE + 1; $i <= GAMES_COUNT; ++$i)
            {
                if (isset($_POST[$i]))
                    $rankMask .= $_POST[$i];
                else
                {
                    // If, for whatever, one rank is not set, use the master rank as default, if this is not set either, we have a serious problem :(
                    if (isset($_POST['0']))
                        $rankMask .= $_POST['0'];
                    else
                        $rankMask .= "2";
                }
            }
            if ($db->ExecuteStmt(Statements::UPDATE_USERS_RANKS, $db->BuildStmtArray("si", $rankMask, $_POST['editUserId'])))
            {
                if ($db->ExecuteStmt(Statements::DELETE_USERS_TS3_TOKEN, $db->BuildStmtArray("i", $_POST['editUserId'])))
                    $error = false;
            }
            $extraParams .= "&lastEditedUser=" . $_POST['editUserId'];
        }
        // Admin wants to ban an user
        if (isset($_POST['editUserId']) && isset($_POST['action']) && $_POST['action'] == "banUser")
        {
            // Find how much time the user must be banned
            $totalBanTime = 0;
            if (isset($_POST['banExpiresMinutes']))
                if (is_numeric($_POST['banExpiresMinutes']))
                    $totalBanTime += $_POST['banExpiresMinutes'];
            if (isset($_POST['banExpiresHours']))
                if (is_numeric($_POST['banExpiresHours']))
                    $totalBanTime += $_POST['banExpiresHours'] * 60;
            if (isset($_POST['banExpiresDays']))
                if (is_numeric($_POST['banExpiresDays']))
                    $totalBanTime += $_POST['banExpiresDays'] * 60 * 24;
            if (isset($_POST['banExpiresMonths']))
                if (is_numeric($_POST['banExpiresMonths']))
                    $totalBanTime += $_POST['banExpiresMonths'] * 60 * 24 * 30;
            if (isset($_POST['banExpiresYears']))
                if (is_numeric($_POST['banExpiresYears']))
                    $totalBanTime += $_POST['banExpiresYears'] * 60 * 24 * 30 * 12;
            // Time must be in seconds
            $totalBanTime *= 60;
            // Add current timestamp
            $totalBanTime += time();
            // Convert to valid MySQL date
            $totalBanTime = date("Y-m-d H:i:s", $totalBanTime);
            // Ban reason
            $reason = NULL;
            if (isset($_POST['banReason']))
                $reason = $_POST['banReason'];
            // Create the user object, it handles all the bans in all the servers
            $targetUser = new User(intval($_POST['editUserId']));
            if ($targetUser->GetRanks(GAME_OVERALL) < $user->GetRanks(GAME_OVERALL))
                if ($targetUser->SetBanned(true, $totalBanTime, $user->GetId(), $reason))
                    $error = false;
            $extraParams .= "&lastEditedUser=" . $_POST['editUserId'];
        }
        // Admin wants to unban an user
        if (isset($_POST['editUserId']) && isset($_POST['action']) && $_POST['action'] == "unbanUser")
        {
            // Create the user object and unban him
            $targetUser = new User(intval($_POST['editUserId']));
            if ($targetUser->SetBanned(false))
                $error = false;
            $extraParams .= "&lastEditedUser=" . $_POST['editUserId'];
        }
        if (isset($_POST['from']))
        {
            if ($error)
                header("Location:" . $_POST['from'] . ".php?adminError=true" . $extraParams);
            else
                header("Location:" . $_POST['from'] . ".php?adminError=false" . $extraParams);
            exit();
        }
    }
}
header("Location:controlpanel.php");


?>