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
$_Layout = new Layout();
$db = new Database($DATABASES['USERS']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Steel Gamers - Miembros</title>
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/members.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.fancybox.pack.js"></script>
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
    		<div class="latestNewsLabel <?php if (isset($user)) { echo $user->IsPremium() ? " premiumLatestNewsLabel" : ""; } ?>">Lista de miembros</div>
    		<?php
    		if ($result = $db->Execute(Statements::SELECT_ALL_MEMBERS))
    		{
    		    $i = 1;
    		    while ($row = $result->fetch_assoc())
    		    {
    		        $memberRank = str_split($row['rank_mask']);
    		        $memberRank = $RANK_NAMES[$memberRank[0]];
    		?>
    			<div class="new">
        			<div class="newAvatarContainer"><img src="<?php echo GenerateGravatarUrl($row['email'], 60); ?>"></div>
        			<div class="newContainer">
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
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>