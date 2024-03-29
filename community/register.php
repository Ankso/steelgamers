<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/recaptchalib.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Layout.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../libs/TeamSpeak3/TeamSpeak3.php");
// PEAR Mail.php is compatible with php 4, and in php 5 it has a E_STRICT error, so turn off E_STRICT error reporting for this script
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
    header("Location:index.php");

$db = new Database($DATABASES['USERS']);
$recaptchaPrivateKey = "6LfR1N4SAAAAAPjwK0EU2cJpzbVgnIKa_pJvx_0v";
$loggedIn = false;
$errors = array(
    'incomplete_form'      => ERROR_NONE,
    'username'             => ERROR_NONE,
    'password'             => ERROR_NONE,
    'password_check'       => ERROR_NONE,
    'email'                => ERROR_NONE,
    'email_check'          => ERROR_NONE,
    'terms_and_conditions' => ERROR_NONE,
    'captcha'              => ERROR_NONE,
    'critical'             => ERROR_NONE,
);

// It should enter here only if all the form parts are set
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password_check']) && isset($_POST['email'])
    && isset($_POST['email_check']) && isset($_POST['recaptcha_challenge_field']) && isset($_POST['recaptcha_response_field']))
{
    
    if ($_POST['username'] !== "" && $_POST['password'] !== "" && $_POST['password_check'] !== "" && $_POST['email'] !== "" && isset($_POST['terms_and_conditions'])
        && $_POST['email_check'] !== "" && $_POST['recaptcha_challenge_field'] != "" && $_POST['recaptcha_response_field'] != "")
    {
        // Check Recaptcha
        $recaptchaResponse = recaptcha_check_answer($recaptchaPrivateKey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
        if (!$recaptchaResponse->is_valid)
            $errors['captcha'] = ERROR_INVALID;
        // Check Username
        // Check for valid characters only
        if (htmlspecialchars($_POST['username']) != $_POST['username'] || strlen($_POST['username']) < USERNAME_MIN_LENGHT || (
            strpos(strtolower($_POST['username']), "a") === false &&
            strpos(strtolower($_POST['username']), "e") === false &&
            strpos(strtolower($_POST['username']), "i") === false &&
            strpos(strtolower($_POST['username']), "o") === false &&
            strpos(strtolower($_POST['username']), "u") === false
        ))
            $errors['username'] = ERROR_INVALID;
        // Check that the username is not in use
        $result = $db->ExecuteStmt(Statements::SELECT_USERS_ID, $db->BuildStmtArray("s", $_POST['username']));
        if ($result->num_rows > 0)
            $errors['username'] = ERROR_INVALID;
        // Check password
        // Check that both passwords match
        if ($_POST['password'] != $_POST['password_check'])
            $errors['password_check'] = ERROR_INVALID;
        // Check that the password has valid characters
        if (htmlspecialchars($_POST['password']) != $_POST['password'] || strlen($_POST['password']) < PASSWORD_MIN_LENGHT)
            $errors['password'] = ERROR_INVALID;
        // Check email
        // Check that both emails match
        if ($_POST['email'] != $_POST['email_check'])
            $errors['email_check'] = ERROR_INVALID;
        // Check that the email is a valid one
        if (!IsValidEmail($_POST['email']))
            $errors['email'] = ERROR_INVALID;
        // Check that the email is not in use
        $result = $db->ExecuteStmt(Statements::SELECT_USERS_EMAIL, $db->BuildStmtArray("s", $_POST['email']));
        if ($result->num_rows > 0)
            $errors['email'] = ERROR_INVALID;
        // Check that everything was OK
        $allOk = true;
        foreach ($errors as $i => $value)
        {
            if ($value != ERROR_NONE)
            {
                $allOk = false;
                break;
            }
        }
        if ($allOk)
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            // Simple control var to ensure that no errors are triggered while the user is being created.
            $allOk = true;
            $data = NULL;
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
                $data = $db->BuildStmtArray("sssssissi", $username, CreateSha1Pass($username, $password), $email, NULL, $ip, 0, "1000-01-01 00:00:00", date("Y-m-d H:i:s"), 0);
            else
                $data = $db->BuildStmtArray("sssssissi", $username, CreateSha1Pass($username, $password), $email, $ip, NULL, 0, "1000-01-01 00:00:00", date("Y-m-d H:i:s"), 0);
            // Here we start the DB operations
            if ($db->ExecuteStmt(Statements::INSERT_USERS, $data))
            {
                $db->BeginTransaction();
                // Now we can initialize the User object. Note that this is to obtain the user ID and create the rows in any related tables.
                $user = new User($username);
                // Begin the transaction and insert the data. This is to create all the rows in the related tables of the user's Database. Not used btw.
                $hash = md5("cosSjv .adf%" . microtime() * rand(0, 999999));
                if ($db->ExecuteStmt(Statements::INSERT_USERS_EMAIL_VERIFICATION, $db->BuildStmtArray("iss", $user->GetId(), $hash, date("Y-m-d H:i:s"))))
                {
                    $ranks = "";
                    for ($i = GAME_OVERALL; $i <= GAMES_COUNT; ++$i)
                        $ranks .= USER_RANK_EMAIL_NOT_VERIFIED;
                    if ($db->ExecuteStmt(Statements::INSERT_USERS_RANKS, $db->BuildStmtArray("is", $user->GetId(), $ranks)))
                    {
                        // Send verification mail
                        $from    = "administracion@steelgamers.es";
                        $to      = $email;
                        $subject = "Steel Gamers Community - Activar cuenta";
                        $body    = "
                        
                        �Gracias por registrarte en la comunidad Steel Gamers!
                        Tu cuenta ha sido creada con las siguientes credenciales:
                        
                        ----------------------------------
                        Nombre de usuario: " . $username . "
                        Contrase�a:        ********
                        ----------------------------------
                        
                        Sin embargo, para poder acceder debes verificar el correo electr�nico haciendo click en el siguiente enlace:
                        
                        http://steelgamers.es/activate.php?username=" . $username . "&activation=" . $hash . "
                        
                        Si usted no se ha registrado en nuestro sitio web y cree que ha recibido este correo por error, le rogamos que lo elimine y que disculpe las molestias.
                        
                        ";
                        
                        $headers = array(
                            'From'      => $from,
                            'To'        => $to,
                            'Subject'   => $subject
                        );
                        
                        $smtp = Mail::factory('smtp', $PEAR_MAIL_CONFIG);
                        
                        $mail = $smtp->send($to, $headers, $body);
                        if (PEAR::isError($mail))
                            $allOk = false;
                            
                        TeamSpeak3::init();
                        $ts3UserToken = "";
                        try
                        {
                            $ts3_ServerInstance = TeamSpeak3::factory("serverquery://" . $TEAMSPEAK3['USER'] . ":" . $TEAMSPEAK3['PASSWORD'] . "@" . $TEAMSPEAK3['HOST'] . ":" . $TEAMSPEAK3['QUERY_PORT'] . "/");
                            $ts3_VirtualServer = $ts3_ServerInstance->serverGetByPort($TEAMSPEAK3['VOICE_PORT']);
                            $ts3_ServerGroup = $ts3_VirtualServer->serverGroupGetByName($RANK_NAMES[USER_RANK_MEMBER]);
                            $ts3UserToken = $ts3_ServerGroup->privilegeKeyCreate("Token creado para el usuario " . $user->GetUsername(), "ident=web_username value=" . $user->GetUsername() . "\pident=web_id value=" . $user->GetId());
                            $ts3UserToken = $ts3UserToken->toString();
                        }
                        catch(Exception $e)
                        {
                            // It's not neccesary to cancel account creation here, token can be created also from the control panel
                            /* $allOk = false; */ 
                        }
                        if ($ts3UserToken != "")
                        {
                            if (!$user->SetTs3Token($ts3UserToken))
                                $allOk = false;
                        }
                    }
                }
                else
                    $allOk = false;
            }
            else
                $allOk = false;
            // If an error(s) has happen, we must rollback the DB transactions
            if (!$allOk)
            {
                $db->RollbackTransaction();
                if (isset($user))
                    $db->ExecuteStmt(Statements::DELETE_USERS, $db->BuildStmtArray("i", $user->GetId()));
                $errors['critical'] = ERROR_INVALID;
            }
            else
            {
                $db->CommitTransaction();
                /*
                 * Not used now, as the WoW server is not currently online
                 * 
                // Create account in the WoW server
                $wowAccountsDb = new Database($DATABASES['TBCSERVER_ACCOUNTS'], $TBCSERVER_INFO);
                // User object is created here already, else we can't be here.
                // Also if this fails, it's not neccesary to cancel account creation, as wow account creation can be triggered later from the control panel.
                // TODO: If this fails, inform the user!
                $wowAccountsDb->ExecuteStmt(Statements::INSERT_USER_WOW_ACCOUNT, $wowAccountsDb->BuildStmtArray("ssissssssiisiiii", strtoupper($user->GetUsername()), CreateWoWServerSha1Pass($user->GetUsername(), $password), 0, "", "0", "0", $user->GetEmail(), time(), "0.0.0.0", 0, 0, "0000-00-00 00:00:00", 0, 1, 0, 0));
            	*/
            }
        }
    }
    else
    {
        $errors['incomplete_form'] = ERROR_INVALID;
        if ($_POST['username'] == "")
            $errors['username'] = ERROR_UNFILLED;
        if ($_POST['password'] == "")
            $errors['password'] = ERROR_UNFILLED;
        if ($_POST['password_check'] == "")
            $errors['password_check'] = ERROR_UNFILLED;
        if ($_POST['email'] == "")
            $errors['email'] = ERROR_UNFILLED;
        if ($_POST['email_check'] == "")
            $errors['email_check'] = ERROR_UNFILLED;
        if (!isset($_POST['terms_and_conditions']))
            $errors['terms_and_conditions'] = ERROR_INVALID;
    }
}
$_Layout = new Layout(true, false, false, false, false, true, true);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Registrarse - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/fancyboxjQuery.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/register.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.fancybox.pack.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/common.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript">
	$(document).ready(function() {
		$("a.fancyLink").fancybox();
	});
	</script>
	<script type="text/javascript">
    var RecaptchaOptions = {
    	theme : "blackglass",
    	lang : "es"
    };
	</script>
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
    			<h1>Crear nueva cuenta:</h1>
    			<div class="newContainer">
    				<div class="formWrapper">
    					<form name="register" action="register.php" method="post" style="float:left;">
        					<div class="formItem formItemLabel">Nombre de usuario:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['username'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="text" name="username" value="<?php echo (isset($_POST['username']) ? $_POST['username'] : "");?>"></div>
        					<div class="formItem formItemLabel">Contrase&ntilde;a:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['password'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="password" name="password" value="<?php echo (isset($_POST['password']) ? $_POST['password'] : "");?>"></div>
        					<div class="formItem formItemLabel">Confirmar contrase&ntilde;a:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['password_check'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="password" name="password_check" value="<?php echo (isset($_POST['password_check']) ? $_POST['password_check'] : "");?>"></div>
        					<div class="formItem formItemLabel">E-mail:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['email'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="text" name="email" value="<?php echo (isset($_POST['email']) ? $_POST['email'] : "");?>"></div>
        					<div class="formItem formItemLabel">Confirmar e-mail:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['email_check'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="text" name="email_check" value="<?php echo (isset($_POST['email_check']) ? $_POST['email_check'] : "");?>"></div>
        					<div class="formItem formItemInput">
        					<?php 
        					    $publickey = "6LfR1N4SAAAAALwtORCIoidjdpx_R8BM6hOP2C8t";
        					    echo recaptcha_get_html($publickey);
        					?>
        					</div>
        					<div class="formItem formItemInput"><input type="checkbox" name="terms_and_conditions" value="terms_and_conditions">Acepto los <a class="fancyLink" href="docs/terminos_y_condiciones.html">T&eacute;rminos y Condiciones de Uso</a>.</div>
        					<div class="formItem formItemSubmit"><input class="button" type="submit" value="Registrarme"></div>
    					</form>
    				</div>
    				<?php 
				    $hasErrors = false;
				    foreach($errors as $i => $value)
				    {
				        $hasErrors = $value;
				        if ($hasErrors != ERROR_NONE )
				            break;
				    }
				    if ($hasErrors != ERROR_NONE)
				    {
    				?>
    				<div class="formErrorsWrapper">
						Se han encontrado los siguientes errores:<br>
						<ul>
							<li <?php echo ($errors['incomplete_form'] != ERROR_NONE ? "" : 'style="display:none"'); ?>>No se han completado todos los campos del formulario.
							<li <?php echo ($errors['username'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>El nombre de usuario ya est&aacute; en uso o no es v&aacute;lido (m&iacute;nimo 4 caracteres con alguna vocal).
							<li <?php echo ($errors['password_check'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>Las contrase&ntilde;as no coinciden.
							<li <?php echo ($errors['password'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>La contrase&ntilde;a contiene caracteres inv&aacute;lidos o es demasiado corta (m&iacute;nimo 7 caracteres).
							<li <?php echo ($errors['email'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>El e-mail no es v&aacute;lido o ya est&aacute; en uso.
							<li <?php echo ($errors['email_check'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>Los correos electr&oacute;nicos no coinciden.
							<li <?php echo ($errors['terms_and_conditions'] != ERROR_NONE ? "" : 'style="display:none"'); ?>>Los T&eacute;rminos y Condiciones de Uso deben ser le&iacute;dos y aceptados.
							<li <?php echo ($errors['captcha'] != ERROR_NONE ? "" : 'style="display:none"'); ?>>El valor del CAPTCHA es incorrecto.
							<li <?php echo ($errors['critical'] != ERROR_NONE ? "" : 'style="display:none"'); ?>>Se ha producido un error en la base de datos. Es posible que uno de los servidores est&eacute; saturado o ca&iacute;do. Por favor, int&eacute;ntalo de nuevo m&aacute;s tarde.
						</ul>
					</div>
					<?php 
    				}
    				elseif ($_SERVER['REQUEST_METHOD'] == "POST" && isset($allOk) && $allOk == true)
    				{
					?>
					<div class="formErrorsWrapper">
						Cuenta creada correctamente. Se ha enviado un correo de verificaci&oacute;n a la direcci&oacute;n indicada. Aseg&uacute;rate de comprobar tu carpeta de spam.<br>
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