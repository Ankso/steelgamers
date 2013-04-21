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
	<title>ArmA 2 - Steel Gamers</title>
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
    		<?php 
    		if (!isset($_GET['lang']))
    		{
    		?>
    		<div class="latestNewsLabel">Servidor: Steel Gamers ArmA 2 #1 - <a href="http://arma2.steelgamers.es/servidores/sgc1_arma2.php?lang=enGB">view in english</a></div>
    		<?php
    		}
    		else
    		{
    		?>
    		<div class="latestNewsLabel">Server: Steel Gamers ArmA 2 #1 - <a href="http://arma2.steelgamers.es/servidores/sgc1_arma2.php">ver en castellano</a></div>
    		<?php 
    		}
    		?>
    		<div class="new">
    			<div class="newContainer">
    				<?php
    				if (!isset($_GET['lang']))
    				{
    				?>
    				<h2>C&oacute;mo conectarse</h2>
    				<ul>
    					<li>Ejecutar la beta del juego:</li>
    					<ul>
    						<li>Si el juego la versi&oacute;n de Steam, se puede hacer desde la biblioteca de juegos.</li>
    						<li>Si no, tienes que descargar e instalar la &uacute;ltima versi&oacute;n de la beta desde <a href="http://www.arma2.com/beta-patch.php">http://www.arma2.com/beta-patch.php</a> y ejecutar el juego desde la carpeta del mismo, utilizando el acceso directo &quot;Launch Arma 2 OA beta patch&quot;</li>
    					</ul>
    					<li>&iexcl;Ya puedes conectarte!</li>
    					<li>Direcci&oacute;n IP: 94.23.240.154</li>
    					<li>Puerto: 2302</li>
    					<li>Tambi&eacute;n tenemos TeamSpeak 3 disponible para todos los miembros, para saber c&oacute;mo conectarte, visita el <a href="http://steelgamers.es/faq.php">FAQ</a>.
    				</ul>
    				<h2>Caracter&iacute;sticas</h2>
    				<ul>
    					<li>Procesador: 3.4+GHz x2 - 4 hilos</li>
    					<li>Memoria: 8Gb DDR3</li>
    					<li>Ancho de banda: 100Mbps.</li>
    				</ul>
    				<h2>Descripci&oacute;n</h2>
    				<ul>
    			    	<li>Reporta todos los bugs que encuentres en el <a href="http://foro.steelgamers.es/index.php?p=/categories/arma">foro</a></li>
    					<li>Este servidor est&aacute; conectado a la red Steel Gamers. Para m&aacute;s informaci&oacute;n, puedes revisar el <a href="http://steelgamers.es/faq.php">FAQ</a></li>
    					<li>El servidor utiliza una versi&oacute;n de Wasteland custom, basada en 404Wasteland Takistan pero muy modificada:</li>
    					<ul>
    						<li>Combate urbano: algunos objetivos aparecen en ciudades.</li>
    						<li>Mayor variedad de veh&iacute;culos y armas, tanto en objetivos como spawneados en el mundo.</li>
    						<li>Spawn dentro de edificios. En vez de aparecer en medio del campo, el spawn es dentro edificios aleatorios, por lo tanto el campeo es m&aacute;s complicado.</li>
    						<li>Posibilidad de hacer grupos o pelotones en los equipos OPFOR y BLUFOR</li>
    						<li>Nuevo sistema de aparici&oacute;n de armas: En funci&oacute;n del tipo de veh&iacute;culo en el que vayan a spawnear (civil, militar o militar armado) ser&aacute;n de una calidad mayor o menor.</li>
    						<li>Y muchas m&aacute;s cosas que descubrir&aacute;s jugando...
    					</ul>
    				</ul>
    				<?php 
    				}
    				else
    				{
    				?>
    				<h2>Connection</h2>
    				<ul>
    					<li>Execute the beta version of the game:
    					<ul>
    						<li>If the game is the Steam version, you can do it from your games library.</li>
    						<li>Else, you have to download and install the latest beta patch from <a href="http://www.arma2.com/beta-patch.php">http://www.arma2.com/beta-patch.php</a> and execute the game using the shortcut &quot;Launch Arma 2 OA Beta Patch&quot; created in the main game folder.
    					</ul>
    					<li>You should be able to connect now!</li>
    					<li>IP address: 94.23.240.154</li>
    					<li>Port: 2302</li>
    				</ul>
    				<h2>Server features</h2>
    				<ul>
    					<li>Processor: 3.4+GHz x2 - 4 threads</li>
    					<li>Memory: 8Gb DDR3</li>
    					<li>Bandwidth: 100Mbps.</li>
    				</ul>
    				<h2>Descripci&oacute;n</h2>
    				<ul>
    			    	<li>Por favor reporta todos los bugs que encuentres en el <a href="http://steelgamers.es/foro/index.php?p=/categories/arma">foro</a></li>
    					<li>Este servidor est&aacute; conectado a la red Steel Gamers. Para m&aacute;s informaci&oacute;n, puedes revisar el <a href="http://steelgamers.es/faq.php">FAQ</a></li>
    					<li>El servidor utiliza una versi&oacute;n de Wasteland custom, basada en 404Wasteland Takistan pero muy modificada:</li>
    					<ul>
    						<li>Combate urbano: algunos objetivos aparecen en ciudades.</li>
    						<li>Mayor variedad de veh&iacute;culos y armas, tanto en objetivos como spawneados en el mundo.</li>
    						<li>Spawn dentro de edificios. En vez de aparecer en medio del campo, el spawn es dentro edificios aleatorios, por lo tanto el campeo es m&aacute;s complicado.</li>
    						<li>Posibilidad de hacer grupos o pelotones en los equipos OPFOR y BLUFOR</li>
    						<li>Nuevo sistema de aparici&oacute;n de armas: En funci&oacute;n del tipo de veh&iacute;culo en el que vayan a spawnear (civil, militar o militar armado) ser&aacute;n de una calidad mayor o menor.</li>
    						<li>Y muchas m&aacute;s cosas que descubrir&aacute;s jugando...
    					</ul>
    				</ul>
    				<?php
    				}
    				?>
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
            				<a class="plainLink" href="http://steelgamers.es/logout.php?redirect=arma2"><div class="button">Desconectarse</div></a>
        				</div>
        			</div>
        		<?php
        		}
        		else
        		{
        		?>
        			<form class="loginForm" action="http://steelgamers.es/login.php?redirect=arma2" method="post">
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