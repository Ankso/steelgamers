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
if (!isset($_SESSION['userId']))
{
    header("location:login.php");
    exit();
}
$loggedIn = true;
$errors = array(
    'password'            => ERROR_NONE,
    'password_check'      => ERROR_NONE,
    'email'               => ERROR_NONE,
    'server'              => ERROR_NONE,
    'syncAccountPassword' => ERROR_NONE,
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
    // Create an account in the specified WoW server
    if (isset($_POST['syncAccountPassword']) && isset($_POST['syncAccountConfirm']))
    {
        if ($_POST['syncAccountPassword'] == $_POST['syncAccountConfirm'])
        {
            $wowAccountsDb = new Database($DATABASES['TBCSERVER_ACCOUNTS'], $TBCSERVER_INFO);
            $gmlevel = 0;
            if ($user->GetRanks(GAME_OVERALL) == USER_RANK_MODERATOR || $user->GetRanks(GAME_WORLD_OF_WARCRAFT_TBC) > USER_RANK_MODERATOR)
                $gmlevel = 1;
            elseif ($user->GetRanks(GAME_OVERALL) == USER_RANK_COMMUNITY_MANAGER || $user->GetRanks(GAME_WORLD_OF_WARCRAFT_TBC) > USER_RANK_COMMUNITY_MANAGER)
                $gmlevel = 2;
            elseif ($user->GetRanks(GAME_OVERALL) > USER_RANK_COMMUNITY_MANAGER || $user->GetRanks(GAME_WORLD_OF_WARCRAFT_TBC) > USER_RANK_COMMUNITY_MANAGER)
                $gmlevel = 3;
            if (!$wowAccountsDb->ExecuteStmt(Statements::INSERT_USER_WOW_ACCOUNT, $wowAccountsDb->BuildStmtArray("ssissssssiisiiii", strtoupper($user->GetUsername()), CreateWoWServerSha1Pass($user->GetUsername(), $_POST['syncAccountPassword']), $gmlevel, "", "0", "0", $user->GetEmail(), time(), $user->GetLastIp(), 0, 0, $user->GetLastLogin(), 0, 1, 0, 0)))
                $errors['server'] = ERROR_CRITICAL;
        }
        else
            $errors['syncAccountPassword'] = ERROR_INVALID;
    }
}
$userRank = $user->GetRanks(GAME_OVERALL);
$isAdmin = ($userRank > USER_RANK_MODERATOR);
// If the user is not admin yet, check if he has the page specific rank
if (!$isAdmin)
{
    $userRank = $user->GetRanks(GAME_WORLD_OF_WARCRAFT_TBC);
    $isAdmin = ($userRank > USER_RANK_MODERATOR);
}
// Overwrite displayed user rank with the specific game rank, but save admin privileges if the user is admin globaly.
$userRank = $user->GetRanks(GAME_WORLD_OF_WARCRAFT_TBC);
// This operations are here because this is specific for each of the wow servers
// Use the previous connection if it's available
if (!isset($wowAccountsDb))
    $wowAccountsDb = new Database($DATABASES['TBCSERVER_ACCOUNTS'], $TBCSERVER_INFO);
