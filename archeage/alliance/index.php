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
			$('div#applyToJoinLink').click(function(event) {
			    $('html,body').animate({
		          scrollTop: $('div#learnMoreLinkBottom').offset().top
		        }, 1000);
			});
			$('div#learnMoreLinkBottom').click(function(event) {
			    $('html,body').animate({
			      scrollTop: $('div#homePageLink').offset().top
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
		<h2>Are there any requirements to join?</h2>
		<div style="width:60%; margin-left:20%; margin-bottom:75px;">
			Yes. Your guild must be based on the usage of TeamSpeak 3 (or any other voice communication software), at least for PvP. High activity of the guild members is also recommended. You must read and agree with the <a href="">Alliance rules</a>, and your diplomat must be able to communicate in english.
		</div>
	</div>
	<div class="bottomContent">
		<div id="applyToJoinLink" class="bottomRoute"><b>Apply to join!</b></div>
	</div>
</div>
<div id="applyToJoinContent" class="centralDiv" style="background: url('images/steelgamers_logo_background.png') no-repeat center center;">
	<div id="learnMoreLinkBottom" class="topContent">
		<div class="topRoute"><b>Learn More</b></div>
	</div>
	<div style="height:10%; float:left; width:100%;"></div>
	<div class="mediumContent" style="height:90%;">
		<div class="formContainerLeft">
			<div class="applicationItem">Guild name:</div>
			<div class="applicationItem">Diplomatic in-game contact(s):</div>
			<div class="applicationItem">Normally online members:</div>
			<div class="applicationItem">Total number of members:</div>
		</div>
		<div class="formContainerRight">
			<div class="applicationItem"><input type="text" id="guildName"></div>
			<div class="applicationItem"><input type="text" id="guildContact"></div>
			<div class="applicationItem">
				<select id="guildOnlineMembers">
					<option value="null">Choose one...</option>
					<option value="5-10">5 to 10</option>
					<option value="10-20">10 to 20</option>
					<option value="20-30">20 to 30</option>
					<option value="30-40">30 to 40</option>
					<option value="40-50">40 to 50</option>
					<option value="50+">More than 50</option>
				</select>
			</div>
			<div class="applicationItem">
				<select id="guildTotalMembers">
					<option value="null">Choose one...</option>
					<option value="10-20">10 to 20</option>
					<option value="20-40">20 to 40</option>
					<option value="40-70">40 to 70</option>
					<option value="70-100">70 to 100</option>
					<option value="100-150">100 to 150</option>
					<option value="150+">More than 150</option>
				</select>
			</div>
		</div>
		<div style="margin-left:35%; text-align:left;">
			<div class="applicationItem">Brief description of your guild:</div>
			<div class="applicationItem">
				<textarea id="guildDescription" class="textbox"></textarea>
			</div>
		</div>
		<div style="margin-left:35%; text-align:left;">
			<div class="applicationItem">Explain briefly why do you want to join the Alliance?:</div>
			<div class="applicationItem">
				<textarea id="guildJoinReason" class="textbox"></textarea>
			</div>
		</div>
		<div style="margin-top:20px;">
			<div class="button" style="width:75px; margin:auto;">Submit</div>
		</div>
	</div>
</div>
</body>
</html>