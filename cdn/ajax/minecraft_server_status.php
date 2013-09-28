<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");

if (!isset($_GET['callback']))
    die();

$serverStatus = GetMinecraftServerStatus();
echo $_GET['callback'] . "(" . json_encode($serverStatus) . ")";
?>