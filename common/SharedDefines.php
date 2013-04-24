<?php
/*
 * Common definitions for the entire application
 * @author Ankso
 */
// The config file is included here because, really, it's part of this file.
// But it's more user friendly to have a separate config file with common changing variables.
require($_SERVER['DOCUMENT_ROOT'] . "/../config/config.php");

/**
 * General
 */
define("USER_DOESNT_EXISTS", -1);
define("PASSWORD_MIN_LENGHT", 7);
define("USERNAME_MIN_LENGHT", 4);

/**
 * For the registration system
 */
define("ERROR_NONE", 0);
define("ERROR_CRITICAL", -1);
define("ERROR_UNFILLED", -2);
define("ERROR_INVALID", -3);
define("ERROR_LOGIN_PASSWORD", -4);
define("ERROR_LOGIN_USERNAME", -5);
define("ERROR_LOGIN_VERIFICATION", -6);
define("ERROR_NOT_ALLOWED", -7);
define("ERROR_NOT_FOUND", -8);

/**
 * User ranks
 */
define("USER_RANK_NONE", 0);
define("USER_RANK_EMAIL_NOT_VERIFIED", 1);
define("USER_RANK_MEMBER", 2);
define("USER_RANK_PREMIUM_MEMBER", 3);
define("USER_RANK_MODERATOR", 4);
define("USER_RANK_COMMUNITY_MANAGER", 5);
define("USER_RANK_ADMINISTRATOR", 6);
define("USER_RANK_SUPERADMIN", 7);
define("USER_RANKS_COUNT", 8);
/**
 * Site supported games (each game has his own webpage)
 */
define("GAME_NONE", -1);
define("GAME_OVERALL", 0);
define("GAME_ARMA2", 1);
define("GAME_BATTLEFIELD_3", 2);
define("GAME_DAYZ", 3);
define("GAME_DOTA_2", 4);
define("GAME_MINECRAFT", 5);
define("GAME_WAR_THUNDER", 6);
define("GAME_WORLD_OF_TANKS", 7);
define("GAME_WORLD_OF_WARCRAFT_TBC", 8);
define("GAMES_COUNT", 8); // GAME_OVERALL is not really a game
/**
 * Array with the game names used in the webpage
 */
$GAME_NAMES = array(
    0 => "Globales",
    1 => "Arma 2",
    2 => "Battlefield 3",
    3 => "DayZ",
    4 => "DOTA 2",
    5 => "Minecraft",
    6 => "War Thunder",
    7 => "World of Tanks",
    8 => "World of Warcraft: The Burning Crusade",
);
/**
 * Different design options when printing the page
 */
define("LAYOUT_SHOW_LOGIN", 0);
define("LAYOUT_SHOW_TS3", 1);
define("LAYOUT_SHOW_WOW_TBC", 2);
define("LAYOUT_SHOW_MITRACRAFT", 3);
define("LAYOUT_SHOW_ARMA", 4);
define("LAYOUT_SHOW_TWITTER", 5);
define("LAYOUT_SHOW_RECOVER_PASSWORD", 6);
define("LAYOUT_OPTIONS_COUNT", 7);
?>