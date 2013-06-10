<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Layout.Class.php");
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
$_Layout = new Layout(true, false, false, false, false, false);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Verificar cuenta - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/common.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
</head>
<body>
<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/top.php"); ?>
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
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>