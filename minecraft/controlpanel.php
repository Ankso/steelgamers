<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Layout.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../libs/TeamSpeak3/TeamSpeak3.php");

/**
 * xAuth function for compare hashed passwords
 * @author CypherX
 */
function CheckPassword($checkPass, $realPass)
{
	// xAuth hashing
	$saltPos = (strlen($checkPass) >= strlen($realPass) ? strlen($realPass) : strlen($checkPass));
	$salt = substr($realPass, $saltPos, 12);
	$hash = hash('whirlpool', $salt . $checkPass);
	return $realPass == substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
}

/**
 * xAuth function to encrypt a username
 * @author CypherX
 */
function encryptPassword($password)
{
	$salt = substr(hash('whirlpool', uniqid(rand(), true)), 0, 12);
	$hash = hash('whirlpool', $salt . $password);
	$saltPos = (strlen($password) >= strlen($hash) ? strlen($hash) : strlen($password));
	return substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
}

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
    'password'          => ERROR_NONE,
    'password_check'    => ERROR_NONE,
    'email'             => ERROR_NONE,
    'server'            => ERROR_NONE,
    'newCharacter'      => ERROR_NONE,
    'newCharacterPass'  => ERROR_NONE,
    'newCharacterCheck' => ERROR_NONE,
    'retrieveCharacter' => ERROR_NONE,
);
$user = new User($_SESSION['userId']);
if ($user->IsBanned())
{
    header("Location:http://steelgamers.es/logout.php?redirect=banned");
    exit();
}
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
    // Minecraft server character creation/retrieval
    // User wants to create a new minecraft character
    if (isset($_POST['newCharacterName']) && isset($_POST['newCharacterPassword']) && isset($_POST['newCharacterCheck']))
    {
        $name = htmlentities($_POST['newCharacterName']);
        // Username checks...
        if (strlen($name) > 2 && $name == $_POST['newCharacterName'])
        {
            if ($_POST['newCharacterPassword'] == $_POST['newCharacterCheck'])
            {
                if (strlen($_POST['newCharacterPassword']) >= 6)
                {
                    $minecraftDb = New Database($DATABASES['MITRACRAFT'], $MITRACRAFT_INFO);
                    $result = $minecraftDb->ExecuteStmt(Statements::SELECT_USER_CHARACTER_BY_NAME, $minecraftDb->BuildStmtArray("s", $name));
                    if ($result->num_rows == 0)
                    {
                        $password = encryptPassword($_POST['newCharacterPassword']);
                        if (!$minecraftDb->ExecuteStmt(Statements::INSERT_USER_CHARACTER, $minecraftDb->BuildStmtArray("ssisssssi", $name, $password, 0, $user->GetEmail(), date("Y-m-d H:i:s"), $user->GetLastIp(), NULL, NULL, 1)))
                            $errors['newCharacter'] = ERROR_CRITICAL;
                    }
                    else
                        $errors['newCharacter'] = ERROR_LOGIN_USERNAME;
                }
                else
                    $errors['newCharacterPass'] = ERROR_INVALID;
            }
            else
                $errors['newCharacterCheck'] = ERROR_INVALID;
        }
        else
            $errors['newCharacter'] = ERROR_INVALID;
    }
    // User wants to retrieve a character from the Minecraft server
    if (isset($_POST['retrieveCharacterPassword']) && isset($_POST['retrieveCharacterName']))
    {
        $name = $_POST['retrieveCharacterName'];
        $minecraftDb = New Database($DATABASES['MITRACRAFT'], $MITRACRAFT_INFO);
        $result = $minecraftDb->ExecuteStmt(Statements::SELECT_USER_CHARACTER_BY_NAME, $minecraftDb->BuildStmtArray("s", $name));
        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            if (CheckPassword($_POST['retrieveCharacterPassword'], $row['password']))
            {
                if (!$minecraftDb->ExecuteStmt(Statements::UPDATE_USER_CHARACTER_EMAIL, $minecraftDb->BuildStmtArray("ss", $user->GetEmail(), $name)))
                    $errors['retrieveCharacter'] = ERROR_CRITICAL;
            }
            else
                $errors['retrieveCharacter'] = ERROR_LOGIN_PASSWORD;
        }
        else
            $errors['retrieveCharacter'] = ERROR_INVALID;
    }
}
$userRank = $user->GetRanks(GAME_OVERALL);
$isAdmin = ($userRank > USER_RANK_MODERATOR);
// If the user is not admin yet, check if he has the page specific rank
if (!$isAdmin)
{
    $userRank = $user->GetRanks(GAME_MINECRAFT);
    $isAdmin = ($userRank > USER_RANK_MODERATOR);
}
// Overwrite displayed user rank with the specific game rank, but save admin privileges if the user is admin globaly.
$userRank = $user->GetRanks(GAME_MINECRAFT);
// This operations are here because this is specific for each of the minecraft servers
// Use the previous connection if it's available
if (isset($minecraftDb))
    $db = $minecraftDb;