$wowCharactersDb = new Database($DATABASES['TBCSERVER_CHARS'], $TBCSERVER_INFO);
$notSync = true;
if ($result = $wowAccountsDb->ExecuteStmt(Statements::SELECT_USER_WOW_ACCOUNT, $wowAccountsDb->BuildStmtArray("s", $user->GetUsername())))
{
    if ($row = $result->fetch_assoc())
    {
        $notSync = false;
        $characters = array();
        if ($result = $wowCharactersDb->ExecuteStmt(Statements::SELECT_USER_WOW_CHARACTERS, $wowCharactersDb->BuildStmtArray("i", $row['id'])))
        {
            while ($row = $result->fetch_assoc())
            {
                $characters[] = array(
                    "name"                 => $row['name'],
                    "race"                 => $row['race'],
                    "class"                => $row['class'],
                    "gender"               => $row['gender'],
                    "level"                => $row['level'],
                    "money"                => $row['money'],
                    "online"               => $row['online'],
                    "totalTime"            => $row['totaltime'],
                    "arenaPoints"          => $row['arenaPoints'],
                    "totalHonorPoints"     => $row['totalHonorPoints'],
                    "todayHonorPoints"     => $row['todayHonorPoints'],
                    "yesterdayHonorPoints" => $row['yesterdayHonorPoints'],
                    "totalKills"           => $row['totalKills'],
                    "todayKills"           => $row['todayKills'],
                    "yesterdayKills"       => $row['yesterdayKills'],
                );
            }
        }
    }
}
$_Layout = new Layout();
$isControlPanel = true;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Panel de WoW: TBC - Steel Gamers</title>
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
    		<div class="latestNewsLabel <?php echo $user->IsPremium() ? " premiumLatestNewsLabel" : ""; ?>"><a class="plainLink" href="controlpanel.php">Panel de World of Warcraft</a></div>
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
    				Aqu&iacute; puedes ver todos los personajes de los diferentes servidores privados de World of Warcraft que forman parte de la red Steel Gamers.
    				<h2>Servidor: The Burning Crusade (2.4.3)</h2>
    				<?php
    				if (!isset($characters))
    				{
    				?>
    				<div>No tienes una cuenta en este servidor. Sincroniza tu cuenta con la de la red Steel Gamers usando el formulario a continuaci&oacute;n.</div>
    				<?php
    				}
    				elseif (count($characters) === 0)
    				{
    				?>
    				<div>No tienes ning&uacute;n personaje en este servidor.</div>
    				<?php
    				}
    				else
    				{
        				foreach ($characters as $i => $character)
        				{
    				?>
					<div class="characterDataContainer">
    					<div class="formItem formItemLabel">Personaje:</div>
    					<div class="formItem formItemCharacterData"><?php echo $character['name']; ?></div>
    					<div class="formItem formItemLabel">Nivel:</div>
    					<div class="formItem formItemCharacterData"><?php echo $character['level']; ?></div>
    					<div class="formItem formItemLabel">Cobres:</div>
    					<div class="formItem formItemCharacterData"><?php echo $character['money']; ?></div>
    					<div class="formItem formItemLabel">Online:</div>
    					<div class="formItem formItemCharacterData"><?php echo ($character['online'] == 1 ? "Si" : "No"); ?></div>
    				</div>
    				<?php
    				    }
    				}
    				if ($notSync)
    				{
    				?>
    				<h3>Sincronizar cuenta</h3>
    				<div class="formSubLabel">Si ya ten&iacute;as una cuenta en la red Steel Gamers antes de la existencia en este servidor, introduce la contrase&ntilde;a que desees para tu cuenta en este servidor de World of Warcraft y haz click en sincronizar. Esta operaci&oacute;n s&oacute;lo se realiza una vez. Si tienes dudas, recuerda visitar el <a href="http://steelgamers.es/faq.php">FAQ</a>.</div>
    				<div class="formWrapper">
        				<form action="controlpanel.php" method="post">
        					<div class="formItem formItemLabel">Nueva contrase&ntilde;a:</div>
        					<div class="formItem"><input type="password" name="syncAccountPassword"></div>
        					<div class="formItem formItemLabel">Confirmar nueva contrase&ntilde;a:</div>
        					<div class="formItem"><input type="password" name="syncAccountConfirm"></div>
        					<div class="formItem"><input class="button" type="submit" value="Sincronizar"></div>
        				</form>
    				</div>
    				<?php
        				if ($_SERVER['REQUEST_METHOD'] == "POST" && ($errors['syncAccountPassword'] == ERROR_INVALID || $errors['server'] == ERROR_CRITICAL))
        				{
    				?>
    				<div class="formErrorsWrapper">
						Se han encontrado los siguientes errores:<br>
						<ul>
							<li <?php echo ($errors['syncAccountPassword'] == ERROR_INVALID ? "" : 'style="display:none"'); ?>>Las contrase&ntilde;as no coinciden.
							<li <?php echo ($errors['server'] != ERROR_NONE ? "" : 'style="display:none"'); ?>>Se ha producido un error en la base de datos. Es posible que uno de los servidores est&eacute; saturado o ca&iacute;do. Por favor, int&eacute;ntalo de nuevo m&aacute;s tarde.
						</ul>
					</div>
					<?php 
    				    }
    				}
					?>
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