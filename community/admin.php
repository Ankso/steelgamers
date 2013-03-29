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
        if ($error)
            header("Location:" . $_POST['from'] . ".php?adminError=true");
        else
            header("Location:" . $_POST['from'] . ".php?adminError=false");
    }
}
else
{
    header("Location:index.php");
}

?>