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
			$('div#applicationSendButton').click(function(event) {
				// Check that all fields are filled up
				var error = '';
				var guildName = $('input#guildName').val();
				var guildContact = $('input#guildContact').val();
				var guildWebpage = $('input#guildWebpage').val();
				var guildOnlineMembers = $('select#guildOnlineMembers').val();
				var guildTotalMembers = $('select#guildTotalMembers').val();
				var guildDescription = $('textarea#guildDescription').val();
				var guildJoinReason = $('textarea#guildJoinReason').val();
				if (guildName == '')
					error = 'A guild Name is mandatory';
				else if (guildContact == '')
					error = 'At least one guild contact is mandatory';
				else if (guildOnlineMembers == 'null')
					error = 'You must choose one of the options for the average online members';
				else if (guildTotalMembers == 'null')
					error = 'You must choose one of the options for the total members count';
				else if (guildDescription == '')
					error = 'Please, write a brief description about your guild';
				else if (guildJoinReason == '')
					error = 'Please, specify briefly why you want to join the Alliance';

				if (error == '')
					SendApplicationRequest(guildName, guildContact, guildWebpage, guildOnlineMembers, guildTotalMembers, guildDescription, guildJoinReason);
				else
				{
				    $('div#applicationStatus').css('color', '#CD0000');
					$('div#applicationStatus').text(error);
				}
				
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

		function SendApplicationRequest(name, contacts, webpage, onlineMembers, totalMembers, description, joinReason)
		{ 
			$('div#applicationStatus').css('color', '#B9B9B9');
			$('div#applicationStatus').text('Sending...');
			var data = {
				name: name,
				contacts: contacts,
				webpage: webpage,
				onlineMembers: onlineMembers,
				totalMembers: totalMembers,
				description: description,
				joinReason: joinReason
			};

			$.ajax('application.php', {
				type: "POST",
				data: data,
				success: function(result) {
					if (result == 'SUCCESS')
					{
				    	$('div#applicationStatus').css('color', '#336600');
						$('div#applicationStatus').text('Thanks for sending your application! We will contact your diplomats in-game soon.');
					}
					else
					{
					    $('div#applicationStatus').css('color', '#CD0000');
						$('div#applicationStatus').text('An error has occurred on the server, the applications could be temporarily disabled, please try again later.');
					}
				},
				error: function() {
				    $('div#applicationStatus').css('color', '#CD0000');
					$('div#applicationStatus').text('An error has occurred on the server, the applications could be temporarily disabled, please try again later.');
				},
			});
		}

		function SwitchFormStatus(enabled)
		{
		    $('input#guildName').val();
			$('input#guildContact').val();
			$('input#guildWebpage').val();
			$('select#guildOnlineMembers').val();
			$('select#guildTotalMembers').val();
			$('textarea#guildDescription').val();
			$('textarea#guildJoinReason').val();
		}
	</script>
</head>
<body>
<div id="homePage" class="centralDiv" style="display:inherit; background:#000000;">
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
<div id="learnMoreContent" class="centralDiv">
	<div id="homePageLink" class="topContent">
		<div class="topRoute"><b>Main page</b></div>
	</div>
	<div class="mediumContent" style="height:90%;">
		<div style="margin-top:5%;">
			<h2>What is it?</h2>
			<div style="width:60%; margin-left:20%; margin-bottom:75px;">
				Steel Gamers Alliance (SGA) was born as an international project to reunite small to medium size guilds under one banner and gain power against the well known "Zerg guilds", based on a large numbers of players, on the Archeage server <b>Melisara</b>.
			</div>
			<h2>How can we face Zerg guilds or even Zerg alliances?</h2>
			<div style="width:60%; margin-left:20%; margin-bottom:75px;">
				How can we face Zerg guilds or even Zerg alliances?
				By being organized. The advantage of small and medium sized guilds is the high level of internal organization when doing PvP. The objective of SGA is to coordinate those small groups and make them a larger, more lethal force. Five galleons, each one with its well trained crew, syncronized, can defeat ten driven by the members of a much more chaotic zerg guild.
			</div>
			<h2>Are there any requirements to join?</h2>
			<div style="width:60%; margin-left:20%; margin-bottom:75px;">
				Yes. Your guild must use TeamSpeak 3 (or any other voice communication software), at least for PvP. High activity members are also recommended. You must read and agree with the <a href="">Alliance rules</a>,  and your diplomat must be able to communicate in english.
			</div>
		</div>
	</div>
	<div class="bottomContent">
		<div id="applyToJoinLink" class="bottomRoute"><b>Apply to join!</b></div>
	</div>
</div>
<div id="applyToJoinContent" class="centralDiv">
	<div id="learnMoreLinkBottom" class="topContent">
		<div class="topRoute"><b>Learn More</b></div>
	</div>
	<div style="height:10%; float:left; width:100%;"></div>
		<div class="formContainerLeft">
			<div class="applicationItem">Guild name:</div>
			<div class="applicationItem">Diplomatic in-game contact(s):</div>
			<div class="applicationItem">Guild webpage/forums (if any):</div>
			<div class="applicationItem">Normally online members:</div>
			<div class="applicationItem">Total number of members:</div>
		</div>
		<div class="formContainerRight">
			<div class="applicationItem"><input type="text" id="guildName"></div>
			<div class="applicationItem"><input type="text" id="guildContact"></div>
			<div class="applicationItem"><input type="text" id="guildWebpage"></div>
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
		<div style="margin-left:30%; text-align:left;">
			<div class="applicationItemBig">Brief description of your guild:</div>
			<div class="applicationItemBig">
				<textarea id="guildDescription" class="textbox"></textarea>
			</div>
		</div>
		<div style="margin-left:30%; text-align:left;">
			<div class="applicationItemBig">Explain briefly why do you want to join the Alliance:</div>
			<div class="applicationItemBig">
				<textarea id="guildJoinReason" class="textbox"></textarea>
			</div>
		</div>
		<div style="margin-top:20px;">
			<div id="applicationSendButton" class="button" style="width:75px; margin:auto;">Submit</div>
		</div>
		<div id="applicationStatus" style="margin-top:20px;">
			
		</div>
	</div>
</div>
</body>
</html>