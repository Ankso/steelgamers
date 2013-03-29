<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
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
if (isset($_SESSION['userId']))
{
    $user = new User($_SESSION['userId']);
    $user->SetOnline(false);
    // Destroy forum session
    setcookie("Vanilla", "", time() - 3600);
    setcookie("Vanilla-Volatile", "", time() - 3600);
}
session_destroy();
if (isset($_GET['redirect']))
{
    switch($_GET['redirect'])
    {
        case "forum":
            header("location:foro/index.php");
            exit();
        case "mitracraft":
            header("Location:http://mitracraft.es");
            exit();
        default:
            break;
    }
}
header("location:index.php");
?>