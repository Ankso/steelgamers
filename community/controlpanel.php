<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
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
if (!isset($_SESSION['userId']))
{
    header("location:login.php");
    exit();
}
$loggedIn = true;
$errors = array(
    'password'       => ERROR_NONE,
    'password_check' => ERROR_NONE,
    'email'          => ERROR_NONE,
    'server'         => ERROR_NONE,
);
$user = new User($_SESSION['userId']);
if ($_SERVER['REQUEST_METHOD'] == "POST")
{
    $noChange = true;
    // User wants to change the password
    if (isset($_POST['password']) && isset($_POST['password_check']) && $_POST['password'] != "" && $_POST['password_check'] != "")
    {
        if ($_POST['password'] == $_POST['password_check'])
        {
            if (htmlentities($_POST['password']) == $_POST['password'] && strlen($_POST['password']) >= PASSWORD_MIN_LENGHT)
            {
                if ($user->SetPasswordSha1(CreateSha1Pass($user->GetUsername(), $_POST['password'])))
                    $noChange = false;
                else
                    $errors['server'] = ERROR_CRITICAL;
            }
            else
                $errors['password'] = ERROR_INVALID;
        }
        else
            $errors['password_check'] = ERROR_INVALID;
    }
    // User wants to change his email
    if (isset($_POST['email']) && $user->GetEmail() != $_POST['email'] && $_POST['email'] != "")
    {
        if (IsValidEmail($_POST['email']))
        {
            if (!UserExists($_POST['email']))
            {
                if ($user->SetEmail($_POST['email']))
                    $noChange = false;
                else
                    $errors['server'] = ERROR_CRITICAL;
            }
            else
                $errors['email'] = ERROR_CRITICAL;
        }
        else
            $errors['email'] = ERROR_INVALID;
    }
}
$userRank = $user->GetRanks(GAME_OVERALL);
$isAdmin = ($userRank > USER_RANK_MODERATOR);
$ts3Token = $user->GetTs3Token();
if (!$ts3Token)
{
    // Create new token for this user
    TeamSpeak3::init();
    $ts3UserToken = "";
    try
    {
        $ts3Rank = $RANK_NAMES[$userRank];
        if ($ts3Rank = $RANK_NAMES[USER_RANK_SUPERADMIN])
            $ts3Rank = $RANK_NAMES[USER_RANK_ADMINISTRATOR];
        $ts3_ServerInstance = TeamSpeak3::factory("serverquery://" . $TEAMSPEAK3['USER'] . ":" . $TEAMSPEAK3['PASSWORD'] . "@" . $TEAMSPEAK3['HOST'] . ":" . $TEAMSPEAK3['QUERY_PORT'] . "/");
        $ts3_VirtualServer = $ts3_ServerInstance->serverGetByPort($TEAMSPEAK3['VOICE_PORT']);
        $ts3_ServerGroup = $ts3_VirtualServer->serverGroupGetByName($ts3Rank);
        $ts3UserToken = $ts3_ServerGroup->privilegeKeyCreate("Token creado para el usuario " . $user->GetUsername(), "ident=web_username value=" . $user->GetUsername() . "\pident=web_id value=" . $user->GetId());
        $ts3UserToken = $ts3UserToken->toString();
        $user->SetTs3Token($ts3UserToken);
        $ts3Token = $ts3UserToken;
    }
    catch(Exception $e)
    {
        $ts3Token = false;
    }
}
?>
<html>
<head>
	<title>Panel de control - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<link type="text/css" rel="stylesheet" href="css/controlpanel.css">
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
		setTimeout(function() {
			$("div.adminError").fadeOut();
			$("div.noAdminError").fadeOut();
		}, 5000);
	});
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
    		<div class="latestNewsLabel"><a class="plainLink" href="controlpanel.php">Panel de control</a></div>
    		<?php if (isset($_GET['adminError']))
    		{
    		?>
    		<div class="<?php echo ($_GET['adminError'] == "true") ? "adminError" : "noAdminError"; ?>"><?php echo ($_GET['adminError'] == "true") ? "Se ha producido un error al procesar la solicitud" : "Solicitud procesada correctamente"; ?></div>
    		<?php 
    		}
    		?>
    		<div class="new">
    			<h1>Opciones b&aacute;sicas</h1>
    			<div class="newContainer">
    				<div class="formWrapper">
        				<form class="formBasicOptions" action="controlpanel.php" method="post">
            				<div class="formItem formItemLabel">Nombre de usuario:</div>
        					<div class="formItem formItemInput"><input class="formItemDisabled" type="text" disabled value="<?php echo $user->GetUsername(); ?>"></div>
        					<div class="formItem formItemLabel">Nueva contrase&ntilde;a:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['password'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="password" name="password" value=""></div>
        					<div class="formItem formItemLabel">Confirmar nueva contrase&ntilde;a:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['password_check'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="password" name="password_check" value=""></div>
        					<div class="formItem formItemLabel">E-mail:</div>
        					<div class="formItem formItemInput"><input <?php echo ($errors['email'] != ERROR_NONE ? 'class="badInput"' : ''); ?> type="text" name="email" value="<?php echo $user->GetEmail(); ?>"></div>
        					<div class="formItem"><input class="button" type="submit" value="Actualizar"></div>
        				</form>
    				</div>
    				<?php 
				    $hasErrors = false;
				    foreach($errors as $i => $value)
				    {
				        $hasErrors = $value;
				        if ($hasErrors != ERROR_NONE)
				            break;
				    }
				    if ($hasErrors != ERROR_NONE)
				    {
    				?>
    				<div class="formErrorsWrapper">
						Se han encontrado los siguientes errores:<br>
						<ul>
							<li <?php echo ($errors['password_check'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>Las contrase&ntilde;as no coinciden.
							<li <?php echo ($errors['password'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>La contrase&ntilde;a contiene caracteres inv&aacute;lidos o es demasiado corta (m&iacute;nimo 7 caracteres).
							<li <?php echo ($errors['email'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>El e-mail introducido no es v&aacute;lido.
							<li <?php echo ($errors['email'] == ERROR_CRITICAL ? "" : 'style="display:none"'); ?>>El e-mail introducido ya est&aacute; en uso.
							<li <?php echo ($errors['server'] != ERROR_NONE ? "" : 'style="display:none"'); ?>>Se ha producido un error en la base de datos. Es posible que uno de los servidores est&eacute; saturado o ca&iacute;do. Por favor, int&eacute;ntalo de nuevo m&aacute;s tarde.
						</ul>
						<?php if (isset($noChange) && $noChange) { ?>
						<br>No se ha producido ning&uacute;n cambio.
						<?php } ?>
					</div>
					<?php 
    				}
    				elseif (isset($noChange) && !$noChange)
    				{
					?>
					<div class="formErrorsWrapper">
						<span style="color:#00FF00;">Cambios aplicados correctamente.</span>
					</div>
					<?php 
    				}
    				elseif (isset($noChange) && $noChange)
    				{
    				?>
    				<div class="formErrorsWrapper">
						No se ha realizado ning&uacute;n cambio.<br>
					</div>
    				<?php
    				}
    				?>
    			</div>
    		</div>
    		<div class="new">
    			<h1>Team Speak 3</h1>
    			<div class="newContainer">
    			    <?php
            		if ($ts3Token)
            		{
            		?>
            		<div>
        				<div class="formItem formItemLabel">Token:</div>
            			<div class="formItem formItemInput"><?php echo $ts3Token; ?></div>
            		</div>
            		<div>
            			<div class="formItem formItemLabel">Link de conexi&oacute;n:</div>
            			<div class="formItem formItemInput"><a href="ts3server://steelgamers.es?nickname=<?php echo $user->GetUsername(); ?>&addbookmark=Steel%20Gamers%20Community&token=<?php echo $ts3Token; ?>/">Haz click aqu&iacute;</a></div>
        			</div>
            		<?php
            		}
            		else
            		{
        			?>
        			<div>
            			<div class="formItem formItemLabel">Token:</div>
                		<div class="formItem formItemInput">Error: no se ha podido crear un token de usuario. Es posible que el servidor de TeamSpeak 3 no est&eacute; disponible temporalmente.</div>
                	</div>
                	<div>
                		<div class="formItem formItemLabel">Link de conexi&oacute;n:</div>
                		<div class="formItem formItemInput">El link no est&aacute; disponible en estos momentos. Recarga la p&aacute;gina para volver a intentarlo.</div>
                	</div>
        			<?php
            		}
        			?>
        		</div>
    		</div>
    		<div class="new">
    			<h1>Rango de usuario</h1>
    			<div class="newContainer">
        			<div class="formItem formItemLabel">Rango:</div>
            		<div class="formItem formItemInput"><?php echo $RANK_NAMES[$userRank]; ?></div>
        		</div>
    		</div>
    		<?php 
    		if ($isAdmin)
    		{
    		    $db = new Database($DATABASES['USERS']);
    		    $faqQuestions = array();
    		    if ($result = $db->Execute(Statements::SELECT_FAQ))
    		    {
    		        while ($row = $result->fetch_assoc())
    		        {
    		            $faqQuestions[] = array(
    		                'id'       => $row['id'],
    		                'question' => $row['question'],
    		            );
    		        }
    		    }
    		        
    		?>
    		<div class="new">
    			<h1>Gesti&oacute;n &uacute;ltimas noticias</h1>
    			<div class="newContainer">
    				<form class="formBottomBorder" action="admin.php" method="post">
    					<input type="hidden" name="from" value="controlpanel">
            			<div class="formItem">T&iacute;tulo:</div>
                		<div class="formItem"><input type="text" name="new_title"></div>
                		<div class="formItem">
                			Cuerpo:
                			<ul>
                				<li>Tags html permitidas: <?php echo htmlentities(HTML_ALLOWED_TAGS); ?> (ojo con el XSS)
                				<li>Los links son parseados autom&aacute;ticamente.
                				<li>Los links a v&iacute;deos de Youtube son embebidos autom&aacute;ticamente.
                			</ul>
                		</div>
                		<div class="formItem"><textarea rows="10" cols="70" name="new_body"></textarea></div>
                		<div class="formItem"><input class="button" type="submit" value="Enviar"></div>
            		</form>
            		<form action="admin.php" method="post">
            			<input type="hidden" name="from" value="controlpanel">
            			<div class="formItem formItemLabel">Eliminar noticia n&#176;:</div>
                		<div class="formItem"><input type="text" name="new_delete"></div>
                		<div class="formItem"><input class="button" type="submit" value="Eliminar"></div>
            		</form>
        		</div>
    		</div>
    		<div class="new">
    			<h1>Gesti&oacute;n de FAQ</h1>
    			<div class="newContainer">
    				<form class="formBottomBorder" action="admin.php" method="post">
    					<input type="hidden" name="from" value="controlpanel">
            			<div class="formItem">Pregunta:</div>
                		<div class="formItem"><input type="text" name="faq_question"></div>
                		<div class="formItem">
                			Respuesta:
                			<ul>
                				<li>Tags html permitidas: <?php echo htmlentities(HTML_ALLOWED_TAGS); ?> (ojo con el XSS)
                				<li>Los links son parseados autom&aacute;ticamente.
                				<li>Los links a v&iacute;deos de Youtube son embebidos autom&aacute;ticamente.
                			</ul>
                		</div>
                		<div class="formItem"><textarea rows="10" cols="70" name="faq_answer"></textarea></div>
                		<div class="formItem"><input class="button" type="submit" value="Enviar"></div>
            		</form>
            		<form action="admin.php" method="post">
            			<input type="hidden" name="from" value="controlpanel">
            			<div class="formItem formItemLabel">Eliminar pregunta:</div>
                		<div class="formItem">
							<select name="faq_delete">
								<?php 
								foreach ($faqQuestions as $i => $value)
								{
								?>
								<option value="<?php echo $value['id']; ?>"><?php echo "#", $i + 1, ": ", $value['question']; ?></option>
								<?php 
								}
								?>
							</select>
						</div>
                		<div class="formItem"><input class="button" type="submit" value="Eliminar"></div>
            		</form>
        		</div>
    		</div>
    		<?php 
    		}
    		?>
    	</div>
    	<div class="rightContainer">
    		<div class="userProfile">
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
        				<a class="plainLink" href="logout.php"><div class="button">Desconectarse</div></a>
    				</div>
    			</div>
    		<?php
    		}
    		else
    		{
    		?>
    			<form class="loginForm" action="login.php" method="post">
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
    	</div>
	</div>
	<div class="bottomBarContainer">
	</div>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>