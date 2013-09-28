<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Layout.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../libs/TeamSpeak3/TeamSpeak3.php");

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
$loggedIn = false;
if (isset($_SESSION['userId']))
{
    $loggedIn = true;
    $user = new User($_SESSION['userId']);
}
$_Layout = new Layout();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Servidores Minecraft - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="../css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/common.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
</head>
<body>
<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
<div class="backToMainPageContainer">
	<a href="http://steelgamers.es"><img src="/images/back_logo.png"></a>
</div>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="/index.php"><img class="bannerLabelImg" src="../images/banner_label.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/top.php"); ?>
    		<div class="latestNewsLabel <?php if (isset($user)) { echo $user->IsPremium() ? " premiumLatestNewsLabel" : ""; } ?>">Minecraft: Servidor privado de la comunidad</div>
    		<div class="new">
    			<div class="newContainer">
    				<h2>Conexi&oacute;n</h2>
    				<ul>
    					<li>Direcci&oacute;n IP: minecraftserver.steelgamers.es</li>
    					<li>Versi&oacute;n: 1.6.4</li>
    				</ul>
    				<h2>Caracter&iacute;sticas</h2>
    				<ul>
    					<li>Procesador: 3.1GHz x2</li>
    					<li>Memoria: 8Gb DDR3</li>
    					<li>Ancho de banda: 100Mbps</li>
    				</ul>
    				<h2>Descripci&oacute;n</h2>
    				<ul>
    			    	<li>Conectado a la red Steel Gamers: Si (Whitelist)</li>
    					<li>Informaci&oacute;n detallada y c&oacute;mo conectarse: <a href="http://foro.steelgamers.es/index.php?p=/discussion/36/servidor-de-minecraft-de-la-comunidad">http://foro.steelgamers.es/index.php?p=/discussion/36/servidor-de-minecraft-de-la-comunidad</a></li>
    				</ul>
    			</div>
    		</div>
    	</div>
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>