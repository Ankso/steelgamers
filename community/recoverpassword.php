<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Layout.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");
// PEAR mail.php is compatible with php 4, and in php 5 it has a E_STRICT error, so turn off E_STRICT error reporting for this script
error_reporting(E_NOTICE);
require "Mail.php";
error_reporting(E_ALL);

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
    header("location:index.php");

$error = false;
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['email']) && IsValidEmail($_POST['email']))
{
    $userId = UserExists($_POST['email']);
    echo $userId;
    if ($userId === false)
        $error = true;
    else
    {
        $user = new User($userId);
        $newPassword = md5(MAGIC_STRING . microtime());
        // Send verification mail
        $from    = "administracion@steelgamers.es";
        $to      = $_POST['email'];
        $subject = "Nueva contraseña - Steel Gamers Community";
        $body    = "
        
        Has recibido este correo electrónico porque has solicitado una nueva contraseña, la cual es:
        
        -------------------------------------------------------
        Contraseña:        " . $newPassword . "
        -------------------------------------------------------
        
        Puedes cambiar la contraseña desde tu panel de control:
        
        http://steelgamers.es/controlpanel.php
        
        Si usted no ha solicitado cambiar la contraseña de su cuenta, es posible que la seguridad de ésta esté comprometida. Cambie la contraseña cuanto antes e informe a la administración del sitio.
        
        ";
        
        $headers=array(
            'From'      => $from,
            'To'        => $to,
            'Subject'   => $subject
        );
        
        $smtp = Mail::factory('smtp', $PEAR_MAIL_CONFIG);
        
        $mail = $smtp->send($to, $headers, $body);
        if (PEAR::isError($mail))
            $error = true;
        else
            $user->SetPasswordSha1(CreateSha1Pass($user->GetUsername(), $newPassword));
    }
}
else
    $error = true;
$_Layout = new Layout(true, false, false, false, false, false, true);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Recuperar contrase&ntilde;a - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<link type="text/css" rel="stylesheet" href="css/login.css">
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
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/top.php"); ?>
    		<div class="new">
    			<h1>Conectarse</h1>
    			<div class="newContainer">
    				<?php 
    				if ($error)
    				    echo "Error al enviar el correo electr&oacute;nico. Es posible que esa direcci&oacute;n no se encuentre en nuestra base de datos.";
    				else
    				    echo "Correo electr&oacute;nico enviado correctamente. Sigue las instrucciones en el mismo para recuperar tu cuenta.";
    				?>
    			</div>
    		</div>
    	</div>
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>