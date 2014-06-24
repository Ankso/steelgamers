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
    // User wants to change the password
    if (isset($_POST['password']) && isset($_POST['password_check']) && $_POST['password'] != "" && $_POST['password_check'] != "")
    {
        $noChange = true;
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
        $noChange = true;
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
    // User wants to update his or her premium bonifications
    if (isset($_POST['premiumGoldBonus']) && isset($_POST['premiumReputationBonus']) && isset($_POST['premiumHonorBonus']) && isset($_POST['premiumArenaBonus']) && $user->IsPremium())
    {
        $wowAccountsDb = new Database($DATABASES['TBCSERVER_ACCOUNTS'], $TBCSERVER_INFO);
        $wowAccountsDb->ExecuteStmt(Statements::UPDATE_USER_WOW_PREMIUM_BONUS,
                                    $wowAccountsDb->BuildStmtArray("iiiii",
                                                                   $_POST['premiumGoldBonus'] == "enabled" ? 1 : 0,
                                                                   $_POST['premiumReputationBonus'] == "enabled" ? 1 : 0,
                                                                   $_POST['premiumHonorBonus'] == "enabled" ? 1 : 0,
                                                                   $_POST['premiumArenaBonus'] == "enabled" ? 1 : 0,
                                                                   $user->GetWowAccountId()));
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
$premiumData = NULL;
if ($result = $wowAccountsDb->ExecuteStmt(Statements::SELECT_USER_WOW_ACCOUNT, $wowAccountsDb->BuildStmtArray("s", $user->GetUsername())))
{
    if ($row = $result->fetch_assoc())
    {
        $notSync = false;
        $accountId = $row['id'];
        $characters = array();
        // Fetch characters
        if ($result = $wowCharactersDb->ExecuteStmt(Statements::SELECT_USER_WOW_CHARACTERS, $wowCharactersDb->BuildStmtArray("i", $accountId)))
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
        // Fetch premium data
        if ($user->IsPremium())
        {
            if ($result = $wowAccountsDb->ExecuteStmt(Statements::SELECT_USER_WOW_PREMIUM_ACTIVE, $wowAccountsDb->BuildStmtArray("i", $accountId)))
            {
                if ($row = $result->fetch_assoc())
                {
                    $premiumData = array(
                        'goldBonus'       => $row['gold_bonus'],
                        'reputationBonus' => $row['reputation_bonus'],
                        'honorBonus'      => $row['honor_bonus'],
                        'arenaBonus'      => $row['arena_bonus'],
                    );
                }
            }
        }
    }
}
$_Layout = new Layout(true, true, true, false, false, true, false);
$isControlPanel = true;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Panel de WoW: TBC - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/controlpanel.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/common.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
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
    				else
    				{
    				?>
    				<div>Estos cambios se aplican a toda la web de Steel Gamers, pero no al servidor de World of Warcraft.</div>
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
    					<div class="formItem formItemLabel">Dinero:</div>
    					<div class="formItem formItemCharacterData"><?php echo intval(($character['money'] - ((($character['money'] % 10000) - ($character['money'] % 100)) / 100)) / 10000), " oros ", intval((($character['money'] % 10000) - ($character['money'] % 100)) / 100), " platas ", intval($character['money'] % 100), " cobres."; ?></div>
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
    				elseif ($user->IsPremium())
    				{
					?>
					<h3>Bonificaciones Premium</h3>
    				<div class="formSubLabel">Aqu&iacute; puedes activar o desactivar las bonificaciones premium, por si eres un aut&eacute;ntico jugador hardcore y no necesitas ventajas :)</div>
    				<div class="formWrapper">
        				<form action="controlpanel.php" method="post">
        					<div class="formItem formItemLabel">Bonus de oro:</div>
        					<div class="formItem">
        						<select name="premiumGoldBonus">
        							<option value="enabled" <?php echo $premiumData['goldBonus'] ? "selected" : ""; ?>>Activado</option>
        							<option value="disabled" <?php echo !$premiumData['goldBonus'] ? "selected" : ""; ?>>Desactivado</option>
        						</select>
        					</div>
        					<div class="formItem formItemLabel">Bonus de reputaci&oacute;n:</div>
        					<div class="formItem">
        						<select name="premiumReputationBonus">
        							<option value="enabled" <?php echo $premiumData['reputationBonus'] ? "selected" : ""; ?>>Activado</option>
        							<option value="disabled" <?php echo !$premiumData['reputationBonus'] ? "selected" : ""; ?>>Desactivado</option>
        						</select>
        					</div>
        					<div class="formItem formItemLabel">Bonus de honor:</div>
        					<div class="formItem">
        						<select name="premiumHonorBonus">
        							<option value="enabled" <?php echo $premiumData['honorBonus'] ? "selected" : ""; ?>>Activado</option>
        							<option value="disabled" <?php echo !$premiumData['honorBonus'] ? "selected" : ""; ?>>Desactivado</option>
        						</select>
        					</div>
        					<div class="formItem formItemLabel">Bonus de puntos de arena:</div>
        					<div class="formItem">
        						<select name="premiumArenaBonus">
        							<option value="enabled" <?php echo $premiumData['arenaBonus'] ? "selected" : ""; ?>>Activado</option>
        							<option value="disabled" <?php echo !$premiumData['arenaBonus'] ? "selected" : ""; ?>>Desactivado</option>
        						</select>
        					</div>
        					<div class="formItem"><input class="button" type="submit" value="Modificar"></div>
        				</form>
    				</div>
    				<div class="formErrorsWrapper">NOTA: Si est&aacute;s online, debes reconectarte al servidor para que estos cambios surtan efecto.</div>
					<?php
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