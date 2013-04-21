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
$userId = NULL;
if (isset($_SESSION['userId']))
{
    $userId = $_SESSION['userId'];
    $user = new User($_SESSION['userId']);
    $user->SetOnline(false);
}
session_destroy();
if (isset($_GET['redirect']))
{
    // Destroy vanilla forum session if needed
    switch($_GET['redirect'])
    {
        case "forum":
            header("location:http://foro.steelgamers.es");
            exit();
        case "mitracraft":
            header("Location:http://foro.steelgamers.es/logout.php?redirect=mitracraft");
            exit();
        case "minecraft":
            header("Location:http://foro.steelgamers.es/logout.php?redirect=minecraft");
            exit();
        case "arma2":
            header("Location:http://foro.steelgamers.es/logout.php?redirect=arma2");
            exit();
        case "wow":
            header("Location:http://foro.steelgamers.es/logout.php?redirect=wow");
            exit();
        case "banned":
            header("Location:http://foro.steelgamers.es/logout.php?redirect=banned&reason=" . $userId);
            exit();
        default:
            break;
    }
}
header("location:index.php");
?>