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
{
    header("Location:index.php");
    exit();
}

$error = ERROR_NONE;
if (isset($_POST['username']) && isset($_POST['password']))
{
    $username = $_POST['username'];
    $password = $_POST['password'];
    $db = new Database($DATABASES['USERS']);
    if ($result = $db->ExecuteStmt(Statements::SELECT_USERS_LOGIN_DATA, $db->BuildStmtArray("s", $username)))
    {
        if ($row = $result->fetch_assoc())
        {
            // Check if passwords match and then log the user in
            if ($row['password_sha1'] === CreateSha1Pass($username, $password))
            {
                $user = new User($username);
                if ($user->IsBanned())
                {
                    header("Location:http://steelgamers.es/banned.php?reason=" . $user->GetId());
                    exit();
                }
                if ($user->IsActive())
                {
                    $user->SetLastLogin(date("Y-m-d H:i:s"));
                    $user->SetLastIp($_SERVER['REMOTE_ADDR']);
                    $user->SetOnline(true);
                    $_SESSION['userId'] = $user->GetId();
                    if (isset($_POST['redirect']))
                    {
                        switch($_POST['redirect'])
                        {
                            case "forum":
                                header("Location:http://foro.steelgamers.es");
                                break;
                            case "mitracraft":
                                header("Location:http://mitracraft.es");
                                break;
                            case "minecraft":
                                header("Location:http://minecraft.steelgamers.es");
                                break;
                            case "arma2":
                                header("Location:http://arma2.steelgamers.es");
                                exit();
                            case "wow":
                                header("Location:http://wow.steelgamers.es");
                                exit();
                            case "membersList":
                                header("Location:http://steelgamers.es/members.php");
                                exit();
                            default:
                                header("Location:index.php");
                                break;
                        }
                    }
                    else
                        header("Location:index.php");
                }
                else
                    $error = ERROR_LOGIN_VERIFICATION;
            }
            else
                $error = ERROR_LOGIN_PASSWORD;
        }
        else
            $error = ERROR_LOGIN_USERNAME;
    }
    else
        $error = ERROR_CRITICAL;
}
$_Layout = new Layout(false, false, false, false, false, true, true);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Conectarse - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/login.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
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
    			<h1>Conectarse</h1>
    			<div class="newContainer">
    			    <div class="formWrapper">
        				<form name="login" action="login.php" method="post" style="float:left;">
        						<input type="hidden" name="redirect" value="<?php if (isset($_GET['redirect'])) { echo $_GET['redirect']; }?>">
            					<div class="formItem formItemLabel">Nombre de usuario:</div>
            					<div class="formItem formItemInput <?php echo ($error == ERROR_LOGIN_USERNAME) ? "badInput" : ""; ?>"><input type="text" name="username"></div>
            					<div class="formItem formItemLabel">Contrase&ntilde;a:</div>
            					<div class="formItem formItemInput <?php echo ($error == ERROR_LOGIN_PASSWORD) ? "badInput" : ""; ?>"><input type="password" name="password"></div>
            					<div class="formItem formItemSubmit"><input class="button" type="submit" value="Conectarse"></div>
            					<div class="formItem formItemNewAccount">o <a href="register.php">crear una cuenta</a></div>
    
        				</form>
    				</div>
    				<?php 
    				if ($error != ERROR_NONE)
    				{
    				?>
    				<div class="formErrorsWrapper">
    					Se ha producido el siguiente error:
    					<ul>
    						<li <?php echo ($error == ERROR_LOGIN_USERNAME) ? '' : 'style="display:none;"'; ?>>El nombre de usuario no existe en nuestra base de datos.
    						<li <?php echo ($error == ERROR_LOGIN_PASSWORD) ? '' : 'style="display:none;"'; ?>>La contrase&ntilde;a es incorrecta.
    						<li <?php echo ($error == ERROR_CRITICAL) ? '' : 'style="display:none;"'; ?>>Se ha producido un error al conectarse a la base de datos. Es posible que el servidor est&eacute; saturado, o que no se encuentre disponible en estos momento. Por favor, int&eacute;ntalo de nuevo m&aacute;s tarde.
    						<li <?php echo ($error == ERROR_LOGIN_VERIFICATION) ? '' : 'style="display:none"'; ?>>La direcci&oacute;n de correo electr&oacute;nico no ha sido verificada. Para poder acceder a la web, debes verificar tu e-mail.
    					</ul>
    				</div>
    				<?php 
    				}
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