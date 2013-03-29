<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
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
if (isset($_SESSION['userId']))
{
    // Vanilla SSO login
    // 1. Get your client ID and secret here. These must match those in your jsConnect settings.
    $clientID = "1952785965";
    $secret = "b75d433c3a1871bba4353d6f78bf880e";
    
    // 2. Grab the current user from your session management system or database here.
    $user = new User($_SESSION['userId']);
    // 3. Fill in the user information in a way that Vanilla can understand.
    $vanillaUser = array(
        'uniqueid' => $user->GetId(),
        'name'     => $user->GetUsername(),
        'email'    => $user->GetEmail(),
        'photourl' => GenerateGravatarUrl($user->GetEmail()),
    	'roles'    => $RANK_NAMES[$user->GetRanks(GAME_OVERALL)], // OPTIONAL. You can configure jsconnect to synchronise roles
    );
    
    // 4. Generate the jsConnect string.
    
    // This should be true unless you are testing. 
    // You can also use a hash name like md5, sha1 etc which must be the name as the connection settings in Vanilla.
    $secure = true; 
    WriteJsConnect($vanillaUser, $_GET, VANILLA_JSCONNECT_CLIENT_ID, VANILLA_JSCONNECT_SECRET, $secure);
    // header("Location:index.php");
}
