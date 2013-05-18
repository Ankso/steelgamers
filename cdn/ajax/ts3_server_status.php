<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../libs/TeamSpeak3/TeamSpeak3.php");

if (!isset($_GET['callback']))
    die();

$serverStatus = GetTs3Status();
echo $_GET['callback'] . "(" . json_encode($serverStatus) . ")";
?>