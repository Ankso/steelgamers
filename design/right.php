<?php if (!isset($_Layout)) exit(); ?>
<?php 
    // Set domain specific vars
    $isDark = true;
    $siteName = "main";
    switch ($_SERVER['SERVER_NAME'])
    {
        case "steelgamers.es":
            break;
        case "arma2.steelgamers.es":
            $isDark = false;
            $siteName = "arma2";
            break;
        case "battlefield.steelgamers.es":
            $siteName = "battlefield";
            break;
        case "dayz.steelgamers.es":
            $siteName = "dayz";
            break;
        case "dota2.steelgamers.es":
            $siteName = "dota2";
            break;
        case "mitracraft.steelgamers.es":
            $isDark = false;
            $siteName = "minecraft";
            break;
        case "warthunder.steelgamers.es":
            $siteName = "warthunder";
            break;
        case "worldoftanks.steelgamers.es":
            $siteName = "worldoftanks";
            break;
        case "wow.steelgamers.es":
            $siteName = "wow";
            $isDark = true;
            break;
    }
?>
<div class="rightWrapper">
	<div class="rightContainer">
		<?php if ($_Layout->GetLayoutOption(LAYOUT_SHOW_RECOVER_PASSWORD)) { ?>
		<div class="rightItem">
			<form class="recoverForm <?php echo (isset($error) && $error === ERROR_LOGIN_PASSWORD) ? "recoverFormResalted" : ""; ?>" action="recoverpassword.php" method="post">
				<div class="formItem">&iquest;Has olvidado tu contrase&ntilde;a? Introduce tu correo electr&oacute;nico para recuperarla:</div>
				<div class="formItem"><input type="text" name="email"></div>
				<div class="formItem"><input class="button" type="submit" value="Enviar"></div>
			</form>
		</div>
		<?php
		}
		if ($_Layout->GetLayoutOption(LAYOUT_SHOW_LOGIN))
		{
	        if (isset($loggedIn) && $loggedIn)
	        {
	    ?>
		<div class="rightItem<?php echo $user->IsPremium() ? " premiumRightItem" : ""; ?>">
			<div class="profileWrapper">
				<div class="avatarWrapper">
					<img src="<?php echo GenerateGravatarUrl($user->GetEmail(), 150); ?>">
				</div>
				<div>
    				<div>Conectado como: <b><?php echo $user->GetUsername(); ?></b></div>
    				<?php
    				if ($user->IsPremium())
    				{
    				    $timeLeft = $user->GetPremiumTimeLeft();
    				?>
    				<div style="color:#CFB53B;">Miembro Premium</div>
    				<div style="color:#CFB53B;">(<?php echo ($timeLeft !== PREMIUM_TIME_INFINITE ? intval($timeLeft / 60 / 60 / 24) : "&infin;")?> d&iacute;as restantes)</div>
    				<?php } if (!isset($isControlPanel) || !$isControlPanel) { ?>
    				<a class="plainLink" href="/controlpanel.php"><div class="button">Panel de control</div></a>
    				<?php } elseif ($siteName != "main") { ?>
    				<a class="plainLink" href="http://steelgamers.es/controlpanel.php"><div class="button">Panel de control principal</div></a>
    				<?php } ?>
    				<a class="plainLink" href="http://steelgamers.es/logout.php?redirect=<?php echo $siteName; ?>"><div class="button">Desconectarse</div></a>
				</div>
			</div>
		</div>
	    <?php } else { ?>
	    <div class="rightItem">
			<form class="loginForm" action="login.php?redirect=<?php echo $siteName; ?>" method="post">
				<div class="formItem">Usuario</div>
				<div class="formItem"><input type="text" name="username"></div>
				<div class="formItem">Contrase&ntilde;a</div>
				<div class="formItem"><input type="password" name="password"></div>
				<div class="formItem"><input class="button" type="submit" value="Conectarse"></div>
				<div class="formItem">o <a href="http://steelgamers.es/register.php">crear una cuenta</a></div>
			</form>
		</div>
		<?php
	        } 
		}
		if ($_Layout->GetLayoutOption(LAYOUT_SHOW_SOCIAL)) { ?>
		<div style="margin-top:10px;">
      		<a class="twitter-timeline" href="https://twitter.com/SteelGamersSGC" <?php  echo $isDark ? 'data-theme="dark"' : '' ?> data-widget-id="326778925160202241">Tweets por @SteelGamersSGC</a>
    	</div>
    	<div style="margin-top:10px;">
			<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
    		<div class="g-page" data-href="https://plus.google.com/104321172907932626147/104321172907932626147" <?php  echo $isDark ? 'data-theme="dark"' : '' ?> data-width="250"></div>
		</div>
		<?php
		} if ($_Layout->GetLayoutOption(LAYOUT_SHOW_TS3)) {
		?>
		<div class="rightItem">
			<div class="serverStatusContainer">
    			<div><h3>Servidor TeamSpeak 3</h3></div>
                <div id="ts3ServerStatusLabel" class="serverStatus unknown">Comprobando...</div>
				<div id="ts3ServerGamersOnlineLabel" class="serverStatusLabel">Gamers conectados: -/-</div>
				<div class="serverStatusLabel">steelgamers.es:9987</div>
			</div>
		</div>
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_WOW_TBC)) { ?>
		<div class="rightItem">
			<div class="serverStatusContainer">
    			<div><h3>Servidor World of Warcraft:<br>The Burning Crusade</h3></div>
                <div id="wowServerStatusLabel" class="serverStatus unknown">Comprobando...</div>
                <div id="wowServerGamersOnlineLabel" class="serverStatusLabel">Gamers conectados: -/-</div>
				<div class="serverStatusLabel"><?php echo $TBCSERVER_INFO['host']; ?></div>
			</div>
		</div>
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_MINECRAFT)) { ?>
		<div class="rightItem">
			<div class="serverStatusContainer">
    			<div><h3>Servidor Mitracraft</h3></div>
                <div id="minecraftServerStatusLabel" class="serverStatus unknown">Comprobando...</div>
                <div id="minecraftServerGamersOnlineLabel" class="serverStatusLabel">Gamers conectados: -/-</div>
				<div class="serverStatusLabel"><?php echo $MITRACRAFT_INFO['host']; ?></div>
			</div>
		</div>
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_ARMA)) { ?>
		<div class="rightItem">
			<div class="serverStatusContainer">
    			<div><h3>Servidores ArmA 2/3</h3></div>
                <div id="arma2ServerStatusLabelWasteland" class="serverStatus unknown">Servidor #1</div>
                <div id="arma2ServerGamersOnlineLabelWasteland" class="serverStatusLabel">Gamers conectados: -/-</div>
                <div id="arma2ServerMapLabelWasteland" class="serverStatusLabel">Mapa: -</div>
				<div class="serverStatusLabel">arma2server.steelgamers.es:2302</div>
				<div id="arma2ServerStatusLabelWarfare" class="serverStatus unknown" style="margin-top:10px;">Servidor #2</div>
                <div id="arma2ServerGamersOnlineLabelWarfare" class="serverStatusLabel">Gamers conectados: -/-</div>
                <div id="arma2ServerMapLabelWarfare" class="serverStatusLabel">Mapa: -</div>
				<div class="serverStatusLabel">arma2server.steelgamers.es:2332</div>
				<?php echo $_SERVER['SERVER_NAME']; ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>