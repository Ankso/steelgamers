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
	<title>WoW: TBC - Steel Gamers</title>
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
    		<div class="latestNewsLabel">Servidor: World of Warcraft: The Burning Crusade (2.4.3)</div>
    		<div class="new">
    			<div class="newContainer">
    				<h2>Conexi&oacute;n</h2>
    				<ul>
    					<li>Realmlist: wowserver.steelgamers.es</li>
    					<li>Abre el archivo realmlist.wtf, situado en la carpeta del juego, con cualquier editor de texto como el bloc de notas. Borra todo el contenido y escribe:<br><br><b>set realmlist wowserver.steelgamers.es</b></li>
    				</ul>
    				<h2>Caracter&iacute;sticas*</h2>
    				<ul>
    					<li>Procesador: 3.4GHz x2 (x4 hilos)</li>
    					<li>Memoria: 8Gb DDR3</li>
    					<li>Ancho de banda: 100Mbps.</li>
    				</ul>
    				<h2>Descripci&oacute;n</h2>
    				<ul>
    			    	<li>Reporta todos los bugs que encuentres en el <a href="http://steelgamers.es/foro/index.php?p=/categories/world-of-warcraft-the-burning-crusade">foro</a>.</li>
    			    	<li>Este servidor est&aacute; conectado a la red Steel Gamers. Para m&aacute;s informaci&oacute;n, puedes revisar el <a href="http://steelgamers.es/faq.php">FAQ</a>.</li>
    					<li>El servidor tiene un reino PvP completamente Blizzlike.</li>
    					<li>Sistema de rates personalizables: &iexcl;Elige tus propias rates! El servidor posee un sistema &uacute;nico de rates personalizables. Con un simple comando in-game, puedes cambiar las rates de tu personaje (entre unos l&iacute;mites). &iquest;Eres un jugador hardcore que le gusta el contenido de Warcraft? Escoge rates x1. &iquest;Prefieres llegar lo m&aacute;s r&aacute;pido posible al contenido de mayor nivel? Sube tus rates a x5. &iquest;Algo intermedio? Sin problema, &iexcl;cualquier n&uacute;mero es v&aacute;lido!</li>
    				</ul>
    				<div>*Las caracter&iacute;sticas del servidor pueden ser aumentadas en funci&oacute;n de las necesidades. En Steel Gamers, o no hay lag, o no hay servidor.</div>
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
				<?php PrintWoWTbcServerStatus(); ?>
				<?php PrintMitracraftServerStatus(); ?>
				<?php PrintArma2ServerStatus(); ?>
        	</div>
        </div>
	</div>
	<?php PrintBottomBar(); ?>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>