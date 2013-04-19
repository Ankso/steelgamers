<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
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
?>
<html>
<head>
	<title>Minecraft - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="../css/main.css">
	<script type="text/javascript" src="../js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="../js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="../js/common.js"></script>
</head>
<body>
<div class="backToMainPageContainer">
	<a href="http://steelgamers.es"><img src="/images/back_logo.png"></a>
</div>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="/index.php"><img class="bannerLabelImg" src="../images/banner_label.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php PrintTopBar(); ?>
    		<div class="latestNewsLabel">Servidor: Mitracraft</div>
    		<div class="new">
    			<div class="newContainer">
    				<h2>Conexi&oacute;n</h2>
    				<ul><li>Direcci&oacute;n IP: mitracraft.es</li></ul>
    				<h2>Caracter&iacute;sticas</h2>
    				<ul>
    					<li>Procesador: 3.1GHz x4</li>
    					<li>Memoria: 16Gb DDR3</li>
    					<li>Ancho de banda: 100Mbps sin l&iacute;mite.</li>
    				</ul>
    				<h2>Descripci&oacute;n</h2>
    				<ul>
    			    	<li>Foro propio: Si (<a href="http://mitracraft.es">http://mitracraft.es</a>)</li>
    					<li>Conectado a la red Steel Gamers: Si</li>
    					<li>Informaci&oacute;n detallada: <a href="http://mitracraft.es/index.php?p=/discussion/6/presentacion-de-mitracraft">http://mitracraft.es/index.php?p=/discussion/6/presentacion-de-mitracraft</a></li>
    				</ul>
    			</div>
    		</div>
    	</div>
    	<div class="rightWrapper">
        	<div class="rightContainer">
        		<div class="rightItem">
        		<?php 
        		if ($loggedIn)
        		{
        		?>
        			<div class="profileWrapper">
        				<div class="avatarWrapper">
        					<img src="<?php echo GenerateGravatarUrl($user->GetEmail(), 150); ?>">
        				</div>
        				<div>
            				<div>Conectado como: <b><?php echo $user->GetUsername(); ?></b></div>
            				<a class="plainLink" href="../controlpanel.php"><div class="button">Panel de control</div></a>
            				<a class="plainLink" href="http://steelgamers.es/logout.php?redirect=minecraft"><div class="button">Desconectarse</div></a>
        				</div>
        			</div>
        		<?php
        		}
        		else
        		{
        		?>
        			<form class="loginForm" action="http://steelgamers.es/login.php?redirect=minecraft" method="post">
        				<div class="formItem">Usuario</div>
        				<div class="formItem"><input type="text" name="username"></div>
        				<div class="formItem">Contrase&ntilde;a</div>
        				<div class="formItem"><input type="password" name="password"></div>
        				<div class="formItem"><input class="button" type="submit" value="Conectarse"></div>
        				<div class="formItem">o <a href="register.php">crear una cuenta</a></div>
        			</form>
        		<?php
        		}
        		?>
        		</div>
        		<?php PrintTs3Status(); ?>
        	</div>
        </div>
	</div>
	<?php PrintBottomBar(); ?>
    <?php PrintWoWTbcServerStatus(); ?>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>