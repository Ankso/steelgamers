<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Functions.jsConnect.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/SessionHandler.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/User.Class.php");
?>
<!DOCTYPE html>
<html>
<head>
	<META NAME="ROBOTS" CONTENT="INDEX, FOLLOW">
	<META NAME="DESCRIPTION" CONTENT=".">
	<title>Steel Gamers Alliance</title>
	<link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo STEEL_GAMERS_VERSION; ?>">
	<link href='http://fonts.googleapis.com/css?family=Bree+Serif' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Droid+Serif' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery-ui-1.9.0.custom.min.js"></script>
	<script type="text/javascript" src="http://cdn.steelgamers.es/js/jquery.fancybox.pack.js"></script>
	<!-- <script type="text/javascript" src="http://cdn.steelgamers.es/js/common.js?v=<?php echo STEEL_GAMERS_VERSION; ?>"></script> -->
	<script type="text/javascript">
		$(document).ready(function() {
			$('div#learnMoreLink').click(function(event) {
			    $('html,body').animate({
		          scrollTop: $('div#homePageLink').offset().top
		        }, 1000);
			});
			$('div#homePageLink').click(function(event) {
			    $('html,body').animate({
		          scrollTop: 0
		        }, 1000);
			});
			// Change font size if the screen has a small resolution (720p for example)
			if ($('body').height() < 900)
			{
				$('body').css('font-size', '14px');
				$('div.bottomContent').css('font-size', '20px');
				$('div.topContent').css('font-size', '20px');
			}
			$('div#mainLogo').fadeIn(2000);
			setTimeout(function() {
			    $('div#mainLogoText').fadeIn(1000);
			}, 1000);
			setTimeout(function() {
			    $('div#learnMoreLink').fadeIn(1000);
			}, 2000);
			
		});
	</script>
</head>
<body>
<div id="homePage" class="centralDiv" style="display:inherit;">
	<div class="topContent"></div>
	<div class="mediumContent">
		<div id="mainLogo" style="height:60%; display:none; padding-top:40px;"><img class="mainLogo" src="images/steelgamers_logo_white.png"></div>
		<div id="mainLogoText" style="display:none;">
			<h1>Steel Gamers Alliance</h1>
			<h2><i>The power of organization</i></h2>
			<h3>EU Melisara server</h3>
		</div>
	</div>
	<div class="bottomContent">
		<div id="learnMoreLink" class="bottomRoute" style="display:none;"><b>Learn More</b></div>
	</div>
</div>
<div id="learnMoreContent" class="centralDiv" style="background: url('images/steelgamers_logo_background.png') no-repeat center center;">
	<div id="homePageLink" class="topContent">
		<div class="topRoute"><b>Main page</b></div>
	</div>
	<div style="height:10%; float:left; width:100%;"></div>
	<div class="mediumContent" style="height:80%;">
		<h2>What is it?</h2>
		<div style="width:60%; margin-left:20%; margin-bottom:75px;">
			Steel Gamers Alliance (SGA) has born as an international project to join small to medium sized guilds under one banner and gain power against the well known <i>&quot;Zerg guilds&quot;</i>, based on large numbers of players, on the Archeage server <b>Melisara</b>.
		</div>
		<h2>How can we face the Zerg guilds or even Zerg alliances?</h2>
		<div style="width:60%; margin-left:20%; margin-bottom:75px;">
			Using organization. The advantage of small and medium sized guilds is the high level of internal organization when doing PvP. The objective of SGA is to coordinate those small groups and make them a larger, more lethal force. Five galleons, each one with it's well trained crew, syncronized, can defeat ten manned by the members of a much more chaotic zerg guild.
		</div>
		<h2>Is there any requirements to join?</h2>
		<div style="width:60%; margin-left:20%; margin-bottom:75px;">
			Yes. Your guild must be based on the usage of TeamSpeak 3 (or any other voice communication software), at least for PvP. High activity of the guild members is also recommended. You must read and agree with the <a href="">Alliance rules</a>, and your diplomat must be able to communicate in english.
		</div>
	</div>
	<div class="bottomContent">
		<div class="bottomRoute"><b>Apply to join!</b></div>
	</div>
</div>
</body>
</html>