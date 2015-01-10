<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");

if (!isset($_GET['callback']))
    die();

$armaStatus = array(
    'wasteland' => GetArma2ServerStatus(2302),
);
echo $_GET['callback'] . "(" . json_encode($armaStatus) . ")";
?>