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
        case "minecraft.steelgamers.es":
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
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_LOGIN)) { ?>
		<div class="rightItem">
		    <?php if (isset($loggedIn) && $loggedIn) { ?>
			<div class="profileWrapper">
				<div class="avatarWrapper">
					<img src="<?php echo GenerateGravatarUrl($user->GetEmail(), 150); ?>">
				</div>
				<div>
    				<div>Conectado como: <b><?php echo $user->GetUsername(); ?></b></div>
    				<?php if (!isset($isControlPanel) || !$isControlPanel) { ?>
    				<a class="plainLink" href="/controlpanel.php"><div class="button">Panel de control</div></a>
    				<?php } elseif ($siteName != "main") { ?>
    				<a class="plainLink" href="http://steelgamers.es/controlpanel.php"><div class="button">Panel de control principal</div></a>
    				<?php } ?>
    				<a class="plainLink" href="http://steelgamers.es/logout.php?redirect=<?php echo $siteName; ?>"><div class="button">Desconectarse</div></a>
				</div>
			</div>
		    <?php } else { ?>
			<form class="loginForm" action="http://steelgamers.es/login.php?redirect=<?php echo $siteName; ?>" method="post">
				<div class="formItem">Usuario</div>
				<div class="formItem"><input type="text" name="username"></div>
				<div class="formItem">Contrase&ntilde;a</div>
				<div class="formItem"><input type="password" name="password"></div>
				<div class="formItem"><input class="button" type="submit" value="Conectarse"></div>
				<div class="formItem">o <a href="http://steelgamers.es/register.php">crear una cuenta</a></div>
			</form>
		    <?php } ?>
		</div>
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_TS3)) { ?>
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
				<div class="serverStatusLabel">wowserver.steelgamers.es</div>
			</div>
		</div>
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_MITRACRAFT)) { ?>
		<div class="rightItem">
			<div class="serverStatusContainer">
    			<div><h3>Servidor Mitracraft</h3></div>
                <div id="mitracraftServerStatusLabel" class="serverStatus unknown">Comprobando...</div>
                <div id="mitracraftServerGamersOnlineLabel" class="serverStatusLabel">Gamers conectados: -/-</div>
				<div class="serverStatusLabel">mitracraft.es</div>
			</div>
		</div>
		<?php } if ($_Layout->GetLayoutOption(LAYOUT_SHOW_ARMA)) { ?>
		<div class="rightItem">
			<div class="serverStatusContainer">
    			<div><h3>Servidor ArmA 2 Wasteland</h3></div>
                <div id="arma2ServerStatusLabel" class="serverStatus unknown">Comprobando...</div>
                <div id="arma2ServerGamersOnlineLabel" class="serverStatusLabel">Gamers conectados: -/-</div>
                <div id="arma2ServerMapLabel" class="serverStatusLabel">Mapa: -</div>
				<div class="serverStatusLabel">arma2server.steelgamers.es:2302</div>
				<?php echo $_SERVER['SERVER_NAME']; ?>
			</div>
		</div>
		<?php
		} if ($_Layout->GetLayoutOption(LAYOUT_SHOW_TWITTER)) { ?>
		<div style="margin-top:10px;"><a class="twitter-timeline" href="https://twitter.com/SteelGamersSGC" <?php  echo $isDark ? 'data-theme="dark"' : '' ?> data-widget-id="326778925160202241">Tweets por @SteelGamersSGC</a>
		<?php } ?>
	</div>
</div>