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
// Initialize Layout class, that stores design options.
$_Layout = new Layout();
// Other initializations
$db = new Database($DATABASES['USERS']);
$news = array();
if ($result = $db->Execute(Statements::SELECT_LATEST_NEWS . MAX_DISPLAYED_NEWS))
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
<!DOCTYPE html>
<html>
<head>
	<META NAME="ROBOTS" CONTENT="INDEX, FOLLOW">
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/metadata.php"); ?>
	<title>Steel Gamers - Multimedia</title>
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/multimedia.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link type="text/css" rel="stylesheet" href="css/jquery.fancybox.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.fancybox.pack.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.fancybox-media.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/common.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("a.multimediaLink").fancybox();
			$("img.multimediaThumbnail").click(function(event) {
				event.preventDefault();
				$(event.target.parentElement).trigger("click");
			});
			$("img.multimediaYoutubePlay").click(function(event) {
				event.preventDefault();
				$(event.target.parentElement).trigger("click");
			});
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
    		<div class="latestNewsLabel <?php if (isset($user)) { echo $user->IsPremium() ? " premiumLatestNewsLabel" : ""; } ?>">Multimedia</div>
    		<?php if ($loggedIn) { ?>
			<div class="new">
    			<div class="newContainer">
    				Puedes subir una imagen o screenshot desde tu <a href="controlpanel.php">Panel de control</a>.
    			</div>
    		</div>
    		<?php
    		}
    		if (isset($_GET['uploader']))
    		    $result = $db->ExecuteStmt(Statements::SELECT_USER_MULTIMEDIA, $db->BuildStmtArray("s", $_GET['uploader']));
    		else
    		    $result = $db->Execute(Statements::SELECT_MULTIMEDIA);
    		if ($result)
    		{
    		    if ($result->num_rows == 0)
    		        echo '<div style="float:left; margin-top:10px; text-align:center; width:100%;">No hay elementos multimedia por el momento.</div>';
    		    else
    		    {
    		        while ($row = $result->fetch_assoc())
    		        {
    		?>
    		<div class="multimediaItem">
    			<?php 
    			    if (parse_url($row['url'], PHP_URL_HOST) == "www.youtube.com")
    			    {
    			?>
    			<a class="multimediaLink plainLink fancybox.iframe" rel="multimedia" href="<?php echo $row['url']; ?>">
    				<img class="multimediaThumbnail" src="<?php echo $row['media_thumbnail']; ?>">
    				<img class="multimediaYoutubePlay" src="images/youtube.png">
    			</a>
    			<?php
    			    } else {
    			?>
    			<a class="multimediaLink plainLink fancybox.image" rel="multimedia" href="<?php echo $row['url']; ?>">
    				<img class="multimediaThumbnail" src="<?php echo $row['media_thumbnail']; ?>">
    			</a>
    			<?php
    			    }
    			?>
    			<div class="metaData">Por <b><?php echo $row['uploader']; ?></b> el <?php echo date("d-m-Y", strtotime($row['upload_date'])); ?></div>
    		</div>
    		<?php
    		        }
    		    }
    		?>
    		<?php 
    		}
    		else
    		    echo '<div style="float:left; margin-top:10px; text-align:center; width:100%;">No hay elementos multimedia por el momento.</div>';
    		?>
		</div>
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>