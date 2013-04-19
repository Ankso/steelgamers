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
$db = new Database($DATABASES['USERS']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Steel Gamers - Miembros</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<!-- <link type="text/css" rel="stylesheet" href="css/members.css"> -->
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php PrintTopBar(); ?>
    		<div class="latestNewsLabel">Lista de miembros</div>
			
    		<?php
    		if ($result = $db->Execute(Statements::SELECT_ALL_MEMBERS))
    		{
    		    $i = 1;
    		    while ($row = $result->fetch_assoc())
    		    {
    		        $memberRank = str_split($row['rank_mask']);
    		        $memberRank = $RANK_NAMES[$memberRank[0]];
    		?>
    			<div class="new" style="padding:10px;">
        			<div style="float:left;"><img src="<?php echo GenerateGravatarUrl($row['email'], 60); ?>"></div>
        			<div class="newContainer" style="float:left; width:500px;">
        				<b><?php echo "#", $i, ' <a class="plainLink" href="foro/index.php?p=/profile/', $row['username'], '">', $row['username'], '</a>'; ?></b>
        				<div>Rango General: <?php echo $memberRank; ?></div>
        				<div>Visto por &uacute;ltima vez: <?php echo date("d-m-Y H:i:s", strtotime($row['last_login'])); ?></div>
        				<div>Miembro desde: <?php echo ($row['register_date'] == NULL ? "El origen de los tiempos" : date("d-m-Y H:i:s", strtotime($row['register_date']))); ?></div>
        			</div>
    			</div>
    		<?php
    		        ++$i;
    		    }
    		}
    		?>
		</div>
    	<div class="rightContainer">
    		<div class="rightItem">
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
        				<a class="plainLink" href="controlpanel.php"><div class="button">Panel de control</div></a>
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
    		<?php PrintTs3Status(); ?>
    		<?php PrintWoWTbcServerStatus(); ?>
    	</div>
	</div>
	<?php PrintBottomBar(); ?>
	<div style="height:10px;">&nbsp;</div>
</div>
</body>
</html>