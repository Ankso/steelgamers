<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
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
    if ($user->IsBanned())
    {
        header("Location:http://steelgamers.es/logout.php?redirect=banned");
        exit();
    }
}
$_Layout = new Layout(true, false, false, false, false, true);
$db = new Database($DATABASES['USERS']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>FAQ - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<link type="text/css" rel="stylesheet" href="css/faq.css">
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
    		<div class="latestNewsLabel <?php if (isset($user)) { echo $user->IsPremium() ? " premiumLatestNewsLabel" : ""; } ?>">Normas</div>
        		<div class="new">
        			<div class="newContainer">
						<h1>No est&aacute; permitido...</h1>
						<ol>
							<li>Utilizar ning&uacute;n tipo de hack/cheat/script/exploit o modificaci&oacute;n del cliente en ninguno de nuestros servidores. La infracci&oacute;n de esta norma conllevar&aacute; un baneo indefinido de toda la Red Steel Gamers.</li>
        					<li>Faltar al respeto a cualquier miembro en la web o por un canal p&uacute;blico dentro de los servidores de juego. No podemos controlar todos los insultos que se hacen de forma privada, por ello en todos los juegos y en el foro existe una opci&oacute;n de ignorar. &Uacute;sala.</li>
        					<li>La utilizaci&oacute;n de cualquier sistema o parte de la infraestructura de la Red Steel Gamers para hacer spam.</li>
        					<li>Realizar cualquier tipo de comentario o publicar contenido racista o sexista.</li>
        					<li>Hacer publicidad de otros servicios o empresas privadas agenas a la Red de Steel Gamers. La infracci&oacute;n de esta norma conllevar&aacute; un baneo indefinido de toda la Red Steel Gamers.</li>
        				</ol>
        				Para m&aacute;s detalles a cerca de la normativa del sitio, puedes consultar los <a href="/docs/terminos_y_condiciones.html">T&eacute;rminos y Condiciones de Uso</a> que deber&iacute;as haber le&iacute;do al registrarte.
        			</div>
    			</div>
    		</div>
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>