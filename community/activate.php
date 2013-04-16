<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
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
// Check if the user is logged in
if (isset($_SESSION['userId']))
    header("Location:index.php");

$error = true;
if (isset($_GET['username']) && isset($_GET['activation']))
{
    if ($user = new User($_GET['username']))
    {
        if ($user->ActivateAccount($_GET['activation']))
            $error = false;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Verificar cuenta - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php PrintTopBar(); ?>
    		<div class="new">
    			<h1>Verificaci&oacute;n de e-mail</h1>
    			<?php 
    			if (!$error)
    			{
    			?>
    			<div class="newContainer">
    				Tu correo electr&oacute;nico ha sido verificado correctamente, ahora ya puedes entrar desde <a href="login.php">aqu&iacute;</a> o utilizando el panel de la derecha.
    			</div>
    			<?php 
    			}
    			else
    			{
    			?>
    			<div class="newContainer">
    				Error en la verificaci&oacute;n. Es posible que el enlace del correo electr&oacute;nico est&eacute; obsoleto porque ha pasado demasiado tiempo, o que la cuenta ya est&eacute; verificada.
    			</div>
    			<?php 
    			}
    			?>
    		</div>
    	</div>
    	<div class="rightContainer">
    		<div class="rightItem">
    			<form class="loginForm" action="login.php" method="post">
    				<div class="formItem">Usuario</div>
    				<div class="formItem"><input type="text" name="username"></div>
    				<div class="formItem">Contrase&ntilde;a</div>
    				<div class="formItem"><input type="password" name="password"></div>
    				<div class="formItem"><input class="button" type="submit" value="Conectarse"></div>
    				<div class="formItem">o <a href="register.php">crear una cuenta</a></div>
    			</form>
    		</div>
    	</div>
	</div>
	<?php PrintBottomBar(); ?>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>