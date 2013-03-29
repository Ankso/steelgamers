<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/recaptchalib.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");
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
                $data = $db->BuildStmtArray("sssssisi", $username, CreateSha1Pass($username, $password), $email, NULL, $ip, 0, "1000-01-01 00:00:00", 0);
            else
                $data = $db->BuildStmtArray("sssssisi", $username, CreateSha1Pass($username, $password), $email, $ip, NULL, 0, "1000-01-01 00:00:00", 0);
            // Here we start the DB operations
            if ($db->ExecuteStmt(Statements::INSERT_USERS, $data))
            {
                $db->BeginTransaction();
                // Now we can initialize the User object. Note that this is for obtain the user ID to create the rows in any related tables.
                $user = new User($username);
                // Begin the transaction and insert the data. This is to create all the rows in the related tables of the users Database. Not used btw.
                $hash = md5("cosSjv .adf%" . microtime() * rand(0, 999999));
                if ($db->ExecuteStmt(Statements::INSERT_USERS_EMAIL_VERIFICATION, $db->BuildStmtArray("iss", $user->GetId(), $hash, date("Y-m-d H:i:s"))))
                {
                    $ranks = "";
                    for ($i = GAME_OVERALL; $i <= GAMES_COUNT; ++$i)
                        $ranks .= USER_RANK_EMAIL_NOT_VERIFIED;
                    if ($db->ExecuteStmt(Statements::INSERT_USERS_RANKS, $db->BuildStmtArray("is", $user->GetId(), $ranks)))
                    {
                        // Send verification mail
                        $from    = "noreply@steelgamers.com";
                        $to      = $email;
                        $subject = "Activar cuenta - Steel Gamers Community";
                        $body    = "
                        
                        ¡Gracias por registrarte en la comunidad Steel Gamers!
                        Tu cuenta ha sido creada con las siguientes credenciales:
                        
                        ----------------------------------
                        Nombre de usuario: " . $username . "
                        Contraseña:        ********
                        ----------------------------------
                        
                        Sin embargo, para poder acceder debes verificar el correo electrónico haciendo click en el siguiente enlace:
                        
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
                $db->CommitTransaction();
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
?>
<html>
<head>
	<title>Registrarse - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<link type="text/css" rel="stylesheet" href="css/register.css">
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
	<script type="text/javascript">
    var RecaptchaOptions = {
    	theme : "blackglass",
    	lang : "es"
    };
	</script>
</head>
<body>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner_label.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php PrintTopBar(); ?>
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
        					<div class="formItem formItemInput"><input type="checkbox" name="terms_and_conditions" value="terms_and_conditions">Acepto los <a href="#">T&eacute;rminos y Condiciones de Uso</a>.</div>
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
    	<div class="rightContainer">
    		<div class="userProfile">
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
	<div class="bottomBarContainer">
	</div>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>