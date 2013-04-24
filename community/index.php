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
// Initialize Layout class, that stores design options.
$_Layout = new Layout();
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
<!--                                                 
          SSSSSSS                                                         
        SSSSSSSSSS                                                        
       SSSS     SSS                                                       
       SSS       SS                                                       
      SSSS                                                                
      SSSS           TTTTTTTTTT  EEEEEEEEE  EEEEEEEEE  LL                 
       SSSSSS        TTTTTTTTTT  EEEEEEEEE  EEEEEEEEE  LL                 
        SSSSSSSSS        TT      EE         EE         LL                 
             SSSSS       TT      EE         EE         LL                 
                 SS      TT      EEEEEE     EEEEEE     LL                 
     SSS         SS      TT      EEEEEE     EEEEEE     LL                 
     SSS        SS       TT      EE         EE         LL                 
      SS       SSS       TT      EE         EE         LL                 
      SSSSSSSSSSS        TT      EEEEEEEEE  EEEEEEEEE  LLLLLLLLLL         
       SSSSSSSS          TT      EEEEEEEEE  EEEEEEEEE  LLLLLLLLLL         
                                                                          
                                                                          
                        GGGGGG  AAAAAAAA  MM     MM EEEEEEE RRRRR   SSSS  
                      GGG       AA    AA  MMM   MMM EE      RR  RR SSSSSS 
                     GG         AA    AA  M MM MM M E       R    R S    S 
                     G          AA    AA  M  MMM  M E       R   RR SS     
                     G          AAAAAAAA  M       M EEEEEE  R RR    SSS   
                     G    GGG   AA    AA  M       M E       RRR       SS  
                     G      GG  AA    AA  M       M E       R RRR      SS 
                     GGG    GG  AA    AA  M       M EE      R   RRRS   SS 
                      GGGGGGG   AA    AA  M       M EEEEEEE R     RSSSSS  
-->
<!DOCTYPE html>
<html>
<head>
	<META NAME="ROBOTS" CONTENT="INDEX, FOLLOW">
	<META NAME="DESCRIPTION" CONTENT="Steel Gamers es una comunidad de jugones que posee una gran infraestructura, desde servidores privados de juegos como World of Warcraft, Minecraft o ArmA hasta foros o TeamSpeak 3. Diseñada por Gamers, para Gamers.">
	<title>Steel Gamers Community</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
	<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<!-- Third party scripts -->
<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/header.php"); ?>
<div class="wrapper">
	<div class="bannerContainer">
		<a href="index.php"><img class="bannerLabelImg" src="images/banner.png"></a>
	</div>
	<div class="contentWrapper">
    	<div class="mainContainer">
    		<!-- Top bar -->
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
    	<!-- Right elements -->
    	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/right.php"); ?>
	</div>
	<!-- Bottom bar -->
	<?php include ($_SERVER['DOCUMENT_ROOT'] . "/../design/footer.php"); ?>
</div>
</body>
</html>