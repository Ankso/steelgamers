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
    'password'       => ERROR_NONE,
    'password_check' => ERROR_NONE,
    'email'          => ERROR_NONE,
    'server'         => ERROR_NONE,
    'multimedia'     => ERROR_NONE,
);
$user = new User($_SESSION['userId']);
if ($user->IsBanned())
{
    header("Location:http://steelgamers.es/logout.php?redirect=banned");
    exit();
}
$_Layout = new Layout();
$isControlPanel = true;
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
        if ($ts3Rank == $RANK_NAMES[USER_RANK_SUPERADMIN])
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
    // User wants to upload an image or video to the media section
    if (isset($_POST['multimedia']))
    {
        if (IsUrl($_POST['multimedia']))
        {
            if (IsImage($_POST['multimedia']))
                $user->UploadMultimedia($_POST['multimedia'], $_POST['multimedia']);
            else
            {
                // Check if it is a youtube link
                $url_host = parse_url($_POST['multimedia'], PHP_URL_HOST);
                if ($url_host == "www.youtube.com" || $url_host == "youtube.com")
                {
                    parse_str(parse_url($_POST['multimedia'], PHP_URL_QUERY), $url_parts);
                    $user->UploadMultimedia("http://www.youtube.com/embed/" . $url_parts['v'], "http://img.youtube.com/vi/" . $url_parts['v'] . "/default.jpg");
                }
                else
                    $errors['multimedia'] = ERROR_INVALID;
            }
        }
        else
            $errors['multimedia'] = ERROR_INVALID;
    }
}
// Admins only code
if ($isAdmin)
{
    // Search for an user to edit in the database
    if (isset($_POST['search_username']) || isset($_GET['lastEditedUser']))
    {
        if (isset($_POST['search_username']) || isset($_GET['lastEditedUser']))
        {
            $searchUsername = USER_DOESNT_EXISTS;
            if (isset($_POST['search_username']))
                $searchUsername = $_POST['search_username'];
            elseif (isset($_GET['lastEditedUser']))
                $searchUsername = GetUsernameFromId($_GET['lastEditedUser']);
            if ($searchUsername != USER_DOESNT_EXISTS)
            {
                $db = new Database($DATABASES['USERS']);
                if ($result = $db->ExecuteStmt(Statements::SELECT_USERS_DATA_ADMIN, $db->BuildStmtArray("s", $searchUsername)))
                {
                    if ($row = $result->fetch_assoc())
                    {
                        $userData = array(
                            'id'        => $row['id'],
                            'username'  => $row['username'],
                            'email'     => $row['email'],
                            'lastIp'    => $row['ip_v4'],
                            'lastLogin' => $row['last_login'],
                            'active'    => $row['active'],
                            'rank'      => str_split($row['rank_mask']),
                            'banStart'  => NULL,
                            'banEnd'    => NULL,
                            'banReason' => NULL,
                            'bannedBy'  => NULL,
                        );
                        
                        if ($userData['rank'][GAME_OVERALL] > $userRank && $userRank < USER_RANK_SUPERADMIN)
                            $userData = ERROR_NOT_ALLOWED;
                        
                        if ($result = $db->ExecuteStmt(Statements::SELECT_USERS_BANNED, $db->BuildStmtArray("i", $userData['id'])))
                        {
                            if ($row = $result->fetch_assoc())
                            {
                                $userData['banStart'] = $row['ban_start'];
                                $userData['banEnd'] = $row['ban_end'];
                                $userData['banReason'] = $row['ban_reason'];
                                $userData['bannedBy'] = $row['banned_by'];
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<META NAME="ROBOTS" CONTENT="INDEX, FOLLOW">
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/metadata.php"); ?>
	<title>Panel de control - Steel Gamers</title>
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
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/top.php"); ?>
    		<div class="latestNewsLabel <?php echo $user->IsPremium() ? " premiumLatestNewsLabel" : ""; ?>"><a class="plainLink" href="controlpanel.php">Panel de control</a></div>
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
						<?php if (isset($noChange) && $noChange && !isset($_POST['search_user'])) { ?>
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
    			<h1>Multimedia</h1>
    			<div class="newContainer">
        			Desde aqu&iacute; puedes enviarnos im&aacute;genes o v&iacute;deos para que sean mostrados en el apartado multimedia de la p&aacute;gina web. Recuerda que las im&aacute;genes deben respetar nuestros <a href="http://steelgamers.es/docs/terminos_y_condiciones.html">T&eacute;rminos y Condiciones de Uso</a>. Tambi&eacute;n puedes ver tus subidas desde <a href="multimedia.php?uploader=<?php echo $user->GetUsername(); ?>">aqu&iacute;</a>.
    				<form style="margin-top:20px;" action="controlpanel.php" method="post">
    					<input type="hidden" name="from" value="controlpanel">
            			<div class="formItem formItemLabel">URL de la imagen o v&iacute;deo:</div>
                		<div class="formItem"><input type="text" name="multimedia" style="width:300px;"></div>
                		<div class="formItem"><input class="button" type="submit" value="Enviar"></div>
            		</form>
            		<?php if (isset($_POST['multimedia']) && $errors['multimedia'] != ERROR_NONE) { ?>
            		<span style="color:#FF0000;">Se ha producido un eror. Posiblemente la url sea inv&aacute;lida. Debe ser la direcci&oacute;n directa a la imagen, o un link a youtube.</span>
            		<?php } elseif (isset($_POST['multimedia'])) { ?>
            		<span style="color:#00FF00;">&iexcl;Operaci&oacute;n realizada con &eacute;xito!</span>
            		<?php } ?>
    			</div>
    		</div>
    		<div class="new">
    			<h1>TeamSpeak 3</h1>
    			<div class="newContainer">
    			    <?php
            		if ($ts3Token)
            		{
            		?>
        			<div class="formItem">Desde aqu&iacute; puedes conectarte por primera vez a nuestro servidor de TeamSpeak 3, o si tu rango ha cambiado, puedes obtener los nuevos permisos haciendo click en el bot&oacute;n. <b>&iexcl;Recuerda a&ntilde;adir el servidor a tus bookmarks!</b></div>
            		<div class="formItem" style="margin-top:15px;"><a class="button" href="ts3server://steelgamers.es?nickname=<?php echo $user->GetUsername(); ?>&addbookmark=Steel%20Gamers%20Community&token=<?php echo $ts3Token; ?>">Sincronizar</a></div>
            		<?php
            		}
            		else
            		{
        			?>
        			<div class="formItem">Error: no se ha podido crear un token de usuario. Es posible que el servidor de TeamSpeak 3 no est&eacute; disponible temporalmente. Por favor, int&eacute;ntalo de nuevo m&aacute;s tarde.</div>
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
    			<h1>Gesti&oacute;n de usuarios</h1>
    			<div class="newContainer">
    				<form class="formBottomBorder" action="controlpanel.php" method="post">
    					<input type="hidden" name="from" value="controlpanel">
            			<div class="formItem formItemLabel">Nombre de usuario:</div>
                		<div class="formItem"><input type="text" name="search_username"></div>
                		<div class="formItem"><input class="button" type="submit" value="Buscar"></div>
            		</form>
            		<?php
            		if (isset($userData))
            		{
            		    if ($userData == ERROR_NOT_ALLOWED)
            		    {
            		?>
            		<div>No tienes permisos suficientes para ver los datos de este usuario.</div>
            		<?php 
            		    }
            		    else
            		    {
            		?>
            		<form action="admin.php" method="post">
            			<input type="hidden" name="from" value="controlpanel">
            			<input type="hidden" name="editUserId" value="<?php echo $userData['id']; ?>">
            			<div class="formItem formItemLabel">ID de usuario:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo $userData['id']; ?>"></div>
            			<div class="formItem formItemLabel">Nombre de usuario:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo $userData['username']; ?>"></div>
        				<div class="formItem formItemLabel">Email:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo $userData['email']; ?>"></div>
        				<div class="formItem formItemLabel">&Uacute;ltima IP:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo $userData['lastIp']; ?>"></div>
        				<div class="formItem formItemLabel">&Uacute;ltima conexi&oacute;n:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo $userData['lastLogin']; ?>"></div>
        				<div class="formItem formItemLabel">Estado de la cuenta:</div>
        				<?php 
        				$accountStatus = "Activada";
        				if ($userData['banStart'] && $userData['banEnd'] && strtotime($userData['banEnd']) >= time())
        				    $accountStatus = "Baneada";
        				elseif ($userData['active'] == 0)
        				    $accountStatus = "No activada";
        				?>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo $accountStatus; ?>"></div>
        				<?php 
        				if ($accountStatus == "Baneada")
        				{
        				?>
        				<input type="hidden" name="action" value="unbanUser">
        				<div class="formItem formItemLabel">Desde:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo date("d-m-Y H:i:s", strtotime($userData['banStart'])); ?>"></div>
        				<div class="formItem formItemLabel">Hasta:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo date("d-m-Y H:i:s", strtotime($userData['banEnd'])); ?>"></div>
        				<div class="formItem formItemLabel">Baneada por:</div>
        				<div class="formItem"><input class="formItemDisabled" type="text" disabled value="<?php echo GetUsernameFromId($userData['bannedBy']); ?>"></div>
        				<div class="formItem"><input class="button unbanButton" type="submit" value="Desbanear"></div>
        				<?php
        				}
        				else
        				{
        			    ?>
        			    <input type="hidden" name="action" value="banUser">
        			    <div class="formItem formItemLabel">Banear...</div>
        			    <div class="formItem"><input class="formItemBanInput" type="text" name="banExpiresMinutes" value="0"> minutos</div>
        			    <div class="formItem formItemLabel">&nbsp;</div>
        			    <div class="formItem"><input class="formItemBanInput" type="text" name="banExpiresHours" value="0"> horas</div>
        			    <div class="formItem formItemLabel">&nbsp;</div>
        			    <div class="formItem"><input class="formItemBanInput" type="text" name="banExpiresDays" value="0"> d&iacute;as</div>
        			    <div class="formItem formItemLabel">&nbsp;</div>
        			    <div class="formItem"><input class="formItemBanInput" type="text" name="banExpiresMonths" value="0"> meses</div>
        			    <div class="formItem formItemLabel">&nbsp;</div>
        			    <div class="formItem"><input class="formItemBanInput" type="text" name="banExpiresYears" value="0"> a&ntilde;os</div>
        			    <div class="formItem formItemLabel">Raz&oacute;n:</div>
        			    <div class="formItem"><input type="text" name="banReason" value=""></div>
        			    <div class="formItem"><input class="button banButton" type="submit" value="Banear"></div>
        			    <?php
        				}
        				?>
        			</form>
        			<form action="admin.php" method="post">
            			<input type="hidden" name="from" value="controlpanel">
            			<input type="hidden" name="editUserId" value="<?php echo $userData['id']; ?>">
            			<input type="hidden" name="action" value="permissions">
            			<?php 
        				foreach ($GAME_NAMES as $i => $gameName)
        				{
        				?>
        				<div class="formItem formItemLabel">Permisos <?php echo $gameName; ?>:</div>
        				<div class="formItem">
        					<select name="<?php echo $i; ?>">
        					<?php 
        					foreach ($RANK_NAMES as $j => $rankName)
        					{
        					?>
        						<option value="<?php echo $j; ?>" <?php echo ($userData['rank'][$i] == $j) ? "selected" : ""; ?>><?php echo $rankName; ?></option>
        					<?php
        					}
        					?>
        					</select>
        				</div>
        				<?php
        				}
        				?>
        				<div class="formItem"><input class="button" type="submit" value="Actualizar permisos"></div>
            		</form>
            		<?php
            		    }
            		}
            		elseif (isset($_POST['search_username']) || isset($_GET['lastEditedUser']))
            		{
            		?>
            		<div>No se ha encontrado ning&uacute;n usuario en la base de datos llamado &quot;<?php echo isset($_POST['search_username']) ? $_POST['search_username'] : GetUsernameFromId($_GET['lastEditedUser']); ?>&quot;</div>
            		<?php
            		}
            		?>
    			</div>
    		</div>
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
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>