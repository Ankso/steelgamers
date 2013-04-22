<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");

if (!isset($_GET['callback']))
    die();

$serverStatus = GetWowTbcServerStatus();
echo $_GET['callback'] . "(" . json_encode($serverStatus) . ")";
?>