else
    $db = New Database($DATABASES['MITRACRAFT'], $MITRACRAFT_INFO);
$characters = array();
if ($result = $db->ExecuteStmt(Statements::SELECT_USER_CHARACTERS, $db->BuildStmtArray("s", $user->GetEmail())))
{
    while ($row = $result->fetch_assoc())
    {
        $characters[] = array(
            "id"            => $row['id'],
            "name"          => $row['playername'],
            "registered"    => $row['registerdate'],
            "lastLoginIp"   => $row['lastloginip'],
            "lastLoginDate" => $row['lastlogindate'],
        );
    }
}
$_Layout = new Layout();
?>
<html>
<head>
	<title>Panel de Minecraft - Steel Gamers</title>
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
<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
<div class="backToMainPageContainer">
	<a href="http://steelgamers.es"><img src="/images/back_logo.png"></a>
</div>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner_label.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/top.php"); ?>
    		<div class="latestNewsLabel"><a class="plainLink" href="controlpanel.php">Panel de Minecraft</a></div>
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
    			<h1>Rango de usuario</h1>
    			<div class="newContainer">
        			<div class="formItem formItemLabel">Rango:</div>
            		<div class="formItem formItemInput"><?php echo $RANK_NAMES[$userRank]; ?></div>
        		</div>
    		</div>
    		<div class="new">
    			<h1>Tus personajes - Red Steel Gamers</h1>
    			<div class="newContainer">
    				Aqu&iacute; puedes gestionar todos los personajes de los diferentes servidores de Minecraft que forman parte de la red Steel Gamers.
    				<h2>Servidor: Mitracraft</h2>
    				<?php
    				if (isset($_POST['newCharacterName']) || isset($_POST['retrieveCharacterName']))
    				{
        				$text = "";
        				if ($errors['newCharacter'] == ERROR_NONE && $errors['newCharacterPass'] == ERROR_NONE && $errors['newCharacterCheck'] == ERROR_NONE
        				    && $errors['retrieveCharacter'] == ERROR_NONE)
        				{
    				?>
    				<div class="minecraftError" style="padding:10px; margin-top:10px; margin-bottom:10px; border-radius:5px; background-color:#00FF00;">Operaci&oacute;n realizada con &eacute;xito.</div>
    				<?php
        				}
        				else
        				{
            				if ($errors['newCharacter'] == ERROR_INVALID)
            				    $text = "El nombre de usuario no es v&aacute;lido, debe tener m&aacute;s de 2 caracteres.";
            				elseif ($errors['newCharacterCheck'] == ERROR_INVALID)
            				    $text = "Las contrase&ntilde;as no coinciden.";
            				elseif ($errors['newCharacterPass'] == ERROR_INVALID)
            				    $text = "La contrase&ntilde;a debe tener al menos 6 caracteres.";
            				elseif ($errors['newCharacter'] == ERROR_LOGIN_USERNAME)
            				    $text = "Ese nombre de personaje ya est&aacute; en uso.";
            				elseif ($errors['newCharacter'] == ERROR_CRITICAL || $errors['retrieveCharacter'] == ERROR_CRITICAL)
            				    $text = "Se ha producido un error al conectarse al servidor de Minecraft. Es posible que el servidor no est&eacute; disponible temporalmente.";
            				elseif ($errors['retrieveCharacter'] == ERROR_INVALID)
            				    $text = "No se ha encontrado al personaje \"" . $_POST['retrieveCharacterName'] . "\" en la base de datos.";
            				elseif ($errors['retrieveCharacter'] == ERROR_LOGIN_PASSWORD)
            				    $text = "Contrase&ntilde;a incorrecta. Recuerda que tiene que ser la clave introducida al crear el personaje dentro del servidor.";
    				?>
    				<div class="minecraftOk" style="padding:10px; margin-top:10px; margin-bottom:10px; border-radius:5px; background-color:#FF0000;">Se ha producido un error: <?php echo $text; ?></div>
    				<?php
        				}
    				}
    				if (count($characters) === 0)
    				{
    				?>
    				<div>No tienes ning&uacute;n personaje en este servidor (o no tienes ning&uacute;n personaje asociado a la red Steel Gamers).</div>
    				<?php
    				}
    				else
    				{
        				foreach ($characters as $i => $character)
        				{
    				?>
					<div class="characterDataContainer">
    					<div class="formItem formItemLabel">Personaje:</div>
    					<div class="formItem formItemCharacterData"><?php echo $character['name']; ?> [ID: <?php echo $character['id']; ?>]</div>
    					<div class="formItem formItemLabel">Fecha de creaci&oacute;n:</div>
    					<div class="formItem formItemCharacterData"><?php echo date("d-m-Y H:i:s", strtotime($character['registered'])); ?></div>
    					<div class="formItem formItemLabel">&Uacute;ltima conexi&oacute;n:</div>
    					<div class="formItem formItemCharacterData"><?php echo ($character['lastLoginDate'] == NULL || $character['lastLoginIp'] == NULL) ? "No hay datos" : date("d-m-Y H:i:s", strtotime($character['lastLoginDate'])) . " desde " . $character['lastLoginIp']; ?></div>
					</div>
    				<?php
    				    }
    				}
    				?>
    				<h3>Crear nuevo personaje</h3>
    				<form class="formBottomBorder" action="controlpanel.php" method="post">
    					<div class="formItem formItemLabel">Nombre de personaje:</div>
    					<div class="formItem"><input type="text" name="newCharacterName"></div>
    					<div class="formItem formItemLabel">Contrase&ntilde;a (en el servidor):</div>
    					<div class="formItem"><input type="password" name="newCharacterPassword"></div>
    					<div class="formItem formItemLabel">Confirmar contrase&ntilde;a:</div>
    					<div class="formItem"><input type="password" name="newCharacterCheck"></div>
    					<div class="formItem"><input class="button" type="submit" value="Crear"></div>
    				</form>
    				<h3>Asociar personaje a la cuenta</h3>
    				<div class="formSubLabel">Si ya tienes personajes en el servidor anteriores a la red Steel Gamers, o has creado el personaje desde dentro del servidor, puedes asociar el personaje a tu cuenta para que gane los rangos adecuados desde aqu&iacute;. Debes introducir la contrase&ntilde;a que usas dentro del servidor para acceder al personaje, no la de tu cuenta de Steel Gamers. Si tienes cualquier problema, aseg&uacute;rate de revisar el <a href="http://steelgamers.es/faq.php">FAQ</a>.</div>
    				<form action="controlpanel.php" method="post">
    					<div class="formItem formItemLabel">Nombre de personaje:</div>
    					<div class="formItem"><input type="text" name="retrieveCharacterName"></div>
    					<div class="formItem formItemLabel">Contrase&ntilde;a (en el servidor):</div>
    					<div class="formItem"><input type="password" name="retrieveCharacterPassword"></div>
    					<div class="formItem"><input class="button" type="submit" value="Asociar"></div>
    				</form>
    			</div>
    		</div>
    		<?php 
    		if ($isAdmin)
    		{
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
    		<?php 
    		}
    		?>
    	</div>
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>