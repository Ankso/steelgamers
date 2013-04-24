<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
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
$news = array();
if ($result = $db->Execute(Statements::SELECT_ARMA2_NEWS . MAX_DISPLAYED_NEWS))
{
    while ($row = $result->fetch_assoc())
    {
        $news[] = array(
            'id'        => $row['id'],
            'writer'    => $row['writer_name'],
            'title'     => $row['title'],
            'body'      => $row['body'],
            'timestamp' => $row['timestamp'],
        );
    }
}
?>
<html>
<head>
	<META NAME="ROBOTS" CONTENT="INDEX, FOLLOW">
	<META NAME="DESCRIPTION" CONTENT="Zona dedicada a la saga ArmA dentro de la infraestructura de la comunidad Steel Gamers.">
	<title>ArmA 2 - Steel Gamers</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
<div class="backToMainPageContainer">
	<a href="http://steelgamers.es"><img src="/images/back_logo.png"></a>
</div>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="/index.php"><img class="bannerLabelImg" src="images/banner_label.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/top.php"); ?>
    		<div class="latestNewsLabel">&Uacute;ltimas noticias</div>
    		<?php 
    		foreach ($news as $i => $new)
    		{
    		?>
    		<div class="new">
    			<h1><?php echo $new['title']; ?></h1>
    			<div class="newContainer">
    				<?php echo $new['body']; ?>
    				<div class="newDetails" data-newId="<?php echo $new['id']; ?>">Por <b><?php echo $new['writer']; ?></b> <span class="timestamp" data-timestamp="<?php echo strtotime($new['timestamp']); ?>">desconocido</span></div>
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